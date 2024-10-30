<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ReactLuma\Theme\ViewModel\Product;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Escaper;

/**
 * Product breadcrumbs view model.
 */
class Breadcrumbs extends DataObject implements ArgumentInterface
{
    /**
     * Catalog data.
     *
     * @var Data
     */
    private $catalogData;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Helper\Data                                    $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Framework\Serialize\Serializer\Json|null               $json
     * @param \Magento\Framework\Escaper|null                                 $escaper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Data $catalogData,
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Json $json = null,
        Escaper $escaper = null,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct();
        $this->storeManager = $storeManager;
        $this->catalogData = $catalogData;
        $this->scopeConfig = $scopeConfig;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Returns category URL suffix.
     *
     * @return mixed
     */
    public function getCategoryUrlSuffix()
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks if categories path is used for product URLs.
     *
     * @return bool
     */
    public function isCategoryUsedInProductUrl(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'catalog/seo/product_use_categories',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns product name.
     *
     * @return string
     */
    public function getProductName(): string
    {
        return $this->catalogData->getProduct() !== null
            ? $this->catalogData->getProduct()->getName()
            : '';
    }

    /**
     * Returns breadcrumb json with html escaped names
     *
     * @return string
     */
    public function getJsonConfigurationHtmlEscaped() : string
    {
        $lastCategory = $this->getLastCategory();
        $lastCategoryName = $lastCategory->getName(); 
        $lastCategoryLink = $lastCategory->getUrl(); 
        //$baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $product = $this->catalogData->getProduct();
        return json_encode(
            [
                'breadcrumbs' => [
                    'categoryUrlSuffix' => $this->escaper->escapeHtml($this->getCategoryUrlSuffix()),
                    'useCategoryPathInUrl' => (int)$this->isCategoryUsedInProductUrl(),
                    'product' => $this->escaper->escapeHtml($this->getProductName()),
                    'categories' => $this->getCatByPath($lastCategory->getPath()),
                    'lastCategoryName' => $lastCategoryName,
                    'lastCategoryUrl' => $lastCategoryLink,
                    //'baseUrl' => $baseUrl,
                ]
            ],
            JSON_HEX_TAG
        );
    }

    /**
     * Returns breadcrumb json.
     *
     * @return string
     * @deprecated 103.0.0 in favor of new method with name {suffix}Html{postfix}()
     */
    public function getJsonConfiguration()
    {
        return $this->getJsonConfigurationHtmlEscaped();
    }

    public function getExcludeBreadcrumbs($store = null)
    {
        $excludeCatIds = $this->scopeConfig->getValue(
            'catalog/breadcrumbs/exclude_cats',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        $excludeCatIds = explode(',', $excludeCatIds);
        array_walk($excludeCatIds, 'trim');

        return $excludeCatIds;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastCategory()
    {
        $product = $this->catalogData->getProduct();
        if (!$product) {
            return [];
        }

        $categoryIds = $product->getCategoryIds();
        $excludeCatIds = [];//  $this->getExcludeBreadcrumbs();
        $collection = $this->getCategoriesByIds($categoryIds);
        $filterCategories = [];
        $excludeCats = [];
        foreach ($collection as $category) {
            //$position = $category->getAllCollectionsPosition();
            if (in_array($category->getId(), $excludeCatIds) || $category->getDisplayMode() === 'INDIVIDUAL_COLLECTION_PAGE')
            {
                $excludeCats[(int)$category->getPosition()] =
                    $category;
                
                continue;
            }
            $filterCategories[(int)$category->getPosition()] = 
                $category;
            
        }

        if (sizeof($filterCategories) > 0) {
            ksort($filterCategories);
            //return array_pop($filterCategories);
            return current($filterCategories);
        }

        // if has no match than use from exclude categories
        if (sizeof($excludeCats) > 0) {
            ksort($excludeCats);

            return current($excludeCats);
        }

        return [];
    }

    /**
     * @param $catIds
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesByIds($catIds)
    {
        $catIds = array_diff($catIds, [1,2]);
        $collection =   $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in' => $catIds]);
        $collection->addFieldToFilter('is_active', ['eq' => 1]);
        $collection->setOrder("position", 'ASC')->setPageSize(1)->setCurPage(1);

        return $collection;
    }

    public function getCatByPath($path){
        $pathArray =  array_diff(explode('/',$path), [1,2]);
        array_pop($pathArray);
        $collection =   $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in' => $pathArray]);
        $collection->addFieldToFilter('is_active', ['eq' => 1]);

        $return = [];
        foreach($collection as $cat) {
            $return[$cat->getId()]['name'] = $cat->getName();
            $return[$cat->getId()]['url'] = $cat->getURL();
            $return[$cat->getId()]['id'] = $cat->getId();
        }
        $end = microtime(true);

        return  $return;
    }

}
