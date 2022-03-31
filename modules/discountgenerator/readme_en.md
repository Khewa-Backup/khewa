INSTALLATION
-----------------
A standard installation process, common for all Prestashop modules, applies to Discount Generator. If your shop has multiple stores, please go to Configuration (Configure button) and check the option "Activate module for this shop context: all shops".
This will allow you to generate vouchers later either separately (for any of your stores) or in common (for all the stores at once). If you need to stop using the module for some time, please use "Disable" button.

Important : If for any reason, you want to completely remove the Discount Generator module from the module list, please UNINSTALL it first, and only then DELETE. This order of actions is important for cleaning database tables related to the module.

Important : Since the Discount Generator module extends native cart rule functions, you need to keep "Disable all overrides" and "Disable non PrestaShop modules" options under "Advanced parameters" > "Performance" > "Debug mode" section disabled.

DESCRIPTION
-----------------
Discount Generator module allows you to generate at once important amounts of vouchers with unique promotion codes. You can generate vouchers for a specific product, a group of selected products, a specific category, or an entire order, or specify any other condition available for standard cart rules.

You will be able to define :
- how many vouchers with randomly generated unique codes you want to generate
- the structure of unique codes (a combination of numbers and letters)
- how many times a voucher can be used by any customers
- how many times a voucher can be used by the same customer


MODULE SETTINGS
------------------
This module installs directly into Catalog > Discounts tab and extends its native functions.

1. In Prestashop 1.7, proceed to Catalog > Discounts tab, and click on "Add new cart rule". In Prestashop 1.5 - 1.6, proceed to Price rules > Cart Rules menu tab , and click on "Add new cart rule".
2. To generate vouchers with Discount Generator, check "Generate many unique vouchers" option and fill in new fields that will appear on the page.
3. Fill in new required fields:
	- Total quantity of generated coupons : enter here the number of vouchers you want to generate.
	- Code configurations : 
	  - Prefix : any letters or numbers are allowed. The following characters are not allowed: ^!,;?=+()@"°{}_$% . The prefix is a stable part of your code, it will not change within a group of vouchers. 
	  - Mask : a sequence of x or/and y - that will define the structure of your code. X stands for any number, Y stands for any letter of standard Latin alphabet. X and Y will be randomly generated to make your codes unique. Example : if Prefix = TEST- and Mask = XXYY , the module will generate codes like TEST-96FA, TEST-27ME etc.
4. Make sure that all other required fields are properly filled.
5. Submit the form by clicking on Save. The module will generate the number of vouchers you have defined.

EXPORT LISTS
-------------------
All vouchers generated will be automatically listed in the table named "Discount generator module history" located at the bottom of the module configuration page. 

There are three types of lists downloadable as CSV files :

- "All" - will list all vouchers generated, their start and expiration date, a discount type. This list is generated at the moment vouchers are created and remains as it is, without getting updated.
- "Used" - will only list vouchers that have already been used by your customers, showing customer's name and email. This list changes dynamically and gets updated every time you download it.
- "Unused" - will only list vouchers that have NOT been used yet. This list changes dynamically and gets updated every time you download it.

CONTACTS
-----------------
Support : Please contact us using your Addons account, to help us identify your order id : https://addons.prestashop.com/en/order-history.