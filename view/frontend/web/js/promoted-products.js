/**
 * Copyright © Topsort, Inc. All rights reserved.
 */
define([
    'jquery', 'loader'
], function ($, loader) {
    return function(config, element) {

        let loaderEl = $(config.hasOwnProperty('loaderMaskContainer') ? config.loaderMaskContainer : element);
        let productsContainerEl = $(config.hasOwnProperty('productsContainer') ? config.productsContainer : element);

        if (productsContainerEl.length === 0) {
            // there is no products section on this page, do nothing
            return;
        }

        loaderEl.loader({
            icon: config.loaderIcon
        });

        loaderEl.trigger('processStart');
        let finished = false;

        // let other widgets initialize first
        setTimeout(function () {

            // check current page number before loading starts
            try {
                if (config.hasOwnProperty('toolbarSelector')) {
                    let toolbar = $(config.toolbarSelector);
                    if (toolbar.length !== 0) {
                        // there is no getCurrentPage method in Magento 2.3.5
                        let page;
                        try {
                            page = toolbar.productListToolbarForm("getCurrentPage");
                        } catch (e) {
                            if (window.hasOwnProperty('console')) {
                                console.log(e);
                                console.log('Method getCurrentPage is not available in some versions of Magento. This is not essential and this notice might be ignored.');
                            }
                            page = 1; // assume that we are on the first page
                        }
                        if (page > 1) {
                            // remove the loading state from the pag and continue sending the ajax request in order
                            // to track impressions
                            loaderEl.trigger('processStop');
                        }
                    }
                }
            } catch (e) {
                if (window.hasOwnProperty('console')) {
                    console.error(e);
                }
            }

            // give 5 seconds to load the promotions, else - remove the loading mask
            setTimeout(function() {
                if (!finished) {
                    loaderEl.trigger('processStop');
                }
            }, 5000);

            function renderPromotedProducts(responseData) {
                productsContainerEl.prepend(responseData.html);
                productsContainerEl.trigger('contentUpdated');
                $('[data-role=tocart-form], .form.map.checkout').catalogAddToCart()
            }

            $.ajax({
                url: config.loadPromotionsUrl,
                type: 'GET',
                async: true,
                data: {},
                dataType: 'json',
            }).done(function (data) {
                if (data.error) {
                    if (window.console && window.console.error) {
                        console.error(data.error);
                    }
                } else {
                    renderPromotedProducts(data);
                }
                setTimeout(function() {
                    loaderEl.trigger('processStop');
                }, 500);
                finished = true;
            }).fail(function (jqXHR, textStatus) {
                if (window.console) {
                    console.log(textStatus);
                }
                loaderEl.trigger('processStop');
                finished = true;
            });
        }, 1);
    };
});
