# Overview
Scandi_HreflangScope module functionality is represented by the following:
 - Everytime a CMS page is rendered, the module will check if that page is available in any other store.
 - If true, it'll add a `<link rel="alternate" hreflang="xx-xx" href="baseUrl . pageUrl">` for each store.

# Installation
## Composer Mehod
1. Run `composer require scandi/module-store-hreflang-scope` in your project directory.
2. Run `bin/magento setup:upgrade`.

## Zip Method (app/code) 
Also, you can insert the module files directly in the app/code directory, in Magento 2 installation.
1. Create the app/code/Scandi/StoreHreflangScope directory.
2. Insert all files from zip inside this created directory.
