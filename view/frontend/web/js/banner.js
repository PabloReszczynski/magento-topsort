/**
 * Copyright © Topsort, Inc. All rights reserved.
 */
define([
    'jquery', 'mage/url'
], function ($, urlBuilder) {

    return function(config, element) {

        function renderBanner(responseData) {
            let bannerContainerEl = $(config.hasOwnProperty('bannerContainer') ? config.bannerContainer : element);

            if (bannerContainerEl.length === 0) {
                // there is no products section on this page, do nothing
                return;
            }

            bannerContainerEl.html(responseData.html);
            bannerContainerEl.trigger('contentUpdated');
        }

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
                renderBanner(data);
            }
        }).fail(function (jqXHR, textStatus) {
            if (window.hasOwnProperty('console') && window.console.hasOwnProperty('log')) {
                console.log(textStatus);
            }
        });

    };
});
