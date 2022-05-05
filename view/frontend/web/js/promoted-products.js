/**
 * Copyright © Topsort, Inc. All rights reserved.
 */
define([
    'jquery', 'loader'
], function ($, loader) {
    'use strict';

    $.widget('topsort.promotedProducts', {

        loaded: false,

        bannerHtml: '',

        options: {
            loaderIcon: '',
            loadPromotionsUrl: '',
            loaderMaskContainer: 'div.products',
            productsContainer: 'ol.products',
            productItemSelector: '.product-item',
            toolbarSelector: 'div.toolbar-products',
            productsLimit: 5 // minimum amount of products required for promotions to be displayed
        },

        getProductsCount: function() {
            try {
                return $(this.options.productsContainer).find(this.options.productItemSelector);
            } catch (e) {
                if (window.hasOwnProperty('console') && window.console.hasOwnProperty('error')) {
                    console.error(e);
                }
            }
            return 0;
        },

        getBannerIdsOnPage: function() {
            let ids = [];
            if (window.hasOwnProperty('topsortBanners')) {
                jQuery.each(window.topsortBanners, function(bannerId, bannerConfig) {
                    if (bannerConfig.placement === 'Category-page' || bannerConfig.placement === 'Search-page') {
                        ids.push(bannerId);
                    }
                })
            }
            return ids.join(',');
        },

        isReady: function() {
            return true;
        },

        isLoaded: function() {
            return this.loaded;
        },

        renderPromotedProducts: function(responseData) {
            let productsContainerEl = $(this.options.hasOwnProperty('productsContainer') ? this.options.productsContainer : this.element);

            if (productsContainerEl.length === 0 || !responseData.hasOwnProperty('html') || responseData.html === '') {
                // there is no products section on this page (or no html to insert), do nothing
                return;
            }
            productsContainerEl.prepend(responseData.html);
            productsContainerEl.trigger('contentUpdated');
            $('[data-role=tocart-form], .form.map.checkout').catalogAddToCart()
        },

        saveBannerHtml: function(responseData) {
            this.bannerHtml = responseData.hasOwnProperty('bannerHtml') ? responseData.bannerHtml : '';
        },

        getBannerHtml: function() {
            return this.bannerHtml;
        },

        // check current page number before loading starts
        getCurrentPageNumber: function() {
            let page = 0;
            let config = this.options;
            try {
                if (config.hasOwnProperty('toolbarSelector')) {
                    let toolbar = $(config.toolbarSelector);
                    if (toolbar.length !== 0) {
                        // there is no getCurrentPage method in Magento 2.3.5
                        try {
                            page = toolbar.productListToolbarForm("getCurrentPage");
                        } catch (e) {
                            if (window.hasOwnProperty('console') && window.console.hasOwnProperty('log')) {
                                console.log(e);
                                console.log('Method getCurrentPage is not available in some versions of Magento. This is not essential and this notice might be ignored.');
                            }
                            page = 1; // assume that we are on the first page
                        }
                    }
                }
            } catch (e) {
                if (window.hasOwnProperty('console') && window.console.hasOwnProperty('error')) {
                    console.error(e);
                }
            }
            return page;
        },

        /** @inheritdoc */
        _create: function () {
            this.loadPromotions();
        },

        loadPromotions: function() {
            let element = this.element;
            let config = this.options;
            let me = this;

            let loaderEl = $(config.hasOwnProperty('loaderMaskContainer') ? config.loaderMaskContainer : element);
            let productsContainerEl = $(config.hasOwnProperty('productsContainer') ? config.productsContainer : element);

            if (productsContainerEl.length === 0) {
                // there is no products section on this page, do nothing
                return;
            }

            // initialize loading state
            loaderEl.loader({
                icon: config.loaderIcon
            });

            loaderEl.trigger('processStart');
            let finished = false;

            // let other widgets initialize first and do the loading of promotions
            setTimeout(function () {

                let bannerIds = me.getBannerIdsOnPage();
                let productsCount = me.getProductsCount();
                let loadBanners = bannerIds !== '';
                let currentPage = me.getCurrentPageNumber();
                let loadPromotedProducts = productsCount >= me.options.productsLimit || currentPage > 1;

                // remove the loading state from the page and continue sending the ajax request
                // only if banner ads have to be loaded
                if (!loadPromotedProducts) {
                    loaderEl.trigger('processStop');
                } else {
                    // give 5 seconds to load the promotions, else - remove the loading mask
                    setTimeout(function() {
                        if (!finished) {
                            loaderEl.trigger('processStop');
                        }
                    }, 5000);
                }

                if (loadPromotedProducts || loadBanners) {
                    $.ajax({
                        url: config.loadPromotionsUrl,
                        type: 'GET',
                        async: true,
                        data: {
                            banners: bannerIds,
                            load_promoted_products: loadPromotedProducts ? 1 : 0
                        },
                        dataType: 'json',
                    }).done(function (data) {
                        if (data.error) {
                            if (window.hasOwnProperty('console') && window.console.hasOwnProperty('error')) {
                                console.error(data.error);
                            }
                        } else {
                            me.saveBannerHtml(data);
                            me.renderPromotedProducts(data);
                        }
                        setTimeout(function() {
                            loaderEl.trigger('processStop');
                        }, 500);
                        finished = true;
                        me.loaded = true;
                    }).fail(function (jqXHR, textStatus) {
                        if (window.hasOwnProperty('console') && window.console.hasOwnProperty('log')) {
                            console.log(textStatus);
                        }
                        loaderEl.trigger('processStop');
                        finished = true;
                        me.loaded = true;
                    });
                }
            }, 1);
        }
    });

    return $.topsort.promotedProducts;
});
