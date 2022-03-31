# Canada Post Module

Offer real-time rates to customers, generate bulk order labels in one click, allow customers to track their orders, and more.

*Developed by Zack Hussain @ [zhmedia.ca](https://zhmedia.ca)*

 1. [Installation & Configuration](#installation--configuration)
 2. [Upgrading From Version 3.x](#upgrading-from-version-3.x)
 3. [Notice for PrestaShop 1.6.0.0 to 1.7.0.5](#notice-for-prestashop-1.6.0.0-to-1.7.0.5)
 4. [Rates](#rates)
 5. [Boxes & Box Packing](#boxes--box-packing)
 6. [Rate Discount Rules & Free Shipping](#rate-discount-rules--free-shipping)
 7. [Delivery Time Estimates](#delivery-time-estimates)
 8. [Shipping Estimator](#shipping-estimator)
 9. [Addresses](#addresses)
 10. [Labels](#labels)
 11. [Return Labels](#return-labels)
 12. [Bulk Labels](#bulk-labels)
 13. [Manifests](#manifests)
 14. [Groups](#groups)
 15. [Tracking](#tracking)
 16. [Auto-Update Order Status on Delivered](#auto-update-on-delivered)
 17. [Setting Up Cron Job](#setting-up-cron-job)
 18. [Smart Caching](#smart-caching)
 19. [Troubleshooting](#troubleshooting)
 20. [Common Error Messages](#common-error-messages)
 21. [Requirements](#requirements)
 22. [Changelog](#changelog)

## Installation & Configuration

 1. Go to the Modules page.
 2. Search for "Canada Post" and click **Install**
 3. If you don't see the module, click **Upload a Module** and upload the module .zip file.

The module must be configured with your Canada Post account, an origin address, and at least one box.

To configure the module:

 1. Go to Modules page.
 2. Find the **Canada Post** module and click **Configure**.
 3. Click **Sign in with Canada Post**
 4. You'll be redirected to the Canada Post website where it will prompt you to login to your account and authorize the module to create shipments on your behalf. You can revoke these permissions at any time from the Canada Post website.

> If this is your first time creating a Canada Post account and/or adding a new credit card to your Canada Post profile, please allow up to 24 hours for your information to process before using this module. You may encounter errors during that time.

Once you're connected, it's recommended to configure all the default label values in the **Labels** preferences so that you don't have to set them for every single label you create.

## Upgrading From Version 3.x

**Notice for users upgrading the module from any version prior to 4.0.0**: While care was taken to ensure a smooth update, it is highly recommended to uninstall and reinstall the module as version 4.0.0 is completely re-written from scratch and there's a possibility of encountering errors.

There are also several dozens of new configuration and customization options in version 4.0.0 that are not in prior versions which should be configured immediately after updating.

## Notice for PrestaShop 1.6.0.0 to 1.7.0.5

PS versions 1.6.x and early versions of PS 1.7 contain bugs that this module must work around.

For versions 1.6.0.0 to 1.7.0.5, this module automatically installs an **Override** file at `{your_prestashop}/override/classes/Hook.php` to solve a particular bug. Before using the module, verify that this file present. If you encounter the error "*Invalid id_module or hook_name*", try clearing your cache in *Advanced Parameters > Performance* and click "*Clear Cache*" in the top right.

If you still encounter the above error, compare the following two files to see that there isn't another module attempting to override the same function in the same file: `{your_prestashop}/override/classes/Hook.php` and `{your_prestashop}/modules/canadapostlabels/override/classes/Hook.php`. As a last resort, you can manually transfer the module's `Hook.php` contents into the `Hook.php` file in your store's `override/classes` folder.

In PS 1.7.0.0 to 1.7.0.5, you may also need to alter one line of your theme to get the front-office order tracking feature to display properly - add "nofilter" to the following hook:

 1. Open the file `{your_theme}/templates/customer/order-detail.tpl`
 2. Replace `{$HOOK_DISPLAYORDERDETAIL}` with `{$HOOK_DISPLAYORDERDETAIL nofilter}`

## Rates

The checkout rates can be configured by selecting the carriers you wish to enable in the **Rates** preferences. Enabling carriers in the **Rates** preferences will install them in your back-office automatically *(Shipping > Carriers)*.

The **Rates** preferences include various settings for **Box Packing** which are explained in the next section.

If you are not seeing the rates after enabling the carriers, please see the [Troubleshooting](#troubleshooting) section.

## Boxes / Box Packing

Add all the box dimensions that you ship orders with in the **Boxes** preferences.

The module uses a sophisticated [**box packing algorithm**](https://en.wikipedia.org/wiki/Bin_packing_problem) to determine the **smallest box(es)** that will fit all the products in the customer's cart; it can **split the products** into **multiple boxes** when the cart has more products than your largest box can fit.

You can add unlimited boxes into the module from the configuration page. The more boxes you add, the more accurate the rates will be.

You can **disable product splitting** and only use 1 box for each cart by setting **Multiple Box Rates** to **Off**. This will show standard rates for a single box; the module will still use box packing to determine the best box to use.

The **Multiple Box Rates** setting determines how the module sums up the rates for each box:

> **Off**: Disables multiple boxes and only uses 1 box.
>
> **Simple & Fast (recommended)**: Finds the rate for the LARGEST required box and
> MULTIPLIES the rate by the amount of boxes needed. e.g. For 3 boxes:
> $10 * 3 boxes = $30 Shipping Fee. This method only calls the Canada
> Post API once to get the rates for the cart.
>
> **Accurate & Slower**: Finds the rate for EVERY box required to fit all
> the products in the cart and SUMS all the rates together. e.g. For 3
> boxes: $10 + $11 + $12 = $33 Shipping Fee. This method will call the
> Canada Post API once for each box that the cart requires. Carts
> requiring many boxes may have a longer loading time when retrieving
> rates. If your store has a lot of traffic, you may reach your Canada
> Post API throttle limit which results in an API timeout for 60
> seconds. You can request an increased API limit from Canada Post -
> more info on the throttle limit can be found here:
> https://www.canadapost.ca/cpo/mc/business/productsservices/developers/throttlelimits.jsf


If the cart products require more boxes than the maximum allowed, the module will choose boxes that fit the largest products in the cart until it reaches the maximum.

The Canada Post weight limit per box is **30kg** and the module will spread weight across multiple boxes if required.

The limit of boxes per cart is **25**.

**Possible Issue:** If the cart contains a product that does not fit in any of your boxes, the module will be unable to pack the products into your boxes and will revert to using **(1)** of the **largest box** you have. The module will display a warning on the configuration page if any of your products are too large for your largest box.

## Rate Discount Rules & Free Shipping

You can configure discounts for each Canada Post carrier in the **Rate Discount Rules** preferences.

Each carrier can have a percentage, dollar amount, or Free Shipping discount applied to it. e.g. **Free Shipping** when **Order Total Is At Least** CAD$100.00 **tax excl.**

You can only create one discount rule per carrier for each **Shop**.

If you require more flexible ranges, consider using PrestaShop's built-in carrier system using the following example as a starting point:

In this example we will offer free shipping for only **Canada** on orders above **$100**.

1. Create a new **zone** called "Canada" *(International > Locations > Zones menu)*.
2. Edit the country **Canada** and change its zone from **North America** to **Canada** *(International > Locations > Countries menu)*. Zones are only used for Shipping, so update any other carriers with the new Zone if needed.
3. Create a new **carrier** called "Free Shipping" (or any other name).
4. Set its Billing to '**According to total price**'.
5. Set its Out-of-range behavior to '**Disable Carrier**'.
6. Set its range to '>= 100' and '< 999999'.
7. Deselect all zones except Canada, and set Canada to 0.00      This will display free shipping for Canada orders when the order total is above $100.00. To disable one or more carriers when free shipping is enabled, do the following for each carrier.
9. Edit a Canada Post carrier.
10. Set its Billing to '**According to total price**'.
11. Set its Out-of-range behavior to '**Disable Carrier**'.
12. Set its range to '>= 0' and '< 100'.      This will disable that particular carrier when the order total is above $100.00. Repeat for each carrier you wish to disable.

## Delivery Time Estimates

To display delivery time estimates on the checkout page (e.g. 3 Business Days), you must edit one line of code on your theme. Use the following instructions to enable delivery times.

1. Enable **Show Delivery Time Estimates** in the **Rates** preferences.
2. Open the file at `/themes/(your_theme_name)/templates/checkout/_partials/steps/shipping.tpl`
3. Near the top, locate the following line: `{block name='step_content'}`
4. Immediately after that line, paste the following code: `{if isset($delay_times)}{assign var=delivery_options value=$delay_times}{/if}`

## Shipping Estimator

The **Shipping Estimator** allows guests/customers to estimate shipping fees on the **Product** and **Cart** pages. To display the Shipping Estimator, enable the **Enable Shipping Estimator** setting in the **Rates** preferences.

To estimate rates, guests *without addresses* can enter a **Country** & **Postal/Zip Code**, and customers *with addresses* will see rates based on their account's shipping address. Customers *with addresses* can select which address they wish to estimate shipping rates for.

On the **Product** page, the shipping estimates are based on the product that the customer is currently viewing along with any products already in their cart, any carrier restrictions for those particular products will apply.

When a guest/customer adds a product to their cart, the **Shipping Estimator** will then allow the customer to change their cart's selected shipping method from the **Product** or **Cart** pages.

## Addresses

Configure your addresses in the **Addresses** preferences.

Select one address as your **Origin** address to be used as the origin for front-office rate calculation.

Your addresses will also be available to be selected for any of the following:

 1. The shipping label "From" address.
 2. The return label "Receiver" address.
 3. The manifest address.

## Labels

You can create shipping labels on any **Order** page or in the "*Canada Post > Create Label & Return*" page in the sidebar.

Creating a label will also create a **Shipment** in the *Canada Post > View Shipments* page where you can *re-print*, *void*, or *track* previously created labels.

**Shipments** created for an **Order** can also be viewed on the **Order**'s page.

You can set default values for most of the label fields in the **Labels** preferences.

You can choose to auto-update **Orders** to a custom order status and auto-update an **Order**'s tracking number on label creation in the **Labels** preferences.

You can view real-time rates for your selected label settings by clicking *Update Rate* in the label form.

PDF files for created labels can be found in `/modules/canadapostlabels/pdf/shipping` folder.

> Note: Commercial Contract Canada Post customers will need to **Transmit** their **Shipments** to create a **Manifest** in the *Canada Post > End of Day / Transmit page*. More information can be found in the [Manifests](#manifests) section.

## Return Labels

You can create a return label for any order that has a Canadian shipping address. Create a return label on an order's individual **Order** page and clicking "*Create Return Label*" or in the *Canada Post > Create Label & Return* page in the sidebar.

Creating a return label will also create a **Return Shipment** in the *Canada Post > View Return Shipments* page where you can *re-print* or *track* previously created return labels.

PDF files for created return labels can be found in `/modules/canadapostlabels/pdf/returns` folder.

## Bulk Labels

The **Bulk Labels** feature allows you to create labels for multiple orders at once with powerful customization options to optimize your workflow.

### Overview

You can create **Bulk Labels** in the *Canada Post > Create Bulk Order Labels* page in the sidebar.

Select multiple orders by clicking the checkboxes next to each order, or by clicking *Bulk actions > Select All* at the bottom of the page.

Filter your orders by configuring the filters at the top of the *Bulk Order Labels* table. Your configured filters will be saved and persist every time you visit the page until you reset them.

Create labels for the selected orders by clicking *Bulk actions > Create Labels for Selected*.

Creating labels for multiple orders will also create a **Batch** of labels that can be viewed and re-printed in the *Canada Post > View Batches* page in the sidebar.

If there are any errors for one or more labels during the label creation process, those labels will be excluded from the **Batch**. You will be able to view the error message by clicking the red error link under the **Error** column. You can also view all the errors that occurred for a **Batch** on the *Canada Post > View Batches* page under the **Errors** column. Fix the errors for each order and try re-creating the labels.

PDF files for created batches of labels can be found in `/modules/canadapostlabels/pdf/batch` folder.

> **Note:** Creating large amounts of labels may take several minutes to complete, please allow the page to finish loading and do not refresh/cancel the page load.

### Modifying Label Settings

You can modify each individual **Order**'s label settings by clicking the **Edit Label Settings** button for the **Order** or from each **Order**'s order page.

Any modifications made to an order's label settings will be stored permanently and will be used instead of the default label values.

Orders with modified label settings will have a *Yes* under the **Modified** column and will also be indicated as *modified* next to **Saved Changes** in the label form.

> **Be careful** when trying to change an order's shipping address **after** modifying an order's label settings. When you modify an order's label settings, the shipping address in the label form will be stored independently from the shipping address on the customer's order. In which case, you will then have to change the shipping address in both the **Order** and the **Label Form Settings**.

### Live Rates

Real-time rates can be fetched for each order by clicking on the **refresh** icon under the **Live Rate** column. These rates are tax incl/excl depending on your tax setting in the **Rates** preferences.

### Label Carriers

The **Label Carrier** column indicates which Canada Post shipping method the label will use. By default, the module will automatically choose the **Label Carrier** based on the following in order of priority: (1) the carrier that the customer chose in their **Order** or (2) the first active carrier for the Order's shipping country or (3) the first inactive carrier for the Order's shipping country.

You can override these defaults by using **Carrier Mappings** in the following section.

### Carrier Mappings

You can map your PrestaShop carriers to Canada Post carriers in the **Carrier Mappings** preferences. Mapping your carriers will pre-select the **Label Carrier** for you.

**Example**: If you map "My Custom Carrier" to "Canada Post Expedited" - any orders that chose "My Custom Carrier" as their shipping method will have their **Label Carrier** defaulted to "Canada Post Expedited".

### Preferences

You can configure bulk label preferences in the **Bulk Order Labels** preferences.

You can exclude orders with certain order statuses from showing up in the Bulk Order Labels page by selecting one or more statuses in the **Exclude Order Statuses** setting. Select/Deselect statuses by holding Cmd/Ctrl+Click.

**Example:** If you choose to exclude "*Shipped*" orders, they will disappear from the page after you create labels for them (provided that you have *Auto-Update Order Statuses* enabled in the **Labels** preferences).

You can choose to sort your bulk label PDFs by Order ID, Shipping Date, or Order Date (ASC/DESC).

The **Delay Between Labels** setting is used to slow down the label creation process when more than 60 labels are requested. This adds a delay in between each label to make sure you aren't hitting the Canada Post API throttle limit and timing out. An **API timeout** is indicated by the error message "***Rejected by SLM Monitor***". You can request an increase in API limits by signing into the [Canada Post Developer Program](https://www.canadapost.ca/cpotools/apps/drc/registered?execution=e2s1) and clicking "*Increase Limits*".

## Manifests

A **Manifest** is proof of payment for a collection of physical shipments. If you are a commercial Canada Post customer you must produce a hard copy of your manifest and provide to Canada Post when they pick up your shipments or when you drop off your shipments at a mail processing plant.

You can **Transmit** a group of **Shipments** in the "*Canada Post > End of Day / Transmit*" page to create a **Manifest**. Manifests are only used by commercial Canada Post customers with a parcel agreement, regular customers do not need to transmit shipments or present manifests.

You can view all previously created **Manifests** in the "*Canada Post > View Manifests*" page in the sidebar.

PDF files for created manifests can be found in `/modules/canadapostlabels/pdf/manifests/` folder.

> **Note from Canada Post:** After you print your commercial shipping labels, you must also transmit your shipment information, create a manifest, and provide a hard-copy manifest to Canada Post with your shipment. We monitor all shipments that have not followed this process. Failure to comply will result in manual billing at full rates and/or a loss of your automation discount.

## Groups

For commercial contract Canada Post customers, Groups are used to organize shipments into groups and transmitting them at separate times. For regular Canada Post customers, Groups can be used as an internal organizational tool.

You can choose which group a shipment will belong to when creating a shipment label. If you don't need to group shipments, you can simply use the "Default" group for all your shipments.

You can configure your groups in the **Groups** preferences.

## Tracking

Allow customers to see their order's tracking progress from their front-office **Order Details** page by enabling **Enable Order Tracking** in the **Tracking** preferences.

This will display a green progress bar in the order details page along with brief information about the last tracking event for their parcel (event description, current location, event time).

The tracking progress will only be displayed if a label was created for the order either via the **Bulk Order Labels** page or from the individual **Order** page. It will **not** be displayed if you created a custom label from the "*Create Label & Return*" page or if you entered a tracking number manually.

An order may have multiple labels associated with it, in which case the front-office order detail page will have multiple tracking progress bars (one for each label).

> **For PrestaShop versions 1.7.0.0 to 1.7.0.5**: A theme modification may be required to make this feature work due to a bug in the default theme.
>
>  1. Open the file `{your_theme}/templates/customer/order-detail.tpl`
>  2. Replace `{$HOOK_DISPLAYORDERDETAIL}` with `{$HOOK_DISPLAYORDERDETAIL nofilter}`

## Auto-Update on Delivered

You can choose to auto-update orders to a custom order status when the Canada Post tracking says it has been delivered. Enable **Auto-Update Order Status When Delivered** in the **Tracking** preferences.

The **Auto-Update Order Status When Delivered** feature requires that you have the free "**Cron tasks manager**" module made by PrestaShop installed and enabled. Install it by going to the "Module Catalog" page and searching for "Cron" and clicking "Install". You will then be able to customize the frequency of tracking updates from the "Cron tasks manager" module configuration page.

You can choose multiple order statuses that you want the module to track and auto-update when delivered in the **Order Statuses to Track** setting. Select/Deselect statuses by holding Cmd/Ctrl+Click. The module will only track orders with those selected statuses and will ignore any orders without those statuses.

Choose which status to update an order with once it has been tracked as delivered in the **Delivered Order Status** setting.

## Setting Up Cron Job

### What is Cron

Cron is a Unix system tool that provides time-based job scheduling: you can create many cron jobs on your server which are then run periodically at fixed times, dates, or intervals.

This module uses Cron Jobs for the following two tasks:

 1. Track all shipped orders and auto-update their order status when they are delivered by Canada Post.
 2. Clear cached front-office rates that are older than 3 months.

### How to set it up

 1. Install the free "Cron tasks manager" module by PrestaShop by going to the "Module Catalog" page and searching for "Cron" and clicking "Install".
 2. Go to the "Cron tasks manager" configuration page and change "Cron mode" to "Advanced".

If you are comfortable setting up a cron job on your own server:
: Copy the cron command from the "Cron tasks manager" module and paste it in your server's cron manager (varies by server). Set the cron job to run hourly (`0 * * * *`).

 If you're not comfortable setting up a cron job yourself, or you don't have a developer who can do it for you:
 : Consider using the service **EasyCron** (free for 200 cron jobs per day): https://www.easycron.com/cron-job-tutorials/how-to-set-up-cron-job-for-prestashop-cron-tasks-manager - and configure the task to run hourly.


## Smart Caching

The module stores rates for each cart in the database to speed up your website. It will only retrieve new rates if the customer changes their zip/postal code, the products in their cart, or the quantities for products already in their cart.

In some cases you may be trying to change the module configuration/carriers and refreshing your website to see updated rates. The rates will not update until one of the above conditions have been met, so try changing the products/quantities in your cart to see the new rates.

If you have the free "*Cron task manager*" module by PrestaShop installed, the module will regularly delete cached front-office rates older than 3 months (*cpl_cache* & *cpl_cache_rate tables*) to save space.

## Troubleshooting

### Q: Why are the rates not showing up?

- Check each **product's** Shipping settings to see if the carriers are enabled/disabled in Catalog > Products > (edit a product) > Shipping *(this is the most common issue)*.
- Make sure the **carriers' zones** are enabled in Shipping > Carriers.
- If you're using **Advanced Stock Management**, make sure your **Warehouses** are set to the proper carriers in Stock > Warehouses > (edit Warehouse). Make sure that your **products** are set to the proper **Warehouses** in each product's configuration page.
- Go to the module's configuration page and see if there are any warnings/errors.

Enable **Error Logging** in the module configuration page to help troubleshoot rates. If there are any errors while retrieving rates, the errors will get logged in the **Advanced Parameters > Logs** menu. Search for "**Canada Post"** in the log page to filter your logs.

## Common Error Messages

"Rejected by SLM Monitor"
: You have exceeded the [throttle limit for your API key](https://www.canadapost.ca/cpo/mc/business/productsservices/developers/throttlelimits.jsf). You will be blocked from making additional calls for up to a minute. You can request for a limit increase using the “Increase Limits” link found below your API keys on canadapost.ca.

"The service USA.EP is not available for the specified country or customer/contract."
: You are trying to create a label with a shipping method that is not supported for the destination country. "USA.EP" refers to "Expedited Parcel USA". Other service codes include "DOM.RP" for "Expedited Parcel" (DOM = domestic/Canada), or "INT.XP" for "International Xpresspost").

"postal-code value 'A1A1' is not a valid instance of type PostalCodeType"
: You are trying to create a label or retrieve a rate with an invalid postal code.

" " is not a valid Shipping Point Id on the date of deposit. Use Find a Deposit Location on canadapost.ca to find valid site numbers."
: You must enter a "Shipping Point ID" in the **Labels** preferences. Or enable "Pickup" and enter a "Requested Shipping Point" instead.

"requested-shipping-point of type PostalCodeType may not be empty"
: You must enter a "Requested Shipping Point" postal code in the **Labels** preferences. Or disable "Pickup" and enter a "Shipping Point ID" instead.




## Requirements

1. A Canada Post account.
2. Your website's PHP version should be at least 5.4.0

## Changelog

- TODO: 
    - use product dimensions values on order page
    - Add option to use product dimensions when calculating rates when only one product is in the cart

- 4.0.8 *(07/01/2021)*
    - Fix bug where product attribute weight was ignored

- 4.0.7 *(12/12/2020)*
    - Fix bug where Estimator would display carriers as "free" when no carriers were available
    - Add box packing optimization to help speed it up when it requires many boxes
    - Fix bug where customs product details only showed one product when customers ordered multiple combinations of the same product
    - Fix bug where tab "name" field doesn't exist for all languages 
    - Fix tooltip/popovers missing in PS 1.7.7
    - Fix URI length error when fetching rates or creating a label for an order containing lots of products
    - Add secure token to front controller
    - Abstract HTML tags in PHP files with render methods
    - Fix icon.tpl template vars conflicting with list_action_button.tpl template vars
    - Add HTML var escaping format

- 4.0.6
    - Fix bugs for non-contract users using contract services on certain orders
    - Fix Refund button for non-contract users using contract services
    - Enable JS in configuration page when module is disabled

- 4.0.5
    - NEW: Added support for new order page in PS 1.7.7
    - NEW: Ability for non-contract Canada Post customers to use contract services (e.g. 4x6 labels) — they have to change their account type to "Commercial Contract" in the module preferences to use these services
    - Fix issue where multi-select options in configuration page were deselecting (tracking order statuses, excluding bulk statuses)
    - Add ability for PS 1.6 users to send tracking emails
    - Add default "output-format", "requested-shipping-point", and "intended-method-of-payment" values upon initial configuration
    - Fix certain return labels for orders not showing up in their respective order page
    - Fix bug where editing an address makes it inactive
    - Fix bug when toggling origin address via checkmark

- 4.0.4
    - Fix missing empty box weight when Default Box is configured
    - Fix errors not displaying when registering to the platform
    - Add jQuery to module configuration page in PS 1.7.7+
    - Fix Strip spaces from return label postal codes
    - Change Shipment->name length from 32 to 255
    - Make province code optional on international shipments

- 4.0.3
    - NEW: Option to send tracking emails on label creation
    - Fix error on orders containing only virtual products
    - Fix: save tracking number before changing order status
    - Fix styling on estimator
    - Prevent user from removing all Groups

- 4.0.2
    - Fix Rate Discount rule error when discount value is empty
    - Fix styling on estimator
    - Add HS Tariff in label form
    - Fix double div closing tag in development template
    - Fix return label error on intl shipments
    - Fix unit weight on intl shipments

- 4.0.1
    - Fix estimator showing as Free on out-of-stock products

- 4.0.0
	- Module completely re-built from the ground up with new features and better performance.
	- NEW: Create Bulk Order Labels in one click
	- NEW: Re-designed label interface with dozens of new label options
	- NEW: Front-office Shipping Estimator on Product/Cart pages
	- NEW: Front-office Order Tracking
	- NEW: Create Return Labels
	- NEW: Custom Rate Discount Rules
	- NEW: Auto-Update Order Status when Delivered
	- NEW: Box Packing Algorithm
	- NEW: Powerful bulk-shipping workflow optimization options
	- NEW: Smart Rate Caching to speed up the front-office
	- NEW: Re-designed Shipment/Manifest History
	- NEW: Bulk-re-print and bulk-void/refund labels
	- NEW: Track parcels from the back-office
	- NEW: Get rates with tax included OR excluded
	- NEW: Upload your own carrier logo
	- Added metrics for storage space used by module
	- Rewritten documentation
	- Contract/Non-Contract Modules are now consolidated into one module
	- Numerous bug fixes and improvements
	- Add Hook.php override for backwards compatibility (namespace issue)
    - Fix carrier logo not updating during upgrades from v3
    - Change download ID

- 3.0.4
	 - FIX delivery estimate bug

- 3.0.3
	 - FIX uninstall bug in Prestashop 1.7.2.2

- 3.0.2
	 - FIX Bug with creating manual orders.

- 3.0.1
	 - Updated for Prestashop 1.7
	 - Added more documentation
	 - Added manual workaround for delivery times
	 - Fixed cookie->exists

- 2.2.8
	 - FIX Bug with adding package weight to total weight.
	 - FIX Bug with creating custom orders.

- 2.2.7
	 - FIX Some rate services not appearing for certain addresses.

- 2.2.6
	 - FIX DC option code enabled on addresses that don't support it.
	 - FIX Add conversion rate from CAD for International shipments.
	 - FIX Bug preventing from deleting a box on some servers.
	 - NEW Added two new International shipping methods (parcel air, parcel surface).

- 2.2.5
	 - Fixed rate not updating for Shipping Estimator module.
	 - Added $delay_times smarty variable.

- 2.2.4
	 - IMPROVED: Rate volume calculation
	 - FIX: Admin order page now correctly pre-selects the optimal package size

- 2.2.3
	 - FIX: Syntax for select form fields in latest PS version on older PHP versions.
	 - FIX: CSS and JS selectors for new select form fields in latest PS version.

- 2.2.2
	 - NEW: Rate algorithm improvement with long edges
	 - FIX: Syntax for select form fields in latest PS version
	 - FIX: Bug with commercial invoices
