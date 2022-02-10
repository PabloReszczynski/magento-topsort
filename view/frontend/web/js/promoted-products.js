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
                        let page = toolbar.productListToolbarForm("getCurrentPage");
                        if (page > 1) {
                            // remove the loading state from the pag and continue sending the ajax request in order
                            // to track impressions
                            loaderEl.trigger('processStop');
                            // finished = true;
                            // return ;
                        }
                    }
                }
            } catch (e) {
                console.error(e);
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
