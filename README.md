## TopSort Magento Extension

Extension for Magento 2 for integration with TopSort auction-based infrastructure for sponsored product listings on magento marketplaces.

### In development...

The extension is under development.

### Installation

#### 1. Add repository information to composer.json of the Magento 2 instance:

```
{
    ....
    "repositories": {
      "topsort-extension": {
        "type": "vcs",
        "url": "git@github.com:Topsort/magento-topsort.git"
      },
      "topsort-sdk": {
        "type": "vcs",
        "url": "git@github.com:Topsort/php-sdk.git"
      },
      ...
    }
}

```

Note: Topsort/php-sdk repository is required only if v1 of composer is used (relevant for Magento versions below 2.3.7).*

#### 2. Install the composer package:

```
composer require topsort/module-topsort-integration-magento2 
```

#### 3. Finish the installation by running the required magento commands:

```
bin/magento module:enable Topsort_Integration
bin/magento setup:upgrade
```