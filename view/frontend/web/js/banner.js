/**
 * Copyright © Topsort, Inc. All rights reserved.
 */
define([
    'jquery', 'mage/url'
], function ($, urlBuilder) {
    'use strict';

    $.widget('topsort.banner', {

        options: {
            placement: 'Home-page',
            promotedProductsComponentSelector: '#promoted-products-component'
        },

        getBannerData: function() {
            return 'banner.banner-data-function-called';
        },

        /**
         * @returns Object|false
         */
        getPromotedProductsComponent: function ()
        {
            let el = $(this.options.promotedProductsComponentSelector);
            return el.length > 0 ? el : false;
        },

        isPromotedProductsComponentInitialized() {
            let el = this.getPromotedProductsComponent();
            try {
                return el && el.promotedProducts("isReady") === true;
            } catch (e) {
                return false;
            }
        },

        isPromotedProductsLoaded: function() {
            let el = this.getPromotedProductsComponent();
            try {
                return el && el.promotedProducts("isLoaded") === true;
            } catch (e) {
                return false;
            }
        },

        getBannerHtmlFromPromotedProducts: function() {
            let el = this.getPromotedProductsComponent();
            let bannerId = this.options.bannerId;
            try {
                let htmlData = el ? el.promotedProducts("getBannerHtml") : {};
                return (htmlData && htmlData.hasOwnProperty(bannerId) && htmlData[bannerId]) ? htmlData[bannerId] : '';
            } catch (e) {
                return '';
            }
        },

        waitForPromotedProductsComponentInitialization: function(callbackFn, errorCallbackFn, maxSeconds) {
            let frequency = 100; // attempt every 100 milliseconds
            let countDown = (maxSeconds*1000) / frequency;
            let me = this;

            function attemptFn() {

                if (me.isPromotedProductsComponentInitialized()) {
                    // call success function
                    callbackFn();
                    // stop waiting
                    return;
                }

                countDown--;
                if (countDown > 1) {
                    setTimeout(attemptFn, frequency);
                } else {
                    errorCallbackFn("Component not initialized after " + maxSeconds + " seconds.");
                }
            }

            setTimeout(attemptFn, frequency);
        },

        waitForPromotedProductsLoading: function(callbackFn, errorCallbackFn, maxSeconds) {
            let frequency = 100; // attempt every 100 milliseconds
            let countDown = (maxSeconds*1000) / frequency;
            let me = this;

            function attemptFn() {

                if (me.isPromotedProductsLoaded()) {
                    // call success function
                    callbackFn();
                    // stop waiting
                    return;
                }

                countDown--;
                if (countDown > 1) {
                    setTimeout(attemptFn, frequency);
                } else {
                    errorCallbackFn("Promotions are not loaded after " + maxSeconds + " seconds.");
                }
            }

            setTimeout(attemptFn, frequency);
        },

        handleError: function (errorMessage) {
            if (window.hasOwnProperty('console') && window.console.hasOwnProperty('log')) {
                console.error(errorMessage);
            }
            this.removeBannerCls('topsort-banner-loading');
            this.addBannerCls('topsort-banner-loaded');
            this.addBannerCls('topsort-empty-banner');
        },

        renderBanner: function(bannerHtml) {
            this.removeBannerCls('topsort-banner-loading');
            this.addBannerCls('topsort-banner-loaded');
            let bannerContainerEl = $(this.options.hasOwnProperty('bannerContainer') ? this.options.bannerContainer : this.element);

            if (bannerContainerEl.length === 0) {
                // there is no products section on this page, do nothing
                return;
            }

            bannerContainerEl.html(bannerHtml);
            bannerContainerEl.trigger('contentUpdated');

            if (!bannerHtml) {
                this.addBannerCls('topsort-empty-banner');
            }
        },

        removeBannerCls: function(cls) {
            let bannerContainerEl = $(this.options.hasOwnProperty('bannerContainer') ? this.options.bannerContainer : this.element);

            if (bannerContainerEl.length !== 0) {
                bannerContainerEl.removeClass(cls);
            }
        },

        addBannerCls: function(cls) {
            let bannerContainerEl = $(this.options.hasOwnProperty('bannerContainer') ? this.options.bannerContainer : this.element);

            if (bannerContainerEl.length !== 0) {
                bannerContainerEl.addClass(cls);
            }
        },

        /** @inheritdoc */
        _create: function () {

            let config = this.options;
            let me = this;

            me.addBannerCls('topsort-banner-loading');

            // let other widgets initialize first
            setTimeout(function () {

                if (config.placement === 'Category-page') {
                    // Get banner HTML from promoted products request
                    me.waitForPromotedProductsComponentInitialization(
                        function () {
                            me.waitForPromotedProductsLoading(
                                function () {
                                    me.renderBanner(me.getBannerHtmlFromPromotedProducts());
                                },
                                me.handleError,
                                30
                            );
                        },
                        me.handleError,
                        5
                    );

                } else {
                    // load banner for Home-page
                    $.ajax({
                        url:  urlBuilder.build("topsort/banner/content/id/" + config.bannerId),
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
                            me.renderBanner(data.html);
                        }
                    }).fail(function (jqXHR, textStatus) {
                        if (window.hasOwnProperty('console') && window.console.hasOwnProperty('log')) {
                            console.log(textStatus);
                        }
                    });
                }
            }, 1);
        }
    });

    return $.topsort.banner;
});
