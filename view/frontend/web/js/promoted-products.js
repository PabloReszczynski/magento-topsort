/**
 * Copyright © Topsort, Inc. All rights reserved.
 */
define([
    'jquery', 'loader'
], function ($, loader) {
    return function(config, element) {

        let loaderEl = $(config.hasOwnProperty('loaderMaskContainer') ? config.loaderMaskContainer : element);
        let productsContainerEl = $(config.hasOwnProperty('productsContainer') ? config.productsContainer : element);

        loaderEl.loader({
            icon: config.loaderIcon// 'http://php73.local.com/test/m2.3.5/pbi-sc-beta-dev/pub/static/version1635426714/frontend/Magento/luma/en_US/images/loader-2.gif'//jQuery('body').data().mageLoader.options.icon
        });

        loaderEl.trigger('processStart');
        let finished = false;

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
    };
});
