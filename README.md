# Topsort Magento Extension

[![Latest Stable Version](http://poser.pugx.org/topsort/module-topsort-integration-magento2/v)](https://packagist.org/packages/topsort/module-topsort-integration-magento2) [![License](http://poser.pugx.org/topsort/module-topsort-integration-magento2/license)](https://packagist.org/packages/topsort/module-topsort-integration-magento2)


## Auction-based advertising *made simple*

Topsort provides **auction-based native ad infrastructure and APIs** for the world's top marketplaces and multi-brand retailers to monetize fast and stress-free.

This Magento 2 extension allows a Magento-based marketplace to integrate with Topsort's API hassle free.

## System Requirements

- Magento >= 2.3.7 <= to 2.4.3.
- Composer v2

## Installation

### 1. Install the module with Composer

```
composer require topsort/module-topsort-integration-magento2
```

### 2. Finish the installation by running the required magento commands:

```
bin/magento module:enable Topsort_Integration
bin/magento setup:upgrade
```

## Configuration

### Catalog Service API

The Catalog Service API gives Topsort access to your marketplace product
catalog for the creation of advertising campaigns..

#### Setting up vendors

By default, Magento does not have a notion of vendor or merchant. You may use the
default product attribute `manufacturer` or create your own attribute.

**Option 1. Using the existing "manufacturer" attribute**

This can be done by navigating to “*Product Attributes*” page (Menu → Stores → Attributes → Product),
then search for the `manufacturer` attribute code.
By filling in the attribute options you can define what vendors products can be assigned to each vendor.

**Option 2. Adding a new "*Vendor*" attribute**

By using the standard Magento functionality, we can configure a new `vendor` product attribute.
This can be done by clicking on “*Add New Attribute*” under the “*Product Attributes*” page (Menu → Stores → Attributes → Product).

Similar to the configuration or `manufacturer` attribute, the new attribute
should be configured as a “Drop-down” list. By configuring the attribute options
you will provide the list of Vendors that will later be shared with the Topsort API.

Please, note that once the list of options is defined and your vendors already
got their products and start using the system, you should not remove them from
the list of vendors (the option associated with the vendor should not be removed).

#### Setting up Brands

Topsort Brands are configured in a very similar way to Topsort Vendors.
Please, see the [previous section](#setting-up-vendors) on how to configure them.
In the simplest setup the same attribute might be representing both: vendors and
brands.

#### Activating the Catalog Service API

The Catalog Service API can be activated and configured in Magento configuration section: (Stores → Configuration → Topsort → Catalog Service APIs)

You'll need:

- **Access Token**: This is a secret key you and Topsort should share to allow the connection.
- **Topsort Vendor Attribute code**: This property is defining the product attribute code used to identify Topsort Vendors
- **Topsort Brand Attribute code**: This property is defining the product attribute code used to identify Topsort Vendors

#### Configuration for Sponsored Products

The behavior of sponsored products can be configured in Magento configuration under Store → Configuration → Topsort → Sponsored Products.

You'll need:

- **Topsort API Key**: API key required to authenticate with Topsort.
- **Topsort API URL**: The base URL for the Topsort API endpoints.
- **Currency sub-unit multiplier**: The multiplier used to convert the amount expressed in currency sub-units into its main unit (e.g. is 100 for US Dollar since 1 Dollar has 100 Cents).
- **Enabled sponsored products in catalog/search**: Set to *Yes* in order to start running auction with sponsored products in catalog and search views of the site.
- **Amount of sponsored products to render**: Specifies the maximum amount of sponsored products that might be displayed on the page.
- **Add sponsored products if amount of products greater than**: You may avoid displaying sponsored products if the catalog or search result pages have too few items on it.
- **Sponsored product label text**: The text for the label that will be displayed on top of sponsored products in the list of products.


## Limitations

The full-page cache on category pages needs to be disabled for sponsored products to render.
Promoted products will increase the number of products displayed on the first page in the product lists: in search results and on the catalog pages.

