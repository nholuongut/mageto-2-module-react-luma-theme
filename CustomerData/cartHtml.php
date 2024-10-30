<?php

$template = '<button type="button" id="btn-minicart-close" class="action close" data-action="close"' .
'data-bind="attr: { title: t(\'Close\') }" title="Close">' .
'<span data-bind="i18n: \'Close\'">Close</span>' .
'</button><h4> Empty Cart. <br> Add something to cart to be happy! </h4>';

if ($return['summary_count'] > 0){

$template =<<<HTML
<div id="minicart-content" data-bind="scope: 'minicart_content'">
    <div class="block-title">
        <strong>
            <span class="text" data-bind="i18n: 'My Cart'">My Cart</span>
            <span class="qty" data-bind="css: { empty: !!getCartParam('summary_count') == false },
                       attr: { title: t('Items in Cart') }, text: getCartParam('summary_count')"
                title="Items in Cart">1</span>
        </strong>
    </div>
    <div class="block-content">
        <button type="button" id="btn-minicart-close" class="action close" data-action="close"
            data-bind="attr: { title: t('Close') }" title="Close">
            <span data-bind="i18n: 'Close'">Close</span>
        </button>
        <div class="items-total">
            <span class="count" data-bind="text: getCartParam('summary_count')">{$return['summary_count']}</span>
            <span data-bind="i18n: 'Item in Cart'">Item in Cart</span>
        </div>

        <div class="subtotal">
            <span class="label">
                <span>Cart Subtotal</span>
            </span>

            <div class="amount price-container">
                <span class="price-wrapper" data-bind="html: cart().subtotal_excl_tax"><span class="price">
                        {$return['subtotal']} </span></span>
            </div>
        </div>
        <div class="actions">
            <div class="primary">
                <button id="top-cart-btn-checkout" type="button" class="action primary checkout" onclick="location.href='/checkout/';"
                    title="Proceed to Checkout">Proceed to Checkout</button>
                <div data-bind="html: getCartParamUnsanitizedHtml('extra_actions')"></div>
            </div>
        </div>
        <strong class="subtitle" data-bind="i18n: 'Recently added item(s)'">Recently added item(s)</strong>
        <div data-action="scroll" class="minicart-items-wrapper" style="height: 109px;">
            <ol id="mini-cart" class="minicart-items" data-bind="foreach: { data: getCartItems(), as: 'item' }">
HTML;

                foreach($return['items'] as $item) {

                $template .= 
                '<li class="item product product-item odd last" data-role="product-item">' .
                    '<div class="product">';

                        $template .= 
                        '<a tabindex="-1" class="product-item-photo" href="'. $item['product_url'] . '"
                            title="'.$item['product_name'].'">'.
                            '<span class="product-image-container" data-bind="style: {width: width/2 + \'px\'}"
                                style="width: 75px;">'.
                                '<span class="product-image-wrapper"
                                    data-bind="style: {\'padding-bottom\': height/width*100 + \'%\'}"
                                    style="padding-bottom: 100%;">'.
                                    '<img class="product-image-photo"
                                        data-bind="attr: {src: src, alt: alt}, style: {width: \'auto\', height: \'auto\'}"
                                        src="'.$item['product_image']['src'].'" alt="Item in catr image"
                                        style="width: auto; height: auto;">'.
                                    '</span>'.
                                '</span>'.
                        '</a>'.
                        '<div class="product-item-details">'.
                            '<strong class="product-item-name">'.
                                '<a data-bind="attr: {href: product_url}, html: product_name"
                                    href="'.$item['product_url'].'">' .$item['product_name'] . '</a>'.
                                '</strong>'.
                            '<div class="product-item-pricing">'.
                                '<div class="price-container">'.
                                    '<span class="price-wrapper" data-bind="html: price">'.
                                        '<span class="price-excluding-tax" data-label="Excl. Tax">'.
                                            '<span class="minicart-price">'.
                                                '<span class="price">'.$item['product_price'].'</span> </span>'.
                                            '</span>'.
                                        '</span>'.
                                '</div>'.

                                '<div class="details-qty qty">'.
                                    '<label class="label" data-bind="i18n: \'Qty\'" for="cart-item-5-qty">Qty</label>'.
                                    '<input type="number" size="4" class="item-qty cart-item-qty" id="cart-item-5-qty"
                                        value="'.$item['qty'].'" data-cart-item="5" data-item-qty="1"
                                        data-cart-item-id="24-MB02">'.
                                '</div>'.
                            '</div>'.
                            '<div class="product actions">'.
                                '<div class="primary">'.
                                    '<a data-bind="attr: {href: configure_url, title: t(\'Edit item\')}"
                                        class="action edit" href="/checkout/cart/" title="Edit item">'.
                                        '<span data-bind="i18n: \'Edit\'">Edit</span>'.
                                        '</a>'.
                                '</div>'.
                            '</div>'.
                        '</div>'.
                    '</div>'.
                '</li>';
                }

            $template .=      
            '</ol>'.
        '</div>'.
        '<div class="actions">'.
            '<div class="secondary">'.
                '<a class="action viewcart" data-bind="attr: {href: shoppingCartUrl}" href="/checkout/cart/">'.
                    '<span data-bind="i18n:\'View and Edit Cart\'">View and Edit Cart</span>'.
                '</a>'.
            '</div>'.
        //'</div>'.
        '</div>'.
    '</div>'.
'</div>';

    }

    return $template;

