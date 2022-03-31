<?php

if (!defined('_PS_VERSION_')){
  exit;
}

class updateProducts extends Module{

  private $_model;
  public $fieldIdSpecificPrice;
  private $_exportTabInformation;
  private $_exportTabPrices;
  private $_exportTabSeo;
  private $_exportTabAssociations;
  private $_exportTabShipping;
  private $_exportTabCombinations;
  private $_exportTabQuantities;
  private $_exportTabImages;
  private $_exportTabFeatures;
  private $_exportTabCustomization;
  private $_exportTabAttachments;
  private $_exportTabSuppliers;
  private $_updateTabInformation;
  private $_updateTabPrices;
  private $_updateTabSeo;
  private $_updateTabAssociations;
  private $_updateTabShipping;
  private $_updateTabCombinations;
  private $_updateTabQuantities;
  private $_updateTabImages;
  private $_updateTabFeatures;
  private $_updateTabCustomization;
  private $_updateTabAttachments;
  private $_updateTabSuppliers;


  public function __construct(){
    include_once(_PS_MODULE_DIR_ . 'updateproducts/datamodel.php');
    $this->_model = new productsUpdateModel();

    if( isset(Context::getContext()->shop->id_shop_group) ){
      $this->_shopGroupId = Context::getContext()->shop->id_shop_group;
    }
    elseif( isset(Context::getContext()->shop->id_group_shop) ){
      $this->_shopGroupId = Context::getContext()->shop->id_group_shop;
    }

    $this->_shopId = Context::getContext()->shop->id;

    $this->name = 'updateproducts';
    $this->tab = 'quick_bulk_update';
    $this->version = '3.7.1';
    $this->author = 'MyPrestaModules';
    $this->need_instance = 0;
    $this->bootstrap = true;
    $this->module_key = "c6c523426a37f6035086d59f2f48981f";

    parent::__construct();

    $this->displayName = $this->l('Product Catalog Export/Update');
    $this->description = $this->l('Product Catalog Export/Update module is a convenient module especially designed to perform export and update operations with the PrestaShop products.');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    $this->fieldIdSpecificPrice = $this->l('Specific Price ID');

    $this->_exportTabInformation = array(
      array(
        'val'      => 'id_product',
        'name'     => $this->l('Product ID'),
        'disabled' => true,
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val'  => 'name',
        'name' => $this->l('Product name'),
        'hint' => $this->l('The public name for product.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'reference',
        'name' => $this->l('Reference code'),
        'hint' => $this->l('Your internal reference code for this product.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'active',
        'name' => $this->l('Enabled'),
        'hint' => $this->l('Value 0 or 1'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'description_short',
        'name' => $this->l('Short description'),
        'hint' => $this->l('Appears in the product list(s), and at the top of the product page.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'description',
        'name' => $this->l('Description'),
        'hint' => $this->l('Appears in the body of the product page.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'tags',
        'name' => $this->l('Tags'),
        'hint' => $this->l('Will be displayed in the tags block when enabled. Tags help customers easily find your products.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'ean13',
        'name' => $this->l('EAN-13 or JAN barcode'),
        'hint' => $this->l('This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'upc',
        'name' => $this->l('UPC barcode'),
        'hint' => $this->l('This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'condition',
        'name' => $this->l('Condition'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'new',
        'name' => $this->l('new'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'available_for_order',
        'name' => $this->l('Available for order'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'online_only',
        'name' => $this->l('Online only'),
        'hint' => $this->l('Online only (not sold in your retail store)'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'is_virtual',
        'name' => $this->l('Is virtual product'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'visibility',
        'name' => $this->l('Visibility'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'cache_is_pack',
        'name' => $this->l('cache_is_pack'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'product_link',
        'name' => $this->l('Product url'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'date_add',
        'name' => $this->l('Date add'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'date_upd',
        'name' => $this->l('Date update'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'id_shop_default',
        'name' => $this->l('Default Shop ID'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'quantity_discount',
        'name' => $this->l('quantity_discount'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'redirect_type',
        'name' => $this->l('redirect_type'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'id_product_redirected',
        'name' => $this->l('id_product_redirected'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'indexed',
        'name' => $this->l('Indexed'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'id_color_default',
        'name' => $this->l('id_color_default'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'isFullyLoaded',
        'name' => $this->l('isFullyLoaded'),
        'tab'   => 'exportTabInformation'
      ),
      array(
        'val' => 'id_pack_product_attribute',
        'name' => $this->l('Id pack product attribute'),
        'tab'   => 'exportTabInformation'
      ),
    );

    $this->_exportTabPrices = array(
      array(
        'val' => 'show_price',
        'name' => $this->l('Show price'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'wholesale_price',
        'name' => $this->l('Pre-tax wholesale price'),
        'hint' => $this->l('The wholesale price is the price you paid for the product. Do not include the tax.'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'base_price',
        'name' => $this->l('Pre-tax retail price'),
        'hint' => $this->l('The pre-tax retail price is the price for which you intend sell this product to your customers. It should be higher than the pre-tax wholesale price: the difference between the two will be your margin.'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'base_price_with_tax',
        'name' => $this->l('Retail price with tax'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'price',
        'name' => $this->l('Final price (pre-tax)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'final_price_with_tax',
        'name' => $this->l('Final price (with-tax)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'tax_rate',
        'name' => $this->l('Tax rate'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'id_tax_rules_group',
        'name' => $this->l('Tax rules group ID'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'unit_price_ratio',
        'name' => $this->l('Unit price ratio'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'unit_price',
        'name' => $this->l('Unit price (tax excl.)'),
        'hint' => $this->l('When selling a pack of items, you can indicate the unit price for each item of the pack. For instance, "per bottle" or "per pound".'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'unity',
        'name' => $this->l('Unit price (per)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'ecotax',
        'name' => $this->l('Ecotax (tax incl.)'),
        'hint' => $this->l('The ecotax is a local set of taxes intended to "promote ecologically sustainable activities via economic incentives". It is already included in retail price: the higher this ecotax is, the lower your margin will be.'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'on_sale',
        'name' => $this->l('Display the ON SALE icon'),
        'hint' => $this->l('Display the "on sale" icon on the product page, and in the text found within the product listing. Value 0 or 1'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'id_specific_price',
        'name' => $this->l('Specific Price ID'),
        'hint' => $this->l('This value is automatically added when any of the special_price fields is chosen, and can be removed only if none of the special_price fields is added'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price',
        'name' => $this->l('Specific fixed prices'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_reduction',
        'name' => $this->l('Specific price reduction'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_reduction_type',
        'name' => $this->l('Specific price reduction type'),
        'hint' => $this->l('Reduction type (amount or percentage)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_from',
        'name' => $this->l('Specific price Available from (date)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_to',
        'name' => $this->l('Specific price Available to (date)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_from_quantity',
        'name' => $this->l('Specific price Starting at (unit)'),
        'tab'   => 'exportTabPrices'
      ),
      array(
        'val' => 'specific_price_id_group',
        'name' => $this->l('Specific price Group ID'),
        'tab'   => 'exportTabPrices'
      ),
    );

    $this->_exportTabSeo = array(
      array(
        'val' => 'link_rewrite',
        'name' => $this->l('Friendly URL'),
        'tab'   => 'exportTabSeo'
      ),
      array(
        'val' => 'meta_title',
        'name' => $this->l('Meta title'),
        'hint' => $this->l('Public title for the product\'s page, and for search engines. Leave blank to use the product name. The number of remaining characters is displayed to the left of the field.'),
        'tab'   => 'exportTabSeo'
      ),
      array(
        'val' => 'meta_description',
        'name' => $this->l('Meta description'),
        'hint' => $this->l('This description will appear in search engines. You need a single sentence, shorter than 160 characters (including spaces).'),
        'tab'   => 'exportTabSeo'
      ),
      array(
        'val' => 'meta_keywords',
        'name' => $this->l('Meta keywords'),
        'hint' => $this->l('Keywords for HTML header, separated by commas.'),
        'tab'   => 'exportTabSeo'
      ),
    );

    $this->_exportTabAssociations = array(
      array(
        'val' => 'categories_ids',
        'name' => $this->l('Associated categories Ids'),
        'hint' => $this->l('Each associated category id separated by a semicolon'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'categories_names',
        'name' => $this->l('Associated categories name'),
        'hint' => $this->l('Each associated category name separated by a semicolon'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'id_category_default',
        'name' => $this->l('Category Default ID'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'id_product_accessories',
        'name' => $this->l('Accessories Product ID'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'id_manufacturer',
        'name' => $this->l('Manufacturer ID'),
        'tab'   => 'exportTabAssociations'
      ),
      array(
        'val' => 'manufacturer_name',
        'name' => $this->l('Manufacturer'),
        'tab'   => 'exportTabAssociations'
      ),
    );

    $this->_exportTabShipping = array(
      array(
        'val' => 'width',
        'name' => $this->l('Package width'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'height',
        'name' => $this->l('Package height'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'depth',
        'name' => $this->l('Package depth'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'weight',
        'name' => $this->l('Package weight'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'additional_shipping_cost',
        'name' => $this->l('Additional shipping fees'),
        'hint' => $this->l('Additional shipping fees (for a single item)'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'id_carriers',
        'name' => $this->l('Product Carriers ID'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'additional_delivery_times',
        'name' => $this->l('Delivery Time'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'delivery_in_stock',
        'name' => $this->l('Delivery time of in-stock products'),
        'tab'   => 'exportTabShipping'
      ),
      array(
        'val' => 'delivery_out_stock',
        'name' => $this->l('Delivery time of out-of-stock products with allowed orders'),
        'tab'   => 'exportTabShipping'
      ),
    );

    $this->_exportTabCombinations = array(
      array(
        'val' => 'id_product_attribute',
        'name' => $this->l('Product Combinations ID'),
        'disabled' => true,
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_name',
        'name' => $this->l('Combinations (Attribute - value pair)'),
        'hint' => $this->l('Each combination name separated by a semicolon'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_reference',
        'name' => $this->l('Combinations Reference code'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_price',
        'name' => $this->l('Combinations Impact on price (pre-tax)'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_price_with_tax',
        'name' => $this->l('Combinations Impact on price (with-tax)'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_unit_price_impact',
        'name' => $this->l('Combinations Impact on unit price'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_wholesale_price',
        'name' => $this->l('Combinations wholesale price'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'cache_default_attribute',
        'name' => $this->l('Default Product Combination ID '),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_ean13',
        'name' => $this->l('Combinations EAN-13 or JAN barcode'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_upc',
        'name' => $this->l('Combinations UPC barcode'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_ecotax',
        'name' => $this->l('Combination Ecotax (tax excl.)'),
        'hint' => $this->l('Overrides the ecotax from the "Prices" tab.'),
        'tab'   => 'exportTabCombinations'
      ),
      array(
        'val' => 'combinations_weight',
        'name' => $this->l('Combinations Impact on weight'),
        'tab'   => 'exportTabCombinations'
      ),
    );

    $this->_exportTabQuantities = array(
      array(
        'val' => 'quantity',
        'name' => $this->l('Quantity'),
        'hint' => $this->l('Available quantities for sale'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'minimal_quantity',
        'name' => $this->l('Minimum quantity'),
        'hint' => $this->l('The minimum quantity to buy this product (set to 1 to disable this feature)'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'location',
        'name' => $this->l('Stock location'),
        'xml_head' => $this->l('location'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'low_stock_threshold',
        'name' => $this->l('Low stock level'),
        'xml_head' => $this->l('low_stock_threshold'),
        'hint' => $this->l(''),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'low_stock_alert',
        'name' => $this->l('Low stock email alert'),
        'xml_head' => $this->l('low_stock_alert'),
        'hint' => $this->l('Send me an email when the quantity is below or equals this level'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'out_of_stock',
        'name' => $this->l('When out of stock'),
        'hint' => $this->l('0 - Deny orders, 1 - Allow orders, 2 - Default'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'available_now',
        'name' => $this->l('Displayed text when in-stock'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'available_later',
        'name' => $this->l('Displayed text when backordering is allowed'),
        'hint' => $this->l('If empty, the message "in stock" will be displayed.'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'advanced_stock_management',
        'name' => $this->l('advanced_stock_management'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'depends_on_stock',
        'name' => $this->l('depends_on_stock'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'pack_stock_type',
        'name' => $this->l('pack_stock_type'),
        'tab'   => 'exportTabQuantities'
      ),
      array(
        'val' => 'available_date',
        'name' => $this->l('Availability date'),
        'hint' => $this->l('The next date of availability for this product when it is out of stock.'),
        'tab'   => 'exportTabQuantities'
      ),
    );

    $this->_exportTabImages = array(
      array(
        'val' => 'images',
        'name' => $this->l('Product Image urls'),
        'tab'   => 'exportTabImages'
      ),
      array(
        'val'  => 'image_cover',
        'name' => $this->l('Product Cover Image'),
        'tab'   => 'exportTabImages'
      ),
      array(
        'val' => 'image_caption',
        'name' => $this->l('Product Image caption'),
        'tab'   => 'exportTabImages'
      ),
    );

    $this->_exportTabFeatures = array();

    foreach( Feature::getFeatures( Context::getContext()->language->id ) as $feature ){
      $this->_exportTabFeatures[] = array(
        'val' => 'feature_' . $feature['id_feature'],
        'name' => $this->l('Feature ') . $feature['name'],
        'tab'   => 'exportTabFeatures'
      );
    }

    $this->_exportTabCustomization = array(
      array(
        'val' => 'customizable',
        'name' => $this->l('Customizable'),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'uploadable_files',
        'name' => $this->l('File fields'),
        'hint' => $this->l('Number of upload file fields to be displayed to the user.'),
        'tab'   => 'exportTabCustomization'
      ),
      array(
        'val' => 'text_fields',
        'name' => $this->l('Text fields'),
        'hint' => $this->l('Number of text fields to be displayed to the user.'),
        'tab'   => 'exportTabCustomization'
      ),
    );

    $this->_exportTabAttachments = array(
      array(
        'val' => 'id_attachments',
        'name' => $this->l('Attachments ID'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'attachments_name',
        'name' => $this->l('Attachments Name'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'attachments_description',
        'name' => $this->l('Attachments Description'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'attachments_file',
        'name' => $this->l('Attachments file URL'),
        'tab'   => 'exportTabAttachments'
      ),
      array(
        'val' => 'cache_has_attachments',
        'name' => $this->l('cache_has_attachments'),
        'tab'   => 'exportTabAttachments'
      ),
    );

    $this->_exportTabSuppliers = array(
      array(
        'val'  => 'suppliers_ids',
        'name' => $this->l('Suppliers Ids'),
        'hint' => $this->l('Each supplier ID separated by a semicolon'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'suppliers_name',
        'name' => $this->l('Suppliers Name'),
        'hint' => $this->l('Each supplier name separated by a semicolon'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'suppliers_reference',
        'name' => $this->l('Suppliers Reference'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'suppliers_price',
        'name' => $this->l('Suppliers Unit price tax excluded'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'suppliers_price_currency',
        'name' => $this->l('Suppliers Unit price currency'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'id_supplier',
        'name' => $this->l('Default supplier ID'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_name',
        'name' => $this->l('Default supplier name'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_reference',
        'name' => $this->l('Default supplier reference'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_price',
        'name' => $this->l('Default supplier Unit price tax excluded'),
        'tab'   => 'exportTabSuppliers'
      ),
      array(
        'val' => 'supplier_price_currency',
        'name' => $this->l('Default supplier Unit price currency'),
        'tab'   => 'exportTabSuppliers'
      ),
    );


    $this->_updateTabInformation = array(
      array(
        'val'      => 'id_product',
        'name'     => $this->l('Product ID'),
        'disabled' => true,
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'id_product_attribute',
        'name' => $this->l('Product Combinations ID'),
        'disabled' => true,
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'name',
        'name' => $this->l('Product name'),
        'hint' => $this->l('The public name for product.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'reference',
        'name' => $this->l('Reference code'),
        'hint' => $this->l('Your internal reference code for this product.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'active',
        'name' => $this->l('Enabled'),
        'hint' => $this->l('Value 0 or 1'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'description_short',
        'name' => $this->l('Short description'),
        'hint' => $this->l('Appears in the product list(s), and at the top of the product page.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'description',
        'name' => $this->l('Description'),
        'hint' => $this->l('Appears in the body of the product page.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'tags',
        'name' => $this->l('Tags'),
        'hint' => $this->l('Will be displayed in the tags block when enabled. Tags help customers easily find your products.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'ean13',
        'name' => $this->l('EAN-13 or JAN barcode'),
        'hint' => $this->l('This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'upc',
        'name' => $this->l('UPC barcode'),
        'hint' => $this->l('This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'condition',
        'name' => $this->l('Condition'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'available_for_order',
        'name' => $this->l('Available for order'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'online_only',
        'name' => $this->l('Online only'),
        'hint' => $this->l('Online only (not sold in your retail store)'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'is_virtual',
        'name' => $this->l('Is virtual product'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'visibility',
        'name' => $this->l('Visibility'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'cache_is_pack',
        'name' => $this->l('cache_is_pack'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'date_add',
        'name' => $this->l('Date add'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'date_upd',
        'name' => $this->l('Date update'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'quantity_discount',
        'name' => $this->l('quantity_discount'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'redirect_type',
        'name' => $this->l('redirect_type'),
        'tab'   => 'updateTabInformation'
      ),
      array(
        'val' => 'id_product_redirected',
        'name' => $this->l('id_product_redirected'),
        'tab'   => 'updateTabInformation'
      ),
    );

    $this->_updateTabPrices = array(
      array(
        'val' => 'show_price',
        'name' => $this->l('Show price'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'wholesale_price',
        'name' => $this->l('Pre-tax wholesale price'),
        'hint' => $this->l('The wholesale price is the price you paid for the product. Do not include the tax.'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'base_price',
        'name' => $this->l('Pre-tax retail price'),
        'hint' => $this->l('The pre-tax retail price is the price for which you intend sell this product to your customers. It should be higher than the pre-tax wholesale price: the difference between the two will be your margin.'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'base_price_with_tax',
        'name' => $this->l('Retail price with tax'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'unit_price_ratio',
        'name' => $this->l('Unit price ratio'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'unit_price',
        'name' => $this->l('Unit price (tax excl.)'),
        'hint' => $this->l('When selling a pack of items, you can indicate the unit price for each item of the pack. For instance, "per bottle" or "per pound".'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'unity',
        'name' => $this->l('Unit price (per)'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'id_tax_rules_group',
        'name' => $this->l('Tax rules group ID'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'ecotax',
        'name' => $this->l('Ecotax (tax incl.)'),
        'hint' => $this->l('The ecotax is a local set of taxes intended to "promote ecologically sustainable activities via economic incentives". It is already included in retail price: the higher this ecotax is, the lower your margin will be.'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'on_sale',
        'name' => $this->l('Display the ON SALE icon'),
        'hint' => $this->l('Display the "on sale" icon on the product page, and in the text found within the product listing. Value 0 or 1'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'id_specific_price',
        'name' => $this->l('Specific Price ID'),
        'hint' => $this->l('This value is automatically added when any of the special_price fields is chosen, and can be removed only if none of the special_price fields is added'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price',
        'name' => $this->l('Specific fixed prices'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price_reduction',
        'name' => $this->l('Specific price reduction'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price_reduction_type',
        'name' => $this->l('Specific price reduction type'),
        'hint' => $this->l('Reduction type (amount or percentage)'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price_from',
        'name' => $this->l('Specific price Available from (date)'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price_to',
        'name' => $this->l('Specific price Available to (date)'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price_from_quantity',
        'name' => $this->l('Specific price Starting at (unit)'),
        'tab'   => 'updateTabPrices'
      ),
      array(
        'val' => 'specific_price_id_group',
        'name' => $this->l('Specific price Group ID'),
        'tab'   => 'updateTabPrices'
      ),
    );

    $this->_updateTabSeo = array(
      array(
        'val' => 'link_rewrite',
        'name' => $this->l('Friendly URL'),
        'tab'   => 'updateTabSeo'
      ),
      array(
        'val' => 'meta_title',
        'name' => $this->l('Meta title'),
        'hint' => $this->l('Public title for the product\'s page, and for search engines. Leave blank to use the product name. The number of remaining characters is displayed to the left of the field.'),
        'tab'   => 'updateTabSeo'
      ),
      array(
        'val' => 'meta_description',
        'name' => $this->l('Meta description'),
        'hint' => $this->l('This description will appear in search engines. You need a single sentence, shorter than 160 characters (including spaces).'),
        'tab'   => 'updateTabSeo'
      ),
      array(
        'val' => 'meta_keywords',
        'name' => $this->l('Meta keywords'),
        'hint' => $this->l('Keywords for HTML header, separated by commas.'),
        'tab'   => 'updateTabSeo'
      ),
    );

    $this->_updateTabAssociations = array(
      array(
        'val' => 'categories_ids',
        'name' => $this->l('Associated categories Ids'),
        'hint' => $this->l('Each associated category id must be separated by a semicolon'),
        'tab'   => 'updateTabAssociations'
      ),
      array(
        'val' => 'id_category_default',
        'name' => $this->l('Category Default ID'),
        'hint' => $this->l(''),
        'tab'   => 'updateTabAssociations'
      ),
      array(
        'val' => 'id_manufacturer',
        'name' => $this->l('Manufacturer ID'),
        'tab'   => 'updateTabAssociations'
      ),
      array(
        'val' => 'id_product_accessories',
        'name' => $this->l('Accessories Product ID'),
        'tab'   => 'updateTabAssociations'
      ),
    );

    $this->_updateTabShipping = array(
      array(
        'val' => 'width',
        'name' => $this->l('Package width'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'height',
        'name' => $this->l('Package height'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'depth',
        'name' => $this->l('Package depth'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'weight',
        'name' => $this->l('Package weight'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'additional_shipping_cost',
        'name' => $this->l('Additional shipping fees'),
        'hint' => $this->l('Additional shipping fees (for a single item)'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'id_carriers',
        'name' => $this->l('Product Carriers ID'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'additional_delivery_times',
        'name' => $this->l('Delivery Time'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'delivery_in_stock',
        'name' => $this->l('Delivery time of in-stock products'),
        'tab'   => 'updateTabShipping'
      ),
      array(
        'val' => 'delivery_out_stock',
        'name' => $this->l('Delivery time of out-of-stock products with allowed orders'),
        'tab'   => 'updateTabShipping'
      ),
    );

    $this->_updateTabCombinations = array(
      array(
        'val' => 'combinations_reference',
        'name' => $this->l('Combinations Reference code'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_price',
        'name' => $this->l('Combinations Impact on price (pre-tax)'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_price_with_tax',
        'name' => $this->l('Combinations Impact on price (with-tax)'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_final_price',
        'name' => $this->l('Final price (pre-tax)'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_final_price_with_tax',
        'name' => $this->l('Final price (with-tax)'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_unit_price_impact',
        'name' => $this->l('Combinations Impact on unit price'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_wholesale_price',
        'name' => $this->l('Combinations wholesale price'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'cache_default_attribute',
        'name' => $this->l('Default Product Combination ID '),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_ean13',
        'name' => $this->l('Combinations EAN-13 or JAN barcode'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_upc',
        'name' => $this->l('Combinations UPC barcode'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_ecotax',
        'name' => $this->l('Combination Ecotax (tax excl.)'),
        'hint' => $this->l('Overrides the ecotax from the "Prices" tab.'),
        'tab'   => 'updateTabCombinations'
      ),
      array(
        'val' => 'combinations_weight',
        'name' => $this->l('Combinations Impact on weight'),
        'tab'   => 'updateTabCombinations'
      ),
    );

    $this->_updateTabQuantities = array(
      array(
        'val' => 'quantity',
        'name' => $this->l('Quantity'),
        'hint' => $this->l('Available quantities for sale'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'minimal_quantity',
        'name' => $this->l('Minimum quantity'),
        'hint' => $this->l('The minimum quantity to buy this product (set to 1 to disable this feature)'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'location',
        'name' => $this->l('Stock location'),
        'xml_head' => $this->l('location'),
        'hint' => $this->l(''),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'low_stock_threshold',
        'name' => $this->l('Low stock level'),
        'xml_head' => $this->l('low_stock_threshold'),
        'hint' => $this->l(''),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'low_stock_alert',
        'name' => $this->l('Low stock email alert'),
        'xml_head' => $this->l('low_stock_alert'),
        'hint' => $this->l('Send me an email when the quantity is below or equals this level'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'out_of_stock',
        'name' => $this->l('When out of stock'),
        'hint' => $this->l('0 - Deny orders, 1 - Allow orders, 2 - Default'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'available_now',
        'name' => $this->l('Displayed text when in-stock'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'available_later',
        'name' => $this->l('Displayed text when backordering is allowed'),
        'hint' => $this->l('If empty, the message "in stock" will be displayed.'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'advanced_stock_management',
        'name' => $this->l('advanced_stock_management'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'pack_stock_type',
        'name' => $this->l('pack_stock_type'),
        'tab'   => 'updateTabQuantities'
      ),
      array(
        'val' => 'available_date',
        'name' => $this->l('Availability date'),
        'hint' => $this->l('The next date of availability for this product when it is out of stock.'),
        'tab'   => 'updateTabQuantities'
      ),
    );

    $this->_updateTabImages = array(
      array(
        'val' => 'image_caption',
        'name' => $this->l('Product Image caption'),
        'tab'   => 'updateTabImages'
      ),
      array(
        'val' => 'images',
        'name' => $this->l('Product Image urls'),
        'tab'   => 'updateTabImages'
      ),

    );

    $this->_updateTabFeatures = array();

    foreach( Feature::getFeatures( Context::getContext()->language->id ) as $feature ){
      $this->_updateTabFeatures[] = array(
        'val' => $feature['id_feature'] . '_FEATURE_'.$feature['name'],
        'name' => $this->l('Feature ') . $feature['name'],
        'xml_head' => $this->l('FEATURE_').$feature['name'],
        'tab'   => 'updateTabFeatures'
      );
    }

    $this->_updateTabCustomization = array(
      array(
        'val' => 'customizable',
        'name' => $this->l('Customizable'),
        'tab'   => 'updateTabCustomization'
      ),
      array(
        'val' => 'uploadable_files',
        'name' => $this->l('File fields'),
        'hint' => $this->l('Number of upload file fields to be displayed to the user.'),
        'tab'   => 'updateTabCustomization'
      ),
      array(
        'val' => 'text_fields',
        'name' => $this->l('Text fields'),
        'hint' => $this->l('Number of text fields to be displayed to the user.'),
        'tab'   => 'updateTabCustomization'
      ),
    );

    $this->_updateTabAttachments = array(
      array(
        'val' => 'cache_has_attachments',
        'name' => $this->l('cache_has_attachments'),
        'tab'   => 'updateTabAttachments'
      ),
    );

    $this->_updateTabSuppliers = array(
      array(
        'val' => 'suppliers_ids',
        'name' => $this->l('Suppliers Ids'),
        'hint' => $this->l('Each supplier ID must be separated by a semicolon'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'suppliers_reference',
        'name' => $this->l('Suppliers Reference'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'suppliers_price',
        'name' => $this->l('Suppliers Unit price tax excluded'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'suppliers_price_currency',
        'name' => $this->l('Suppliers Unit price currency'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'id_supplier',
        'name' => $this->l('Default supplier ID'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'supplier_reference',
        'name' => $this->l('Default supplier reference'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'supplier_price',
        'name' => $this->l('Default supplier Unit price tax excluded'),
        'tab'   => 'updateTabSuppliers'
      ),
      array(
        'val' => 'supplier_price_currency',
        'name' => $this->l('Default supplier Unit price currency'),
        'tab'   => 'updateTabSuppliers'
      ),
    );

  }

  public function install()
  {
    Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_ALL_SETTINGS', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_ALL_UPDATE_SETTINGS', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
    if ( !parent::install()  || !$this->registerHook('ActionAdminControllerSetMedia') ) {
      return false;
    }

    $this->installDb();

    return true;
  }

  public function installDb()
  {
    // Table  pages lang
    $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'updateproducts_images';
    Db::getInstance()->execute($sql);

    $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'updateproducts_images(
			`id` INT NOT NULL AUTO_INCREMENT,
      `image_url` VARCHAR(500) NULL,
      `id_shop` INT NULL,
      `id_product` INT NULL,
      `id_image` INT NULL,
      PRIMARY KEY (`id`),
      INDEX `updateproducts_images` (`id_product` ASC, `image_url` ASC, `id_shop` ASC)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';

    Db::getInstance()->execute($sql);

    return true;
  }


  public function uninstall(){

    $this->removeAllSettings();
    $this->uninstallDb();

    Configuration::deleteByName('GOMAKOIL_PRODUCTS_CHECKED');
    Configuration::deleteByName('GOMAKOIL_MANUFACTURERS_CHECKED');
    Configuration::deleteByName('GOMAKOIL_SUPPLIERS_CHECKED');
    Configuration::deleteByName('GOMAKOIL_CATEGORIES_CHECKED');
    Configuration::deleteByName('GOMAKOIL_FIELDS_CHECKED');
    Configuration::deleteByName('GOMAKOIL_ALL_SETTINGS');
    Configuration::deleteByName('GOMAKOIL_ALL_UPDATE_SETTINGS');

    return parent::uninstall();
  }

  public function uninstallDb()
  {
    $sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'updateproducts_images';
    Db::getInstance()->execute($sql);

  }

  public function removeAllSettings(){
    $all_setting = array();
    $all_setting =Tools::unserialize( Configuration::get('GOMAKOIL_ALL_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));

    foreach($all_setting as $value){
      Configuration::deleteByName('GOMAKOIL_PRODUCTS_CHECKED_'.$value);
      Configuration::deleteByName('GOMAKOIL_MANUFACTURERS_CHECKED_'.$value);
      Configuration::deleteByName('GOMAKOIL_SUPPLIERS_CHECKED_'.$value);
      Configuration::deleteByName('GOMAKOIL_CATEGORIES_CHECKED_'.$value);
      Configuration::deleteByName('GOMAKOIL_FIELDS_CHECKED_'.$value);
      Configuration::deleteByName('GOMAKOIL_LANG_CHECKED_'.$value);
      Configuration::deleteByName('GOMAKOIL_NAME_SETTING_'.$value);
      Configuration::deleteByName('GOMAKOIL_TYPE_FILE_'.$value);
    }

    $all_setting_update = array();
    $all_setting_update = Tools::unserialize( Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));

    foreach($all_setting_update as $value){
      Configuration::deleteByName('GOMAKOIL_NAME_SETTING_UPDATE_'.$value);
      Configuration::deleteByName('GOMAKOIL_FIELDS_CHECKED_UPDATE_'.$value);
      Configuration::deleteByName('GOMAKOIL_LANG_CHECKED_UPDATE_'.$value);
      Configuration::deleteByName('GOMAKOIL_TYPE_FILE_UPDATE_'.$value);
    }

  }

  public function hookActionAdminControllerSetMedia()
  {
	if(Tools::getValue('configure') == 'updateproducts'){
    $this->context->controller->addCSS($this->_path.'views/css/style.css');
    $this->context->controller->addJS($this->_path.'views/js/main.js');
    $this->context->controller->addJqueryUI('ui.sortable');
  }

  }

  public function getContent()
  {
    $logo = '<img class="logo_myprestamodules" src="../modules/'.$this->name.'/logo.png" />';
    $name = '<h2 id="bootstrap_products">'.$logo.$this->displayName.'</h2>';



    return $name.$this->displayForm();
  }

  public function getPath()
  {
    return $_SERVER['REWRITEBASE'] . "modules/updateproducts/";
  }

  public function supportBlock(){
    return $this->display(__FILE__, "views/templates/hook/supportForm.tpl");
  }

  public function displayTabModules(){
    return $this->display(__FILE__, 'views/templates/hook/modules.tpl');
  }

  public function displayForm()
  {
    $sort = array(
      array(
        'name' => 'ID',
        'id' => 'id',
      ),
      array(
        'name' => 'Name',
        'id' => 'name',
      ),
      array(
        'name' => 'Price',
        'id' => 'price',
      ),
      array(
        'name' => 'Quantity',
        'id' => 'quantity',
      ),
      array(
        'name' => 'Date add',
        'id' => 'date_add',
      ),
      array(
        'name' => 'Date update',
        'id' => 'date_update',
      )
    );
    $round_value = array(
      array(
        'id' => '0',
        'name' => '0',
      ),
      array(
        'id' => '1',
        'name' => '1',
      ),
      array(
        'id' => '2',
        'name' => '2',
      ),
      array(
        'id' => '3',
        'name' => '3',
      ),
      array(
        'id' => '4',
        'name' => '4',
      ),
      array(
        'id' => '5',
        'name' => '5',
      ),
      array(
        'id' => '6',
        'name' => '6',
      ),
    );

    if( Tools::getValue('settings') ){
      $id = Tools::getValue('settings');
    }
    else{
      $id = false;
    }

    if( Tools::getValue('settingsUpdate') ){
      $idUpdate = Tools::getValue('settingsUpdate');
    }
    else{
      $idUpdate = false;
    }

    $class = '';
    $name = '';
    $show = 0;

    if(Tools::getValue('settings')){
      $last_id = Tools::getValue('settings');
      $this->replaceConfig($last_id);
    }
    else{
      $all_setting = array();
      Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      $all_setting =Tools::unserialize( Configuration::get('GOMAKOIL_ALL_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));
      if($all_setting){
        $all_setting = max($all_setting);
        $last_id = $all_setting + 1;
      }
      else{
        $last_id = 1;
      }
    }

    if( Tools::getValue('settingsUpdate') ){
      $last_id_update = Tools::getValue('settingsUpdate');
      $this->replaceConfigUpdate($last_id_update);
    }
    else{
      $all_setting_update = array();
      Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED_UPDATE', '', false, $this->_shopGroupId, Context::getContext()->shop->id);
      $all_setting_update =Tools::unserialize( Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));

      if($all_setting_update){
        $all_setting_update = max($all_setting_update);
        $last_id_update = $all_setting_update + 1;
      }
      else{
        $last_id_update = 1;
      }
    }

    $products = Product::getProducts(Context::getContext()->language->id, 0, 300, 'id_product', 'asc' );
    $manufacturers = Manufacturer::getManufacturers(false, Context::getContext()->language->id, true, false, false, false, true );
    $suppliers = Supplier::getSuppliers(false, Context::getContext()->language->id);
    $selected_products = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $selected_manufacturers = Tools::unserialize(Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $selected_suppliers = Tools::unserialize(Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));
    $selected_categories = Tools::unserialize(Configuration::get('GOMAKOIL_CATEGORIES_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));


    if( Tools::getValue('settings') ){
      $priceSettings = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_PRICE_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));
      $quantitySettings = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_QUANTITY_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));
      $visibility = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_VISIBILITY_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));
      $condition = Tools::unserialize(Configuration::get('GOMAKOIL_PRODUCTS_CONDITION_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id));

      $show =  Configuration::get('GOMAKOIL_SHOW_NAME_FILE_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $name =  Configuration::get('GOMAKOIL_NAME_FILE_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      if($show){
        $class = ' active_block';
      }
    }
    else{
      $priceSettings = false;
      $quantitySettings = false;
      $visibility = false;
      $condition = false;
    }
    $url_base = AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&configure=updateproducts';
    $file_url = _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/updateproducts/files/';
    $nameDescription = '<p class="available_url">'.$this->l('The file will be available by link below::').'</p>';
    $nameDescription .= '<p ><strong><a class="href_export_file"  href="" data-file-url="'.$file_url.'"></a></strong></p>';

    $this->fields_form[0]['form'] = array(
      'tabs' => array(
        'export' => $this->l('General export settings '),
        'filter_products' => $this->l('Filter products for export'),
        'filter_fields' => $this->l('Filter fields for export'),
        'new_settings' => $this->l('Export settings'),
        'update' => $this->l('Update Catalog'),
        'update_settings' => $this->l('Update settings'),
        'support' => $this->l('Support'),
        'modules' => $this->l('Related Modules'),
      ),
      'input' => array(
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_module_hind form_group_module_hind_update',
          'tab' => 'export',
          'name' => '<div class="alert alert-info">' . $this->l('If no filter is selected, module will export all products!') . '</div>',
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'exportFields',
          'tab' => 'support',
          'name' => $this->supportBlock(),
        ),
        array(
          'type' => 'html',
          'tab' => 'modules',
          'form_group_class' => 'support_tab_content exportFields',
          'name' => $this->displayTabModules()
        ),
        array(
          'type' => 'radio',
          'label' => $this->l('Select file format:'),
          'name' => 'format_file',
          'required' => true,
          'class' => 'format_file',
          'form_group_class' => 'form_group_select_format',
          'br' => true,
          'tab' => 'export',
          'values' => array(
            array(
              'id' => 'format_csv',
              'value' => 'csv',
              'label' => $this->l('CSV')
            ),
            array(
              'id' => 'format_xlsx',
              'value' => 'xlsx',
              'label' => $this->l('XLSX')
            )
          ),
          'desc' => $this->l('Choose a file format you wish to export'),
        ),




        array(
          'type' => 'checkbox_table',
          'name' => 'products[]',
          'class_block' => 'product_list',
          'label' => $this->l('Filter by product:'),
          'class_input' => 'select_products',
          'lang' => true,
          'hint' => '',
          'tab' => 'filter_products',
          'search' => true,
          'display'=> true,
          'values' => array(
            'query' => $products,
            'id' => 'id_product',
            'name' => 'name',
            'value' => $selected_products
          )
        ),
        array(
          'type'  => 'categories',
          'label' => $this->l('Filter by category'),
          'name'  => 'categories',
          'tab'   => 'filter_products',
          'form_group_class' => 'form_group_filter_categories',
          'tree'  => array(
            'id'  => 'categories-tree',
            'use_checkbox' => true,
            'use_search' => true,
            'selected_categories' => $selected_categories ? $selected_categories : array()
          ),
        ),
        array(
          'type' => 'checkbox_table',
          'name' => 'manufacturers[]',
          'class_block' => 'manufacturer_list',
          'label' => $this->l('Filter by manufacturer:'),
          'class_input' => 'select_manufacturers',
          'lang' => true,
          'hint' => '',
          'tab' => 'filter_products',
          'search' => true,
          'display'=> true,
          'values' => array(
            'query' => $manufacturers,
            'id' => 'id_manufacturer',
            'name' => 'name',
            'value' => $selected_manufacturers
          )
        ),
        array(
          'type' => 'checkbox_table',
          'name' => 'suppliers[]',
          'class_block' => 'supplier_list',
          'label' => $this->l('Filter by suppliers:'),
          'class_input' => 'select_suppliers',
          'lang' => true,
          'hint' => '',
          'tab' => 'filter_products',
          'search' => true,
          'display'=> true,
          'values' => array(
            'query' => $suppliers,
            'id' => 'id_supplier',
            'name' => 'name',
            'value' => $selected_suppliers
          )
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Language'),
          'name' => 'id_lang',
          'required' => true,
          'default_value' => (int)$this->context->language->id,
          'form_group_class' => 'form_group_language',
          'tab' => 'export',
          'options' => array(
            'query' => Language::getLanguages(),
            'id' => 'id_lang',
            'name' => 'name',
          ),
          'desc' => $this->l('Choose a language you wish to export'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Strip tags'),
          'name' => 'strip_tags',
          'class' => 'strip_tags',
          'form_group_class' => 'export_strip_tags form_left_margin',
          'tab' => 'export',
          'is_bool' => true,
          'values' => array(
            array(
              'id' => 'strip_tags_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'strip_tags_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('Strip HTML and PHP tags from a description'),
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Number of decimal points'),
          'name' => 'round_value',
          'class' => 'round_value',
          'tab' => 'export',
          'form_group_class' => 'round_value_block form_left_margin',
          'options' => array(
            'query' =>$round_value,
            'id' => 'id',
            'name' => 'name'
          ),
          'desc' =>  $this->l('Will be used in the prices and size. You can choose to have 5.12 instead of 5.121123.'),
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Sort by'),
          'name' => 'orderby',
          'class' => 'orderby',
          'tab' => 'export',
          'form_group_class' => 'sort_block form_left_margin',
          'options' => array(
            'query' =>$sort,
            'id' => 'id',
            'name' => 'name'
          )
        ),
        array(
          'type' => 'radio',
          'label' => $this->l(' '),
          'name' => 'orderway',
          'tab' => 'export',
          'required' => true,
          'form_group_class' => 'sort_block_orderway form_left_margin',
          'br' => true,
          'values' => array(
            array(
              'id' => 'orderway_asc',
              'value' => 'asc',
              'label' => $this->l('ASC')
            ),
            array(
              'id' => 'orderway_desc',
              'value' => 'desc',
              'label' => $this->l('DESC')
            )
          )
        ),

        array(
          'type' => 'switch',
          'label' => $this->l('Each product combinations  in a separate line'),
          'name' => 'separate',
          'class' => 'separate',
          'form_group_class' => 'form_group_class_update',
          'is_bool' => true,
          'tab' => 'export',
          'values' => array(
            array(
              'id' => 'separate_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'separate_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('If activated, a line will be created for each attributes of the products'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Set specific file name'),
          'name' => 'name_export_file',
          'class' => 'name_export_file',
          'form_group_class' => 'form_group_class_hide form_group_class_set_name',
          'is_bool' => true,
          'tab' => 'export',
          'values' => array(
            array(
              'id' => 'name_export_file_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'name_export_file_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('You can set name for file or name will be given by system.'),
        ),
        array(
          'type' => 'text',
          'label' => $this->l('Name for exported file'),
          'name' => 'name_file',
          'tab' => 'export',
          'form_group_class' => 'form_group_name_file'.$class,
        ),
        array(
          'type' => 'html',
          'name' => $nameDescription,
          'tab' => 'export',
          'form_group_class' => ' auto_description_ex'.$class,
        ),

        array(
          'type' => 'html',
          'name' => 'html_data',
          'form_group_class' => 'updateFields',
          'tab' => 'update',
          'html_content' => $this->updateFields(),
        ),


        array(
          'type' => 'radio',
          'label' => $this->l('Select file format:'),
          'name' => 'format_file_update',
          'required' => true,
          'class' => 'format_file',
          'br' => true,
          'tab' => 'update',
          'form_group_class' => 'form_group_select_format',
          'values' => array(
            array(
              'id' => 'format_csv',
              'value' => 'csv',
              'label' => $this->l('CSV')
            ),
            array(
              'id' => 'format_xlsx',
              'value' => 'xlsx',
              'label' => $this->l('XLSX')
            )
          ),
          'desc' => $this->l('Choose a file format you wish to update'),
        ),
        array(
          'type' => 'file',
          'label' => $this->l('File'),
          'form_group_class' => 'form_group_upload_file',
          'name' => 'file',
          'tab' => 'update',
        ),
        array(
          'type' => 'select',
          'label' => $this->l('Language'),
          'name' => 'id_lang_update',
          'required' => true,
          'default_value' => (int)$this->context->language->id,
          'tab' => 'update',
          'form_group_class' => 'form_group_language',
          'options' => array(
            'query' => Language::getLanguages(),
            'id' => 'id_lang',
            'name' => 'name',
          ),
          'desc' => $this->l('Choose a language you wish to update'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Remove current images'),
          'name' => 'remove_images',
          'class' => 'remove_images',
          'form_group_class' => 'form_group_class_update',
          'is_bool' => true,
          'tab' => 'update',
          'values' => array(
            array(
              'id' => 'remove_images_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'remove_images_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('Before update remove current images'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Each product combinations  in a separate line'),
          'name' => 'separate_update',
          'class' => 'separate_update',
          'form_group_class' => 'form_group_class_update',
          'is_bool' => true,
          'tab' => 'update',
          'values' => array(
            array(
              'id' => 'separate_update_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'separate_update_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('If activated, a line will be created for each attributes of the products'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Disable modules hooks for update'),
          'name' => 'disable_hooks',
          'class' => 'disable_hooks',
          'form_group_class' => 'form_group_class_update',
          'is_bool' => true,
          'tab' => 'update',
          'values' => array(
            array(
              'id' => 'disable_hooks_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'disable_hooks_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
          'desc' => $this->l('This feature disable hooks in all modules during update that will increase products update speed'),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Only active products'),
          'name' => 'active_products',
          'class' => 'active_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'active_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'active_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),

        array(
          'type' => 'switch',
          'label' => $this->l('Only inactive products'),
          'name' => 'inactive_products',
          'class' => 'inactive_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'inactive_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'inactive_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),

        array(
          'type' => 'switch',
          'label' => $this->l('Products with EAN13'),
          'name' => 'ean_products',
          'class' => 'ean_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'ean_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'ean_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'switch',
          'label' => $this->l('Products with specific prices'),
          'name' => 'specific_prices_products',
          'class' => 'specific_prices_products',
          'form_group_class' => 'form_group_class_hide',
          'is_bool' => true,
          'tab' => 'filter_products',
          'values' => array(
            array(
              'id' => 'specific_prices_products_on',
              'value' => 1,
              'label' => $this->l('Enabled')
            ),
            array(
              'id' => 'specific_prices_products_off',
              'value' => 0,
              'label' => $this->l('Disabled')
            )
          ),
        ),
        array(
          'type' => 'html',
          'label' => $this->l('Products with pre-tax retail price'),
          'form_group_class' => 'form_group_class_hide',
          'tab' => 'filter_products',
          'name' => 'html_data',
          'html_content' => $this->priceSelection($priceSettings),
        ),
        array(
          'type' => 'html',
          'label' => $this->l('Products with quantity'),
          'form_group_class' => 'form_group_class_hide',
          'tab' => 'filter_products',
          'name' => 'html_data',
          'html_content' => $this->quantitySelection($quantitySettings),
        ),
        array(
          'type' => 'html',
          'label' => $this->l('Condition'),
          'form_group_class' => 'form_group_class_hide',
          'tab' => 'filter_products',
          'name' => 'html_data',
          'html_content' => $this->conditionBlock($condition),
        ),

        array(
          'type' => 'html',
          'label' => $this->l('Visibility'),
          'form_group_class' => 'form_group_class_hide',
          'tab' => 'filter_products',
          'name' => 'html_data',
          'html_content' => $this->visibilityBlock($visibility),
        ),
        array(
          'type' => 'html',
          'name' => 'html_data',
          'form_group_class' => 'exportFields',
          'tab' => 'filter_fields',
          'html_content' => $this->exportFields(),
        ),
        array(
          'type' => 'hidden',
          'name' => 'id_shop',
        ),
        array(
          'type' => 'hidden',
          'name' => 'last_id',
        ),
        array(
          'type' => 'hidden',
          'name' => 'last_id_update',
        ),
        array(
          'type' => 'hidden',
          'name' => 'base_url',
        ),
        array(
          'type' => 'hidden',
          'name' => 'shopGroupId',
        ),
        array(
          'type' => 'hidden',
          'name' => 'current_lang_id',
        ),

        array(
          'type' => 'html',
          'tab' => 'update_settings',
          'form_group_class' => 'save_settings_reset_filters',
          'name' => '<div class="url_base_setting"><a href="'.$url_base.'"><i class="icon-refresh process-icon-refresh"></i>'.$this->l('Reset filters').'</a></div>'
        ),
        array(
          'label' => $this->l('Settings name'),
          'type' => 'text',
          'name' => 'save_setting_update',
          'tab' => 'update_settings',
          'form_group_class' => 'update_settings_form',
        ),
        array(
          'type' => 'html',
          'tab' => 'update_settings',
          'form_group_class' => 'saveSettingsUpdateButton',
          'name' => '<button type="button" class="btn btn-default saveSettingsUpdate" style="padding: 4px 30px;font-size: 16px;">'.$this->l('Save').'</button>'
        ),
        array(
          'type' => 'html',
          'tab' => 'update_settings',
          'form_group_class' => 'settingsAfter',
          'name' => '<div></div>'
        ),

        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'save_settings_reset_filters',
          'name' => '<div class="url_base_setting"><a href="'.$url_base.'"><i class="icon-refresh process-icon-refresh"></i>'.$this->l('Reset filters').'</a></div>'
        ),
        array(
          'label' => $this->l('Settings name'),
          'type' => 'text',
          'form_group_class' => 'new_settings_form',
          'name' => 'save_setting',
          'tab' => 'new_settings',
        ),
        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'saveSettingsExportButton',
          'name' => '<button type="button" class="btn btn-default saveSettingsExport" style="padding: 4px 30px;font-size: 16px;">'.$this->l('Save').'</button>'
        ),
        array(
          'type' => 'html',
          'tab' => 'new_settings',
          'form_group_class' => 'settingsAfter',
          'name' => '<div></div>'
        ),

        array(
          'type' => 'html',
          'form_group_class' => 'form_group_list_settings',
          'tab' => 'new_settings',
          'name' => 'html_data',
          'html_content' => $this->listSettings($id, false, 'new_settings'),
        ),
        array(
          'type' => 'html',
          'form_group_class' => 'form_group_list_settings',
          'tab' => 'update_settings',
          'name' => 'html_data',
          'html_content' => $this->listSettings(false, $idUpdate, 'update_settings'),
        ),
      ),
    );

    $this->fields_form[1]['form'] = array(
      'input' => array(
        array(
          'type' => 'html',
          'name' => 'html_data',
          'form_group_class' => 'exportButton',
          'html_content' => '<button type="button" class="btn btn-default export">'.$this->l('Export').'</button>'
        ),
      ),
    );

    $this->fields_form[2]['form'] = array(
      'input' => array(
        array(
          'type' => 'html',
          'name' => 'html_data',
          'form_group_class' => 'updateButton',
          'html_content' => '<button type="button" class="btn btn-default update">'.$this->l('Update').'</button>'
        ),
      ),
    );

    $helper = new HelperForm();
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->fields_value['last_id'] = $last_id;
    $helper->fields_value['last_id_update'] = $last_id_update;
    $helper->fields_value['id_shop'] = $this->_shopId;
    $helper->fields_value['base_url'] = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');
    $helper->fields_value['shopGroupId'] = $this->_shopGroupId;
    $helper->fields_value['id_lang'] = Context::getContext()->language->id;
    $helper->fields_value['search_field'] = '';
    $helper->fields_value['current_lang_id'] = Context::getContext()->language->id;



    if(Tools::getValue('settings')){
      $config = Configuration::get('GOMAKOIL_LANG_CHECKED_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $type = Configuration::get('GOMAKOIL_TYPE_FILE_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $separate = Configuration::get('GOMAKOIL_SEPARATE_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $name_setting = Configuration::get('GOMAKOIL_NAME_SETTING_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $active = Configuration::get('GOMAKOIL_ACTIVE_PRODUCTS_SETTING_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $inactive = Configuration::get('GOMAKOIL_INACTIVE_PRODUCTS_SETTING_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $ean_products = Configuration::get('GOMAKOIL_EAN_PRODUCTS_SETTING_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $specific_prices_products = Configuration::get('GOMAKOIL_SPECIFIC_PRICES_PRODUCTS_SETTING_2_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['strip_tags'] = Configuration::get('GOMAKOIL_STRIP_TAGS_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['round_value'] = Configuration::get('GOMAKOIL_DESIMAL_POINTS_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['orderby'] = Configuration::get('GOMAKOIL_ORDER_BY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['orderway'] = Configuration::get('GOMAKOIL_ORDER_WAY_'.Tools::getValue('settings'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);

      $helper->fields_value['separate'] = $separate;
      $helper->fields_value['save_setting'] = $name_setting;
      $helper->fields_value['id_lang'] = $config;
      $helper->fields_value['active_products'] = $active;
      $helper->fields_value['inactive_products'] = $inactive;
      $helper->fields_value['ean_products'] = $ean_products;
      $helper->fields_value['specific_prices_products'] = $specific_prices_products;
      $helper->fields_value['name_file'] = $name;
      $helper->fields_value['name_export_file'] = $show;
      if($type){
        $helper->fields_value['format_file'] = $type;
      }
      else{
        $helper->fields_value['format_file'] = 'xlsx';
      }

    }
    else{
      $helper->fields_value['id_lang'] = Context::getContext()->language->id;
      $helper->fields_value['format_file'] = 'xlsx';
      $helper->fields_value['separate'] = 0;
      $helper->fields_value['save_setting'] = '';
      $helper->fields_value['active_products'] = 0;
      $helper->fields_value['inactive_products'] = 0;
      $helper->fields_value['ean_products'] = 0;
      $helper->fields_value['specific_prices_products'] = 0;
      $helper->fields_value['name_file'] = '';
      $helper->fields_value['name_export_file'] = 0;
      $helper->fields_value['strip_tags'] = 0;
      $helper->fields_value['round_value'] = '2';
      $helper->fields_value['orderway'] = 'asc';
      $helper->fields_value['orderby'] = 1;
    }

    if(Tools::getValue('settingsUpdate')){
      $config = Configuration::get('GOMAKOIL_LANG_CHECKED_UPDATE_'.Tools::getValue('settingsUpdate'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $type = Configuration::get('GOMAKOIL_TYPE_FILE_UPDATE_'.Tools::getValue('settingsUpdate'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $separate = Configuration::get('GOMAKOIL_SEPARATE_SETTING_UPDATE_'.Tools::getValue('settingsUpdate'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $disableHooks = Configuration::get('GOMAKOIL_DISABLE_HOOKS_SETTING_UPDATE_'.Tools::getValue('settingsUpdate'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $remove_images = Configuration::get('GOMAKOIL_REMOVE_IMAGES_SETTING_UPDATE_'.Tools::getValue('settingsUpdate'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $name_setting = Configuration::get('GOMAKOIL_NAME_SETTING_UPDATE_'.Tools::getValue('settingsUpdate'), '' ,$this->_shopGroupId, Context::getContext()->shop->id);
      $helper->fields_value['save_setting_update'] = $name_setting;
      $helper->fields_value['separate_update'] = $separate;
      $helper->fields_value['disable_hooks'] = $disableHooks;
      $helper->fields_value['remove_images'] = $remove_images;
      $helper->fields_value['id_lang_update'] = $config;
      if($type){
        $helper->fields_value['format_file_update'] = $type;
      }
      else{
        $helper->fields_value['format_file_update'] = 'xlsx';
      }
    }
    else{
      $helper->fields_value['format_file_update'] = 'xlsx';
      $helper->fields_value['save_setting_update'] = ' ';
      $helper->fields_value['separate_update'] = 0;
      $helper->fields_value['disable_hooks'] = 1;
      $helper->fields_value['remove_images'] = 0;
      $helper->fields_value['id_lang_update'] = Context::getContext()->language->id;
    }



    return $helper->generateForm($this->fields_form);
  }



  public function visibilityBlock($settings){
    $this->context->smarty->assign(
      array(
        'settings'   => $settings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockVisibility.tpl');
  }

  public function conditionBlock($settings){
    $this->context->smarty->assign(
      array(
        'settings'   => $settings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockCondition.tpl');
  }


  public function priceSelection($priceSettings){


    $this->context->smarty->assign(
      array(
        'priceSettings'   => $priceSettings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockSelectionPrice.tpl');
  }


  public function quantitySelection($quantitySettings){

    $this->context->smarty->assign(
      array(
        'quantitySettings'   => $quantitySettings,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/blockSelectionQuantity.tpl');
  }





  public function searchProducts($search,$id_shop, $id_lang, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $products = $this->_model->searchProduct($id_shop, $id_lang, $search);
    $products_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    $this->context->smarty->assign(
      array(
        'data'        => $products,
        'items_check' => $products_check,
        'name'        => 'products[]',
        'id'          => 'id_product',
        'title'       => 'name',
        'class'       => 'select_products'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function searchManufacturers($search, $id_shop, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $items = $this->_model->searchManufacturer($search);
    $items_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'manufacturers[]',
        'id'          => 'id_manufacturer',
        'title'       => 'name',
        'class'       => 'select_manufacturers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function searchSuppliers($search, $id_shop, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $items = $this->_model->searchSupplier($search);
    $items_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'suppliers[]',
        'id'          => 'id_supplier',
        'title'       => 'name',
        'class'       => 'select_suppliers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showCheckedProducts($id_shop, $id_lang, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $products_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    if( !$products_check ){
      $products_check = "";
    }
    $products = $this->_model->showCheckedProducts($id_shop, $id_lang, $products_check);
    $this->context->smarty->assign(
      array(
        'data'        => $products,
        'items_check' => $products_check,
        'name'        => 'products[]',
        'id'          => 'id_product',
        'title'       => 'name',
        'class'       => 'select_products'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showCheckedManufacturers($id_shop,$shopGroupId)
  {
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedManufacturers($items_check);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'manufacturers[]',
        'id'          => 'id_manufacturer',
        'title'       => 'name',
        'class'       => 'select_manufacturers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showCheckedSuppliers($id_shop, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedSuppliers($items_check);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'suppliers[]',
        'id'          => 'id_supplier',
        'title'       => 'name',
        'class'       => 'select_suppliers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showAllProducts($id_shop, $id_lang, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_PRODUCTS_CHECKED';
    $products_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    $products = $this->_model->showCheckedProducts($id_shop, $id_lang, false);
    $this->context->smarty->assign(
      array(
        'data'        => $products,
        'items_check' => $products_check,
        'name'        => 'products[]',
        'id'          => 'id_product',
        'title'       => 'name',
        'class'       => 'select_products'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }

  public function showAllManufacturers($id_shop, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_MANUFACTURERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedManufacturers(false);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'manufacturers[]',
        'id'          => 'id_manufacturer',
        'title'       => 'name',
        'class'       => 'select_manufacturers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }


  public function showAllSuppliers($id_shop, $shopGroupId)
  {
    $name_config = 'GOMAKOIL_SUPPLIERS_CHECKED';
    $items_check = Tools::unserialize(Configuration::get($name_config, '' ,$shopGroupId, $id_shop));
    if( !$items_check ){
      $items_check = "";
    }
    $items = $this->_model->showCheckedSuppliers(false);
    $this->context->smarty->assign(
      array(
        'data'        => $items,
        'items_check' => $items_check,
        'name'        => 'suppliers[]',
        'id'          => 'id_supplier',
        'title'       => 'name',
        'class'       => 'select_suppliers'
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/filterForm.tpl');
  }


  public function replaceConfigUpdate($id){

    $config = Configuration::get('GOMAKOIL_FIELDS_CHECKED_UPDATE_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED_UPDATE', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);

  }



  public function replaceConfig($id){

    $config = Configuration::get('GOMAKOIL_PRODUCTS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_PRODUCTS_CHECKED', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);

    $config = Configuration::get('GOMAKOIL_MANUFACTURERS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_MANUFACTURERS_CHECKED', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);

    $config = Configuration::get('GOMAKOIL_SUPPLIERS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_SUPPLIERS_CHECKED', $config, false, $this->_shopGroupId, Context::getContext()->shop->id);


    $config = Configuration::get('GOMAKOIL_CATEGORIES_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_CATEGORIES_CHECKED', $config, false,  $this->_shopGroupId, Context::getContext()->shop->id);

    $config = Configuration::get('GOMAKOIL_FIELDS_CHECKED_'.$id, '' ,$this->_shopGroupId, Context::getContext()->shop->id);
    Configuration::updateValue('GOMAKOIL_FIELDS_CHECKED', $config, false,  $this->_shopGroupId, Context::getContext()->shop->id);

  }


  public function listSettings($id, $idUpdate, $page){

    $setting = array();
    $setting_update = array();
    $all_setting =Tools::unserialize( Configuration::get('GOMAKOIL_ALL_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));


      $all_setting_update = Tools::unserialize( Configuration::get('GOMAKOIL_ALL_UPDATE_SETTINGS','',$this->_shopGroupId, Context::getContext()->shop->id));
      if($all_setting_update){
        foreach($all_setting_update as $value){
          $name_conf = 'GOMAKOIL_NAME_SETTING_UPDATE_'.$value;
          $name =  Configuration::get($name_conf,'',$this->_shopGroupId, Context::getContext()->shop->id);
          $setting_update[] = array(
            'id'    => $value,
            'name'  => $name

          );
        }
      }
      else{
        $setting = false;
      }

      if($all_setting){
        foreach($all_setting as $value){
          $name_conf = 'GOMAKOIL_NAME_SETTING_'.$value;
          $name =  Configuration::get($name_conf,'',$this->_shopGroupId, Context::getContext()->shop->id);
          $setting[] = array(
            'id'    => $value,
            'name'  => $name

          );
        }
      }
      else{
        $setting = false;
      }



    $this->context->smarty->assign(
      array(
        'id'              => $id,
        'page'              => $page,
        'idUpdate'        => $idUpdate,
        'setting_update'  => $setting_update,
        'setting'         => $setting,
        'base_url'        => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
      )
    );
    return $this->display(__FILE__, "views/templates/hook/listSettings.tpl");
  }


  public function exportFields()
  {
    $selected_fields = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED','',$this->_shopGroupId, Context::getContext()->shop->id));

    if(!$selected_fields){
        $selected_fields = array('id_product' => $this->l('Product ID'), 'id_product_attribute' => $this->l('Product Combinations ID'));
    }

    $all_available_fields = array_merge($this->_exportTabInformation, $this->_exportTabPrices, $this->_exportTabSeo, $this->_exportTabAssociations, $this->_exportTabShipping, $this->_exportTabCombinations, $this->_exportTabQuantities, $this->_exportTabImages, $this->_exportTabFeatures, $this->_exportTabCustomization, $this->_exportTabAttachments, $this->_exportTabSuppliers);
    $selected = array();

    foreach($selected_fields as $selected_field_key => $selected_field_value) {
      foreach ($all_available_fields as $field_data) {
        if ($selected_field_key == $field_data['val']) {
            $hint = !empty($field_data['hint']) ? $field_data['hint'] : '';
            $disabled = !empty($field_data['disabled']) ? true : false;

            $selected[$field_data['val']] = array('name' => $field_data['name'], 'hint' => $hint, 'tab' => $field_data['tab'], 'disabled' => $disabled);
        }
      }
    }

    $all_fields = array(
      'exportTabInformation'    => $this->_exportTabInformation,
      'exportTabPrices'         => $this->_exportTabPrices,
      'exportTabSeo'            => $this->_exportTabSeo,
      'exportTabAssociations'   => $this->_exportTabAssociations,
      'exportTabShipping'       => $this->_exportTabShipping,
      'exportTabCombinations'   => $this->_exportTabCombinations,
      'exportTabQuantities'     => $this->_exportTabQuantities,
      'exportTabImages'         => $this->_exportTabImages,
      'exportTabFeatures'       => $this->_exportTabFeatures,
      'exportTabCustomization'  => $this->_exportTabCustomization,
      'exportTabAttachments'    => $this->_exportTabAttachments,
      'exportTabSuppliers'      => $this->_exportTabSuppliers,
    );

    $this->context->smarty->assign(
      array(
        'set'                   => $selected_fields,
        'selected'              => $selected,
        'url_base'              => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&configure=updateproducts',
        'all_fields'            => $all_fields,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/selectFieldsExport.tpl');
  }

  public function  updateFields(){

    $set = Tools::unserialize(Configuration::get('GOMAKOIL_FIELDS_CHECKED_UPDATE','',$this->_shopGroupId, Context::getContext()->shop->id));
    if(!$set){
      $set = array('id_product' => $this->l('Product ID'), 'id_product_attribute' => $this->l('Product Combinations ID'));
    }
    $selected = array();
    if($set){
      $newArray = array_merge($this->_updateTabInformation, $this->_updateTabPrices, $this->_updateTabSeo, $this->_updateTabAssociations, $this->_updateTabShipping, $this->_updateTabCombinations, $this->_updateTabQuantities, $this->_updateTabImages, $this->_updateTabFeatures, $this->_updateTabCustomization, $this->_updateTabAttachments, $this->_updateTabSuppliers);
      foreach($set as $value) {
        foreach ($newArray as $new) {
          if ($value == $new['name']) {
            if(isset($new['hint']) && $new['hint']){
              $hint = $new['hint'];
            }
            else{
              $hint = '';
            }
            if(isset($new['disabled']) && $new['disabled']){
              $disabled = true;
            }
            else{
              $disabled = false;
            }
            $selected[$new['val']] = array('name' => $new['name'], 'hint' => $hint, 'tab' => $new['tab'], 'disabled' => $disabled);
          }
        }
      }
    }
    else{
      $set = false;
    }

    $all_fields = array(
      'updateTabInformation'    => $this->_updateTabInformation,
      'updateTabPrices'         => $this->_updateTabPrices,
      'updateTabSeo'            => $this->_updateTabSeo,
      'updateTabAssociations'   => $this->_updateTabAssociations,
      'updateTabShipping'       => $this->_updateTabShipping,
      'updateTabCombinations'   => $this->_updateTabCombinations,
      'updateTabQuantities'     => $this->_updateTabQuantities,
      'updateTabImages'         => $this->_updateTabImages,
      'updateTabFeatures'       => $this->_updateTabFeatures,
      'updateTabCustomization'  => $this->_updateTabCustomization,
      'updateTabAttachments'    => $this->_updateTabAttachments,
      'updateTabSuppliers'      => $this->_updateTabSuppliers,
    );

    $this->context->smarty->assign(
      array(
        'set'                   => $set,
        'selected'              => $selected,
        'url_base'              => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&configure=updateproducts',
        'all_fields'            => $all_fields,
      )
    );
    return $this->display(__FILE__, 'views/templates/hook/selectFieldsUpdate.tpl');
  }

}
