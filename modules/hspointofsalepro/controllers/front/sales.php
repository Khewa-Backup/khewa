<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

/**
 * Front controller - Point Of Sale.
 */
class HsPointOfSaleProSalesModuleFrontController extends PosModuleFrontController
{
    /**
     * Static actions are ones those don't require initializing context (cart, customer).
     *
     * @var array
     *            <pre>
     *            array(
     *            string,
     *            string
     *            )
     */
    protected $static_actions = array(
        'search',
        'getCombinations',
        'getDiscountInfo',
    );

    /**
     * An actual action which DOES something.
     *
     * @var string
     */
    protected $sub_action = null;

    /**
     * assign value and status when process ajax.
     *
     * @var array
     */
    public $ajax_json = array(
        'success' => false,
        'message' => null,
        'data' => array(),
    );

    /**
     * Validate login when redirecting from BO.
     *
     * @var string
     */
    protected $token;

    /**
     * @see parent:__construct()
     */
    public function __construct()
    {
        parent::__construct();
        $this->display_header = false;
        $this->display_footer = false;
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->action = Tools::getValue('action');
        $this->token = Tools::getValue('token');
        if (!file_exists(_PS_ROOT_DIR_ . '/override/classes/Cart.php')) {
            copy(_ROCKPOS_DIR_ . '/override/classes/Cart.php', _PS_ROOT_DIR_ . '/override/classes/Cart.php');
        }
        if (!headers_sent()) {
            header('Login: true');
        }
    }

    /**
     * @see parent::init()
     */
    public function init()
    {
        parent::init();
        // clear session if change current shop


        if (Tools::getValue('setShopContext')) {
            $this->module->clearSession();
        }
        if ($this->token) {
            $this->module->validateToken($this->token);
        }

        // For all actions, by default, force to false, and set message to this one
        $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
        $this->action = Tools::getValue('action');
        $this->sub_action = Tools::getValue('sub_action', null);
        if ($this->sub_action) {
            $this->sub_action = Tools::strtolower($this->sub_action);
        }

        // Process changing currency
        if ($this->action == 'currency' && $this->sub_action == 'change' && $id_currency = (int) Tools::getValue('id_currency', 0)) {
            $this->context->cookie->id_currency = $id_currency;
        }

        if (!$this->action || !in_array($this->action, $this->static_actions)) {
            $this->module->initContext();
        }

        // install payment for current shop if the current shop don't have any payment methods
        $payments_shop = PosPayment::getPosPayments(null, $this->context->shop->id);
        if (empty($payments_shop)) {
            // duplicate all the current pos payment to current shop
            PosPayment::syncPaymentsShop($this->context->shop->id);
        }
    }

    /**
     * @see parent::initContent
     */
    public function initContent()
    {
        $this->clear();
        $title = array(
            $this->module->displayName,
            'v' . $this->module->version,
        );
        $this->context->smarty->assign(array(
            'title' => implode(' ', $title),
            'css_path' => $this->module->getCSSPath(),
            'js_path' => $this->module->getJsPath(),
            'ready_to_go_mixpanel' => PosMixPanel::readyToGo(),
            'file_version' => $this->module->getFileVersion(),
        ));
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/sales.tpl');
        } else {
            $this->setTemplate('sales.tpl');
        }
    }

    /**
     * @input
     * {
     *      lang_keys:[string, string, ...],
     *      action: "getAllInOne",
     *      ajax: "1"
     * }
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          env: {
     *              filter_products: {
     *                  categories:[{
     *                      id_category: int,
     *                      name: string
     *                  }],
     *                  id_default_category: int,
     *                  root_category: {
     *                      id: int,
     *                      name: string
     *                  },
     *                  cache: {
     *                      products: [{
     *                          id_product: int
     *                          reference: string
     *                          link_rewrite: string
     *                          id_shop: int
     *                          id_product_attribute: int
     *                          has_combinations: boolean
     *                          price: float
     *                          image_url: string
     *                          name: string
     *                          short_name: string
     *                      }],
     *                      total_products: int
     *                  }
     *              },
     *              lang: [
     *                  {
     *                      string => string // key => translation
     *                  }
     *              ],
     *              payment_methods: [
     *                  {
     *                      id_payment: int,
     *                      label: string
     *                  }
     *              ],
     *              order_states: [
     *                  {
     *                      id_order_state: int,
     *                      color: string,
     *                      name: string,
     *                      is_default: boolean,
     *                      is_default_preorder: boolean,
     *                  }
     *              ],
     *              currencies: [
     *                  {
     *                      id_currency: int,
     *                      name: string,
     *                      iso_code: string,
     *                      iso_code_num: string,
     *                      sign: string,
     *                      blank: int,
     *                      format: int,
     *                      decimals: int,
     *                      round_mode: int
     *                  }
     *              ],
     *              discount_types: {
     *                  product: [
     *                      {
     *                          name: string,
     *                          value: string,
     *                          is_default: boolean
     *                      }
     *                  ],
     *                  order: [
     *                      {
     *                          name: string,
     *                          value: string
     *                          is_default: boolean
     *                      }
     *                  ]
     *              },
     *              countries: {
     *                  {
     *                     id_country => int,
     *                     name => string,
     *                     need_zip_code=> int,
     *                     zip_code_format => string,
     *                     display_tax_label => int,
     *                     need_identification_number => int,
     *                     contains_states => int,
     *                     states => [
     *                          int => {
     *                              id_state => int,
     *                              name => string
     *                           }
     *                     ]
     *                   }
     *              },
     *              customer_groups: [
     *                  {
     *                      id_group: int,
     *                      name: string,
     *                      is_default: boolean,
     *                      is_pos: boolean
     *                  }
     *              ],
     *              genders: [
     *                 {
     *                    id_gender: int,
     *                    name: string
     *                 }
     *              ]
     *          },
     *          transaction: {
     *              meta: {
     *                  currency: array()// an element of PosCurency::getAll()
     *              }
     *              note: {
     *                  note: string,
     *                  private: boolean
     *              },
     *              paid_payments: [
     *                  {
     *                  id_cart_payment: int,
     *                  id_payment: 1,
     *                  amount: float,
     *                  given_money: float,
     *                  change: float,
     *                  payment_name: string
     *                  }
     *              ],
     *              cart: {
     *                  id_cart: int,
     *                  id_customer: int,
     *                  id_carrier: int,
     *                  id_address_delivery: int,
     *                  id_address_invoice: int,
     *                  products: [
     *                      {
     *                          id_product_attribute: int,
     *                          id_product: int,
     *                          name: string,
     *                          short_description: html,
     *                          image_url: string,
     *                          combination: string,
     *                          short_combination: string,
     *                          quantity: int,
     *                          price_without_reduction: float,
     *                          price_with_reduction: float,
     *                          reduction_type: string,
     *                          reduction: float
     *                      }
     *                  ],
     *                  discounts: [
     *                      {
     *                          id_cart_rule: int,
     *                          code: string,
     *                          reduction_percent: float,
     *                          reduction_amount: float,
     *                          name: string,
     *                          discounted_value: float
     *                      }
     *                  ],
     *                  total: {
     *                      product: float,
     *                      discount: float,
     *                      tax: float,
     *                      shipping: float,
     *                      order: float,
     *                  },
     *                  shipping: {
     *                      id_carrier: int,
     *                      free: boolean
     *                  }
     *              },
     *              customer: {
     *                  firstname: string,
     *                  lastname: string,
     *                  email: string,
     *                  phone: string,
     *                  website: string,
     *                  company: string,
     *                  siret: string,
     *                  ape: string,
     *                  note: string,
     *                  is_default: boolean,
     *                  tax_enabled: boolean,
     *                  tax_included: boolean
     *                  addresses: [
     *                      {
     *                          id_address: int,
     *                          alias: string,
     *                          company: string,
     *                          lastname: string,
     *                          firstname: string,
     *                          address1: string,
     *                          address2: string,
     *                          postcode: string,
     *                          city: string,
     *                          phone: string,
     *                          phone_mobile: string,
     *                          vat_number: string,
     *                          dni: string,
     *                          id_country: int,
     *                          id_state: string,
     *                      }
     *                  ],
     *                  carriers: [
     *                      {
     *                          name: string,
     *                          delivery: string,
     *                          id_carrier: int,
     *                          is_default: boolean
     *                      }
     *                  ]
     *              },
     *              order: {
     *                  reference: boolean,
     *                  invoice: boolean,
     *                  invoice_auto_print: boolean,
     *                  invoice_url: string,
     *                  receipt: boolean,
     *                  receipt_auto_print: boolean,
     *                  receipt_url: boolean
     *              }
     *          },
     *          plugins: {
     *              filter_products: {
     *                  total_products => int,
     *                  products => [{
     *                      id_product: int,
     *                      reference: string,
     *                      link_rewrite: string,
     *                      name: string,
     *                      short_name: string,
     *                      id_image: int,
     *                      price: float,
     *                      image_url: string,
     *                      has_combinations: boolean
     *                  }]
     *              },
     *              multiple_carts: {
     *                  carts:[
     *                      {
     *                          id: int,
     *                          id_address_delivery: int,
     *                          id_address_invoice: int,
     *                          id_currency: int,
     *                          total: float,
     *                          id_customer: int,
     *                          firstname: string,
     *                          lastname: string,
     *                          email: string
     *                      }
     *                  ]
     *              },
     *              order_history: {
     *                       orders:[
     *                          {
     *                              reference: string,
     *                              date_upd: datetime,
     *                              total_paid: float,
     *                              payment: string,
     *                              status: string,
     *                              url: string,
     *                          }
     *                      ],
     *              },
     *          }
     *      }
     * }
     * </pre>
     */
    public function ajaxProcessInitShop()
    {
        $root_category = PosCategory::getRootCategory();
        $default_category = Configuration::get('POS_DEFAULT_CATEGORIES') ? explode(
            ',',
            Configuration::get('POS_DEFAULT_CATEGORIES')
        ) : array();
        $employee_shops = array();
        if (isset($this->context->employee)) {
            $employee_shops = $this->context->employee->getListShops($this->context->employee->id);
        }
        $expiring_days = Tools::getValue('expiring_days', 0);
        $version = Tools::getValue('version', 'lite');
        $this->ajax_json = array(
            'success' => true,
            'message' => $this->module->i18n['all_set'],
            'data' => array(
                'env' => array(
                    'payment_methods' => PosPayment::getAll($this->context->language->id, $this->context->shop->id),
                    'standard_order_states' => PosOrderState::getOrderStates($this->context->language->id),
                    'pre_order_states' => PosOrderState::getOrderStates($this->context->language->id, false),
                    'currencies' => PosCurrency::getAll($this->context->shop->id),
                    'is_customer_indexing_enabled' => true,
                    'discount_types' => array(
                        'product' => $this->module->getProductDiscountTypes(),
                        'cart' => $this->module->getOrderDiscountTypes(),
                    ),
                    'product_search_by' => $this->module->getProductSearchBy(),
                    'countries' => $this->getCountries(),
                    'customer_groups' => $this->getCustomerGroups(),
                    'genders' => $this->getGenders(),
                    'configurations' => $this->getConfigurations(),
                    'is_logged' => $this->module->is_logged,
                    'languages' => $this->module->getAvailableLanguages(),
                    'sound_url' => $this->module->getMediaPath() . PosConstants::BEEP_FILE,
                    'employee' => $this->module->getEmployeeInfo(),
                    'employeeShops' => $employee_shops,
                    'product_note' => PosConfiguration::get('POS_PRODUCT_NOTE'),
                    'total_order' => PosOrder::getNbFreeOrders($this->module, $expiring_days, $version),
                    'permission' => $this->getPermission(),
                    'warning_sound' => (int) Configuration::get('POS_WARNING_SOUND'),
                ),
                'transaction' => $this->getTransactionData(),
                'plugins' => array(
                    'filter_products' => array(
                        'categories' => PosCategory::getTreeCategories(),
                        'id_default_category' => $default_category,
                        'root_category' => array(
                            'id' => $root_category->id,
                            'name' => $root_category->name,
                        ),
                        'cache' => $default_category ? PosCategory::searchProductsById($default_category, 1) : array(),
                    ),
                    'multiple_carts' => array(
                        'carts' => $this->getActiveCarts(),
                    ),
                    'order_history' => array(
                        'orders' => PosOrder::getOrdersByIdCustomer($this->context->cart->id_customer),
                        'number_orders' => PosOrder::getCustomerNbOrders($this->context->cart->id_customer),
                    ),
                    'abandoned_carts' => $this->getAbandonedCarts($this->context->cart->id_customer),
                    'loyalty' => array(
                        'loyalty' => PosLoyalty::getLoyalty($this->context->customer, $this->context),
                    ),
                    'sales_commission' => array(
                        'employees' => PosSalesCommission::getSalesManByIdCart($this->context->cart->id),
                    ),
                    'custom_product' => array(
                        'tax_rules_groups' => $this->getTaxRulesGroups(),
                        'visibilities' => $this->getVisibilities(),
                    ),
                    'custom_sale' => array(
                        'tax_rules_groups' => $this->getTaxRulesGroups(),
                        'tax_included' => (int) Configuration::get('POS_DEFAULT_TAX_INCLUDED'),
                    ),
                    'return' => array(),
                ),
            ),
        );
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               changeProductPrice => boolean,
     *               giveDiscount => boolean,
     *               completeOrder => boolean,
     *               )
     */
    protected function getPermission()
    {
        $permission = array();
        $configuration_prermissions = Tools::jsonDecode(Configuration::get('POS_PERMISSIONS'), true);
        foreach (PosProfile::$sale_permissions as $sale) {
            if ($this->context->employee->isSuperAdmin()) {
                $permission[$sale] = 1;
            } else {
                $permission[$sale] = isset($configuration_prermissions['sales'][$sale][$this->context->employee->id_profile]) ? $configuration_prermissions['sales'][$sale][$this->context->employee->id_profile] : 0;
            }
        }

        return $permission;
    }

    /**
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          env: {
     *              lang: [
     *                  {
     *                      string => string // key => translation
     *                  }
     *              ],
     *              is_logged => boolean,
     *              home_page_url: string
     *          }
     *      }
     * }
     */
    public function ajaxProcessInitApp()
    {
        $id_employee = $this->module->getIdEmployeeByPersonalSetting();
        $lang_keys = Tools::jsonDecode(Tools::getValue('lang_keys'), true);
        $this->ajax_json = array(
            'success' => true,
            'message' => $this->module->i18n['all_set'],
            'data' => array(
                'env' => array(
                    'lang' => $lang_keys ? $this->module->getTranslations($lang_keys) : array(),
                    'is_logged' => $this->module->is_logged,
                    'home_page_url' => Tools::getShopDomain(true, true) . __PS_BASE_URI__,
                    'shop' => $this->context->shop,
                    'is_device_computer' => $this->module->isDeviceComputer(),
                    'print_in_pdf' => (int) PosConfiguration::getValueByEmployeeId($id_employee, 'POS_PRINT_IN_PDF'),
                    'tracking_data' => array(
                        'ready_to_go' => PosMixPanel::readyToGo(),
                        'nb_orders' => array(
                            'domain' => Tools::getShopDomain(),
                            'nb_orders' => PosMixPanel::readyToGo() ? PosOrder::getNbOrdersByModule($this->module->name) : 0,
                        ),
                        'settings' => PosMixPanel::readyToGo() ? PosConfiguration::getTrackingSettings() : array(),
                    ),
                ),
            ),
        );
    }

    /**
     * input
     * {
     *      action: "MultipleCarts",
     *      sub_action: "getList",
     *      ajax: "1"
     * }.
     *
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string
     *      data:{
     *          carts:[
     *              {
     *                  id: int,
     *                  id_address_delivery: int,
     *                  id_address_invoice: int,
     *                  id_currency: int,
     *                  total: float,
     *                  id_customer: int,
     *                  firstname: string,
     *                  lastname: string,
     *                  email: string
     *              }
     *          ]
     *      }
     * }
     */
    public function ajaxProcessMultipleCarts()
    {
        switch ($this->sub_action) {
            case 'getList':
                $this->ajax_json = array(
                    'success' => true,
                    'message' => '',
                    'data' => array(
                        'carts' => $this->getActiveCarts(),
                    ),
                );
                break;
            default:
                break;
        }
    }

    /**
     * @input
     * {
     *      action: "customProduct",
     *      sub_action: add,
     *      sell_only: boolean,
     *      save_sell: boolean,
     *      ajax: "1"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data:{
     *          transaction: {
     *              customer: {} // Refer to action "getAllInOne" for further details, if success
     *              cart: {} // Refer to action "getAllInOne" for further details, if success
     *          }
     *      }
     * }
     */
    public function ajaxProcessCustomProduct()
    {
        switch ($this->sub_action) {
            case 'add':
                $address = new Address();
                $address->hydrate($this->module->getDefaultAddress());
                $sell_only = Tools::getValue('sell_only');
                $save_sell = Tools::getValue('save_sell');
                $product = new PosProduct();
                $this->copyFromPost($product, 'product');
                $product->tax_included = (bool) Tools::getValue('tax_included');
                $product->vat_address = $address;
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    $product->link_rewrite[(int) $language['id_lang']] = !empty($product->name[(int) $language['id_lang']]) ? Tools::link_rewrite($product->name[(int) $language['id_lang']]) : '';
                }

                if ($sell_only) {
                    $product->active = 0;
                    $product->visibility = 'none';
                }
                $id_categories = Tools::getValue('id_categories');
                if (!is_array($id_categories) || empty($id_categories)) {
                    $id_categories = array((int) Category::getRootCategory()->id);
                }
                try {
                    if ($product->add()) {
                        $product->updateCategories($id_categories);
                        $product->setGroupReduction();
                        StockAvailable::setQuantity($product->id, 0, (int) Tools::getValue('quantity'));
                        if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT')) {
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                $warehouse_location_entity = new WarehouseProductLocation();
                                $warehouse_location_entity->id_product = $product->id;
                                $warehouse_location_entity->id_product_attribute = 0;
                                $warehouse_location_entity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
                                $warehouse_location_entity->location = pSQL('');
                                $warehouse_location_entity->save();
                            }
                        }
                        if ($sell_only || $save_sell) {
                            $this->module->addProductToCart($product->id);
                            if ($sell_only) {
                                $custom_product = new PosCustomProduct();
                                $custom_product->id_cart = (int) $this->context->cart->id;
                                $custom_product->id_product = $product->id;
                                $custom_product->add();
                            }
                        }
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['message'] = $this->module->i18n['this_product_is_saved_successfully'];
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['there_was_an_error_saving_this_product'];
                    }
                } catch (Exception $ex) {
                    $this->ajax_json['message'] = $this->module->i18n['there_was_an_error_saving_this_product'];
                }
                break;
            default:
                break;
        }
        $this->ajax_json['data']['transaction']['customer']['carriers'] = $this->context->cart->getCarriers(
            $this->context->language->id,
            true
        );
        $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails();
    }

    /**
     * @input
     * {
     *      action: "customSale",
     *      sub_action: add,
     *      name: string,
     *      quantity: int,
     *      id_tax_rule_group: int
     *      ajax: "1"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data:{
     *          transaction: {
     *              customer: {} // Refer to action "getAllInOne" for further details, if success
     *              cart: {} // Refer to action "getAllInOne" for further details, if success
     *          }
     *      }
     * }
     */
    public function ajaxProcessCustomSale()
    {
        switch ($this->sub_action) {
            case 'add':
                $address = new Address();
                $address->hydrate($this->module->getDefaultAddress());
                $product = new PosProduct();
                $this->copyFromPost($product, 'product');
                $product->vat_address = $address;
                $languages = Language::getLanguages(false);
                $product_name = array();
                foreach ($languages as $language) {
                    $product_name[$language['id_lang']] = Tools::getValue('name');
                    $product->link_rewrite[(int) $language['id_lang']] = !empty($product_name[(int) $language['id_lang']]) ? Tools::link_rewrite($product_name[(int) $language['id_lang']]) : '';
                }
                $product->name = $product_name;
                $product->active = 0;
                $default_category = Category::getRootCategory();
                $product->id_category_default = $default_category->id;
                $product->visibility = 'none';
                try {
                    if ($product->add()) {
                        $product->addCategory($default_category->id);
                        Configuration::updateValue('POS_DEFAULT_TAX_INCLUDED', $product->tax_included);
                        $product->setGroupReduction();
                        StockAvailable::setQuantity($product->id, 0, (int) Tools::getValue('quantity'));
                        if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT')) {
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                $warehouse_location_entity = new WarehouseProductLocation();
                                $warehouse_location_entity->id_product = $product->id;
                                $warehouse_location_entity->id_product_attribute = 0;
                                $warehouse_location_entity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
                                $warehouse_location_entity->location = pSQL('');
                                $warehouse_location_entity->save();
                            }
                        }

                        $this->ajax_json['success'] = $this->module->addProductToCart($product->id);
                        if ($this->ajax_json['success']) {
                            $custom_product = new PosCustomProduct();
                            $custom_product->id_cart = (int) $this->context->cart->id;
                            $custom_product->id_product = $product->id;
                            $custom_product->add();
                            $this->ajax_json['success'] = true;
                            $this->ajax_json['message'] = $this->module->i18n['this_product_is_saved_successfully'];
                            $productReal = new product($product->id);
                            $productReal->state = '9';
                            $productReal->save();
                        }
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['there_was_an_error_saving_this_product'];
                    }
                } catch (Exception $ex) {
                    $this->ajax_json['message'] = $this->module->i18n['there_was_an_error_saving_this_product'];
                }
                break;
            default:
                break;
        }
        $this->ajax_json['data']['transaction']['customer']['carriers'] = $this->context->cart->getCarriers(
            $this->context->language->id,
            true
        );
        $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails();
        $this->ajax_json['data']['plugins']['customsale']['tax_included'] = (int) Configuration::get('POS_DEFAULT_TAX_INCLUDED');
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               'id_tax_rules_group' => int
     *               'name' => string
     *               )
     *               ...
     *               )
     */
    protected function getTaxRulesGroups()
    {
//        $default_tax = array(
//            array(
//                'name' => $this->module->i18n['no_tax'],
//                'id_tax_rules_group' => 0,
//                'rate' => 0,
//                'active' => 1,
//            ),
//        );
        $default_tax = array(
            array(
                'name' => 'CA Standard Rate',
                'id_tax_rules_group' => 0,
                'rate' => '14.975',
                'active' => 1,
            ),
        );
        $address = new PosAddress((int) $this->context->cart->id_address_delivery);
        if (!Validate::isLoadedObject($address)) {
            $default_address = $this->module->getDefaultAddress();
            if (!empty($default_address)) {
                $address->hydrate($default_address);
            }
        }

        $filtered_tax_rule_groups =  PosTaxRulesGroup::getAssociatedTaxRates($address->id_country, $this->context->cart->id_shop, $address->id_state);


//        $tax_rules_groups = array_merge(
//            $default_tax,
//            PosTaxRulesGroup::getAssociatedTaxRates($address->id_country, $this->context->cart->id_shop, $address->id_state)
//        );


        foreach ($filtered_tax_rule_groups as $filtered_tax_rule_group) {
            if($filtered_tax_rule_group['name'] != 'CA Standard Rate'){
                $custom_filtered_tax_rule_groups[] = $filtered_tax_rule_group;
            }
        }

        $tax_rules_groups = array_merge(
            $default_tax,
            $custom_filtered_tax_rule_groups
        );



        foreach ($tax_rules_groups as &$tax_rules_group) {
            unset($tax_rules_group['active']);
        }




        return $tax_rules_groups;
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               string => string,//visiblity => label
     *               ...
     *               )
     */
    public function getVisibilities()
    {
        return array(
            'both' => $this->module->i18n['everywhere'],
            'search' => $this->module->i18n['search_only'],
            'catalog' => $this->module->i18n['catalog_only'],
            'none' => $this->module->i18n['nowhere'],
        );
    }

    protected function clear()
    {
        return true;
        if (date('Y-m-d') != date('Y-m-d', (int) Configuration::get('CUSTOM_PRODUCT_CRON_LAST_RUN'))) {
            PosCustomProduct::deleteOutOfDateProducts();
            Configuration::updateValue('CUSTOM_PRODUCT_CRON_LAST_RUN', strtotime(date('Y-m-d')));
        }
    }

    /**
     * input
     * {
     *      action: "changeEmployee",
     *      ajax: "1"
     *      id_employee: int
     * }.
     *
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string
     *      data:{
     *      }
     * }
     */
    public function ajaxProcessChangeEmployee()
    {
        $id_employee = (int) Tools::getValue('id_employee', 0);
        $pos_sales_commission = new PosSalesCommission($this->context->cart->id);
        $pos_sales_commission->id_employee = $id_employee;
        $this->ajax_json['success'] = $pos_sales_commission->update();

        if (!$this->ajax_json['success']) {
            $this->ajax_json['message'] = $this->module->i18n['cannot_add_sales_commission'];
        }
    }

    /**
     * input
     * {
     *      action: "getLoyalty",
     *      ajax: "1"
     * }.
     *
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string
     *      data:{
     *          loyalty:{
     *               points: int,
     *               value: float
     *          }
     *      }
     * }
     */
    public function ajaxProcessGetLoyalty()
    {
        $this->ajax_json = array(
            'success' => true,
            'message' => '',
            'data' => array(
                'loyalty' => PosLoyalty::getLoyalty($this->context->customer, $this->context),
            ),
        );
    }

    /**
     * input
     * {
     *      action: "convertAndApply",
     *      ajax: "1"
     * }.
     *
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string
     *      data:{
     *          transaction: {
     *              cart: {
     *                  discounts: [
     *                      {
     *                          id_cart_rule: int,
     *                          code: string,
     *                          reduction_percent: float,
     *                          reduction_amount: float,
     *                          name: string,
     *                          discounted_value: float
     *                      }
     *                  ],
     *                  total: {
     *                      product: float,
     *                      discount: float,
     *                      tax: float,
     *                      shipping: float,
     *                      order: float,
     *                  }
     *              }
     *          }
     *      }
     * }
     */
    public function ajaxProcessConvertAndApply()
    {
        if (!PosLoyalty::loadLoyalty()) {
            return;
        }

        if (!$this->context->customer->isDefaultCustomer($this->context->employee->id, $this->context->shop->id)) {
            $selected_loyalty_categories = PosLoyalty::getRestrictedIdCategories();
            $category_product_ids = PosCategory::getIdProducts($selected_loyalty_categories);
            $cart_product_ids = $this->context->cart->getIdProducts();
            $product_ids = array_intersect($cart_product_ids, $category_product_ids);
            if (empty($product_ids)) {
                $this->ajax_json['success'] = false;
                $this->ajax_json['message'] = $this->module->i18n['you_cannot_apply_loyalty_points_on_these_products'];
            } else {
                $loyalty = PosLoyalty::getInstance();
                $points = (int) $loyalty->getLoyaltyPoints($this->context->customer);
                if ($points > 0) {
                    $amount = $loyalty->convertPoints((int) $points);
                    $cart_rule = PosCartRule::generateCartRule(
                        $amount,
                        $loyalty->voucher_code,
                        (int) $this->context->customer->id,
                        (int) $this->context->currency->id
                    );
                    // Register order(s) which contributed to create this voucher
                    if (Validate::isLoadedObject($cart_rule)) {
                        if ($cart_rule->checkValidity($this->context, false, false) && $this->context->cart->addCartRule((int) $cart_rule->id)) {
                            $this->ajax_json['success'] = true;
                            $this->ajax_json['message'] = sprintf(
                                $this->module->i18n['just_applied_a_voucher_of'],
                                Tools::displayPrice($amount)
                            );
                        } else {
                            $this->ajax_json['success'] = false;
                            $this->ajax_json['message'] = $this->module->i18n['you_cannot_use_this_voucher_with_these_products'];
                        }
                    } elseif (Validate::isLoadedObject($cart_rule)) {
                        $cart_rule->delete();
                    }
                }
            }
        }
        $this->ajax_json['data'] = array(
            'transaction' => array(
                'cart' => array(
                    'discounts' => $this->module->getCartRules(),
                    'total' => $this->getCartTotal(),
                ),
                'customer' => array(
                    'cart_rules' => $this->module->getAvailableCartRules(),
                ),
            ),
        );
    }

    /**
     * input
     * {
     *      $limit: ,
     *      action: "ordersHistory
     *      ajax: "1"
     *      page: int
     * }.
     *
     * @output
     * <pre>
     * {
     *       orders:[
     *              {
     *                  reference: string,
     *                  date_upd: datetime,
     *                  total_paid: float,
     *                  payment: string,
     *                  status: string,
     *                  url: string,
     *              }
     *          ],
     *      }
     * }
     */
    public function ajaxProcessOrdersHistory()
    {
        if ((int) $this->context->customer->id !== 0) {
            $page = (int) Tools::getValue('page', 1);
            $this->ajax_json = array(
                'success' => true,
                'message' => '',
                'data' => array(
                    'orders' => PosOrder::getOrdersByIdCustomer((int) $this->context->cart->id_customer, $page),
                    'number_orders' => PosOrder::getCustomerNbOrders($this->context->cart->id_customer),
                ),
            );
        }
    }

    /**
     * @output
     * <pre>
     * {
     *  carts => array(
     *      firstname => string
     *      firstname => string
     *      email => string
     *      date_add => timestamp
     *      id_cart => int
     * ),
     *      number_cart => int,
     *   }
     */
    public function ajaxProcessOnlineCarts()
    {
        $this->ajax_json = array(
            'success' => true,
            'message' => '',
            'data' => $this->getAbandonedCarts((int) Tools::getValue('id_customer')),
        );
    }

    /**
     * @return array // Refer to action "getAllInOne" for further details,` `
     */
    protected function getActiveCarts()
    {
        $carts = array();
        $cart_fields = array(
            'id',
            'date_add',
            'id_currency',
            'id_address_delivery',
            'id_address_invoice',
            'total',
            'id_customer',
            'email',
            'checkout',
            'customer_name',
        );
        $id_carts = PosActiveCart::getActiveCarts($this->context->cart->id, $this->context->shop->id);
        if (!empty($id_carts)) {
            $cart_collection = new PrestaShopCollection('PosCart');
            $cart_collection->where('id_cart', '=', $id_carts);
            $cart_collection->orderBy('id_cart', 'DESC');
            $carts = $cart_collection->getResults();
            $id_customers = array();
            foreach ($carts as $cart) {
                $id_customers[] = $cart->id_customer;
            }

            $unique_id_customers = array_unique($id_customers);
            if ($unique_id_customers) {
                $customer_collection = new PrestaShopCollection('PosCustomer');
                $customer_collection->where('id_customer', '=', $unique_id_customers);
                $customers = $customer_collection->getResults();
            }
            foreach ($carts as $index => &$cart) {
                if ($cart->nbProducts() <= 0) {
                    unset($carts[$index]);
                    continue;
                }
                $cart->total = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                $cart->checkout = ($cart->id == $this->context->cart->id);
                foreach ($customers as $customer) {
                    if ($cart->id_customer == $customer->id) {
                        $cart->customer_name = $customer->firstname . ' ' . $customer->lastname;
                        $cart->email = $customer->email;
                        break;
                    }
                }
                foreach (array_keys(get_object_vars($cart)) as $key) {
                    if (!in_array($key, $cart_fields)) {
                        unset($cart->$key);
                    } else {
                        // It's not the best practice to call cross-module here
                        $cart->$key = $cart->$key != PosConstants::NOT_AVAILABLE ? $cart->$key : '';
                    }
                }
                $employees = PosEmployee::getEmployeeByIdCart($cart->id);
                if (!empty($employees)) {
                    foreach ($employees as $employee) {
                        $cart->employee_name = $employee['firstname'] . ' ' . $employee['lastname'];
                    }
                }
            }
        }

        return array_values($carts);
    }

    /**
     * @param int $id_order
     *
     * @return array()
     */
    protected function getTransactionData($id_order = null)
    {
        $order = new PosOrder($id_order);
        return array(
            'meta' => array(
                'currency' => PosCurrency::getCurrentCurrency($this->context->shop->id),
            ),
            'note' => PosActiveCart::getNote($this->context->cart->id, $this->context->shop->id),
            'paid_payments' => $this->context->cart->getPayments(),
            'cart' => $this->getCartSummaryDetails(),
            'customer' => $this->getCustomer(),
            'order' => $this->getOrderData($order),
        );
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *               <pre>
     *               array(
     *               id_cart => int,
     *               id_customer => int,
     *               id_carrier => int,
     *               id_address_delivery => int,
     *               id_address_invoice => int,
     *               products => array, refer to getCartProducts()
     *               discounts => array,
     *               total => array, refer to getCartTotal
     *               shipping => array,refer to getShipping
     *               )
     */
    protected function getCartSummaryDetails($refresh = false)
    {
        $products = $this->getCartProducts($refresh);

        return array(
            'id_cart' => (int) $this->context->cart->id,
            'id_customer' => $this->context->customer->isDefaultCustomer(
                $this->context->employee->id,
                $this->context->shop->id
            ) ? 0 : (int) $this->context->customer->id,
            'id_carrier' => (int) $this->context->cart->id_carrier,
            'id_address_delivery' => (int) $this->context->cart->id_address_delivery,
            'id_address_invoice' => (int) $this->context->cart->id_address_invoice,
            'products' => $products,
            // Only pass $refresh once, then the next commands should benefit from latest data
            'discounts' => $this->module->getCartRules(),
            'total' => $this->getCartTotal(),
            'tax' => $this->getTaxDetail($products),
            'shipping' => $this->getShipping(),
        );
    }

    /**
     * @return \stdClass
     *                   <pre>
     *                   stdClass{
     *                   firstname => string,
     *                   lastname => string,
     *                   email => string,
     *                   phone => string,
     *                   website => string,
     *                   company => string,
     *                   siret => string,
     *                   ape => string,
     *                   note => string,
     *                   is_default => boolean,
     *                   tax_enabled => boolean,
     *                   tax_included => boolean,
     *                   carriers => array,
     *                   addresses => array,
     *                   cart_rules => array,
     *                   }
     */
    protected function getCustomer()
    {
        $properties = array(
            'firstname',
            'lastname',
            'email',
            'website',
            'company',
            'siret',
            'ape',
            'note',
        );
        $customer = new stdClass();
        foreach ($properties as $property) {
            $customer->{$property} = !empty($this->context->customer->{$property}) ? $this->context->customer->{$property} : '';
        }
        $customer->id_customer = $this->context->customer->id;
        $customer->is_default = $this->context->customer->isDefaultCustomer(
            $this->context->employee->id,
            $this->context->shop->id
        );
        $customer->tax_enabled = (bool) Configuration::get('PS_TAX');
        $customer->tax_included = PS_TAX_INC == $this->context->customer->getPriceDisplayMethod();
        $customer->newsletter = $this->context->customer->newsletter;
        $customer->phone = '';
        $customer->display_name = '';
        $addresses = array();
        if (Validate::isLoadedObject($this->context->customer)) {
            $addresses = $this->context->customer->getAddresses();

            foreach ($addresses as &$address) {
                $address['formatted_address'] = PosAddressFormat::generateAddress(
                    new PosAddress($address['id_address']),
                    array(),
                    '<br />'
                );
            }
            $customer->phone = PosCustomer::getPhoneNumber($this->context->customer);
            $customer->display_name = $this->context->customer->getDisplayName();
        }

        $customer->addresses = $addresses;
        $customer->carriers = $this->context->cart->getCarriers();
        $customer->cart_rules = $this->module->getAvailableCartRules();
        $customer->barcode = PosCustomer::getBarcodeByIdCustomer($this->context->customer->id);
        $due = PosCustomer::getDueAmountByIdCustomer($this->context->customer->id);
        $due_amount = 0;
        foreach ($due as $k => $v) {
            if ($v['total_paid_real'] < $v['total_paid']) {
                $due_amount += (float) $v['total_paid'] - (float) $v['total_paid_real'];
            }
        }

        $customer->due_amount = Tools::ps_round($due_amount, PosConstants::POS_PRICE_COMPUTE_PRECISION);

        return $customer;
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               id_product => int,
     *               id_product_attribute => int,
     *               name => string,
     *               product_info => string,
     *               short_description => string,
     *               image_url => string,
     *               combination => string,
     *               short_combination => string,
     *               quantity => int,
     *               price_without_reduction => float,
     *               price_with_reduction => float,
     *               reduction_type => string, // percence/ amount
     *               reduction => float,
     *               total => float,
     *               tax_rate => float,
     *               tax_amount => float,
     *               tax_name => string,
     *               discount => float,
     *               customization_datas => array, refer to PosProduct::getCustomizedProduct(),
     *               customization_fields => array, refer to PosProduct::getCustomizedFields(),
     *               )
     *               ...
     *               )
     *
     * )
     */
    protected function getCartProducts($refresh = false)
    {
        $tax_inc = PS_TAX_INC == $this->context->customer->getPriceDisplayMethod();
        $formatted_products = array();
        $summary_details = $this->context->cart->getSummaryDetails($this->context->language->id, $refresh);
        $id_group = Customer::getDefaultGroupId($this->context->cart->id_customer);
        $products = $summary_details['products'];
        foreach ($summary_details['gift_products'] as $gift_product) {
            array_push($products, $gift_product);
        }
        $id_country = Customer::getCurrentCountry($this->context->cart->id_customer, $this->context->cart);
        $notes = PosProductNote::getByIdCart($this->context->cart->id);

        foreach ($products as $product) {
            $specific_price = SpecificPrice::getSpecificPrice(
                $product['id_product'],
                $this->context->cart->id_shop,
                0,
                $id_country,
                $id_group,
                $product['cart_quantity'],
                $product['id_product_attribute'],
                0,
                $this->context->cart->id
            );
//            $tax_detail = PosTax::getProductTaxDetail($product, $id_country, $id_group);
            $product_info = array();
            if (Configuration::get('POS_SHOW_ID')) {
                $product_info[] = $this->module->i18n['id'] . ': ' . $product['id_product'];
            }
            if (Configuration::get('POS_SHOW_REFERENCE') && !empty($product['reference'])) {
                $product_info[] = $this->module->i18n['ref'] . ': ' . $product['reference'];
            }
            if (Configuration::get('POS_SHOW_STOCK')) {
                $product_info[] = $this->module->i18n['stock'] . ': ' . $product['stock_quantity'];
            }

            $price_only_reduction_amount = $tax_inc ? Tools::ps_round($product['price_with_only_reduction_tax_incl'], PosConstants::POS_PRICE_COMPUTE_PRECISION) : Tools::ps_round($product['price_with_only_reduction_tax_excl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_without_reduction = $tax_inc ? Tools::ps_round($product['price_without_reduction_tax_incl'], PosConstants::POS_PRICE_COMPUTE_PRECISION) : Tools::ps_round($product['price_without_reduction_tax_excl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_with_reduction = $tax_inc ? Tools::ps_round($product['price_with_reduction_tax_incl'], PosConstants::POS_PRICE_COMPUTE_PRECISION) : Tools::ps_round($product['price_with_reduction_tax_excl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_discount_amount = $tax_inc ? Tools::ps_round($product['price_with_only_reduction_tax_incl'], PosConstants::POS_PRICE_COMPUTE_PRECISION) : Tools::ps_round($product['price_with_only_reduction_tax_incl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_only_reduction_percentage = 0;
            $reduction_type = 'percentage';

            if (!empty($specific_price)) {
                $reduction_type = $specific_price['reduction_type'];
                if ($reduction_type == 'percentage') {
                    $price_only_reduction_percentage = $specific_price['reduction'] * 100;
                } else if ($price_without_reduction > 0) {
                    $price_only_reduction_percentage = Tools::ps_round(($price_discount_amount / $price_without_reduction), PosConstants::POS_PRICE_COMPUTE_PRECISION) * 100;
                }
            }
            $total = Tools::ps_round($price_with_reduction * $product['cart_quantity'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $_product = array(
                'id_product' => $product['id_product'],
                'id_product_attribute' => $product['id_product_attribute'],
                'name' => PosProduct::getProductNames($product['id_product'], $this->context->language->id),
                'product_info' => empty($product_info) ? null : implode(' - ', $product_info),
                'short_description' => $product['description_short'],
                'image_url' => Configuration::get('POS_IMAGES_IN_SHOPPING_CART') ? $this->context->link->getImageLink(
                    $product['link_rewrite'],
                    $product['id_image'],
                    PosImageType::getFormattedName(PosConstants::DEFAULT_ATTRIBUTE_IMAGE_TYPE)
                ) : null,

                'combination' => !empty($product['attributes']) ? $product['attributes'] : '',
                'short_combination' => !empty($product['attributes_small']) ? $product['attributes_small'] : '',
                'quantity' => $product['cart_quantity'],
                'note' => PosProductNote::findByProductAttribute($notes, $product['id_product'], isset($product['id_product_attribute']) ? $product['id_product_attribute'] : 0, isset($product['id_customization']) ? $product['id_customization'] : 0),
                'price_without_reduction' => $price_without_reduction,
                'price_with_reduction' => $price_with_reduction,
                'price_only_reduction_amount' => $price_only_reduction_amount,
                'price_only_reduction_percent' => $price_only_reduction_percentage,
                'reduction_type' => $reduction_type,
                'reduction' => $price_only_reduction_percentage,
                'total' => $total,
                'tax_rate' => $product['rate'],
                'tax_amount' => $product['price_with_reduction_tax_incl'] - $product['price_with_reduction_tax_excl'],
                'tax_name' => $product['tax_name'],
                'customization_datas' => PosProduct::getCustomizedProduct(
                    $this->context->cart->id,
                    $product['id_product'],
                    $product['id_product_attribute'],
                    $product['id_address_delivery'],
                    $notes
                ),
                'customization_fields' => PosProduct::getCustomizedFields(
                    $product['id_product'],
                    $this->context->language->id,
                    $this->context->shop->id
                ),
                'gift' => !empty($product['gift']) ? $product['gift'] : 0,
            );
            $formatted_products[] = $_product;
        }

        return $formatted_products;
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               product => float,
     *               discount => float,
     *               tax => float,
     *               shipping => float,
     *               order => float,
     *               )
     */
    protected function getCartTotal()
    {
        $deliver_option = $this->getDeliverOption($this->context->cart->id_carrier);
        $summary_details = $this->context->cart->getSummaryDetails(null, false, $deliver_option);
        $total_products_without_reduction = $this->getTotalProductsWithoutReduction($summary_details['products']);
        $total_products_reduction = $total_products_without_reduction - $summary_details['total_products'];
        $totals = array(
            'product' => $total_products_without_reduction,
            'discount' => $summary_details['total_discounts_tax_exc'] + $total_products_reduction,
            'tax' => $summary_details['total_tax'],
            'shipping' => $summary_details['total_shipping_tax_exc'],
            'order' => $summary_details['total_price'],
        );

        return $totals;
    }

    protected function getTaxDetail(array $products)
    {
        return PosTax::getProductsTaxDetail($products);
    }

    /**
     * @param array $products
     * @param bool $tax_excl
     *
     * @return float
     */
    protected function getTotalProductsWithoutReduction($products, $tax_excl = true)
    {
        $total_products_without_reduction = array();
        foreach ($products as $key => $product) {
            if ($tax_excl && $product['price_with_reduction_tax_incl']) {
                $price = $product['price_without_reduction_tax_incl'] - ($product['price_without_reduction_tax_incl'] * ($product['price_with_reduction_tax_incl'] - $product['price_with_reduction_tax_excl']) / $product['price_with_reduction_tax_incl']);
            } else {
                $price = $product['price_without_reduction_tax_incl'];
            }
            $total_products_without_reduction[$key] = $price * $product['cart_quantity'];
        }

        return Tools::ps_round(
            array_sum($total_products_without_reduction),
            (int) $this->context->currency->decimals * PosConstants::POS_PRICE_COMPUTE_PRECISION
        );
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               id_carrier => int,
     *               free => boolean,
     *               name => string
     *               )
     */
    protected function getShipping()
    {
        $pos_carrier = PosCarrier::getPosCarrier();
        $selected_carrier = new PosCarrier($this->context->cart->id_carrier);

        return array(
            'id_carrier' => $this->context->cart->id_carrier,
            'free' => $pos_carrier->id == $this->context->cart->id_carrier,
            'name' => $selected_carrier->name,
        );
    }

    /**
     * @param PosOrder $order
     * @param bool $is_return
     * @param array $exchange_products
     * @param array $return_products
     * @param int $id_cart
     * @param bool $shipping_back
     *
     * @return array
     *               <pre>
     *               array(
     *               'reference' => string,
     *               'invoice' => boolean,
     *               'invoice_auto_print' => boolean,
     *               'invoice_url' => string,
     *               'receipt' => boolean,
     *               'receipt_auto_print' => boolean,
     *               'receipt_url' => string,
     *               'products' => [
     *               {
     *               'id_order_detail' => int,
     *               'id_product' => int,
     *               'id_product_attribute' => int,
     *               'name' => string,
     *               'short_description' => string,
     *               'image_url' => string,
     *               'combination' => string,
     *               'short_combination' => string,
     *               'quantity' => int,
     *               'price_without_reduction' => float,
     *               'price_with_reduction' => float,
     *               'reduction_type' => string,
     *               'reduction' => float,
     *               'total' => float,
     *               'quantity_refunded' => int,
     *               'quantity_return' => int,
     *               'quantity_reinjected' => int,
     *               }
     *               ]
     *               )
     */
    protected function getOrderData(
        $order = null,
        $is_return = false,
        array $exchange_products = array(),
        array $return_products = array(),
        $id_cart = null,
        $shipping_back = false
    ) {
        $id_employee = $this->module->getIdEmployeeByPersonalSetting();
        $order_data = array();
        $payment_methods = PosPayment::getAll($this->context->language->id, $this->context->shop->id);
        if (is_null($order)) {
            if (!empty($this->module->currentOrder)) {
                $order = new PosOrder($this->module->currentOrder);
            } elseif (!empty($this->context->order) && Validate::isLoadedObject($this->context->order)) {
                $order = $this->context->order;
            }
        }

        if ($order && Validate::isLoadedObject($order)) {
            $products = $this->getOrderProducts($order);
            $order_collections = PosOrder::getByReference($order->reference);

            $order_data = array(
                'reference' => $order->reference,
                'id_order_state' => $order->current_state,
                'id_order' => $order->id,
                'invoice' => $order->hasInvoice(),
                'invoice_url' => $order->hasInvoice() ? $this->context->link->getModuleLink(
                    $this->module->name,
                    'sales',
                    array('action' => 'printInvoice', 'id_order' => $order->id)
                ) : null,
                'receipt' => true,
                'products' => $products,
                'total' => array(
                    'product' => $order->total_products,
                    'discount' => $order->total_discounts_tax_excl,
                    'discount_tax_incl' => $order->total_discounts_tax_incl,
                    'tax' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
                    'shipping' => $order->total_shipping_tax_excl,
                    'shipping_tax_incl' => $order->total_shipping_tax_incl,
                    'order' => $order->total_paid_tax_incl,
                ),
                'payment_methods' => !empty($payment_methods) ? $payment_methods : $this->module->getOrderPayments($order),
                'print_data' => array(
                    'currency' => PosCurrency::getCurrencyInstance($order->id_currency),
                    'template' => PosConfiguration::getValueByEmployeeId($id_employee, 'POS_RECEIPT_TEMPLATE'),
                    '1st_bill_printer' => PosConfiguration::getValueByEmployeeId(
                        $id_employee,
                        'POS_RECEIPT_BILL_PRINTER_1'
                    ),
                    '2nd_bill_printer' => PosConfiguration::getValueByEmployeeId(
                        $id_employee,
                        'POS_RECEIPT_BILL_PRINTER_2'
                    ),
                    'receipt' => $this->module->getReceiptData(
                        $order_collections,
                        $is_return,
                        $exchange_products,
                        $return_products,
                        $shipping_back
                    ),
                    'return_order' => $is_return,
                    'available_fields' => $this->module->getAvailableFields(),
                ),
            );
        }

        return $order_data;
    }

    /**
     * @param PosOrder $order
     *
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               id_product => int,
     *               id_product_attribute => int,
     *               name => string,
     *               product_info => string,
     *               short_description => string,
     *               image_url => string,
     *               combination => string,
     *               short_combination => string,
     *               quantity => int,
     *               quantity_refunded => int,
     *               quantity_return => int,
     *               quantity_reinjected => int,
     *               price_without_reduction => float,
     *               price_with_reduction => float,
     *               price_tax_incl => float,
     *               price_tax_excl => float,
     *               reduction_type => string,
     *               reduction => float,
     *               total => float,
     *               tax_amount => float,
     *               discount => float,
     *               customization_datas => array, refer to PosProduct::getCustomizedProduct(),
     *               customization_fields => array, refer to PosProduct::getCustomizedFields(),
     *               )
     *               ...
     *               )
     */
    protected function getOrderProducts($order = null)
    {
        if (empty($this->context->order) || !Validate::isLoadedObject($this->context->order)) {
            return array();
        }
        $tax_inc = PS_TAX_INC == $this->context->customer->getPriceDisplayMethod();
        $formatted_products = array();
        $cart = new PosCart($this->context->order->id_cart);
        $cart_products = $cart->getProducts();

        $order_products = $order ? $order->getProducts() : $this->context->order->getProducts();

        $notes = PosProductNote::getByIdOrder($order->id);
        foreach ($order_products as $key => $order_product) {
            $_current_cart_product = null;
            if ($order_product['gift']) {
                unset($order_product);
            }
            foreach ($cart_products as $cart_product) {
                if (isset($order_product)) {
                    if ($order_product['product_id'] == $cart_product['id_product'] && $order_product['product_attribute_id'] == $cart_product['id_product_attribute']) {
                        $_current_cart_product = $cart_product;
                        break;
                    }
                }
            }
            if (empty($_current_cart_product)) {
                continue;
            }
            $product_info = array();
            if (Configuration::get('POS_SHOW_ID')) {
                $product_info[] = $this->module->i18n['id'] . ': ' . $order_product['product_id'];
            }
            if (Configuration::get('POS_SHOW_REFERENCE') && !empty($order_product['product_reference'])) {
                $product_info[] = $this->module->i18n['ref'] . ': ' . $order_product['product_reference'];
            }
            if (Configuration::get('POS_SHOW_STOCK')) {
                $product_info[] = $this->module->i18n['stock'] . ': ' . $order_product['current_stock'];
            }

            $price_without_reduction = Tools::ps_round($order_product['reduction_percent'] + $order_product['reduction_amount'] <= 0.0 ?
                null :
                (
                    $tax_inc ?
                    $order_product['original_product_price'] + $order_product['tax_calculator']->getTaxesTotalAmount($order_product['original_product_price']) :
                    $order_product['original_product_price']
                ), PosConstants::POS_PRICE_COMPUTE_PRECISION);

            $price_with_reduction = Tools::ps_round($tax_inc ? $order_product['unit_price_tax_incl'] : $order_product['unit_price_tax_excl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_with_reduction_tax_excl = Tools::ps_round($order_product['unit_price_tax_excl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_tax_incl = Tools::ps_round($order_product['unit_price_tax_incl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $price_tax_excl = Tools::ps_round($order_product['unit_price_tax_excl'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
            $_product = array(
                'id_order_detail' => $order_product['id_order_detail'],
                'id_product' => $order_product['product_id'],
                'id_product_attribute' => $order_product['product_attribute_id'],
                'name' => PosProduct::getProductNames($order_product['product_id'], $this->context->order->id_lang),
                'product_info' => empty($product_info) ? null : implode('-', $product_info),
                'short_description' => !empty($_current_cart_product['short_description']) ? $_current_cart_product['short_description'] : '',
                'image_url' => Configuration::get('POS_IMAGES_IN_SHOPPING_CART') ? $this->context->link->getImageLink(
                    $_current_cart_product['link_rewrite'],
                    $_current_cart_product['id_image'],
                    PosImageType::getFormattedName(PosConstants::DEFAULT_ATTRIBUTE_IMAGE_TYPE)
                ) : null,
                'combination' => $order_product['combination'],
                'short_combination' => !empty($_current_cart_product['attributes_small']) ? $_current_cart_product['attributes_small'] : '',
                'quantity' => $order_product['product_quantity'],
                'quantity_refunded' => $order_product['product_quantity_refunded'],
                'quantity_return' => $order_product['product_quantity_return'],
                'note' => PosProductNote::findByProductAttribute($notes, $order_product['product_id'], $order_product['product_attribute_id'], isset($order_product['id_customization']) ? $order_product['id_customization'] : 0),
                'quantity_reinjected' => $order_product['product_quantity_reinjected'],
                'price_without_reduction' => $price_without_reduction,
                'price_with_reduction' => $price_with_reduction,
                'price_tax_incl' => $price_tax_incl,
                'price_tax_excl' => $price_tax_excl,
                'price_with_reduction_tax_excl' => $price_with_reduction_tax_excl,
                'reduction_type' => $order_product['reduction_percent'] > 0.0 ? PosConstants::DISCOUNT_TYPE_PERCENTAGE : ($order_product['reduction_amount'] > 0 ? PosConstants::DISCOUNT_TYPE_AMOUNT : 0),
                'price_only_reduction_percent' => $order_product['reduction_percent'],
                'reduction' => max($order_product['reduction_percent'], $order_product['reduction_amount']),
                //or percent, or amount applied
                'total' => Tools::ps_round($price_with_reduction * $order_product['product_quantity'], PosConstants::POS_PRICE_COMPUTE_PRECISION),
                'tax_amount' => Tools::ps_round($price_tax_incl - $price_tax_excl, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                'discount' => $order_product['original_product_price'] - $order_product['unit_price_tax_excl'],
                // tax excluded please
                'customization_datas' => PosProduct::getCustomizedProduct(
                    $this->context->order->id_cart,
                    $_current_cart_product['id_product'],
                    $_current_cart_product['id_product_attribute'],
                    $_current_cart_product['id_address_delivery'],
                    $notes
                ),
                'customization_fields' => PosProduct::getCustomizedFields(
                    $_current_cart_product['id_product'],
                    $this->context->language->id,
                    $this->context->order->id_shop
                ),
            );
            $formatted_products[] = $_product;
        }

        return $formatted_products;
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               id_country => int,
     *               name => string,
     *               need_zip_code=> int,
     *               zip_code_format => string,
     *               display_tax_label => int,
     *               need_identification_number => int,
     *               contains_states => int,
     *               states => array(
     *               int => array(
     *               id_state => int,
     *               name => string
     *               )
     *               ...
     *               )
     */
    protected function getCountries()
    {
        $countries = PosCountry::getCountries($this->context->language->id, true);
        $list_countries = array();
        foreach ($countries as $key => $country) {
            $list_countries[$key]['id_country'] = $country['id_country'];
            $list_countries[$key]['name'] = $country['name'];
            $list_countries[$key]['need_zip_code'] = $country['need_zip_code'];
            $list_countries[$key]['zip_code_format'] = $country['zip_code_format'];
            $list_countries[$key]['display_tax_label'] = $country['display_tax_label'];
            $list_countries[$key]['need_identification_number'] = $country['need_identification_number'];
            $list_countries[$key]['contains_states'] = $country['contains_states'];
            $list_countries[$key]['iso_code'] = $country['iso_code'];
            $list_countries[$key]['states'] = array();
            if (!empty($country['states'])) {
                foreach ($country['states'] as $key_state => $state) {
                    $list_countries[$key]['states'][$key_state]['id_state'] = $state['id_state'];
                    $list_countries[$key]['states'][$key_state]['name'] = $state['name'];
                }
            }
        }

        return $list_countries;
    }

    /**
     * @param int $id_lang
     * @param int $id_shop
     *
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               id_group => int,
     *               name => string,
     *               is_default => boolean,
     *               is_pos => boolean
     *               ),
     *               ...
     *               )
     */
    protected function getCustomerGroups()
    {
        $customer_groups = array();
        $groups = PosGroup::getGroups($this->context->language->id, $this->context->shop->id);
        foreach ($groups as $key => $group) {
            $customer_groups[$key]['id_group'] = $group['id_group'];
            $customer_groups[$key]['name'] = $group['name'];
            $customer_groups[$key]['is_default'] = Configuration::get('PS_CUSTOMER_GROUP') == $group['id_group'];
            $customer_groups[$key]['is_pos'] = Configuration::get('POS_CUSTOMER_ID_GROUP') == $group['id_group'];
        }

        return $customer_groups;
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               id => int,
     *               name => string
     *               ),
     *               ...
     *               )
     */
    protected function getGenders()
    {
        $genders = Gender::getGenders();
        $list_genders = array();
        foreach ($genders as $key => $gender) {
            $list_genders[$key]['id_gender'] = $gender->id;
            $list_genders[$key]['name'] = $gender->name;
        }

        return $list_genders;
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               string => mixed, // key => value
     *               string => mixed
     *               )
     */
    protected function getConfigurations()
    {
        $id_employee = $this->module->getIdEmployeeByPersonalSetting();

        return array(
            'enable_payment' => (bool) Configuration::get('POS_COLLECTING_PAYMENT'),
            'show_warning_guest_checkout' => (bool) Configuration::get('POS_SHOW_WARNING_GUEST_CHECKOUT'),
            'file_upload_max_size' => (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'),
            'receipt' => array(
                'shop_info' => array(
                    'logo' => (int) Configuration::get('POS_RECEIPT_SHOW_LOGO'),
                    'shop_name' => (int) Configuration::get('POS_RECEIPT_SHOW_SHOP_NAME'),
                    'phone' => (int) Configuration::get('POS_RECEIPT_SHOW_PHONE'),
                    'fax' => (int) Configuration::get('POS_RECEIPT_SHOW_FAX'),
                    'tax_code' => (int) Configuration::get('POS_RECEIPT_SHOW_REG_NUMBER'),
                    'address' => (int) Configuration::get('POS_RECEIPT_SHOW_ADDRESS'),
                ),
                'page_size' => PosConfiguration::getValueByEmployeeId($id_employee, 'POS_RECEIPT_PAGE_SIZE'),
                'show_customer_info' => (int) Configuration::get('POS_SHOW_CUS_INFO_ON_RECEIPT'),
                'order' => array(
                    'id' => (int) Configuration::get('POS_RECEIPT_SHOW_ORDER_ID'),
                    'reference' => (int) Configuration::get('POS_RECEIPT_SHOW_ORDER_REF'),
                    'barcode' => (int) Configuration::get('POS_RECEIPT_DISPLAY_BARCODE'),
                ),
                'currency' => (int) Configuration::get('POS_RECEIPT_PRODUCT_CURRENCY'),
                'products' => array(
                    'original_price' => (int) Configuration::get('POS_RECEIPT_SHOW_MRP_PRICE'),
                    'unit_price' => (int) Configuration::get('POS_RECEIPT_SHOW_UNIT_PRICE'),
                    'discount' => (int) Configuration::get('POS_RECEIPT_SHOW_PROD_DISCOUNT'),
                ),
                'signature' => (int) Configuration::get('POS_RECEIPT_SHOW_SIGNATURE'),
                'website_url' => (int) Configuration::get('POS_RECEIPT_SHOW_WEBSITE_URL'),
                'margin' => (float) Configuration::get('POS_RECEIPT_MARGIN'),
                'font_size' => (float) Configuration::get('POS_RECEIPT_FONT_SIZE'),
                'print_automatically' => Configuration::get('POS_PRINT_AUTOMATICALLY') == "true" ? 1 : 0,
                'close_popup_after_print' => (int) Configuration::get('POS_CLOSE_POPUP_AFTER_PRINT'),
                'print_automatically_quantity' => (int) Configuration::get('POS_AUTOMATIC_PRINT_QUANTITY'),
            ),
            'pos_id_carrier' => Configuration::get('POS_ID_CARRIER'),
            'pos_id_default_carrier' => Configuration::get('POS_ID_DEFAULT_CARRIER'),
        );
    }

    /**
     * @input
     * {
     *      keyword: string,
     *      action: "search",
     *      sub_action: "product" | "customer" | "category"
     *      ajax: "1"
     *
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          products: [// if sub_action = product
     *              {
     *                  id_product: int,
     *                  reference: string,
     *                  quantity: int,
     *                  name: string,
     *                  has_combinations: boolean,
     *                  label: string
     *              }
     *          ],
     *          customers: [// if sub_action = customer
     *              {
     *                  id_customer: int,
     *                  firstname: string,
     *                  lastname: string,
     *                  email: string,
     *                  phone: string,
     *                  phone_mobile: string,
     *                  company: string
     *              }
     *          ],
     *          categories: [// if sub_action = category
     *              {
     *                  [
     *                      id_category => int,
     *                      name => string,
     *                  ]
     *
     *              }
     *          ]
     *      }
     * }
     */
    public function ajaxProcessSearch()
    {
        $keyword = trim(urldecode(Tools::getValue('keyword')));
        switch ($this->sub_action) {
            case 'product':
                $this->ajax_json['data']['products'] = array();
                $keyword = Tools::replaceAccentedChars($keyword);
                $search_by = Tools::strtolower(Tools::getValue('search_by'));

                if ($search_by == 'general' && is_numeric($keyword)) {
                    $check_search_id = new SearchById($keyword, $this->context);
                    $products = $check_search_id->search();
                    $search_by = !empty($products) ? 'id' : 'general';
                }

                switch ($search_by) {
                    case 'id':
                        $search = new SearchById($keyword, $this->context);
                        break;
                    case 'name':
                        $search = new SearchByName($keyword, $this->context);
                        break;
                    case 'reference':
                        $search = new SearchByReference($keyword, $this->context);
                        break;
                    case 'barcode':
                        $search = new SearchByBarcode($keyword, $this->context);
                        break;
                    case 'short_description':
                        $search = new SearchByShortDescription($keyword, $this->context);
                        break;
                    case 'description':
                        $search = new SearchByDescription($keyword, $this->context);
                        break;
                    case 'category':
                        $search = new SearchByCategory($keyword, $this->context);
                        break;
                    case 'feature':
                        $search = new SearchByFeature($keyword, $this->context);
                        break;
                    case 'manufacturer':
                        $search = new SearchByManufacture($keyword, $this->context);
                        break;
                    case 'tag':
                        $search = new SearchByTag($keyword, $this->context);
                        break;
                    case 'attribute':
                        $search = new SearchByAttribute($keyword, $this->context);
                        break;
                    default:
                        $search = new SearchGeneral($keyword, $this->context);
                        break;
                }
                $is_with_image = Configuration::get('POS_IMAGES_ON_SEARCH_BAR');
                $is_with_price = Configuration::get('POS_SHOW_PRICE');
                $is_show_stock = Configuration::get('POS_SHOW_STOCK');
                $is_show_reference = Configuration::get('POS_SHOW_REFERENCE');
                $is_show_id = Configuration::get('POS_SHOW_ID');
                $products = $search->search($is_with_image);

                if (!empty($products)) {
                    foreach ($products as &$product) {
                        unset($product['id_product_attribute']);
                        $labels = array();
                        if ($is_show_id) {
                            $labels[] = $this->module->i18n['id'] . ': ' . $product['id_product'];
                        }
                        if ($is_with_price) {
                            $use_tax = Configuration::get('PS_TAX');
                            $product['price'] = Tools::displayPrice(Tools::ps_round(Product::getPriceStatic($product['id_product'], $use_tax), PosConstants::POS_PRICE_COMPUTE_PRECISION), $this->context->currency);
                        }
                        if ($is_show_reference && !empty($product['reference'])) {
                            $labels[] = $this->module->i18n['ref'] . ': ' . $product['reference'];
                        }
                        if ($is_show_stock) {
                            $labels[] = $this->module->i18n['stock'] . ': ' . $product['quantity'];
                        }
                        if ($is_with_image) {
                            $product['image_url'] = $this->context->link->getImageLink($product['link_rewrite'], $product['id_image'], PosImageType::getFormattedName('small'));
                            unset($product['id_image']);
                        }
                        $product['label'] = $product['name'];
                        if (!empty($labels)) {
                            $product['label'] = $product['name'] . ' (' . implode(' - ', $labels) . ')';
                        }
                    }
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['items_found'];
                    $this->ajax_json['data']['products'] = $products;
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['the_item_is_not_available'];
                }
                break;
            case 'customer':
                $this->ajax_json['data']['customers'] = array();
                $pos_employees = new PosEmployee($this->context->cookie->pos_id_employee);
                $id_employees = $pos_employees->getIdEmployees();
                $customers = array();

                if (!empty($keyword)) {
                    $customers = array_merge($customers, PosCustomer::search($keyword, null, $id_employees, Tools::getValue('indexed'), $this->context->shop->id));
                }
                if ($customers) {
                    $customers = unique_multidim_array($customers, 'id_customer');
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['customer_profiles_found'];
                    $this->ajax_json['data']['customers'] = $customers;
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['no_associated_customer_found'];
                }
                break;
            case 'category':
                $categories = PosCategory::search($this->context->language->id, trim($keyword));
                if ($categories) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['data']['categories'] = $categories;
                    $this->ajax_json['message'] = $this->module->i18n['category_found'];
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['no_associated_category_found'];
                }
                break;
            case 'order':
                $pre_order = Tools::getValue('pre_order');
                $reference = Tools::getValue('reference', null);
                $id_order = (int) Tools::getValue('id_order', 0);
                $id_customer = (int) Tools::getValue('id_customer', 0);

                $orders = array();
                if (!is_null($reference)) {
                    $orders = PosOrder::searchByReference($reference, 1, null, $pre_order);
                    if ($orders) {
                        foreach ($orders as &$order) {
                            $order['un_paid'] = (float) $order['total_paid'] - (float) $order['total_paid_real'];
                        }
                    }
                } elseif ($id_order) {
                    $orders = PosOrder::searchById($id_order, 1, null, $pre_order);
                    if ($orders) {
                        $orders['un_paid'] = (float) $orders['total_paid'] - (float) $orders['total_paid_real'];
                    }
                } elseif ($id_customer) {
                    $orders = PosOrder::searchByIdCustomer($id_customer, 1, null, $pre_order);
                    if ($orders) {
                        foreach ($orders as &$order) {
                            $order['un_paid'] = (float) $order['total_paid'] - (float) $order['total_paid_real'];
                        }
                    }
                }

                if ($orders) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['data']['orders'] = $orders;
                    $this->ajax_json['message'] = $this->module->i18n['order_found'];
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['no_order_found'];
                }
                break;
            default:
                break;
        }
    }

    /**
     * input
     * {
     *      id_category: array,
     *      page: int,
     *      action: "filterProducts",
     *      ajax: "1"
     * }.
     *
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string
     *      data:{
     *          products:[{
     *              id_product: int
     *              reference: string
     *              link_rewrite: string
     *              id_shop: int
     *              id_product_attribute: int
     *              has_combinations: boolean
     *              price: float
     *              image_url: string
     *              name: string
     *              short_name: string
     *          }],
     *          total_products: int
     *      }
     * }
     */
    public function ajaxProcessFilterProducts()
    {
        $id_categories = Tools::getValue('id_categories', array());
        $page = (int) Tools::getValue('page', 1);
        $this->ajax_json = array(
            'success' => true,
            'message' => '',
            'data' => !empty($id_categories) ? PosCategory::searchProductsById(
                $id_categories,
                $page
            ) : array('products' => array(), 'total_products' => 0),
        );
    }

    /**
     * @input
     * {
     *      action: "product",
     *      sub_action: "add" | "remove" | "change_combination" | "edit"
     *      ajax: "1"
     *      id_product: int,
     *      id_product_attribute: null | int,
     *      id_shop: null | int,
     *      new_id_product_attribute: int,// if "change_combination"
     *      barcode: string, // if "add" and add with barcode
     *      quantity: int, //optional, if "edit"
     *      price: float, //optional, if "edit"
     *      discount: float, //optional, if "edit"
     *
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data:{
     *          transaction: {
     *              cart: {} // Refer to action "getAllInOne" for further details, if success
     *          }
     *      }
     * }
     */
    public function ajaxProcessProduct()
    {
        $id_product = (int) Tools::getValue('id_product', 0);
        $id_product_attribute = (int) Tools::getValue('id_product_attribute', 0);
        $id_customization = (int) Tools::getValue('id_customization', 0);
        $id_shop = (int) Tools::getValue('id_shop', 0);
        $shop = !empty($id_shop) ? new Shop($id_shop) : $this->context->shop;
        $refresh_cart = false;
        switch ($this->sub_action) {
            case 'add':
                $barcode = trim(Tools::getValue('barcode', ''));
                if ($barcode) {
                    $search = new SearchBarcode($barcode);
                    $search_result = $search->search();
                    if (!empty($search_result)) {
                        if ($search_result['id_product'] && PosProduct::isAllowOrderingDisableProduct($search_result['id_product'])) {
                            $id_product = $search_result['id_product'];
                            $id_product_attribute = (int) $search_result['id_product_attribute'] > 0 ? (int) $search_result['id_product_attribute'] : PosProduct::getDefaultAttribute($id_product);
                        } else {
                            $this->ajax_json['message'] = $this->module->i18n['the_item_is_not_available'];
                        }
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['the_item_is_not_available'];
                    }
                }
                if (!empty($id_product)) {
                    try {
                        $success = $this->module->addProductToCart($id_product, $id_product_attribute, $shop);
                        if ($success === PosConstants::NOT_ENOUGH_PRODUCT) {
                            $available_quantity = StockAvailable::getQuantityAvailableByProduct(
                                $id_product,
                                $id_product_attribute
                            );
                            $this->ajax_json['message'] = sprintf(
                                $this->module->i18n['oops_not_enough_product_in_stock'],
                                $available_quantity
                            );
                        } elseif ($success === PosConstants::MIN_QUANTITY) {
                            $product = new PosProduct($id_product);
                            $min_quantity = $product->getMinimalQuantity($id_product_attribute);
                            $this->ajax_json['message'] = sprintf(
                                $this->module->i18n['oops_at_least_quantities_required'],
                                $min_quantity
                            );
                        } elseif ($success === false) {
                            $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
                        } else {
                            $this->ajax_json['success'] = true;
                        }
                    } catch (Exception $exception) {
                        if (_PS_MODE_DEV_) {
                            $this->ajax_json['message'] = $exception->getMessage();
                        }
                    }
                }
                if ($this->ajax_json['success']) {
                    $this->ajax_json['message'] = $this->module->i18n['added_to_cart'];
                    $this->ajax_json['data']['transaction']['customer']['carriers'] = $this->context->cart->getCarriers(
                        $this->context->language->id,
                        true
                    );
                }
                break;

            case 'remove':
                if (!empty($id_product)) {
                    if ($this->context->cart->deletePosProduct($id_product, $id_product_attribute, $id_customization) && PosSpecificPrice::deleteByIdCart($this->context->cart->id, $id_product, $id_product_attribute)) {
                        $this->validateOrderDiscount();
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['message'] = $this->module->i18n['item_removed'];
                        if (!PosCart::getNbProducts((int) $this->context->cart->id)) {
                            $this->context->cart->setDeliveryOption(null);
                            $this->context->cart->gift = 0;
                            $this->context->cart->gift_message = '';
                            $this->context->cart->update();
                            PosActiveCart::removeByIdCart($this->context->cart->id);
                            $this->ajax_json['data']['plugins']['multiple_carts']['carts'] = $this->getActiveCarts();
                        }
                        $this->ajax_json['data']['transaction']['customer']['carriers'] = $this->context->cart->getCarriers(
                            $this->context->language->id,
                            true
                        );
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['cannot_remove_product_from_cart'];
                    }
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['cannot_get_product_id'];
                }
                break;

            case 'edit':
                $success = array();
                // process quantity if any
                $quantity = (int) Tools::getValue('quantity');
                $operator = Tools::getValue('operator', 'up');
                if ($quantity) {
                    $flag = $this->context->cart->updateQtyPos($quantity, $id_product, $this->context->shop, $id_product_attribute, $operator, $id_customization);
                    if ($flag === PosConstants::NOT_ENOUGH_PRODUCT) {
                        $available_quantity = StockAvailable::getQuantityAvailableByProduct(
                            $id_product,
                            $id_product_attribute
                        );
                        $success[] = 0;
                        $this->ajax_json['message'] = sprintf(
                            $this->module->i18n['oops_not_enough_product_in_stock'],
                            $available_quantity
                        );
                    } elseif ($flag === PosConstants::MIN_QUANTITY) {
                        $product = new PosProduct($id_product);
                        $min_quantity = $product->getMinimalQuantity($id_product_attribute);
                        $success[] = 0;
                        $this->ajax_json['message'] = sprintf(
                            $this->module->i18n['oops_at_least_quantities_required'],
                            $min_quantity
                        );
                    } elseif ($flag === false) {
                        $success[] = 0;
                        $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
                    } else {
                        $success[] = 1;
                        if (!PosCart::getNbProducts((int) $this->context->cart->id)) {
                            $this->context->cart->setDeliveryOption(null);
                            $this->context->cart->gift = 0;
                            $this->context->cart->gift_message = '';
                            $this->context->cart->update();
                            PosActiveCart::removeByIdCart($this->context->cart->id);
                            $this->ajax_json['data']['plugins']['multiple_carts']['carts'] = $this->getActiveCarts();
                        }
                    }
                    if ($operator == 'down') {
                        $success[] = array_sum($success) >= count($success) && $this->validateOrderDiscount();
                    }
                }

                // process changing price or applyin discount if any
                $price = Tools::getValue('price');
                $discount = Tools::getValue('discount');
                $price_value = $price_type = null;
                if ($price !== false) {
                    $price_type = PosConstants::PRODUCT_PRICE_TYPE;
                    $price_value = (float) $price;
                } elseif ($discount !== false) {
                    $price_type = PosConstants::DISCOUNT_TYPE_PERCENTAGE;
                    $price_value = (float) $discount;
                }
                if ($price_type) {
                    $success[] = array_sum($success) >= count($success) && $this->applySpecificPrice($price_value, $price_type, $id_product, $id_product_attribute);
                    $refresh_cart = true;
                }
                if (count($success) > 0 && array_sum($success) >= count($success)) {
                    $this->ajax_json['success'] = true;
                }
                break;

            case 'change_combination':
                $new_id_product_attribute = (int) Tools::getValue('new_id_product_attribute');
                $id_customization = (int) Tools::getValue('id_customization', 0);
                if (empty($id_product_attribute) || empty($new_id_product_attribute)) {
                    $this->ajax_json['message'] = $this->module->i18n['can_not_change_to_your_option'];

                    return;
                }
                $customization = new Customization($id_customization);
                $product = new PosProduct($id_product);
                $success = array();
                $customization_quantity = PosCustomization::getTotalQuantity(
                    $this->context->cart->id,
                    $id_product,
                    $id_product_attribute
                );
                $old_cart_product_quantity = $this->context->cart->getQuantity($id_product, $id_product_attribute);
                $new_cart_product_quantity = $this->context->cart->getQuantity($id_product, $new_id_product_attribute);
                $old_quantity = $old_cart_product_quantity - $customization_quantity['quantity'];
                $old_minimal_quantity = $product->getMinimalQuantity($id_product_attribute);
                $new_minimal_quantity = $product->getMinimalQuantity($new_id_product_attribute);
                $available_quantity = StockAvailable::getQuantityAvailableByProduct(
                    $id_product,
                    $new_id_product_attribute
                );
                $quantity_to_check = $new_cart_product_quantity;
                if (Validate::isLoadedObject($customization)) {
                    $quantity_to_check += $customization->quantity;
                } else {
                    $quantity_to_check += $old_quantity;
                }
                if (!PosProduct::isEnabledOrderOutOfStock($product->out_of_stock)) {
                    if ($quantity_to_check > $available_quantity) {
                        $this->ajax_json['message'] = sprintf(
                            $this->module->i18n['oops_not_enough_product_in_stock'],
                            $available_quantity
                        );

                        return;
                    }
                }
                if (Validate::isLoadedObject($customization)) {
                    $customization->id_product_attribute = $new_id_product_attribute;
                    if ($new_cart_product_quantity == 0 && $customization->quantity < $new_minimal_quantity) {
                        $customization->quantity = $new_minimal_quantity;
                    }
                    $success[] = $customization->update();
                }
                if ($this->context->cart->containsProduct($id_product, $new_id_product_attribute) && empty($customization_quantity['quantity']) && $id_customization == 0) {
                    $success[] = array_sum($success) >= count($success) && $this->context->cart->deletePosProduct($id_product, $id_product_attribute);
                    $success[] = array_sum($success) >= count($success) && PosSpecificPrice::deleteByIdCart($this->context->cart->id, $id_product, $id_product_attribute);
                    $success[] = array_sum($success) >= count($success) && $this->context->cart->updateQtyPos($old_quantity, $id_product, $this->context->shop, $new_id_product_attribute);
                } else {
                    $old_specific_price = PosSpecificPrice::doesSpecificPriceExist($this->context->cart->id, $id_product, $id_product_attribute);
                    $success[] = array_sum($success) >= count($success) && (!$old_specific_price || ($old_specific_price && PosSpecificPrice::updateProductCombination($this->context->cart->id, $id_product, $new_id_product_attribute, $id_product_attribute)));
                    if (Validate::isLoadedObject($customization)) {
                        if ((int) $old_cart_product_quantity - (int) $customization->quantity > 0) {
                            $quantity_to_down = $customization->quantity;
                            if ($old_cart_product_quantity - $customization->quantity < $old_minimal_quantity) {
                                $quantity_to_down = $old_cart_product_quantity - $old_minimal_quantity;
                            }
                            if ($quantity_to_down != 0) {
                                $success[] = array_sum($success) >= count($success) && $this->context->cart->updateQtyPos($quantity_to_down, $id_product, $this->context->shop, $id_product_attribute, 'down');
                            }
                        } else {
                            $success[] = array_sum($success) >= count($success) && $this->context->cart->deletePosProduct($id_product, $id_product_attribute);
                        }
                        $success[] = array_sum($success) >= count($success) && $this->context->cart->updateQtyPos($customization->quantity, $id_product, $this->context->shop, $new_id_product_attribute);
                    } else {
                        if ($customization_quantity['quantity']) {
                            $quantity_to_down = $old_quantity;
                            if ($old_cart_product_quantity - $customization_quantity['quantity'] < $old_minimal_quantity) {
                                $quantity_to_down = $old_cart_product_quantity - $old_minimal_quantity;
                            }
                            if ($quantity_to_down != 0) {
                                $success[] = array_sum($success) >= count($success) && $this->context->cart->updateQtyPos($quantity_to_down, $id_product, $this->context->shop, $id_product_attribute, 'down');
                            }
                            $quantity_to_add = $old_quantity;
                            if ($old_quantity < $new_minimal_quantity) {
                                $quantity_to_add = $new_minimal_quantity;
                            }
                            $success[] = array_sum($success) >= count($success) && $this->context->cart->updateQtyPos($quantity_to_add, $id_product, $this->context->shop, $new_id_product_attribute);
                        } else {
                            $quantity_to_update = $old_quantity;
                            if ($old_quantity < $new_minimal_quantity) {
                                $quantity_to_update = $new_minimal_quantity;
                            }
                            $success[] = array_sum($success) >= count($success) && $this->context->cart->updateCartProduct($id_product, $new_id_product_attribute, $id_product_attribute, $this->context->shop->id, $quantity_to_update);
                        }
                    }
                }
                if (array_sum($success) >= count($success)) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['changed_combinations'];
                }
                break;

            default:
                break;
        }
        if ($this->ajax_json['success']) {
            $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails($refresh_cart);
        }
    }

    /**
     * @param float $value
     * @param int $type
     * @param int $id_product
     * @param int $id_product_attribute
     *
     * @return bool
     */
    protected function applySpecificPrice($value, $type, $id_product, $id_product_attribute = null)
    {
        $id_shop = $this->context->shop->id;
        if (!$this->isDiscountValid($value, $type, $id_product, $id_product_attribute)) {
            return false;
        }

        $id_group = Customer::getDefaultGroupId($this->context->cart->id_customer);
        $id_country = Customer::getCurrentCountry($this->context->cart->id_customer, $this->context->cart);
        $specific_price_value = SpecificPrice::getSpecificPrice($id_product, $id_shop, 0, $id_country, $id_group, 1, $id_product_attribute, 0, $this->context->cart->id);
        $specific_price = new PosSpecificPrice();
        $specific_price->id = 0;
        // If there is a specific price set for this cart only, this must be created by RockPOS.
        // Let's override it!
        if (!empty($specific_price_value) && $specific_price_value['id_cart'] == $this->context->cart->id) {
            $specific_price->id = $specific_price_value['id_specific_price'];
        }

        $specific_price->from = date('Y-m-d H:i:00', time());
        $specific_price->to = date('Y-m-d H:i:00', time() + PosConstants::DISCOUNT_DURATION_PRODUCT);
        $specific_price->id_customer = 0; // Set to "0" so that it still works when changing customer profile from Guest to a specific Customer
        $specific_price->id_cart = $this->context->cart->id;
        $specific_price->id_shop = $id_shop;
        $specific_price->id_shop_group = $this->context->shop->id_shop_group;
        $specific_price->id_currency = 0; // set to "0" so that it will always in cart
        $specific_price->id_country = $id_country;
        $specific_price->id_product = $id_product;
        $specific_price->id_product_attribute = $id_product_attribute;
        $specific_price->from_quantity = 1;
        $specific_price->id_group = $id_group;
        $price = $value;

        $default_currency = new PosCurrency((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        if ((int) $this->context->currency->id !== (int) $default_currency->id && $type != PosConstants::DISCOUNT_TYPE_PERCENTAGE) {
            $price = Tools::convertPriceFull($value, $this->context->currency, $default_currency);
        }

        switch ($type) {
            case PosConstants::DISCOUNT_TYPE_AMOUNT:
                $specific_price->reduction_type = PosConstants::DISCOUNT_TYPE_AMOUNT;
                $specific_price->price = -1;
                $specific_price->reduction = $price;
                break;

            case PosConstants::DISCOUNT_TYPE_PERCENTAGE:
                $specific_price->reduction_type = PosConstants::DISCOUNT_TYPE_PERCENTAGE;
                $specific_price->price = -1;
                $specific_price->reduction = $price / 100;
                break;

            case PosConstants::PRODUCT_PRICE_TYPE:
            default:
                $specific_price->reduction_type = PosConstants::DISCOUNT_TYPE_AMOUNT;
                $specific_price->price = $price;
                $specific_price->reduction = 0;
                break;
        }

        return $specific_price->save();
    }

    /**
     * Make surre is compatible with current cart amount.
     *
     * @return bool
     */
    protected function validateOrderDiscount()
    {
        $cart_rules = $this->context->cart->getCartRules(CartRule::FILTER_ACTION_REDUCTION);
        $current_cart_rule = array();
        if (!empty($cart_rules) && count($cart_rules) > 0) {
            foreach ($cart_rules as $cart_rule) {
                if ((int) $cart_rule['id_cart'] === (int) $this->context->cart->id) {
                    $current_cart_rule = $cart_rule;
                    break;
                }
            }
        }
        // only check in case of reduction amount
        if (!empty($current_cart_rule) && (float) $current_cart_rule['reduction_amount'] !== 0.0 && (float) $current_cart_rule['reduction_percent'] === 0.0) {
            if ((float) $current_cart_rule['reduction_amount'] > (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)) {
                $this->context->cart->removeCartRule((int) $current_cart_rule['id_cart_rule']);
                // $this->renderBlockOrderDiscounts(); @todo
            }
        }

        return true;
    }

    /**
     * @param float $discount_value
     * @param string $discount_type
     * @param int $id_product
     * @param int $id_product_attribute
     *
     * @return bool
     */
    protected function isDiscountValid($discount_value, $discount_type, $id_product = 0, $id_product_attribute = 0)
    {
        $is_valid = false;
        switch ($discount_type) {
            case PosConstants::DISCOUNT_TYPE_PERCENTAGE:
                $is_valid = is_numeric($discount_value) && $discount_value >= 0 && $discount_value <= 100;
                break;

            case PosConstants::DISCOUNT_TYPE_AMOUNT:
                $price = PosProduct::getPriceStatic(
                    (int) $id_product,
                    true,
                    (int) $id_product_attribute,
                    6,
                    null,
                    false,
                    false
                );
                $is_valid = is_numeric($discount_value) && $discount_value >= 0 && $discount_value <= $price;
                break;

            case PosConstants::PRODUCT_PRICE_TYPE:
                $is_valid = is_numeric($discount_value) && $discount_value >= 0;
                break;

            default:
                break;
        }

        return $is_valid;
    }

    /**
     * @input
     * {
     *      id_product: int,
     *      action: "getCombinations",
     *      ajax: "1"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          combinations: [
     *              {
     *                  int: {
     *                      attributes: {
     *                          string: {
     *                              id_attribute: int,
     *                              group_name: string,
     *                              value: string,
     *                              color: string,
     *                              image: string,
     *                              position: int
     *                          }
     *                      },
     *                      quantity: int
     *                      is_default: boolean,
     *                      allow_ordering_out_of_stock: boolean,
     *                      image_url: string
     *                  }
     *              }
     *          ]
     *      }
     * }
     */
    public function ajaxProcessGetCombinations()
    {
        $id_product = (int) Tools::getValue('id_product');
        if (empty($id_product)) {
            $this->ajax_json['message'] = $this->module->i18n['the_item_is_not_available'];

            return;
        }

        $product = new Product($id_product, false, $this->context->language->id, $this->context->shop->id);
        if (!Validate::isLoadedObject($product)) {
            $this->ajax_json['message'] = $this->module->i18n['the_item_is_not_available'];

            return;
        }
        $combinations = PosProduct::getCombinations($id_product, $this->context->shop->id);
        $default_id_product_attribute = $product->getDefaultIdProductAttribute();
        $allow_ordering_out_of_stock = PosProduct::allowOrderingOutOfStock($id_product);
        foreach ($combinations as $id_product_attribute => &$combination) {
            $combination['is_default'] = ($id_product_attribute == $default_id_product_attribute);
            $combination['allow_ordering_out_of_stock'] = $allow_ordering_out_of_stock;
            $combination['image_url'] = empty($combination['id_image']) ? null : $this->context->link->getImageLink(
                $product->link_rewrite,
                $combination['id_image'],
                PosImageType::getFormattedName(PosConstants::DEFAULT_ATTRIBUTE_IMAGE_TYPE)
            );
            unset($combination['id_image']);
        }
        $this->ajax_json['success'] = true;
        $this->ajax_json['message'] = $this->module->i18n['combinations_found'];
        $this->ajax_json['data']['combinations'] = $combinations;
    }

    /**
     * @input
     * {
     *      action: "customization",
     *      ajax: "1",
     *      id_product => int,
     *      id_product_attribute => int,
     *      quantity => int,
     *      id_address_delivery => int,
     *      delete_images => [int, int],
     *     file => [int => string, int => string]
     *      text_fields => [int => string, int => string],
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              cart: {},// Refer to action "getAllInOne" for further details
     *          }
     *      }
     * }
     */
    public function ajaxProcessCustomization()
    {
        $delete_images = Tools::getValue('delete_images');
        $image_fields = $_FILES;
        $text_fields = Tools::getValue('text_fields', array());
        $id_product = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');
        $quantity = (int) Tools::getValue('quantity');
        $id_address_delivery = (int) Tools::getValue('id_address_delivery');
        $id_customization = (int) Tools::getValue('id_customization', 0);
        if (!empty($delete_images)) {
            foreach ($delete_images as $id_customization_field) {
                $customization = PosCustomization::getCustomization(
                    $this->context->cart->id,
                    $id_product,
                    $id_customization_field
                );
                if (!empty($customization)) {
                    if ($customization['type'] == 0) {
                        unlink(_PS_UPLOAD_DIR_ . $customization['value']);
                        unlink(_PS_UPLOAD_DIR_ . $customization['value'] . PosConstants::IMAGE_SUFFIXE);
                    }
                    PosCustomizationData::delete((int) $customization['id_customization'], $id_customization_field);
                }
            }
        }

        if ($id_customization === 0) {
            $customization = new Customization();
            $customization->id_cart = $this->context->cart->id;
            $customization->id_product = $id_product;
            $customization->id_product_attribute = $id_product_attribute;
            $customization->quantity = $quantity;
            $customization->id_address_delivery = $id_address_delivery;
            $customization->quantity_refunded = 0;
            $customization->quantity_returned = 0;
            $customization->in_cart = 1;
            $customization->add();
            $id_customization = $customization->id;
            if (Tools::version_compare(_PS_VERSION_, '1.7', '>=') == true) {
                $this->context->cart->updateCustomization(
                    $id_product,
                    $id_product_attribute,
                    $id_address_delivery,
                    $id_customization
                );
            }
        }
        $this->addImageFields($image_fields, $id_customization);
        $this->addTextFields($text_fields, $id_product, $id_customization);
        $this->ajax_json['success'] = count($this->errors) == 0;
        if ($this->ajax_json['success']) {
            $this->ajax_json['message'] = $this->module->i18n['saved_customization'];
            $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails();
        }
    }

    /**
     * @param array $image_fields
     *                                <pre>
     *                                array (
     *                                int => array(
     *                                name => string,
     *                                type => string,
     *                                tmp_name => string,
     *                                error => boolean,
     *                                size => float
     *                                )
     *                                ...
     *                                )
     * @param int $id_customization
     *
     * @return bool
     */
    protected function addImageFields(array $image_fields, $id_customization)
    {
        if (!empty($image_fields)) {
            foreach ($image_fields as $id_customization_field => $file) {
                if (!empty($file['tmp_name'])) {
                    $file_name = md5(uniqid(rand(), true));
                    if ($error = ImageManager::validateUpload(
                        $file,
                        (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE')
                    )) {
                        $this->errors[] = $error;
                        $this->ajax_json['message'] = $error;
                    }

                    if (count($this->errors) == 0) {
                        $product_picture_width = (int) Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
                        $product_picture_height = (int) Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
                        $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                        if (!move_uploaded_file($file['tmp_name'], $tmp_name)) {
                            $this->errors[] = $this->module->i18n['an_error_occurred_during_the_image_move_process'];
                            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_during_the_image_move_process'];
                        }
                        if (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name)) {
                            $this->errors[] = $this->module->i18n['an_error_occurred_during_the_image_resize_process'];
                            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_during_the_image_resize_process'];
                        } elseif (!ImageManager::resize(
                            $tmp_name,
                            _PS_UPLOAD_DIR_ . $file_name . PosConstants::IMAGE_SUFFIXE,
                            $product_picture_width,
                            $product_picture_height
                        )) {
                            $this->errors[] = $this->module->i18n['an_error_occurred_during_the_image_resize_process'];
                            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_during_the_image_resize_process'];
                        } elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777) || !chmod(_PS_UPLOAD_DIR_ . $file_name . PosConstants::IMAGE_SUFFIXE, 0777)) {
                            $this->errors[] = $this->module->i18n['an_error_occurred_during_the_image_upload_process'];
                            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_during_the_image_upload_process'];
                        } else {
                            PosCustomizationData::add($id_customization_field, Product::CUSTOMIZE_FILE, $file_name, $id_customization);
                        }
                        unlink($tmp_name);
                    }
                }
            }
        }

        return count($this->errors) == 0;
    }

    /**
     * @param array $text_fields
     *                                <pre>
     *                                array(
     *                                int => string,
     *                                int => string
     *                                ...
     *                                )
     * @param int $id_product
     * @param int $id_customization
     *
     * @return bool
     */
    protected function addTextFields(array $text_fields, $id_product, $id_customization)
    {
        if (!empty($text_fields)) {
            foreach ($text_fields as $id_customization_field => $value) {
                if (Validate::isMessage($value) && $value !== '') {
                    PosCustomizationData::add(
                        $id_customization_field,
                        PosProduct::CUSTOMIZE_TEXTFIELD,
                        $value,
                        $id_customization
                    );
                } elseif ($value == '') {
                    $customization = PosCustomization::getCustomization(
                        $this->context->cart->id,
                        $id_product,
                        $id_customization_field
                    );
                    if (!empty($customization)) {
                        PosCustomizationData::delete((int) $customization['id_customization'], $id_customization_field);
                    }
                } else {
                    $this->errors[] = $this->module->i18n['invalid_message'];
                }
            }
        }

        return count($this->errors) == 0;
    }

    /**
     * @input
     *  {
     *      action: "orderDiscount",
     *      sub_action: "add" | "remove"
     *      ajax: "1",
     *      discount_type: string,// required if sub_action = add. Values: PosConstants::DISCOUNT_TYPE_AMOUNT, PosConstants::DISCOUNT_TYPE_PERCENTAGE, PosConstants::DISCOUNT_TYPE_VOUCHER
     *      discount_value: string,// required if sub_action = add
     *      id_cart_rule: int// required if sub_action = remove
     *  }
     * @output
     *  {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              cart: {
     *                  discounts: [],// Refer to action "getAllInOne" for further details
     *                  total: {}// Refer to action "getAllInOne" for further details
     *              }
     *          }
     *      }
     *  }
     */
    public function ajaxProcessOrderDiscount()
    {
        switch ($this->sub_action) {
            case 'add':
                $discount_type = trim(Tools::getValue('discount_type'));
                $discount_value = trim(Tools::getValue('discount_value'));

//                Configuration::get('msg_after_'.$iso_code);

//                $this->context->cookie->__set($saved_key_guest, 'no');
//                $this->context->cookie->write();
//                $can_guest = $this->context->cookie->__get($saved_key_guest);



                $error_message = $this->isValidOrderDiscount($discount_type, $discount_value);
                if ($error_message) {
                    $this->ajax_json['message'] = $error_message;
                    $this->ajax_json['success'] = false;
                } else {
                    $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));

                    $discount_arr = array(
                        'discount_type' => $discount_type,
                        'discount_value' => $discount_value,
                        'reduction_percent' => $cart_rule->reduction_percent,
                        'reduction_amount' => $cart_rule->reduction_amount,
                    );




                    $discount_index = 'discounts_'.$this->context->cart->id;

                    Configuration::updateValue($discount_index, json_encode($discount_arr));

                    if ($discount_type === PosConstants::DISCOUNT_TYPE_VOUCHER) {
                        $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));
                        // Ensure reduction_tax = false for vouchers
                        if (Validate::isLoadedObject($cart_rule)) {
                            $cart_rule->reduction_tax = false;
                            $cart_rule->update();
                        }
                    }elseif ($discount_type ==='gift-card'){
                        $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));
                        // Ensure reduction_tax = false for gift cards
                        if (Validate::isLoadedObject($cart_rule)) {
                            $cart_rule->reduction_tax = false;
                            $cart_rule->update();
                        }
                    }elseif ($discount_type === 'credit-slip'){
                        $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));
                        // Ensure reduction_tax = false for credit slips
                        if (Validate::isLoadedObject($cart_rule)) {
                            $cart_rule->reduction_tax = false;
                            $cart_rule->update();
                        }
                    } else {
                        // add a new cart rule if it's not an existing voucher
                        $languages = Language::getLanguages(false);
                        $names = array();
                        foreach ($languages as $language) {
                            $names[$language['id_lang']] = $this->module->i18n['point_of_sale'];
                        }
                        $cart_rule = PosCartRule::addOrderDiscount(
                            $this->context,
                            $discount_type,
                            $discount_value,
                            $names,
                            $this->module->i18n['point_of_sale']
                        );
                        // Ensure reduction_tax = false for new percentage/amount discounts
                        if (Validate::isLoadedObject($cart_rule)) {
                            $cart_rule->reduction_tax = false;
                            $cart_rule->update();
                        }
                    }
                    if (Validate::isLoadedObject($cart_rule) && (bool) $this->context->cart->addCartRule((int) $cart_rule->id)) {
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['message'] = $this->module->i18n['discount_applied'];
                    }
                }
                break;

            case 'remove':
                $id_cart_rule = (int) trim(Tools::getValue('id_cart_rule'));
                if (!$id_cart_rule || !Validate::isLoadedObject($this->context->cart)) {
                    return;
                }
                if ($this->context->cart->removeCartRule($id_cart_rule)) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['discount_removed'];
                }

                break;

            default:
                break;
        }

        $this->ajax_json['data']['transaction'] = array();
        $this->ajax_json['data']['transaction']['cart'] = array();
        $this->ajax_json['data']['transaction']['cart']['products'] = $this->getCartProducts(true);
        $this->ajax_json['data']['transaction']['cart']['total'] = $this->getCartTotal();
        $this->ajax_json['data']['transaction']['cart']['discounts'] = $this->module->getCartRules();
    }

    /**
     * If the order discount is not valid, return the error message, otherwise, null!
     *
     * @param string $discount_type
     *                               PosConstants::DISCOUNT_TYPE_PERCENTAGE
     *                               PosConstants::DISCOUNT_TYPE_AMOUNT
     *                               PosConstants::DISCOUNT_TYPE_VOUCHER
     * @param string $discount_value
     *
     * @return string
     */
    protected function isValidOrderDiscount($discount_type, $discount_value)
    {
        $error_message = null;
        switch ($discount_type) {
            case PosConstants::DISCOUNT_TYPE_PERCENTAGE:
                if (!is_numeric($discount_value) || $discount_value <= 0 || $discount_value > 100) {
                    $error_message = $this->module->i18n['discount_should_be_between_0_and_100'];
                }
                break;
            case PosConstants::DISCOUNT_TYPE_AMOUNT:
                if (!is_numeric($discount_value) || $discount_value <= 0 || $discount_value > $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)) {
                    $error_message = $this->module->i18n['discount_invalid'];
                }
                break;

            case PosConstants::DISCOUNT_TYPE_VOUCHER:
                if (!$discount_value) {
                    $error_message = $this->module->i18n['you_must_enter_a_voucher_code'];
                } elseif (!Validate::isCleanHtml($discount_value)) {
                    $error_message = $this->module->i18n['the_voucher_code_is_invalid'];
                } else {
                    $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));
                    if (Validate::isLoadedObject($cart_rule)) {
                        $error_message = $cart_rule->checkValidity($this->context, false, true);
                    } else {
                        $error_message = $this->module->i18n['this_voucher_does_not_exist'];
                    }
                }
                break;
            case 'credit-slip':
                if (!$discount_value) {
                    $error_message = $this->module->i18n['you_must_enter_a_voucher_code'];
                } elseif (!Validate::isCleanHtml($discount_value)) {
//                    $error_message = $this->module->i18n['the_voucher_code_is_invalid'];
                    $error_message = "Code does not exist!";
                } else {
                    $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));
                    if (Validate::isLoadedObject($cart_rule)) {
                        $error_message = $cart_rule->checkValidity($this->context, false, true);
                    } else {
//                        $error_message = $this->module->i18n['this_voucher_does_not_exist'];
                        $error_message = "Code does not exist!";
                    }
                }
                break;
            case 'gift-card':
                if (!$discount_value) {
                    $error_message = $this->module->i18n['you_must_enter_a_voucher_code'];
                } elseif (!Validate::isCleanHtml($discount_value)) {
//                    $error_message = $this->module->i18n['the_voucher_code_is_invalid'];
                    $error_message = "Code does not exist!";
                } else {
                    $cart_rule = new CartRule(CartRule::getIdByCode($discount_value));
                    if (Validate::isLoadedObject($cart_rule)) {
                        $error_message = $cart_rule->checkValidity($this->context, false, true);
                    } else {
//                        $error_message = $this->module->i18n['this_voucher_does_not_exist'];
                        $error_message = "Code does not exist!";
                    }
                }
                break;
            default:
                $error_message = $this->module->i18n['discount_invalid'];
                break;
        }

        return $error_message;
    }

    /**
     * @input
     * {
     *      action: "customer"
     *      sub_action: "associate" | "remove" | "add"
     *      id_customer: int // required, if sub_action = associate,
     *      email: string, // if sub_action = add
     *      firstname: string, // if sub_action = add
     *      lastname: string, // if sub_action = add
     *      id_gender: int, // if sub_action = add
     *      id_default_group: string, // if sub_action = add
     *      company: string, // if sub_action = add
     *      sicret: string, // if sub_action = add
     *      ape: string, // if sub_action = add
     *      website: string, // if sub_action = add
     *      passwd: string, // if sub_action = add
     *      newsletter: boolean, // if sub_action = add
     *      groupBox: array, // if sub_action = add
     *      years: int, // if sub_action = add
     *      months: int, // if sub_action = add
     *      days: int, // if sub_action = add
     *      ajax: "1"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              cart: {},// Refer to action "getAllInOne" for further details
     *              customer: {}// Refer to action "getAllInOne" for further details
     *          }
     *      }
     * }
     */
    public function ajaxProcessCustomer()
    {
        switch ($this->sub_action) {
            case 'associate':
                $barcode = Tools::getValue('barcode');
                $customer = PosCustomer::getCustomerByBarcode($barcode);
                $id_customer = $barcode && $customer ? $customer['id_customer'] : (int) Tools::getValue('id_customer');
                if ($this->associateCustomer($id_customer)) {
                    $this->updateCartRulesByIdCustomer($id_customer);
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['customer_associated'];
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['cannot_associate_this_customer'];
                }
                break;

            case 'remove':
                if (!empty($this->context->cart->id_customer)) {
                    $old_id_customer = $this->context->cart->id_customer;
                    if (configuration::get('POS_GUEST_CHECKOUT')) {
                        $default_id_customer = PosCustomer::getDefaultCustomerIds(
                            $this->context->cookie->pos_id_employee,
                            $this->context->shop->id
                        );
                        $this->context->cart->id_customer = $default_id_customer;
                        $this->context->cart->updateAddressId(
                            (int) Address::getFirstCustomerAddressId($old_id_customer),
                            (int) Address::getFirstCustomerAddressId($default_id_customer)
                        );
                    } else {
                        $this->context->cart->id_customer = 0;
                        $this->context->cart->updateAddressId(
                            (int) Address::getFirstCustomerAddressId($old_id_customer),
                            0
                        );
                    }
                    $this->updateCartRulesByIdCustomer($this->context->cart->id_customer);
                    if ($this->context->cart->save()) {
                        Cache::clean('getContextualValue_*');
                        PosSpecificPrice::deleteByIdCart($old_id_customer);
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['message'] = $this->module->i18n['customer_unassociated'];
                    }
                }
                break;

            case 'add': // this case actually consists of 2 tasks: add a new customer + associate this customer to the current cart
                $customer = new PosCustomer();
                $this->copyFromPost($customer, 'customer');
                $email = $customer->email;
                $phone = Tools::getValue('phone');
                $note = Tools::getValue('note');
                $barcode = Tools::getValue('barcode');
                $fake_email = false;

                if (empty($customer->firstname) && Validate::isPhoneNumber($phone)) {
                    $customer->firstname = $phone;
                }

                if (empty($email) && empty($customer->firstname)) {
                    $this->ajax_json['message'] = $this->module->i18n['please_enter_email_or_firstname'];
                    break;
                }

                if (!empty($barcode)) {
                    if (PosCustomer::getCustomerByBarcode($barcode)) {
                        $this->ajax_json['message'] = $this->module->i18n['an_account_using_this_barcode_has_already_been_registered'];
                        break;
                    }
                }

                if (!empty($email)) {
                    if (PosCustomer::getCustomersByEmail($email)) {
                        $this->ajax_json['message'] = $this->module->i18n['an_email_has_already_been_registered'];
                        break;
                    }
                } else {
                    $email = PosTools::generateFakeEmail(strstr(Configuration::get('PS_SHOP_EMAIL'), '@', false), $customer->firstname . $customer->lastname);
                    $fake_email = true;
                }
                $password = trim($customer->passwd);

                if (empty($password)) {
                    $password = Tools::encrypt(Tools::passwdGen());
                }
                if ($customer->newsletter && !$fake_email) {
                    $this->processCustomerNewsletter($customer);
                }
                $customer->email = $email;
                $customer->note = $note;
                $customer->active = 1;

                if ($customer->add()) {
                    if (property_exists($customer, 'id_employee')) {
                        $customer->updateEmployee($this->context->cookie->pos_id_employee);
                    }
                    if (Validate::isPhoneNumber($phone)) {
                        PosAddress::createAddress($customer, $this->context->shop->id, $phone);
                    }
                    !$fake_email && $this->sendConfirmationMail($customer, $password);
                    if ($this->associateCustomer($customer->id)) {
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['message'] = $this->module->i18n['customer_added'];
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['cannot_associate_this_customer'];
                    }
                    $this->updateCartRulesByIdCustomer($customer->id);
                    PosCustomer::addBarcode(
                        $customer->id,
                        Tools::getValue('barcode') ? Tools::getValue('barcode') : sprintf("%06d", $customer->id)
                    );
                    $this->module->hookActionCustomerSave(['customer' => $customer]);
                }
                break;
            default:
                break;
        }

        if ($this->ajax_json['success']) {
            $this->module->initContext(true);
            $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails();
            $this->ajax_json['data']['transaction']['customer'] = $this->getCustomer();
            $this->ajax_json['data']['plugins']['customsale']['tax_rules_groups'] = $this->getTaxRulesGroups();
        }
    }

    /**
     * Process the newsletter settings and set the customer infos.
     *
     * @param Customer $customer reference on the customer Object
     */
    protected function processCustomerNewsletter(&$customer)
    {
        $module_newsletter = Module::getInstanceByName('blocknewsletter');
        if ($module_newsletter->active) {
            $customer->newsletter = true;
            $customer->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
            $customer->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
            $module_newsletter->confirmSubscription($customer->email);
        }
    }

    /**
     * @param int $id_customer
     *
     * @return bool
     */
    protected function associateCustomer($id_customer)
    {
        Cache::clean('getContextualValue_*');
        $customer = new PosCustomer((int) $id_customer);
        $flag = false;
        if (Validate::isLoadedObject($customer) && $this->context->cart->assignCustomer($id_customer)) {
            $customer->addPosCustomerGroup();
            if (empty($this->context->cookie->pos_id_cart) && !empty($this->context->cart->id)) {
                $this->context->cookie->pos_id_cart = (int) $this->context->cart->id;
                $this->context->cookie->write();
            }
            $flag = true;
        }

        return $flag;
    }

    /**
     * @param int $id_customer
     *
     * @return bool
     */
    protected function updateCartRulesByIdCustomer($id_customer)
    {
        return PosCartRule::updateCartRulesByIdCustomer($id_customer, $this->module->getCartRules());
    }

    /**
     * @param Customer $customer
     * @param string $password
     * @param string $default_email_title
     *
     * @return bool
     */
    protected function sendConfirmationMail(Customer $customer, $password)
    {
        $success = array();
        if (Configuration::get('POS_EMAILING_ACCOUNT_CREATION') && $customer->email !== Configuration::get('PS_SHOP_EMAIL')) {
            $email_title = $customer->getDisplayName($this->module->i18n['there']);
            $email_params = array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{passwd}' => $password,
            );

            $success[] = Mail::Send(
                $this->context->language->id,
                'account',
                $this->module->i18n['welcome'],
                $email_params,
                $customer->email,
                $email_title
            );
        }

        return array_sum($success) >= count($success);
    }

    /**
     * @input
     * {
     *      action: "address",
     *      sub_action: "add" | "update" | "change"
     *      ajax: "1",
     *      id_address => int,
     *      id_customer => int,
     *      id_country => int,
     *      id_state => int,
     *      alias => string,
     *      company => string,
     *      lastname => string,
     *      firstname => string,
     *      address1 => string,
     *      address2 => string,
     *      postcode => string,
     *      city => string,
     *      phone => string,
     *      phone_mobile => string,
     *      vat_number => string,
     *      dni => string,
     *      other => string
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              cart: {},// Refer to action "getAllInOne" for further details
     *              customer: {}// Refer to action "getAllInOne" for further details
     *          }
     *      }
     * }
     */
    public function ajaxProcessAddress()
    {
        $delivery_address = $invoice_address = null;
        switch ($this->sub_action) {
            case 'add':
                $address = new PosAddress();
                $this->copyFromPost($address, 'address');
                if ($address->add()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['address_added'];
                    $delivery_address = $address;
                    $invoice_address = $address;
                }
                break;

            case 'update':
                $address = new PosAddress((int) Tools::getValue('id_address', 0));
                $id_country = (int) Configuration::get('PS_SHOP_COUNTRY_ID');
                $country = new Country($id_country);
                if (!$country->active) {
                    $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
                }
                $address->id_country = $id_country;
                if (Country::containsStates($id_country)) {
                    $address->id_state = Configuration::get('PS_SHOP_STATE_ID');
                }
                $address->company = '';
                $address->address1 = Configuration::get('PS_SHOP_ADDR1');
                $address->address2 = Configuration::get('PS_SHOP_ADDR2');
                $address->city = Configuration::get('PS_SHOP_CITY');

                $address->postcode = Configuration::get('PS_SHOP_CODE');
                $this->copyFromPost($address, 'address', true);
                if ($address->update()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['address_updated'];
                }
                break;

            case 'change':
                $delivery_address = new PosAddress((int) Tools::getValue('id_address_delivery'));
                $invoice_address = new PosAddress((int) Tools::getValue('id_address_invoice'));
                break;
        }

        if ($this->context->cart->updateAddresses($delivery_address, $invoice_address)) {
            // if address(es) changed
            if ($this->sub_action == 'change') {
                $this->ajax_json['success'] = true;
                $this->ajax_json['message'] = $this->module->i18n['address_changed'];
            }
        }
        if ($this->ajax_json['success']) {
            $this->ajax_json['data']['transaction']['customer'] = $this->getCustomer();
            $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails();
        }
    }

    /**
     * @input
     *  {
     *      action: "shipping",
     *      id_carrier: int// required if sub_action = change
     *      shipping_cost: float // required if sub_action = custom
     *  }
     * @output
     *  {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              cart: {
     *                  id_carrier: int,
     *                  shipping: {},// Refer to action "getAllInOne" for further details
     *                  total: {}// Refer to action "getAllInOne" for further details
     *              }
     *          }
     *      }
     *  }
     */
    public function ajaxProcessShipping()
    {
        switch ($this->sub_action) {
            case 'change':
                $deliver_option = $this->getDeliverOption((int) Tools::getValue('id_carrier'));
                $this->context->cart->setDeliveryOption($deliver_option);
                $this->ajax_json['message'] = $this->module->i18n['cannot_change_carrier'];
                if ($this->context->cart->update()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['carrier_changed'];
                } else {
                    $this->ajax_json['success'] = false;
                    $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
                }
                break;

            case 'free':
                $free = Tools::getValue('free');
                $pos_carrier = PosCarrier::getPosCarrier();
                $id_carrier = $pos_carrier->id;
                if (!$free) {
                    $carriers = $this->context->cart->getCarriers($this->context->language->id);
                    foreach ($carriers as $carrier) {
                        if ($carrier['id_carrier'] !== $id_carrier) {
                            $id_carrier = (int) $carrier['id_carrier'];
                            break;
                        }
                    }
                }
                $deliver_option = array(
                    $this->context->cart->id_address_delivery => (int) $id_carrier . ',',
                );
                $this->context->cart->setDeliveryOption($deliver_option);
                $this->ajax_json['message'] = $this->module->i18n['cannot_set_free_shipping'];
                if ($this->context->cart->update()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['update_successfully'];
                } else {
                    $this->ajax_json['success'] = false;
                    $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
                }
                break;

            case 'custom':
                $shipping_cost = (float) Tools::getValue('shipping_cost');
                if (PosActiveCart::setShippingCost($this->context->cart->id, $shipping_cost) && $this->context->cart->update()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['update_successfully'];
                } else {
                    $this->ajax_json['success'] = false;
                    $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
                }
                break;
            case 'get_shipping_fees':
                $id_carriers = array();
                $carriers = $this->context->cart->getCarriers($this->context->language->id, true);
                foreach ($carriers as $carrier) {
                    $id_carriers[] = $carrier['id_carrier'];
                }
                $fees = array();
                if (!empty($id_carriers)) {
                    $products = $this->context->cart->getProducts();
                    $tax = PS_TAX_INC == $this->context->customer->getPriceDisplayMethod();
                    foreach ($id_carriers as $id_carrier) {
                        $fees[$id_carrier] = $this->context->cart->getPackageShippingCost((int) $id_carrier, $tax, null, $products);
                    }
                }

                $this->ajax_json['success'] = true;
                $this->ajax_json['data']['fees'] = (object) $fees;
                break;
            default:
                break;
        }
        $this->ajax_json['data']['transaction'] = array();
        $this->ajax_json['data']['transaction']['cart'] = array();
        $this->ajax_json['data']['transaction']['cart']['id_carrier'] = $this->context->cart->id_carrier;
        $this->ajax_json['data']['transaction']['cart']['shipping'] = $this->getShipping();
        $this->ajax_json['data']['transaction']['cart']['total'] = $this->getCartTotal();
        $this->ajax_json['data']['transaction']['customer']['carriers'] = $this->context->cart->getCarriers(
            $this->context->language->id,
            true
        );
    }

    /**
     * @input
     *  {
     *      action: "login",
     *      password: string,
     *      email: string,
     *      stay_logged_in: int,
     *  }
     * @output
     *  {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          env: {
     *              is_logged: boolean
     *          }
     *      }
     *  }
     */
    public function ajaxProcessLogin()
    {
        $success = array();
        /* Check fields validity */
        $password = trim(Tools::getValue('password'));
        $email = trim(Tools::getValue('email'));

        if (empty($email) || !Validate::isEmail($email)) {
            $success[] = 0;
            $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
        }

        if (empty($password) || !Validate::isPasswd($password)) {
            $success[] = 0;
            $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
        }

        if (array_sum($success) >= count($success)) {
            $this->context->employee = new PosEmployee();
            $is_employee_loaded = $this->context->employee->getByEmail($email, $password);
            $employee_associated_shop = $this->context->employee->getAssociatedShops();
            if (!$is_employee_loaded) {
                $success[] = 0;
                $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
                $this->context->employee->logout();
            } elseif (empty($employee_associated_shop) && !$this->context->employee->isSuperAdmin()) {
                $success[] = 0;
                $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
                $this->context->employee->logout();
            } elseif (!$this->module->isLoggedSell($this->context->employee)) {
                $success[] = 0;
                $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
            } else {
                $this->context->employee->remote_addr = (int) ip2long(Tools::getRemoteAddr());
                // Update cookie
                $cookie = Context::getContext()->cookie;
                $cookie->pos_id_employee = $this->context->employee->id;
                if (!Tools::getValue('stay_logged_in')) {
                    $cookie->last_activity = time();
                }
                $cookie->write();
                $this->ajax_json['message'] = $this->module->i18n['logged'];
                $this->ajax_json['data']['env']['employee'] = $this->module->getEmployeeInfo();
                $this->ajax_json['data']['env']['employee_shops'] = $this->context->employee->getListShops($this->context->employee->id);
            }
        }
        $this->ajax_json['success'] = array_sum($success) >= count($success);
        $this->ajax_json['data']['env']['is_logged'] = $this->module->isLoggedSell($this->context->employee);
    }

    /**
     * @input
     * {
     *      action: "currency",
     *      sub_action: "change"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              meta: {
     *                  currrency: array()// An element of PosCurrency::getAll()
     *              },
     *              cart: {}// Refer to action "getAllInOne" for further details
     *          }
     *      }
     * }
     */
    public function ajaxProcessCurrency()
    {
        // This is a special action which we hanlde at the first point of the flow.
        // If we come here, most likely, everything is done successfully.
        // Just simply return TRUE in this case.
        switch ($this->sub_action) {
            case 'change':
            default:
                $this->ajax_json['success'] = true;
                $this->ajax_json['message'] = $this->module->i18n['currency_changed'];
                break;
        }
        $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails();
        $this->ajax_json['data']['transaction']['meta'] = array();
        $this->ajax_json['data']['transaction']['meta']['currency'] = PosCurrency::getCurrentCurrency();
    }

    /**
     * @input
     * {
     *      action: "order",
     *      sub_action: "cancel" | "new" | "complete" | "load" | "load_return" | "return"
     *      ajax: "1",
     *      id_order_state: int.// required if sub_action = complete
     *      id_cart: int// required if sub_action = "load" | "return"
     *      products: array // required if sub_action = return. Format?
     * }
     * @output
     * <pre>
     * {
     *      success: boolean,
     *      message: string
     *      data:{
     *          transaction:{// if sub_action = "load", return full transaction object. Refer to getAllInOne()
     *              order: {// available if sub_action = complete and success = true
     *                  reference: string,
     *                  invoice: boolean,
     *                  invoice_auto_print: boolean,
     *                  invoice_url: string,
     *                  receipt: boolean,
     *                  receipt_auto_print: boolean,
     *                  receipt_url: string
     *              },
     *              customer: {}// available if sub_action = new/cancel and success = true
     *          }
     *      }
     * }
     */
    public function ajaxProcessOrder()
    {
        $this->ajax_json['data'] = array();
        $this->ajax_json['data']['transaction'] = array();
        $current_state = (int) Tools::getValue('current_state', 0);
        $id_employee = $this->module->getIdEmployeeByPersonalSetting();
        switch ($this->sub_action) {
            case 'cancel':
                PosActiveCart::removeByIdCart($this->context->cart->id);
                $this->context->cart->delete();
                if ($this->module->clearSession()) {
                    $this->ajax_json['success'] = true;
                    $this->module->initContext(true);
                    $this->ajax_json['message'] = $this->module->i18n['order_canceled'];
                    $this->ajax_json['data']['transaction'] = $this->getTransactionData();
                    $this->ajax_json['data']['transaction']['customer'] = $this->getCustomer();
                    $this->ajax_json['data']['plugins']['multiple_carts']['carts'] = $this->getActiveCarts();
                    $this->ajax_json['data']['plugins']['sales_commission']['employees'] = PosSalesCommission::getSalesManByIdCart($this->context->cart->id);
                }
                break;

            case 'new':
                if ($this->module->clearSession()) {
                    $this->ajax_json['success'] = true;
                    $this->module->initContext(true);
                    $this->ajax_json['message'] = $this->module->i18n['new_order_initialized'];
                    $this->ajax_json['data']['transaction'] = $this->getTransactionData();
                    $this->ajax_json['data']['plugins']['sales_commission']['employees'] = PosSalesCommission::getSalesManByIdCart($this->context->cart->id);
                    $this->ajax_json['data']['plugins']['multiple_carts']['carts'] = $this->getActiveCarts();
                }
                break;
            case 'complete':
//                die('malcomn');
//                var_dump($cart_rule);
//                die();

                $payment_mode = Tools::getValue('payment_mode');
                $id_payment = (int) Tools::getValue('id_payment');
                $given_money = (float) Tools::getValue('given_money');
                $note = PosActiveCart::getNote($this->context->cart->id, $this->context->cart->id_shop);
                if ($id_payment == 0) {
                    $pos_payments = PosPayment::getPosPayments(null, $this->context->shop->id, true);
                    $first_payment_method = reset($pos_payments);
                    $id_payment = $first_payment_method['id_pos_payment'];
                    foreach ($pos_payments as $pos_payment) {
                        if ($pos_payment['is_default']) {
                            $id_payment = $pos_payment['id_pos_payment'];
                        }
                    }
                    $given_money = $payment_mode == 'preOrder' ? 0 : $this->context->cart->getAmountDue();
                }
                if ($this->context->cart->addPayment($id_payment, $given_money)) {
                    $amount_due = $this->context->cart->getAmountDue();
                    if ($payment_mode == 'preOrder' || $amount_due <= 0) {
                        // check customer has already had address
                        if ((int) PosCustomer::getAddressesTotalById((int) $this->context->cart->id_customer) === 0) {
                            $customer = new PosCustomer((int) $this->context->cart->id_customer);
                            if (Validate::isLoadedObject($customer)) {
                                PosAddress::createDummyAddress($customer);
                                $this->context->cart->id_address_delivery = PosAddress::getFirstCustomerAddressId((int) $customer->id);
                                $this->context->cart->id_address_invoice = PosAddress::getFirstCustomerAddressId((int) $customer->id);
                                $this->context->cart->save();
                            }
                        }
                        $id_order_state = $this->module->getDefaultOrderState();
                        try {
                            $cart = $this->context->cart;
                            $validated_order = $this->module->validateOrder(
                                $cart->id,
                                $id_order_state,
                                $cart->getOrderTotal(true, Cart::BOTH),
                                $this->module->i18n['unknown'],
                                $note['note'],
                                array(),
                                null,
                                false,
                                $cart->secure_key,
                                $this->context->shop
                            );
                        } catch (PrestaShopException $prestashop_exception) {
                            $cart = $this->context->cart;
                            $this->module->createLog($prestashop_exception->getMessage(), $cart->id, 'order');
                            $this->module->createLog($cart, $cart->id, 'order');
                            $this->ajax_json['message'] = $prestashop_exception->getMessage();
                        }
                        if (isset($validated_order) && $validated_order) {
                            $order = new PosOrder($this->module->currentOrder);

                            PosActiveCart::updateOrderReference($order->id_cart, $order->id_shop, $order->reference);
                            $sales_commission = new PosSalesCommission($this->context->cart->id);
                            if (Validate::isLoadedObject($sales_commission) && Validate::isLoadedObject($order)) {
                                $sales_commission->order_reference = $order->reference;
                                $sales_commission->commission_rate = Configuration::get('POS_STRAIGHT_VALUE') / 100;
                                $sales_commission->update();
                            }

                            PosProductNote::addOrderToNoteList($order->id, $cart->id);
                            $this->ajax_json['success'] = true;
                            $this->ajax_json['message'] = $this->module->i18n['order_completed'];
                            $this->ajax_json['data']['transaction']['order'] = $this->getOrderData($order);
                            $expiring_days = Tools::getValue('expiring_days');
                            $version = Tools::getValue('version');
                            $this->ajax_json['data']['env']['total_order'] = $version == 'lite' ? PosOrder::getNbFreeOrders(
                                $this->module,
                                $expiring_days,
                                $version
                            ) : 0;
                            //-------------------------------------------------------------------------------------------------------

                            $discount_index = 'discounts_'.$this->context->cart->id;
                            $discount_arr = Configuration::get($discount_index);
                            $current_date =  date('Y-m-d h:i:s') ;

                            if($discount_arr !=false){
                                $discount_arr = json_decode($discount_arr,true);
                                if($discount_arr['discount_type'] == 'voucher'){
                                    $custom_payment_method = 'Voucher';
                                }elseif ($discount_arr['discount_type'] == 'gift-card'){
                                    $custom_payment_method = 'InStore Gift Card';

                                }elseif ($discount_arr['discount_type'] == 'credit-slip'){
                                    $custom_payment_method = 'Credit Slip';
                                }else{
                                    $custom_payment_method = 'Voucher';
                                }


//                                var_dump($discount_arr);
//                                var_dump($order->reference);
//                                var_dump($discount_arr['reduction_amount']);
//                                var_dump($custom_payment_method);
//                                var_dump($current_date);
//                                die();
                            if($discount_arr['reduction_amount'] > 0 ){
                             Db::getInstance()->insert(
                                'order_payment',
                                array(
                                    'order_reference' => $order->reference,
                                    'id_currency' => 3,
                                    'amount' => $discount_arr['reduction_amount'],
                                    'payment_method' => $custom_payment_method,
                                    'conversion_rate' => 1.0,
                                    'date_add' => $current_date,
                                    )
                                );
                            }

                                Configuration::deleteByName($discount_index);
                            }


                            //-------------------------------------------------------------------------------------------------------
                            if (isset($this->context->cookie->new_voucher_code) && !empty($this->context->cookie->new_voucher_code)) {
                                $new_cart_rule = new PosCartRule((int) $this->context->cookie->new_voucher_code);
                                if (Validate::isLoadedObject($new_cart_rule)) {
                                    $new_voucher_code_data = array(
                                        'code' => $new_cart_rule->code,
                                        'id_cart_rule' => (int) $new_cart_rule->id,
                                        'name' => $new_cart_rule->name[$this->context->language->id],
                                        'reduction_amount' => $new_cart_rule->reduction_amount,
                                        'reduction_currency' => $new_cart_rule->reduction_currency,
                                        'reduction_percent' => 0,
                                    );
                                    $this->ajax_json['data']['transaction']['order']['print_data']['receipt']['voucher_code'] = $new_voucher_code_data;
                                }
                            }
                            $this->module->clearSession();
                        }
                        $this->ajax_json['add_payment'] = false;
                    } else {
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['message'] = $this->module->i18n['payment_added'];
                        $this->ajax_json['data']['transaction'] = array();
                        $this->ajax_json['data']['transaction']['paid_payments'] = $this->context->cart->getPayments();
                        $this->ajax_json['add_payment'] = true;
                    }
                }

                break;
            case 'change_order_state':
                $id_order_state = (int) Tools::getValue('id_order_state', 0);
                $order_collections = PosOrder::getByReference(Tools::getValue('reference'));
                $unpaid = 0;
                $total_paid = 0;
                $total_paid_real = 0;
                $total_discounts_tax_incl = 0;
                $products = array();
                foreach ($order_collections as $order) {
                    if ((int) $order->current_state !== (int) $id_order_state) {
                        $order_history = new PosOrderHistory();
                        $order_history->id_order = $order->id;
                        $order_history->id_employee = (int) $this->context->employee->id;

                        $use_existings_payment = !$order->hasInvoice();
                        $order_history->changeIdOrderState($id_order_state, $order, $use_existings_payment);
                        $template_vars = array();
                        // Save all changes
                        if ($order_history->addWithemail(true, $template_vars)) {
                            // synchronizes quantities if needed..
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                foreach ($order->getProducts() as $product) {
                                    if (StockAvailable::dependsOnStock($product['product_id'])) {
                                        StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
                                    }
                                }
                            }
                        }
                    }
                    $total_paid += $order->total_paid;
                    $total_paid_real += $order->total_paid_real;
                    $unpaid += $order->total_paid - $order->total_paid_real;
                    $total_discounts_tax_incl += $order->total_discounts_tax_incl;
                }

                if (Validate::isLoadedObject($order)) {
                    $this->ajax_json = array(
                        'success' => true,
                        'message' => $this->module->i18n['change_order_state_successfully'],
                    );
                } else {
                    $this->ajax_json['success'] = false;
                    $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong_2'];
                }
                break;
            case 'change_pre_order_state':
                $current_state = (int) Tools::getValue('current_state', 0);
                $id_order_state = (int) Tools::getValue('id_order_state', 0);
                $order_collections = PosOrder::getByReference(Tools::getValue('reference'));
                $unpaid = 0;
                $total_paid = 0;
                $total_paid_real = 0;
                $total_discounts_tax_incl = 0;
                $products = array();
                $taxes = array();
                if ($order_collections) {
                    foreach ($order_collections as $index => $order) {
                        $unpaid += $order->total_products_wt + $order->total_shipping - $order->total_paid_real - $order->total_discounts;
                        if ((int) $order->current_state !== $current_state) {
                            unset($order_collections[$index]);
                        }
                    }
                }
                if ($order_collections) {
                    foreach ($order_collections as $order) {
                        if ((int) $order->current_state !== (int) $id_order_state) {
                            $order_history = new PosOrderHistory();
                            $order_history->id_order = $order->id;
                            $order_history->id_employee = (int) $this->context->employee->id;
                            try {
                                $order_history->changeIdOrderState($id_order_state, $order, !$order->hasInvoice());
                            } catch (\Exception $e) {
                                $this->ajax_json = array(
                                    'success' => false,
                                    'message' => $this->module->i18n['product_is_out_of_stock'],
                                    'data' => array(),
                                );
                                break;
                            }
                            // Save all changes
                            if ($order_history->addWithemail(true)) {
                                // synchronizes quantities if needed..
                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                    foreach ($order->getProducts() as $product) {
                                        StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
                                    }
                                }
                            }
                        }
                        $total_paid += $order->total_paid;
                        $total_paid_real += $order->total_paid_real;
                        $total_discounts_tax_incl += $order->total_discounts_tax_incl;
                    }
                    $order = $order_collections->getFirst();
                    $this->context->cart = new PosCart($order->id_cart);

                    if (Validate::isLoadedObject($order)) {
                        $this->ajax_json = array(
                            'success' => true,
                            'message' => $this->module->i18n['update_successfully'],
                            'data' => array(
                                'order' => array(
                                    'id_order' => $order->id,
                                    'reference' => $order->reference,
                                    'id_state' => $order->current_state,
                                    'total_paid' => $total_paid,
                                    'total_paid_real' => $total_paid_real,
                                    'amount_due' => Tools::ps_round($unpaid, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                                    'date_add' => $order->date_add,
                                    'invoice_url' => $order->hasInvoice() ? $this->context->link->getModuleLink(
                                        $this->module->name,
                                        'sales',
                                        array('action' => 'printInvoice', 'id_order' => $order->id)
                                    ) : null,
                                ),
                                'template' => PosConfiguration::getValueByEmployeeId(
                                    $id_employee,
                                    'POS_RECEIPT_TEMPLATE'
                                ),
                                'receipt' => $this->module->getReceiptData($order_collections),
                            ),
                        );
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['cannot_load_order'];
                    }
                }
                break;
            case 'load':
                $id_cart = Tools::getValue('id_cart', 0);
                $id_current_order = Tools::getValue('id_order', 0);

                if (!empty($id_cart)) {
                    $cart = new PosCart($id_cart);
                    $id_exchange = 0;

                    // if ($cart->OrderExists()) {
                    //     $exchange = PosExchange::getInstanceByOldOder($id_current_order);
                    //     if (Validate::isLoadedObject($exchange)) {
                    //         $clone_cart = new PosCart($exchange->id_cart_new);
                    //     } else {
                    //         $clone_cart = clone $cart;
                    //         $clone_cart->id = 0;
                    //         $clone_cart->save();
                    //         $exchange->id_cart_new = (int) $clone_cart->id;
                    //         $exchange->id_order_old = (int) $id_current_order;
                    //         $exchange->save();
                    //     }
                    //     $id_exchange = $exchange->id;
                    //     $id_cart = $clone_cart->id;
                    // }
                    $this->context->cart = $cart;
                    if ($this->sub_action == 'load') {
                        unset($this->context->cookie->id_pos_exchange);
                        unset($this->context->order);
                    }
                    $this->module->initSession($id_cart, $id_exchange);
                    $this->module->initContext(true);
                    if ($this->context->cart->id_carrier == 0) {
                        $this->module->setDefaultCarrier();
                    }
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['new_order_initialized'];

                    $out_of_stock = false;
                    $products_out_of_stock = [];
                    foreach ($this->getTransactionData($id_current_order)['cart']['products'] as $k => $v) {
                        $productQuantity = PosProduct::getQuantity($v['id_product'], $v['id_product_attribute'], null, $cart);
                        $allow_ordering_out_of_stock = PosProduct::allowOrderingOutOfStock($v['id_product']);
                        if ($productQuantity <= 0 && !$allow_ordering_out_of_stock) {
                            $out_of_stock = true;

                            $arr = ['name' => $v['name'][0], 'combination' => $v['combination']];
                            array_push($products_out_of_stock, $arr);

                            if (!empty($v['id_product'])) {
                                if ($cart->deletePosProduct($v['id_product'], $v['id_product_attribute'], 0) && PosSpecificPrice::deleteByIdCart($cart->id, $v['id_product'], $v['id_product_attribute'])) {
                                    $this->validateOrderDiscount();
                                    if (!PosCart::getNbProducts((int) $cart->id)) {
                                        $cart->setDeliveryOption(null);
                                        $cart->gift = 0;
                                        $cart->gift_message = '';
                                        $cart->update();
                                        PosActiveCart::removeByIdCart($cart->id);
                                    }
                                }
                            }
                        }
                    }

                    $this->ajax_json['data']['transaction'] = $this->getTransactionData($id_current_order);
                    $this->ajax_json['data']['transaction']['cart'] = $this->getCartSummaryDetails(true);
                    $this->ajax_json['data']['transaction']['out_of_stock'] = $out_of_stock;
                    $this->ajax_json['data']['transaction']['products_out_of_stock'] = $products_out_of_stock;

                    $this->ajax_json['data']['plugins']['sales_commission']['employees'] = PosSalesCommission::getSalesManByIdCart($cart->id);
                }
                break;
            case 'load_return':
                $id_cart = Tools::getValue('id_cart', 0);
                $id_current_order = Tools::getValue('id_order', 0);
                if (!empty($id_cart)) {
                    $cart = new PosCart($id_cart);
                    $id_exchange = 0;
                    if ($cart->OrderExists()) {
                        $exchange = PosExchange::getInstanceByOldOder($id_current_order);
                        if (Validate::isLoadedObject($exchange)) {
                            $clone_cart = new PosCart($exchange->id_cart_new);
                        } else {
                            $clone_cart = clone $cart;
                            $clone_cart->id = 0;
                            $clone_cart->save();
                            $exchange->id_cart_new = (int) $clone_cart->id;
                            $exchange->id_order_old = (int) $id_current_order;
                            $exchange->save();
                        }
                        $id_exchange = $exchange->id;
                        $id_cart = $clone_cart->id;
                    }

                    if ($this->sub_action == 'load') {
                        unset($this->context->cookie->id_pos_exchange);
                        unset($this->context->order);
                    }
                    $this->module->initSession($id_cart, $id_exchange);
                    $this->module->initContext(true);
                    if ($this->context->cart->id_carrier == 0) {
                        $this->module->setDefaultCarrier();
                    }
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['new_order_initialized'];
                    $this->ajax_json['data']['transaction'] = $this->getTransactionData($id_current_order);
                    $this->ajax_json['data']['plugins']['sales_commission']['employees'] = PosSalesCommission::getSalesManByIdCart($cart->id);
                }
                break;
            case 'return':
                $success = array();
                $id_cart = Tools::getValue('id_cart', 0);
                $order = new PosOrder(Tools::getValue('id_order', 0));
                $payment_method = PosPayment::getPaymentById(Tools::getValue('payment_methods'));
                $payment_method = $payment_method ? $payment_method : (int) Configuration::get('POS_DEFAULT_PAYMENT_ID');
                $amount_due = (float) Tools::getValue('amount_due');
                $back_money = (float) Tools::getValue('back_money');
                $given_money = (float) Tools::getValue('given_money');

                PosActiveCart::updateOrderReference($this->context->cart->id, $order->id_shop, $order->reference);
                $temp_products = Tools::getValue('products', array());
                $product_returns = array();
                if (is_array($temp_products) && !empty($temp_products)) {
                    foreach ($temp_products as $json_product) {
                        $product = current(Tools::jsonDecode($json_product, true));
                        $product_returns[$product['id_order_detail']] = $product;
                    }
                }
                $new_order = PosOrder::cloneOrder(
                    $product_returns,
                    $order,
                    $payment_method,
                    Tools::getValue('amount_due'),
                    Tools::getValue('shipping_back')
                );

                if (Tools::getValue('shipping_back')) {
                    $order->removeShipping();
                }
                $product_returns = $order->getProductsReturn($product_returns);
                $exchange_products = array();
                // process create an order
                $cart = new PosCart($id_cart);
                if (Validate::isLoadedObject($cart)) {
                    $note = PosActiveCart::getNote($cart->id, $cart->id_shop);
                    $this->module->origin_order_reference = $order->reference;
                    $id_order_state = (int) Tools::getValue('id_order_state', 0);
                    if ($id_order_state == 0) {
                        $id_order_state = $new_order->current_state;
                    }
                    try {
                        $success[] = $this->module->validateOrder(
                            $cart->id,
                            $id_order_state,
                            $cart->getOrderTotal(true, Cart::BOTH),
                            $payment_method,
                            $note['note'],
                            array(),
                            null,
                            false,
                            $cart->secure_key
                        );

                        $this->context->cart->addPaymentReturn(Tools::getValue('payment_methods'), $amount_due, $back_money, $given_money);
                    } catch (PrestaShopException $prestashop_exception) {
                        $this->module->createLog($prestashop_exception->getMessage(), $cart->id, 'return');
                        $this->module->createLog($cart, $cart->id, 'return');
                        $this->ajax_json['message'] = $prestashop_exception->getMessage();
                    }
                    if (array_sum($success) >= count($success)) {
                        try {
                            $order_exchange = new PosOrder($this->module->currentOrder);
                            if (Validate::isLoadedObject($order_exchange)) {
                                $exchange_products = $order_exchange->getProducts();
                            }
                        } catch (Exception $ex) {
                            $this->ajax_json['message'] = $this->module->i18n['order_completed_but_some_external_processes_are_not_done_as_expected'];
                        }
                    }
                }
                $customization_list = $return_customization_quantities = $full_product_list = $full_quantity_list = $products = array();
                if (is_array($temp_products) && !empty($temp_products)) {
                    foreach ($temp_products as $json_product) {
                        $data = Tools::jsonDecode($json_product, true);
                        $first_product = current($data);
                        if (isset($data[0]) && !empty($data[0])) {
                            $products[] = $data[0];
                        }

                        foreach ($data as $id_customization => $product) {
                            if ($id_customization > 0) {
                                $customization_list[$id_customization] = $product['id_order_detail'];
                                $return_customization_quantities[$id_customization] = $product['quantity'];
                            }
                        }
                        $full_product_list[$first_product['id_order_detail']] = $first_product['id_order_detail'];
                        $full_quantity_list[$first_product['id_order_detail']] = array_sum(array_column($data, 'quantity'));
                    }
                }

                // validate product return quantity
                if (!empty($products)) {
                    $customization_quantities = Customization::countQuantityByCart($order->id_cart);
                    foreach ($products as $product) {
                        $order_detail = new OrderDetail($product['id_order_detail']);
                        $customization_quantity = 0;
                        if (array_key_exists($order_detail->product_id, $customization_quantities) && array_key_exists($order_detail->product_attribute_id, $customization_quantities[$order_detail->product_id])) {
                            $customization_quantity = (int) $customization_quantities[$order_detail->product_id][$order_detail->product_attribute_id];
                        }
                        if (($order_detail->product_quantity - $customization_quantity - $order_detail->product_quantity_refunded - $order_detail->product_quantity_return) < $product['quantity']) {
                            $this->message = $this->module->i18n['invalid_quantity'];
                            $success[] = false;
                            break;
                        }
                    }
                }
                // validate customization return quantity
                if ($customization_list && array_sum($success) >= count($success)) {
                    $customization_quantities = Customization::retrieveQuantitiesFromIds(array_keys($customization_list));
                    foreach ($customization_list as $id_customization => $id_order_detail) {
                        $customization_quantity = $customization_quantities[$id_customization];
                        if ((int) $return_customization_quantities[$id_customization] > ($customization_quantity['quantity'] - ($customization_quantity['quantity_refunded'] + $customization_quantity['quantity_returned']))) {
                            $this->message = $this->module->i18n['invalid_quantity'];
                            $success[] = false;
                            break;
                        }
                    }
                }

                if (Tools::getValue('reinject_quantities') && array_sum($success) >= count($success)) {
                    foreach ($products as $product) {
                        $order_detail = new PosOrderDetail($product['id_order_detail']);
                        if ($order_detail->reinjectQuantity($product['quantity'])) {
                            $success[] = $order->deleteProduct($order, $order_detail, $product['quantity']);
                        }
                    }
                    if (!empty($customization_list)) {
                        foreach ($customization_list as $id_customization => $id_order_detail) {
                            $order_detail = new OrderDetail((int) ($id_order_detail));
                            $success[] = $order->deleteCustomization(
                                $id_customization,
                                (int) $return_customization_quantities[$id_customization],
                                $order_detail
                            );
                        }
                    }
                }

                $params = array();
                // E-mail params
                if ((Tools::getValue('generate_credit_slip') || Tools::getValue('generate_discount'))) {
                    $customer = new Customer((int) ($order->id_customer));
                    $params['{lastname}'] = $customer->lastname;
                    $params['{firstname}'] = $customer->firstname;
                    $params['{id_order}'] = $order->id;
                    $params['{order_name}'] = $order->getUniqReference();
                }

                // Generate credit slip
                $this->ajax_json['data']['transaction']['order']['slip_url'] = '';
                if (Tools::getValue('generate_credit_slip') && array_sum($success) >= count($success)) {
                    $product_list = array();
                    foreach ($full_product_list as $id_order_detail) {
                        $order_detail = new OrderDetail((int) $id_order_detail);
                        $product_list[$id_order_detail] = array(
                            'id_order_detail' => $id_order_detail,
                            'quantity' => $full_quantity_list[$id_order_detail],
                            'unit_price' => $order_detail->unit_price_tax_excl,
                            'amount' => $order_detail->unit_price_tax_incl * $full_quantity_list[$id_order_detail],
                        );
                    }

                    $shipping = Tools::isSubmit('shipping_back') ? null : false; //@todo
                    $success[] = OrderSlip::create($order, $product_list, $shipping);
                    if (array_sum($success) >= count($success)) {
                        Hook::exec(
                            'actionOrderSlipAdd',
                            array('order' => $order, 'productList' => $product_list, 'qtyList' => $full_quantity_list),
                            null,
                            false,
                            true,
                            false,
                            $order->id_shop
                        );
                        @Mail::Send(
                            (int) $order->id_lang,
                            'credit_slip',
                            $this->module->i18n['new_credit_slip_regarding_your_order'],
                            $params,
                            $customer->email,
                            $customer->firstname . ' ' . $customer->lastname,
                            null,
                            null,
                            null,
                            null,
                            _PS_MAIL_DIR_,
                            true,
                            (int) $order->id_shop
                        );
                    }
                    $order_slip = $order->getOrderSlipsCollection()->getFirst();
                    $this->ajax_json['data']['transaction']['order']['slip_url'] = $this->context->link->getModuleLink(
                        $this->module->name,
                        'sales',
                        array('action' => 'printOrderSlip', 'id_order_slip' => $order_slip->id)
                    );
                }
                $voucher_code = array();
                // Generate voucher
                if (Tools::getValue('generate_discount') && array_sum($success) >= count($success)) {
                    $cartrule = new CartRule();
                    $language_ids = Language::getIDs();
                    $cartrule->description = sprintf($this->module->i18n['credit_card_slip_for_order'], $order->id);
                    foreach ($language_ids as $id_lang) {
                        // Define a temporary name
                        $cartrule->name[$id_lang] = 'V0C' . (int) ($order->id_customer) . 'O' . (int) ($order->id);
                    }
                    // Define a temporary code
                    $cartrule->code = 'V0C' . (int) ($order->id_customer) . 'O' . (int) ($order->id);

                    $cartrule->quantity = 1;
                    $cartrule->quantity_per_user = 1;
                    // Specific to the customer
                    $cartrule->id_customer = $order->id_customer;
                    $now = time();
                    $cartrule->date_from = date('Y-m-d H:i:s', $now);
                    $cartrule->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25)); /* 1 year */
                    $cartrule->active = 1;
                    $products = $order->getProducts(false, $full_product_list, $full_quantity_list);
                    $total = 0;
                    foreach ($products as $product) {
                        $total += $product['unit_price_tax_incl'] * $product['product_quantity'];
                    }

                    if (Tools::getValue('shipping_back')) {
//@todo
                        $total += $order->total_shipping;
                    }
                    if (!empty($exchange_products)) {
                        foreach ($exchange_products as $product) {
                            $total -= $product['unit_price_tax_incl'] * $product['product_quantity'];
                        }
                    }
                    if ($total > 0) {
                        $cartrule->reduction_amount = $total;
                        $cartrule->reduction_tax = false;
                        $cartrule->minimum_amount_currency = $order->id_currency;
                        $cartrule->reduction_currency = $order->id_currency;
                        $success[] = $cartrule->add();
                        if (array_sum($success) >= count($success)) {
                            // Update the voucher code and name
                            foreach ($language_ids as $id_lang) {
                                $cartrule->name[$id_lang] = 'V' . (int) ($cartrule->id) . 'C' . (int) ($order->id_customer) . 'O' . $order->id;
                            }
                            $cartrule->code = 'V' . (int) ($cartrule->id) . 'C' . (int) ($order->id_customer) . 'O' . $order->id;
                            $voucher_code = array(
                                'code' => $cartrule->code,
                                'id_cart_rule' => $cartrule->id,
                                'name' => $cartrule->name[$this->context->language->id],
                                'reduction_amount' => $total,
                                'reduction_currency' => $order->id_currency,
                                'reduction_percent' => 0,
                            );
                            $success[] = $cartrule->update();
                            if (array_sum($success) >= count($success)) {
                                $currency = $this->context->currency;
                                $params['{voucher_amount}'] = Tools::displayPrice(
                                    $cartrule->reduction_amount,
                                    $currency,
                                    false
                                );
                                $params['{voucher_num}'] = $cartrule->code;
                                @Mail::Send(
                                    (int) $order->id_lang,
                                    'voucher',
                                    sprintf($this->module->i18n['new_voucher_for_your_order'], $order->reference),
                                    $params,
                                    $customer->email,
                                    $customer->firstname . ' ' . $customer->lastname,
                                    null,
                                    null,
                                    null,
                                    null,
                                    _PS_MAIL_DIR_,
                                    true,
                                    (int) $order->id_shop
                                );
                            }
                        }
                    }
                }

                if (array_sum($success) >= count($success)) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['return_exchange_successfully'];

                    foreach ($exchange_products as &$exchange_product) {
                        $exchange_product['quantity'] = '-' . $exchange_product['quantity'];
                        $exchange_product['total_wt'] = '-' . $exchange_product['total_wt'];
                        PosProductNote::addNoteByIdProduct($order_exchange->id, $cart->id, $exchange_product['product_id'], $exchange_product['product_attribute_id']);
                    }

                    foreach ($product_returns as &$product_return) {
                        PosProductNote::addNoteByIdProduct($order->id, $cart->id, $product_return['product_id'], $product_return['product_attribute_id']);
                    }

                    $this->ajax_json['data']['transaction']['order'] = $this->getOrderData(
                        $order,
                        true,
                        $exchange_products,
                        $product_returns,
                        Tools::getValue('id_cart', 0),
                        Tools::getValue('shipping_back')
                    );
                    $this->ajax_json['data']['transaction']['order']['print_data']['receipt']['voucher_code'] = $voucher_code;
                } else {
                    // nothing todo
                }
                $time_now =  date('y-m-d h:i:s');
                $status =  Db::getInstance()->update(
                    'orders',
                    array(
                        'possible_refund_date' => $time_now,
                    ),
                    'id_order ='.(int) Tools::getValue('id_order', 0)
                );
                break;
            case 'get_nb_free_orders':
                $expiring_days = Tools::getValue('expiring_days');
                $version = Tools::getValue('version');
                $this->ajax_json['success'] = true;
                $this->ajax_json['data']['env']['total_order'] = PosOrder::getNbFreeOrders(
                    $this->module,
                    $expiring_days != null ? $expiring_days : 0,
                    $version != '' ? $version : 'lite'
                );
                break;
            case 'view':
                $order_collections = PosOrder::getByReference(Tools::getValue('reference'));
                $current_order = new PosOrder(Tools::getValue('id_order'));
                $current_order_collections = array();
                array_push($current_order_collections, $current_order);
                $total_products = 0;
                $total_paid = 0;
                $total_paid_real = 0;
                $total_shipping_tax_incl = 0;
                $total_discounts_tax_excl = 0;
                $total_discounts_tax_incl = 0;
                $total_products_wt = 0;
                $unpaid = 0;
                $products = array();
                $taxes = array();
                foreach ($order_collections as $index => $order) {
                    if ((int) $order->current_state !== $current_state) {
                        unset($order_collections[$index]);
                    } else {
                        $unpaid += $order->total_paid - $order->total_paid_real;
                    }
                }
                $order_discounts = $order->getCartRules();
                foreach ($order_collections as $index => $order) {
                    $total_products_wt += $order->total_products_wt;
                    $total_products += $order->total_products;
                    $total_paid += $order->total_paid;
                    $total_paid_real += $order->total_paid_real;
                    $total_shipping_tax_incl += $order->total_shipping_tax_incl;
                    $products = array_merge($products, $this->module->formatProducts($order->getProducts()));
                    $taxes = array_merge($this->module->getTaxDetails($order));
                }
                $total_order_discount = $this->module->getTotalOrderDiscount($order_collections);
                $total_order_gift_discount = $this->module->getTotalOrderGiftDiscount($order_collections);
                $order = $order_collections->getFirst();
                $use_tax = Configuration::get('PS_TAX') && !Product::getTaxCalculationMethod((int) $order->id_customer);
                $this->context->cart = new PosCart($order->id_cart);
                if (Validate::isLoadedObject($order)) {
                    $customer = new PosCustomer($order->id_customer);
                    $this->ajax_json = array(
                        'success' => true,
                        'message' => $this->module->i18n['order_loaded'],
                        'data' => array(
                            'order_states' => PosOrderState::getOrderStates($this->context->language->id),
                            'payment_methods' => PosPayment::getAll($this->context->language->id, $order->id_shop),
                            'products' => $products,
                            'order_payments' => $this->module->getOrderPayments($order),
                            'taxs' => $taxes,
                            'order' => array(
                                'id_order' => $order->id,
                                'reference' => $order->reference,
                                'id_order_state' => $order->current_state,
                                'subtotal' => $use_tax ? $total_products_wt - $total_order_gift_discount : $total_products - $total_order_gift_discount,
                                'total_paid' => $total_paid,
                                'total_paid_real' => $total_paid_real,
                                'total_shipping' => $total_shipping_tax_incl,
                                'total_discount' => $total_order_discount,
                                'total_gift_discount' => $total_order_gift_discount,
                                'amount_due' => Tools::ps_round($unpaid, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                                'date_add' => $order->date_add,
                                'invoice_url' => $order->hasInvoice() ? $this->context->link->getModuleLink(
                                    $this->module->name,
                                    'sales',
                                    array('action' => 'printInvoice', 'id_order' => $order->id)
                                ) : null,
                            ),
                            'customer' => array(
                                'firstname' => $customer->firstname,
                                'lastname' => $customer->lastname,
                            ),
                            'currency' => PosCurrency::getCurrencyInstance($order->id_currency),
                            'template' => PosConfiguration::getValueByEmployeeId($id_employee, 'POS_RECEIPT_TEMPLATE'),
                            'first_bill_printer' => PosConfiguration::getValueByEmployeeId(
                                $id_employee,
                                'POS_RECEIPT_BILL_PRINTER_1'
                            ),
                            'second_bill_printer' => PosConfiguration::getValueByEmployeeId(
                                $id_employee,
                                'POS_RECEIPT_BILL_PRINTER_2'
                            ),
                            'receipt' => $this->module->getReceiptData($current_order_collections),
                        ),
                    );
                } else {
                    $this->ajax_json['message'] = $this->module->i18n['cannot_load_order'];
                }
                break;
            case 'add_payment':
                $success = array();
                $amount = (float) Tools::getValue('amount');
                $payment = new PosPayment((int) Tools::getValue('id_payment'), $this->context->language->id);
                if (!Validate::isLoadedObject($payment)) {
                    $this->ajax_json['message'] = $this->module->i18n['cannot_load_payment'];
                } elseif (!Validate::isNegativePrice($amount)) {
                    $this->ajax_json['message'] = $this->module->i18n['invalid_amount'];
                } else {
                    $order_collections = PosOrder::getByReference(Tools::getValue('reference'), true);
                    $amount_due = 0;
                    $unpaid = 0;
                    $total_paid = 0;
                    $total_paid_real = 0;
                    $total_shipping_tax_incl = 0;
                    $total_discounts_tax_excl = 0;
                    $total_discounts_tax_incl = 0;
                    foreach ($order_collections as $order) {
                        if ($amount == 0) {
                            break;
                        }
                        $unpaid += $order->total_paid - $order->total_paid_real;
                        $total_paid += $order->total_paid;
                        $total_discounts_tax_incl += $order->total_discounts_tax_incl;
                        $total_paid_real += $order->total_paid_real;
                        $amount_due = $order->total_paid - $order->total_paid_real;

                        $amount_paid = 0;
                        if ($amount_due == 0) {
                            continue;
                        } elseif ($amount_due > 0 && $amount_due <= $amount) {
                            $total_paid_real += $amount_due;
                            $amount_paid = $amount_due;
                        } elseif ($amount_due > 0 && $amount_due > $amount) {
                            $total_paid_real += $amount;
                            $amount_paid = $amount;
                        }
                        $amount -= $amount_paid;
                        $currency = new PosCurrency((int) $order->id_currency);
                        $order_invoice = $order->hasInvoice() ? $order->getInvoicesCollection()->getFirst() : null;
                        $success[] = $order->addOrderPayment(
                            $amount_paid,
                            $payment->payment_name,
                            null,
                            $currency,
                            date('Y-m-d H:i:s'),
                            $order_invoice
                        );
                        if (array_sum($success) >= count($success)) {
                            $unpaid -= $amount_paid;
                            $pos_cart = new PosCart((int) $order->id_cart);
                            $order_state = new PosOrderState($order->current_state);
                            $success[] = $pos_cart->addPayment(
                                $payment->id,
                                $amount_paid,
                                $order->reference,
                                null,
                                $amount_paid
                            );
                            $order->payment = $pos_cart->getPaymentMethods();
                            $success[] = $order->update();
                        }
                    }
                    $unpaid = Tools::ps_round($unpaid, PosConstants::POS_PRICE_COMPUTE_PRECISION);

                    if ($unpaid == 0) {
                        foreach ($order_collections as $order) {
                            $order_state = new PosOrderState($order->current_state);
                            $default_standard_order_state = (int) PosConfiguration::get('POS_DEFAULT_STANDARD_ORDER_STATE');
                            if ($default_standard_order_state !== $order_state->invoice) {
                                $history = new PosOrderHistory();
                                $history->id_order = $order->id;
                                $history->id_employee = (int) $this->context->employee->id;
                                $history->changeIdOrderState(
                                    $default_standard_order_state,
                                    $order,
                                    !$order->hasInvoice()
                                );
                                $template_vars = array();
                                // Save all changes
                                if ($history->addWithemail(true, $template_vars)) {
                                    // synchronizes quantities if needed..
                                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                        foreach ($order->getProducts() as $product) {
                                            if (StockAvailable::dependsOnStock($product['product_id'])) {
                                                StockAvailable::synchronize(
                                                    $product['product_id'],
                                                    (int) $product['id_shop']
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $order = $order_collections->getFirst();
                    $this->context->cart = new PosCart($order->id_cart);
                    if (array_sum($success) >= count($success)) {
                        $this->ajax_json = array(
                            'success' => true,
                            'message' => $this->module->i18n['add_successfully'],
                            'data' => array(
                                'order_payments' => $this->module->getOrderPayments($order),
                                'currency' => PosCurrency::getCurrencyInstance($order->id_currency),
                                'first_bill_printer' => Configuration::get('POS_RECEIPT_BILL_PRINTER_1'),
                                'second_billg_printer' => Configuration::get('POS_RECEIPT_BILL_PRINTER_2'),
                                'order' => array(
                                    'id_order' => $order->id,
                                    'reference' => $order->reference,
                                    'id_order_state' => $order->current_state,
                                    'total_paid' => $total_paid,
                                    'total_paid_real' => $total_paid_real,
                                    'amount_due' => Tools::ps_round($unpaid, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                                    'date_add' => $order->date_add,
                                    'invoice_url' => $order->hasInvoice() ? $this->context->link->getModuleLink(
                                        $this->module->name,
                                        'sales',
                                        array('action' => 'printInvoice', 'id_order' => $order->id)
                                    ) : null,
                                ),
                                'template' => PosConfiguration::getValueByEmployeeId(
                                    $id_employee,
                                    'POS_RECEIPT_TEMPLATE'
                                ),
                                'receipt' => $this->module->getReceiptData($order_collections),
                            ),
                        );
                    } else {
                        $this->ajax_json['message'] = $this->module->i18n['cannot_add_payment'];
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * @input
     *  {
     *      action: "payment",
     *      sub_action: "add" | "remove"
     *      ajax: "1",
     *      given_money: float,// required if sub_action = add
     *      id_payment: int,// required if sub_action = add
     *      id_cart_payment: int// required if sub_action = remove
     *  }
     * @output
     *  {
     *      success: boolean,
     *      message: string,
     *      data: {
     *          transaction: {
     *              paid_payments: {},// Refer to action "getAllInOne" for further details
     *          }
     *      }
     *  }
     */
    public function ajaxProcessPayment()
    {
        switch ($this->sub_action) {
            case 'add':
                $id_payment = (int) Tools::getValue('id_payment');
                $given_money = (float) Tools::getValue('given_money');
                if ($this->context->cart->addPayment($id_payment, $given_money)) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['payment_added'];
                }
                break;
            case 'remove':
                $id_cart_payment = (int) Tools::getValue('id_cart_payment', null);
                if ($this->context->cart->removePayment($id_cart_payment)) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['payment_removed'];
                }
                break;

            default:
                break;
        }
        $this->ajax_json['data']['transaction'] = array();
        $this->ajax_json['data']['transaction']['paid_payments'] = $this->context->cart->getPayments();
    }

    /**
     * @input
     * {
     *      action: "note",
     *      sub_action: "update",
     *      note: string,
     *      private: boolean
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {}
     * }
     */
    public function ajaxProcessNote()
    {
        switch ($this->sub_action) {
            case 'update':
                $note = Tools::getValue('note');
                $public = (int) Tools::getValue('public');
                if (PosActiveCart::updateNote($this->context->cart->id, $this->context->shop->id, $note, $public)) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['note_added'];
                }

                break;
            case 'update_product_note':
                $note = Tools::getValue('note');
                $id_product = Tools::getValue('id_product');
                $id_note = Tools::getValue('id');
                $id_product_attribute = Tools::getValue('id_product_attribute');
                $id_customization = Tools::getValue('id_customization');

                if (isset($id_product)) {
                    $new_note = new PosProductNote($id_note);
                    $new_note->id_cart = $this->context->cart->id;
                    $new_note->id_product = $id_product;
                    $new_note->id_product_attribute = $id_product_attribute;
                    $new_note->id_customization = $id_customization;
                    $new_note->note = $note;

                    if ($new_note->save()) {
                        $this->ajax_json['success'] = true;
                        $this->ajax_json['data'] = $new_note;
                        $this->ajax_json['message'] = $this->module->i18n['note_added'];
                    }
                }

                break;

            default:
                break;
        }
    }

    /**
     * @input
     * {
     *      action: "printInvoice",
     *      id_order: int,
     * }
     * @output
     * {
     *      pdf file
     * }
     */
    public function processPrintInvoice()
    {
        if ($this->module->is_logged) {
            $order = new Order((int) Tools::getValue('id_order', 0));
            if (!Validate::isLoadedObject($order)) {
                die($this->module->i18n['the_order_cannot_be_found_within_your_database']);
            }
            $order_invoice_list = $order->getInvoicesCollection();
            $pdf = new PDF($order_invoice_list, PDF::TEMPLATE_INVOICE, $this->context->smarty);
            $pdf->render('I');
        } else {
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'sales'));
        }
    }

    /**
     * @input
     * {
     *      action: "printOrderSlip",
     *      id_order_slip: int,
     * }
     * @output
     * {
     *      pdf file
     * }
     */
    public function processPrintOrderSlip()
    {
        if ($this->module->is_logged) {
            $id_order_slip = (int) Tools::getValue('id_order_slip', 0);
            $order_slip = new OrderSlip($id_order_slip);
            if (!Validate::isLoadedObject($order_slip)) {
                die($this->module->i18n['the_order_slip_cannot_be_found_within_your_database']);
            }
            $pdf = new PDF($order_slip, PDF::TEMPLATE_ORDER_SLIP, $this->context->smarty);
            $pdf->render('I');
        } else {
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'sales'));
        }
    }

    /**
     * @param array $products
     *
     * @return array()
     */
    protected function formatReceiptProducts($products)
    {
        $tax_inc = PS_TAX_INC == $this->context->customer->getPriceDisplayMethod();
        $format_receipt_products = array();
        if (!empty($products)) {
            foreach ($products as $product) {
                $format_receipt_products[] = array(
                    'product_info' => PosReceipt::getProductMetaData($product),
                    'quantity' => $product['product_quantity'],
                    'original_product_price' => $product['original_product_price'],
                    'total_wt' => $product['total_price_tax_incl'],
                    'total_price' => $product['total_price_tax_excl'],
                    'prices_to_show' => PosReceipt::getProductPricesToShow(
                        $product,
                        $this->context->currency->id,
                        $tax_inc
                    ),
                );
            }
        }

        return $format_receipt_products;
    }

    /**
     * @input
     * {
     *      action: "forgotPasswd",
     *      email_forgot: string,
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data: {}
     * }
     */
    public function ajaxProcessForgotPasswd()
    {
        $success = array();
        $email = trim(Tools::getValue('email_forgot'));

        if (Validate::isEmail($email)) {
            $employee = new Employee();
            $is_employee_loaded = $employee->getByEmail($email);
            $employee_associated_shop = $employee->getAssociatedShops();
            if (!$is_employee_loaded) {
                $success[] = 0;
                $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
            } elseif (empty($employee_associated_shop)) {
                $success[] = 0;
                $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
                $this->context->employee->logout();
            } elseif (!$this->module->isLoggedSell($employee)) {
                $success[] = 0;
                $this->ajax_json['message'] = $this->module->i18n['you_dont_have_permission_to_access_this_area'];
            } elseif ((strtotime($employee->last_passwd_gen . '+' . Configuration::get('PS_PASSWD_TIME_BACK') . ' minutes') - time()) > 0) {
                $success[] = 0;
                $this->ajax_json['message'] = sprintf(
                    $this->module->i18n['you_can_regenerate_your_password_only_every_minutes'],
                    Configuration::get('PS_PASSWD_TIME_BACK')
                );
            }
        } else {
            $success[] = 0;
            $this->ajax_json['message'] = $this->module->i18n['invalid_email'];
        }
        if (array_sum($success) >= count($success)) {
            $pwd = Tools::passwdGen(10, 'RANDOM');
            $employee->passwd = Tools::encrypt($pwd);
            $employee->last_passwd_gen = date('Y-m-d H:i:s', time());

            $params = array(
                '{email}' => $employee->email,
                '{lastname}' => $employee->lastname,
                '{firstname}' => $employee->firstname,
                '{passwd}' => $pwd,
            );

            if (Mail::Send(
                $employee->id_lang,
                'employee_password',
                $this->module->i18n['your_new_password'],
                $params,
                $employee->email,
                $employee->firstname . ' ' . $employee->lastname
            )) {
                // Update employee only if the mail can be sent
                Shop::setContext(Shop::CONTEXT_SHOP, (int) min($employee->getAssociatedShops()));
                $this->ajax_json['message'] = $employee->update() ? $this->module->i18n['your_password_has_been_emailed_to_you'] : $this->module->i18n['an_error_occurred_while_attempting_to_change_your_password'];
            } else {
                $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_while_attempting_to_change_your_password'];
            }
        }
        $this->ajax_json['success'] = array_sum($success) >= count($success);
    }

    /**
     * Show content to view.
     */
    public function displayAjax()
    {
        $this->context->cookie->write(); // in PrestaShop, it's done in displayAjax() -> display() ->smartyOutputcontent()
        if ($this->ajax_json) {
            if (Tools::getValue('debug')) {
                p($this->ajax_json);
            } else {
                echo Tools::jsonEncode($this->ajax_json);
            }
        }
    }

    /**
     * @input
     * {
     *      action: "logout"
     *      ajax: "1"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data:{
     *          transaction: {
     *              customer: {} // Refer to action "getAllInOne" for further details, if success
     *          },
     *          plugins: {
     *              multiple_carts: {
     *                  carts: array // Refer to action "getAllInOne" for further details, if success
     *              }
     *          }
     *      }
     * }
     */
    public function ajaxProcessLogout()
    {
        if ($this->module->clearSession()) {
            unset($this->context->cookie->pos_id_employee);
            $this->ajax_json['success'] = true;
            $this->module->initContext(true);
            $this->ajax_json['message'] = $this->module->i18n['log_out'];
            $this->ajax_json['data']['transaction']['customer'] = $this->getCustomer();
            $this->ajax_json['data']['plugins']['multiple_carts']['carts'] = $this->getActiveCarts();
        }
    }

    /**
     * @input
     * {
     *      action: "mixpanel"
     *      sub_action: "updateTime"
     *      ajax: "1"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     *      data:{}
     * }
     */
    public function ajaxProcessMixpanel()
    {
        switch ($this->sub_action) {
            case 'updatetime':
                if (PosMixPanel::updateTime()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['add_successfully'];
                }
                break;
            default:
                break;
        }
    }

    /**
     * @param int id_customer
     *
     * @return array
     *               <pre>
     *               array(
     *               carts => array(
     *               firstname => string
     *               lastname => string
     *               email => string
     *               date_add => timestamp
     *               id_cart => int
     *               )
     *               (
     *               number_cart => int
     *               )
     *),
     */
    public function getAbandonedCarts($id_customer)
    {
        $carts = PosCart::getCartsByIdCustomer($id_customer);
        foreach ($carts as $key => &$cart) {
            $cart['total'] = PosCart::getTotalCart($cart['id_cart'], false, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);
        }

        return array(
            'carts' => array_values($carts),
            'number_cart' => count($carts),
        );
    }

    /**
     * @param int $id_carrier
     *
     * @return array
     *               <pre>
     *               array(
     *               id_address_delivery => string
     *               )
     *),
     */
    public function getDeliverOption($id_carrier)
    {
        return array(
            $this->context->cart->id_address_delivery => implode(
                '',
                array_fill(0, count((array) $this->context->cart->getDeliverOptionFormat()), (int) $id_carrier . ',')
            ),
        );
    }

    /**
     * @input
     * {
     *      action: "action",
     *      img: "img",
     *      passwd: "passwd"
     * }
     * @output
     * {
     *      success: boolean,
     *      message: string,
     * }
     */
    public function ajaxProcessEmployee()
    {
        if (Tools::getValue('checkin') == '' || Tools::getValue('passwd') == '' || Tools::getValue('id_employee') == '') {
            $this->ajax_json['success'] = false;
            $this->ajax_json['message'] = $this->module->i18n['please_fill_all_the_fields'];
            return;
        }
        try {
            $ip = PosTools::getRealIpAddr();
            $action = Tools::getValue('checkin');
            $passwd = Tools::getValue('passwd');
            $id_employee = Tools::getValue('id_employee');
            $date = date('Y-m-d H:i:s');
            $passwd_checked = false;
            $employee = new Employee($id_employee);

            if (Tools::version_compare(_PS_VERSION_, '1.7', '<') == true) {
                if (Tools::encrypt($passwd) == $employee->passwd) {
                    $passwd_checked = true;
                }
            } else {
                /** @var \PrestaShop\PrestaShop\Core\Crypto\Hashing $crypto */
                $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
                if ($crypto->checkHash($passwd, $employee->passwd)) {
                    $passwd_checked = true;
                }
            }
            if ($passwd_checked) {
                $pos_checkin = new PosEmployeeCheckIn(null, $id_employee, $ip, $action, $this->context->shop->id);

                if ($pos_checkin->save()) {
                    $this->ajax_json['success'] = true;
                    $this->ajax_json['message'] = $this->module->i18n['add_successfully'];
                } else {
                    $this->ajax_json['success'] = false;
                    $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
                }
            } else {
                $this->ajax_json['success'] = false;
                $this->ajax_json['message'] = $this->module->i18n['invalid_password'];
            }
        } catch (Exception $e) {
            $this->ajax_json['success'] = false;
            $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
        }
    }

    /**
     * Get discount information for an order
     * 
     * @return void
     */
    public function ajaxProcessGetDiscountInfo()
    {
        $id_order = (int) Tools::getValue('id_order', 0);
        
       
        if (empty($id_order)) {
            $this->ajax_json = array(
                'success' => false,
                'message' => 'Order ID is required',
                'data' => array()
            );
            return;
        }
        
        // try {
            // Query to get discount information from order_cart_rule and cart_rule tables
            $sql = 'SELECT cr.reduction_percent, cr.reduction_amount 
                    FROM ' . _DB_PREFIX_ . 'order_cart_rule ocr
                    INNER JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
                    WHERE ocr.id_order = ' . (int) $id_order;
            
            $result = Db::getInstance()->getRow($sql);
            
            if ($result) {
                $is_percentage = (float) $result['reduction_percent'] > 0;
                $reduction_percent = (float) $result['reduction_percent'];
                $reduction_amount = (float) $result['reduction_amount'];
                
                $this->ajax_json = array(
                    'success' => true,
                    'message' => 'Discount information retrieved',
                    'data' => array(
                        'is_percentage' => $is_percentage,
                        'reduction_percent' => $reduction_percent,
                        'reduction_amount' => $reduction_amount
                    )
                );
            } else {
                $this->ajax_json = array(
                    'success' => true,
                    'message' => 'No discount found for this order',
                    'data' => array(
                        'is_percentage' => false,
                        'reduction_percent' => 0,
                        'reduction_amount' => 0
                    )
                );
            }
        // } catch (Exception $e) {
        //     $this->ajax_json = array(
        //         'success' => false,
        //         'message' => 'Error retrieving discount information: ' . $e->getMessage(),
        //         'data' => array()
        //     );
        // }
    }
}

function unique_multidim_array($array, $key)
{
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return array_values($temp_array);
}

