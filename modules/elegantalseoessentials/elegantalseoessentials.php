<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

require_once('initialize.php');

/**
 * Main class of the module
 */
class ElegantalSeoEssentials extends ElegantalSeoEssentialsModule
{

    /**
     * ID of this module as product on addons
     * @var int
     */
    protected $productIdOnAddons = 41644;

    /**
     * List of hooks to register
     * @var array
     */
    protected $hooksToRegister = array(
        'displayHeader',
        'actionProductAdd',
        'actionProductUpdate',
        'actionProductDelete',
        'displayAdminProductsExtra',
    );

    /**
     * List of module settings to be saved as Configuration record
     * @var array
     */
    protected $settings = array(
        'is_enable_canonical' => 1,
        'is_enable_nextprev' => 1,
        'is_enable_hreflang' => 1,
        'is_hreflang_use_multishop_domain' => 0,
        'is_enable_sitelinks_searchbox' => 1,
        'is_enable_noindex_on_paginated_pages' => 0,
        'meta_title_length' => 70,
        'meta_description_length' => 160,
        'excluded_product_ids' => '',
        'page_param' => 'p',
        'max_csv_file_size' => '',
        'limit_per_request' => 100,
        'security_token_key' => '',
        'disable_url_rewrite' => 0,
    );

    /**
     * List of supported redirect types
     * @var array
     */
    protected $redirect_types = array(301, 302, 303);

    /**
     * Constructor method called on each newly-created object
     */
    public function __construct()
    {
        $this->name = 'elegantalseoessentials';
        $this->tab = 'seo';
        $this->version = '3.4.3';
        $this->author = 'ELEGANTAL';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '9e9d2401a0bde79ac1bfa43504e7402f';

        parent::__construct();

        $this->displayName = $this->l('Essential SEO All-In-One Tools by Experts');
        $this->description = $this->l('Most important SEO tools needed for optimizing your site');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function __call($function, $args)
    {
        $type = Tools::strtolower(Tools::substr($function, 0, 11));
        if ($type == 'hookdisplay') {
            $hook_name = Tools::substr($function, 4);
            return $this->htmlBlockFront($hook_name);
        } else {
            return false;
        }
    }

    /**
     * This function plays controller role for the back-office page of the module
     * @return string HTML
     */
    public function getContent()
    {
        $this->setTimeLimit();

        if (_PS_VERSION_ < '1.6') {
            $this->context->controller->addCSS($this->_path . 'views/css/elegantalseoessentials-bootstrap.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/font-awesome.css', 'all');

            if (!in_array(Tools::getValue('event'), array('settings'))) {
                $this->context->controller->addJS($this->_path . 'views/js/jquery-1.11.0.min.js');
                $this->context->controller->addJS($this->_path . 'views/js/bootstrap.js');
            }
        }

        $this->context->controller->addCSS($this->_path . 'views/css/elegantalseoessentials-back5.css', 'all');
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJS($this->_path . 'views/js/elegantalseoessentials-back5.js');

        $html = $this->getRedirectAlerts();

        try {
            if ($event = Tools::getValue('event')) {
                switch ($event) {
                    case 'settings':
                        $html .= $this->settings();
                        break;
                    case 'editSettingsCanonical':
                        $html .= $this->editSettingsCanonical();
                        break;
                    case 'editSettingsHreflang':
                        $html .= $this->editSettingsHreflang();
                        break;
                    case 'editSettingsNextPrev':
                        $html .= $this->editSettingsNextPrev();
                        break;
                    case 'editSettingsSitelinksSearchbox':
                        $html .= $this->editSettingsSitelinksSearchbox();
                        break;
                    case 'redirectsList':
                        $html .= $this->redirectsList();
                        break;
                    case 'redirectEdit':
                        $html .= $this->redirectEdit();
                        break;
                    case 'redirectChangeStatus':
                        $html .= $this->redirectChangeStatus();
                        break;
                    case 'redirectDelete':
                        $html .= $this->redirectDelete();
                        break;
                    case 'redirectDeleteAll':
                        $html .= $this->redirectDeleteAll();
                        break;
                    case 'redirectsImport':
                        $html .= $this->redirectsImport();
                        break;
                    case 'redirectsExport':
                        $html .= $this->redirectsExport();
                        break;
                    case 'canonicalsList':
                        $html .= $this->canonicalsList();
                        break;
                    case 'canonicalEdit':
                        $html .= $this->canonicalEdit();
                        break;
                    case 'canonicalChangeStatus':
                        $html .= $this->canonicalChangeStatus();
                        break;
                    case 'canonicalDelete':
                        $html .= $this->canonicalDelete();
                        break;
                    case 'canonicalDeleteAll':
                        $html .= $this->canonicalDeleteAll();
                        break;
                    case 'metaTagsList':
                        $html .= $this->metaTagsList();
                        break;
                    case 'metaTagsSettings':
                        $html .= $this->metaTagsSettings();
                        break;
                    case 'metaTagsUpdate':
                        $html .= $this->metaTagsUpdate();
                        break;
                    case 'metaTagsChangeStatus':
                        $html .= $this->metaTagsChangeStatus();
                        break;
                    case 'metaTagsCron':
                        $html .= $this->metaTagsCron();
                        break;
                    case 'metaTagsDuplicate':
                        $html .= $this->metaTagsDuplicate();
                        break;
                    case 'metaTagsDelete':
                        $html .= $this->metaTagsDelete();
                        break;
                    case 'metaTagsApply':
                        $html .= $this->metaTagsApply();
                        break;
                    case 'metaTagsApplySuccess':
                        $html .= $this->metaTagsApplySuccess();
                        break;

                    case 'imageAltList':
                        $html .= $this->imageAltList();
                        break;
                    case 'imageAltUpdate':
                        $html .= $this->imageAltUpdate();
                        break;
                    case 'imageAltChangeStatus':
                        $html .= $this->imageAltChangeStatus();
                        break;
                    case 'imageAltCron':
                        $html .= $this->imageAltCron();
                        break;
                    case 'imageAltDuplicate':
                        $html .= $this->imageAltDuplicate();
                        break;
                    case 'imageAltDelete':
                        $html .= $this->imageAltDelete();
                        break;
                    case 'imageAltApply':
                        $html .= $this->imageAltApply();
                        break;
                    case 'imageAltApplySuccess':
                        $html .= $this->imageAltApplySuccess();
                        break;
                    case 'htmlBlockList':
                        $html .= $this->htmlBlockList();
                        break;
                    case 'htmlBlockUpdate':
                        $html .= $this->htmlBlockUpdate();
                        break;
                    case 'htmlBlockDuplicate':
                        $html .= $this->htmlBlockDuplicate();
                        break;
                    case 'htmlBlockChangeStatus':
                        $html .= $this->htmlBlockChangeStatus();
                        break;
                    case 'htmlBlockUpdatePositions':
                        $html .= $this->htmlBlockUpdatePositions();
                        break;
                    case 'htmlBlockDelete':
                        $html .= $this->htmlBlockDelete();
                        break;
                    case 'pageNotFoundList':
                        $html .= $this->pageNotFoundList();
                        break;
                    case 'pageNotFoundDownload':
                        $html .= $this->pageNotFoundDownload();
                        break;
                    default:
                        $html .= $this->adminDashboard();
                        break;
                }
            } else {
                $html .= $this->adminDashboard();
            }
        } catch (Exception $e) {
            $this->setRedirectAlert($e->getMessage(), 'error');
            $this->redirectAdmin();
        }

        return $html;
    }

    /**
     * Main page of the module
     * @return string HTML
     */
    protected function adminDashboard()
    {
        $this->context->smarty->assign(
            array(
                'version' => $this->version,
                'adminUrl' => $this->getAdminUrl(),
                'img_lang_dir' => _THEME_LANG_DIR_,
                'languages' => Language::getLanguages(false),
                'documentationUrls' => $this->getDocumentationUrls(),
                'contactDeveloperUrl' => $this->getContactDeveloperUrl(),
                'rateModuleUrl' => $this->getRateModuleUrl(),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/home.tpl');
    }

    /**
     * Renders list of auto-fill meta tag rules
     * @return string HTML
     */
    protected function metaTagsList()
    {
        // Pagination data
        $total = ElegantalSeoEssentialsAutoMeta::model()->countAll();
        $limit = 30;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'id_elegantalseoessentials_auto_meta',
            'name',
            'is_active',
            'applied_at',
        );
        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'id_elegantalseoessentials_auto_meta';
        $orderType = Tools::getValue('orderType') == 'desc' ? 'desc' : 'asc';

        $models = ElegantalSeoEssentialsAutoMeta::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'offset' => $offset,
            'limit' => $limit,
        ));

        // Display categories names
        if ($models && is_array($models)) {
            foreach ($models as &$model) {
                $model['categories'] = "";
                $categories = "";
                $category_ids = ElegantalSeoEssentialsTools::unserialize($model['category_ids']);
                if (!$category_ids || empty($category_ids) || !is_array($category_ids)) {
                    continue;
                }
                $category_ids = array_map('intval', $category_ids);
                $sql = "SELECT cl.`name` FROM `" . _DB_PREFIX_ . "category` c 
                    INNER JOIN `" . _DB_PREFIX_ . "category_lang` cl ON cl.`id_category` = c.`id_category` AND cl.`id_lang` = " . (int) $this->context->language->id . " 
                    WHERE c.`id_category` IN (" . implode(',', $category_ids) . ") 
                    GROUP BY c.`id_category`";
                $rows = Db::getInstance()->executeS($sql);
                if (!$rows || !is_array($rows)) {
                    continue;
                }
                $n = 6;
                $count = count($rows);
                if ($count > ($n + 1)) {
                    for ($i = 0; $i < $n; $i++) {
                        $categories .= ($categories ? ', ' : '') . $rows[$i]['name'];
                    }
                    $categories .= " ...";
                } else {
                    foreach ($rows as $row) {
                        $categories .= ($categories ? ', ' : '') . $row['name'];
                    }
                }
                $model['categories'] = $categories;
            }
        }

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'languages' => Language::getLanguages(),
                'img_lang_dir' => _THEME_LANG_DIR_,
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/meta_tags_list.tpl');
    }

    /**
     * Action to manage Meta Tag settings
     * @return string HTML
     */
    protected function metaTagsSettings()
    {
        $html = "";

        // Process Form
        if ($this->isPostRequest()) {
            $errors = array();

            if (Tools::getValue('meta_title_length') > 0 && Tools::getValue('meta_title_length') <= 128) {
                $this->setSetting('meta_title_length', (int) Tools::getValue('meta_title_length'));
            } else {
                $this->setSetting('meta_title_length', 70);
            }
            if (Tools::getValue('meta_description_length') > 0 && Tools::getValue('meta_description_length') <= 255) {
                $this->setSetting('meta_description_length', (int) Tools::getValue('meta_description_length'));
            } else {
                $this->setSetting('meta_description_length', 160);
            }
            if (Tools::getValue('excluded_product_ids')) {
                $this->setSetting('excluded_product_ids', preg_replace("/[^0-9,]/", "", Tools::getValue('excluded_product_ids')));
            } else {
                $this->setSetting('excluded_product_ids', '');
            }

            if (empty($errors)) {
                $this->setRedirectAlert($this->l('Settings saved successfully.'), 'success');
                if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                    $this->redirectAdmin(array(
                        'event' => 'metaTagsSettings',
                    ));
                } else {
                    $this->redirectAdmin(array(
                        'event' => 'metaTagsList',
                    ));
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        // Render Form
        $fields_value = $this->getSettings();
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('SEO Meta Tag Settings'),
                    'icon' => 'icon-search'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Meta title length'),
                        'name' => 'meta_title_length',
                        'desc' => $this->l('Specify maximum characters allowed for Meta Title. Recommended length is 70.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Meta description length'),
                        'name' => 'meta_description_length',
                        'desc' => $this->l('Specify maximum characters allowed for Meta Description. Recommended length is 160.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Product IDs excluded from auto fill'),
                        'name' => 'excluded_product_ids',
                        'desc' => $this->l('Enter product IDs separated by comma.') . ' ' . $this->l('These products will be excluded from auto filling meta tags.') . ' ' . $this->l('For example') . ': 8,9,10,25',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'metaTagsList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'metaTagsSettings';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'metaTagsSettings'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $html .= $helper->generateForm(array($fields_form));

        return $html;
    }

    protected function metaTagsUpdate()
    {
        $model = new ElegantalSeoEssentialsAutoMeta();
        $model_id = Tools::getValue('id_elegantalseoessentials_auto_meta');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsAutoMeta($model_id);
            if (!Validate::isLoadedObject($model)) {
                $this->setRedirectAlert($this->l('Record not found.'), 'error');
                $this->redirectAdmin(array('event' => 'metaTagsList'));
            }
        }

        $html = "";
        if ($this->isPostRequest()) {
            $errors = $model->validateAndAssignModelAttributes();

            // At least one pattern needed
            $default_lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
            if (!$model->url_pattern[$default_lang_id] && !$model->title_pattern[$default_lang_id] && !$model->description_pattern[$default_lang_id] && !$model->keywords_pattern[$default_lang_id]) {
                $errors[] = $this->l('Please use at least one pattern. The rule has no effect without pattern.');
            }

            try {
                // Validate patterns on random product
                $sql = "SELECT p.`id_product` 
                    FROM `" . _DB_PREFIX_ . "product` p 
                    INNER JOIN `" . _DB_PREFIX_ . "product_shop` psh ON (psh.`id_product` = p.`id_product`) 
                    WHERE psh.`active` = 1 AND psh.`available_for_order` = 1 AND psh.`id_shop` = " . (int) $this->context->shop->id . " 
                    GROUP BY p.`id_product` ORDER BY RAND()";
                $row = Db::getInstance()->getRow($sql);
                if ($row && $row['id_product']) {
                    $model->applyRuleOnProduct($row['id_product'], null, false);
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }

            if (empty($errors)) {
                if (!Tools::getValue('category_ids')) {
                    if (Tools::getValue('categoryBox')) {
                        $model->category_ids = ElegantalSeoEssentialsTools::serialize(Tools::getValue('categoryBox'));
                    } else {
                        $model->category_ids = null;
                    }
                }
                try {
                    $result = empty($model_id) ? $model->add() : $model->update();
                } catch (Exception $e) {
                    $this->setRedirectAlert($e->getMessage(), 'error');
                    $this->redirectAdmin(array(
                        'event' => 'metaTagsUpdate',
                        'id_elegantalseoessentials_auto_meta' => $model->id,
                    ));
                }
                if ($result) {
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->setRedirectAlert($model->name . ': ' . $this->l('Rule saved successfully.'), 'success');
                        $this->redirectAdmin(array(
                            'event' => 'metaTagsUpdate',
                            'id_elegantalseoessentials_auto_meta' => $model->id,
                        ));
                    } else {
                        $this->setRedirectAlert($model->name . ': ' . $this->l('Rule saved successfully.'), 'success');
                        $this->redirectAdmin(array('event' => 'metaTagsList'));
                    }
                } else {
                    $html .= $this->displayError($this->l('Rule could not be saved.') . Db::getInstance()->getMsgError());
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $model->getAttributes();
        $fields_value['category_ids'] = ElegantalSeoEssentialsTools::unserialize($fields_value['category_ids']);

        // Default values
        if (!$fields_value['id_elegantalseoessentials_auto_meta'] && !$this->isPostRequest()) {
            $fields_value['is_active'] = 1;
        }

        // Category input is different in 1.5
        $rootCategory = Category::getRootCategory();
        $category_input = array(
            'type' => 'categories',
            'label' => $this->l('Categories'),
            'name' => 'category_ids',
            'tree' => array(
                'use_search' => true,
                'id' => 'elegantal_category_ids',
                'root_category' => $rootCategory->id,
                'use_checkbox' => true,
                'selected_categories' => $fields_value['category_ids'],
            ),
            'desc' => $this->l('Select categories for which you want to apply this rule. You can leave it empty to apply the rule to all categories.'),
        );
        if (_PS_VERSION_ < '1.6') {
            $category_input = array(
                'type' => 'categories',
                'label' => $this->l('Categories'),
                'name' => 'category_ids',
                'values' => array(
                    'trads' => array(
                        'Root' => array('id_category' => $rootCategory->id_category, 'name' => $rootCategory->name),
                        'selected' => $this->l('Selected'),
                        'Collapse All' => $this->l('Collapse All'),
                        'Expand All' => $this->l('Expand All'),
                        'Check All' => $this->l('Check All'),
                        'Uncheck All' => $this->l('Uncheck All'),
                    ),
                    'selected_cat' => $fields_value['category_ids'],
                    'input_name' => 'category_ids[]',
                    'use_checkbox' => true,
                    'use_radio' => false,
                    'use_search' => false,
                    'top_category' => Category::getTopCategory(),
                    'use_context' => true,
                )
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('SEO Meta Tag Rules') . ': ' . ($fields_value['id_elegantalseoessentials_auto_meta'] ? $this->l('Edit Rule') : $this->l('New Rule')),
                    'icon' => 'icon-search'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Rule name'),
                        'name' => 'name',
                        'desc' => $this->l('Name is for your reference only'),
                    ),
                    $category_input,
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pattern for Friendly-URL'),
                        'name' => 'url_pattern',
                        'lang' => true,
                        'class' => 'elegantaltagpopover',
                        'desc' => $this->l('Choose tags to insert into pattern. You can add custom words too. Leave empty to keep product URL unchanged. NOTE: Friendly URL must be enabled for this feature. You may set length for each product attribute in the following format: {product_name|50}'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pattern for Meta Title'),
                        'name' => 'title_pattern',
                        'lang' => true,
                        'class' => 'elegantaltagpopover',
                        'desc' => $this->l('Choose tags to insert into pattern. You can add custom words too. Leave empty to keep product page title unchanged. You may set length for each product attribute in the following format: {product_name|50}'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pattern for Meta Description'),
                        'name' => 'description_pattern',
                        'lang' => true,
                        'class' => 'elegantaltagpopover',
                        'desc' => $this->l('Choose tags to insert into pattern. You can add custom words too. Leave empty to keep product page meta description unchanged. You may set length for each product attribute in the following format: {product_name|50}'),
                    ),
                    /* // Meta-keywords are not being used. keywords_pattern will be removed in future updates.
                      array(
                      'type' => 'text',
                      'label' => $this->l('Pattern for Meta Keywords'),
                      'name' => 'keywords_pattern',
                      'lang' => true,
                      'class' => 'elegantaltagpopover',
                      'desc' => $this->l('Choose tags to insert into pattern. You can add custom words too. Leave empty to keep product page meta keywords unchanged. You may set length for each product attribute in the following format: {product_name|50}'),
                      ), */
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'is_active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Only active rules will be applied'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'metaTagsList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'metaTagsUpdate';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'metaTagsUpdate', 'id_elegantalseoessentials_auto_meta' => $model_id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->context->smarty->assign(array('shortcodes' => ElegantalSeoEssentialsAutoMeta::$shortcodes));
        $html .= $this->display(__FILE__, 'views/templates/admin/shortcodes.tpl');

        return $html . $helper->generateForm(array($fields_form));
    }

    protected function metaTagsChangeStatus()
    {
        $model = new ElegantalSeoEssentialsAutoMeta(Tools::getValue('id_elegantalseoessentials_auto_meta'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'metaTagsList'));
        }
        $model->is_active = $model->is_active == 1 ? 0 : 1;
        if ($model->update()) {
            $this->setRedirectAlert($model->name . ': ' . $this->l('Status changed successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Status could not be changed.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'metaTagsList'));
    }

    protected function metaTagsCron()
    {
        $cron_cpanel_doc = null;
        $documentation_urls = $this->getDocumentationUrls();
        foreach ($documentation_urls as $doc => $url) {
            if ($doc == 'Setup Cron Job In Cpanel') {
                $cron_cpanel_doc = $url;
                break;
            }
        }
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'backUrl' => $this->getAdminUrl() . '&event=metaTagsList',
                'cronUrl' => $this->getControllerUrl('metaTagsCron', array('id' => Tools::getValue('id_elegantalseoessentials_auto_meta'))),
                'cron_cpanel_doc' => $cron_cpanel_doc,
                'header_title' => $this->l('SEO Meta Tag Rule') . " '" . Tools::getValue('name') . "' ",
                'subject_note' => $this->l('You can use CRON to automatically apply the rule on scheduled time periods.'),
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/cron.tpl');
    }

    protected function metaTagsDuplicate()
    {
        $model = new ElegantalSeoEssentialsAutoMeta(Tools::getValue('id_elegantalseoessentials_auto_meta'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'metaTagsList'));
        }
        $model->id = null;
        $model->id_elegantalseoessentials_auto_meta = null;
        $model->name .= ' (Copy)';
        $model->created_at = date('Y-m-d H:i:s');
        $model->applied_at = null;
        if ($model->add()) {
            $this->setRedirectAlert($this->l('Rule duplicated successfully.'), 'success');
            $this->redirectAdmin(array(
                'event' => 'metaTagsUpdate',
                'id_elegantalseoessentials_auto_meta' => $model->id,
            ));
        } else {
            $this->setRedirectAlert($this->l('Rule could not be duplicated.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'metaTagsList'));
    }

    protected function metaTagsDelete()
    {
        $model = new ElegantalSeoEssentialsAutoMeta(Tools::getValue('id_elegantalseoessentials_auto_meta'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'metaTagsList'));
        }
        if ($model->delete()) {
            $this->setRedirectAlert($model->name . ': ' . $this->l('Rule deleted successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Rule could not be deleted.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'metaTagsList'));
    }

    protected function metaTagsApply()
    {
        $model = new ElegantalSeoEssentialsAutoMeta(Tools::getValue('id_elegantalseoessentials_auto_meta'));

        if (Tools::getValue('ajax')) {
            $result = array();
            if (Validate::isLoadedObject($model)) {
                try {
                    $offset = Tools::getValue('offset');
                    $limit = Tools::getValue('limit');
                    $product_ids = $model->getProductIds($offset, $limit);
                    foreach ($product_ids as $product_id) {
                        $model->applyRuleOnProduct($product_id, Tools::getValue('lang_id'));
                    }
                    $result['success'] = true;
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            } else {
                $result['success'] = false;
                $result['message'] = $this->l('Record not found.');
            }
            die(Tools::jsonEncode($result));
        }

        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'metaTagsList'));
        }

        $product_ids = $model->getProductIds();
        $total = count($product_ids);
        $offset = 0;
        $limit = (int) $this->getSetting('limit_per_request');
        $requests = 1;

        if ($total && $total > $offset && $total > $limit) {
            $requests = ceil(($total - $offset) / $limit);
        }

        $this->context->smarty->assign(
            array(
                'model' => $model,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'requests' => $requests,
                'path' => $this->_path,
                'adminUrl' => $this->getAdminUrl(),
                'lang_id' => Tools::getValue('lang_id'),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/meta_tags_apply.tpl');
    }

    /**
     * Action called when meta tags auto fill process is completed
     */
    protected function metaTagsApplySuccess()
    {
        $model = new ElegantalSeoEssentialsAutoMeta(Tools::getValue('id_elegantalseoessentials_auto_meta'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'metaTagsList'));
        }
        $model->applied_at = date('Y-m-d H:i:s');
        $model->update();
        $this->setRedirectAlert($this->l('Rule applied successfully.'), 'success');
        $this->redirectAdmin(array('event' => 'metaTagsList'));
    }

    /**
     * Renders list of image alt rules
     * @return string HTML
     */
    protected function imageAltList()
    {
        // Pagination data
        $total = ElegantalSeoEssentialsImageAlt::model()->countAll();
        $limit = 30;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'id_elegantalseoessentials_image_alt',
            'name',
            'is_active',
            'applied_at',
        );
        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'id_elegantalseoessentials_image_alt';
        $orderType = Tools::getValue('orderType') == 'desc' ? 'desc' : 'asc';

        $models = ElegantalSeoEssentialsImageAlt::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'offset' => $offset,
            'limit' => $limit,
        ));

        // Display categories names
        if ($models && is_array($models)) {
            foreach ($models as &$model) {
                $model['categories'] = "";
                $categories = "";
                $category_ids = ElegantalSeoEssentialsTools::unserialize($model['category_ids']);
                if (!$category_ids || empty($category_ids) || !is_array($category_ids)) {
                    continue;
                }
                $category_ids = array_map('intval', $category_ids);
                $sql = "SELECT cl.`name` FROM `" . _DB_PREFIX_ . "category` c 
                    INNER JOIN `" . _DB_PREFIX_ . "category_lang` cl ON cl.`id_category` = c.`id_category` AND cl.`id_lang` = " . (int) $this->context->language->id . " 
                    WHERE c.`id_category` IN (" . implode(',', $category_ids) . ") 
                    GROUP BY c.`id_category`";
                $rows = Db::getInstance()->executeS($sql);
                if (!$rows || !is_array($rows)) {
                    continue;
                }
                $n = 6;
                $count = count($rows);
                if ($count > ($n + 1)) {
                    for ($i = 0; $i < $n; $i++) {
                        $categories .= ($categories ? ', ' : '') . $rows[$i]['name'];
                    }
                    $categories .= " ...";
                } else {
                    foreach ($rows as $row) {
                        $categories .= ($categories ? ', ' : '') . $row['name'];
                    }
                }
                $model['categories'] = $categories;
            }
        }

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'languages' => Language::getLanguages(),
                'img_lang_dir' => _THEME_LANG_DIR_,
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/image_alt_list.tpl');
    }

    protected function imageAltUpdate()
    {
        $model = new ElegantalSeoEssentialsImageAlt();
        $model_id = Tools::getValue('id_elegantalseoessentials_image_alt');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsImageAlt($model_id);
            if (!Validate::isLoadedObject($model)) {
                $this->setRedirectAlert($this->l('Record not found.'), 'error');
                $this->redirectAdmin(array('event' => 'imageAltList'));
            }
        }

        $html = "";
        if ($this->isPostRequest()) {
            $errors = $model->validateAndAssignModelAttributes();

            if (empty($errors)) {
                if (!Tools::getValue('category_ids')) {
                    if (Tools::getValue('categoryBox')) {
                        $model->category_ids = ElegantalSeoEssentialsTools::serialize(Tools::getValue('categoryBox'));
                    } else {
                        $model->category_ids = null;
                    }
                }
                try {
                    $result = empty($model_id) ? $model->add() : $model->update();
                } catch (Exception $e) {
                    $this->setRedirectAlert($e->getMessage(), 'error');
                    $this->redirectAdmin(array(
                        'event' => 'imageAltUpdate',
                        'id_elegantalseoessentials_image_alt' => $model->id,
                    ));
                }
                if ($result) {
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->setRedirectAlert($model->name . ': ' . $this->l('Rule saved successfully.'), 'success');
                        $this->redirectAdmin(array(
                            'event' => 'imageAltUpdate',
                            'id_elegantalseoessentials_image_alt' => $model->id,
                        ));
                    } else {
                        $this->setRedirectAlert($model->name . ': ' . $this->l('Rule saved successfully.'), 'success');
                        $this->redirectAdmin(array('event' => 'imageAltList'));
                    }
                } else {
                    $html .= $this->displayError($this->l('Rule could not be saved.') . Db::getInstance()->getMsgError());
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $model->getAttributes();
        $fields_value['category_ids'] = ElegantalSeoEssentialsTools::unserialize($fields_value['category_ids']);

        // Default values
        if (!$fields_value['id_elegantalseoessentials_image_alt'] && !$this->isPostRequest()) {
            $fields_value['is_active'] = 1;
        }

        // Category input is different in 1.5
        $rootCategory = Category::getRootCategory();
        $category_input = array(
            'type' => 'categories',
            'label' => $this->l('Categories'),
            'name' => 'category_ids',
            'tree' => array(
                'use_search' => true,
                'id' => 'elegantal_category_ids',
                'root_category' => $rootCategory->id,
                'use_checkbox' => true,
                'selected_categories' => $fields_value['category_ids'],
            ),
            'desc' => $this->l('Select categories for which you want to apply this rule. You can leave it empty to apply the rule to all categories.'),
        );
        if (_PS_VERSION_ < '1.6') {
            $category_input = array(
                'type' => 'categories',
                'label' => $this->l('Categories'),
                'name' => 'category_ids',
                'values' => array(
                    'trads' => array(
                        'Root' => array('id_category' => $rootCategory->id_category, 'name' => $rootCategory->name),
                        'selected' => $this->l('Selected'),
                        'Collapse All' => $this->l('Collapse All'),
                        'Expand All' => $this->l('Expand All'),
                        'Check All' => $this->l('Check All'),
                        'Uncheck All' => $this->l('Uncheck All'),
                    ),
                    'selected_cat' => $fields_value['category_ids'],
                    'input_name' => 'category_ids[]',
                    'use_checkbox' => true,
                    'use_radio' => false,
                    'use_search' => false,
                    'top_category' => Category::getTopCategory(),
                    'use_context' => true,
                )
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => 'SEO Image Alt Tag Rules: ' . ($fields_value['id_elegantalseoessentials_image_alt'] ? $this->l('Edit Rule') : $this->l('New Rule')),
                    'icon' => 'icon-image'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Rule name'),
                        'name' => 'name',
                        'desc' => $this->l('Name is for your reference only'),
                    ),
                    $category_input,
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pattern for image ALT tag'),
                        'name' => 'pattern',
                        'lang' => true,
                        'class' => 'elegantaltagpopover',
                        'desc' => $this->l('Choose product properties to insert into pattern. You can add custom words too. You may set length for each product attribute in the following format: {product_name|50}'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'is_active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Only active rules will be applied'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'imageAltList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'imageAltUpdate';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'imageAltUpdate', 'id_elegantalseoessentials_image_alt' => $model_id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->context->smarty->assign(array('shortcodes' => ElegantalSeoEssentialsImageAlt::$shortcodes));
        $html .= $this->display(__FILE__, 'views/templates/admin/shortcodes.tpl');

        return $html . $helper->generateForm(array($fields_form));
    }

    protected function imageAltChangeStatus()
    {
        $model = new ElegantalSeoEssentialsImageAlt(Tools::getValue('id_elegantalseoessentials_image_alt'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'imageAltList'));
        }
        $model->is_active = $model->is_active == 1 ? 0 : 1;
        if ($model->update()) {
            $this->setRedirectAlert($model->name . ': ' . $this->l('Status changed successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Status could not be changed.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'imageAltList'));
    }

    protected function imageAltCron()
    {
        $cron_cpanel_doc = null;
        $documentation_urls = $this->getDocumentationUrls();
        foreach ($documentation_urls as $doc => $url) {
            if ($doc == 'Setup Cron Job In Cpanel') {
                $cron_cpanel_doc = $url;
                break;
            }
        }
        $this->context->smarty->assign(
            array(
                'adminUrl' => $this->getAdminUrl(),
                'backUrl' => $this->getAdminUrl() . '&event=imageAltList',
                'cronUrl' => $this->getControllerUrl('imageAltCron', array('id' => Tools::getValue('id_elegantalseoessentials_image_alt'))),
                'cron_cpanel_doc' => $cron_cpanel_doc,
                'header_title' => $this->l('SEO Image ALT Rule') . " '" . Tools::getValue('name') . "' ",
                'subject_note' => $this->l('You can use CRON to automatically apply the rule on scheduled time periods.'),
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/cron.tpl');
    }

    protected function imageAltDuplicate()
    {
        $model = new ElegantalSeoEssentialsImageAlt(Tools::getValue('id_elegantalseoessentials_image_alt'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'imageAltList'));
        }
        $model->id = null;
        $model->id_elegantalseoessentials_image_alt = null;
        $model->name .= ' (Copy)';
        $model->created_at = date('Y-m-d H:i:s');
        $model->applied_at = null;
        if ($model->add()) {
            $this->setRedirectAlert($this->l('Rule duplicated successfully.'), 'success');
            $this->redirectAdmin(array(
                'event' => 'imageAltUpdate',
                'id_elegantalseoessentials_image_alt' => $model->id,
            ));
        } else {
            $this->setRedirectAlert($this->l('Rule could not be duplicated.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'imageAltList'));
    }

    protected function imageAltDelete()
    {
        $model = new ElegantalSeoEssentialsImageAlt(Tools::getValue('id_elegantalseoessentials_image_alt'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'imageAltList'));
        }
        if ($model->delete()) {
            $this->setRedirectAlert($model->name . ': ' . $this->l('Rule deleted successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Rule could not be deleted.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'imageAltList'));
    }

    protected function imageAltApply()
    {
        $model = new ElegantalSeoEssentialsImageAlt(Tools::getValue('id_elegantalseoessentials_image_alt'));

        if (Tools::getValue('ajax')) {
            $result = array();
            if (Validate::isLoadedObject($model)) {
                try {
                    $offset = Tools::getValue('offset');
                    $limit = Tools::getValue('limit');
                    $product_ids = $model->getProductIds($offset, $limit);
                    foreach ($product_ids as $product_id) {
                        $model->applyRuleOnProduct($product_id, Tools::getValue('lang_id'));
                    }
                    $result['success'] = true;
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            } else {
                $result['success'] = false;
                $result['message'] = $this->l('Record not found.');
            }
            die(Tools::jsonEncode($result));
        }

        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'imageAltList'));
        }

        $product_ids = $model->getProductIds();
        $total = count($product_ids);
        $offset = 0;
        $limit = (int) $this->getSetting('limit_per_request');
        $requests = 1;

        if ($total && $total > $offset && $total > $limit) {
            $requests = ceil(($total - $offset) / $limit);
        }

        $this->context->smarty->assign(
            array(
                'model' => $model,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'requests' => $requests,
                'path' => $this->_path,
                'adminUrl' => $this->getAdminUrl(),
                'lang_id' => Tools::getValue('lang_id'),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/image_alt_apply.tpl');
    }

    /**
     * Action called when image alt auto fill process is completed
     */
    protected function imageAltApplySuccess()
    {
        $model = new ElegantalSeoEssentialsImageAlt(Tools::getValue('id_elegantalseoessentials_image_alt'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'imageAltList'));
        }
        $model->applied_at = date('Y-m-d H:i:s');
        $model->update();
        $this->setRedirectAlert($this->l('Rule applied successfully.'), 'success');
        $this->redirectAdmin(array('event' => 'imageAltList'));
    }

    /**
     * Renders list of HTML blocks
     * @return string HTML
     */
    protected function htmlBlockList()
    {
        // Pagination data
        $total = ElegantalSeoEssentialsHtml::model()->countAll();
        $limit = 50;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'name',
            'hooks',
            'pages',
            'is_active',
            'position',
        );
        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'position';
        $orderBy = ($orderBy == 'tag') ? 'l.tag' : $orderBy;
        $orderType = Tools::getValue('orderType') == 'desc' ? 'desc' : 'asc';

        // Get records
        $models = ElegantalSeoEssentialsHtml::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'offset' => $offset,
            'limit' => $limit,
        ));

        $webPages = $this->getPagesForSelect();

        foreach ($models as &$model) {
            $model_pages = array();
            $selected_pages = ElegantalSeoEssentialsTools::unserialize($model['pages']);
            if ($selected_pages && is_array($selected_pages)) {
                foreach ($selected_pages as $selected_page) {
                    foreach ($webPages as $page) {
                        if ($page['key'] == $selected_page) {
                            $model_pages[] = $page['value'];
                        }
                    }
                }
            }
            $model['pages'] = implode(', ', $model_pages);

            $selected_hooks = ElegantalSeoEssentialsTools::unserialize($model['hooks']);
            $model['hooks'] = implode(', ', $selected_hooks);
        }

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/html_block_list.tpl');
    }

    /**
     * Render and process edit HTML block form
     * @return string HTML
     */
    protected function htmlBlockUpdate()
    {
        $model = new ElegantalSeoEssentialsHtml();
        $model_id = Tools::getValue('id_elegantalseoessentials_html');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsHtml($model_id);
            if (!Validate::isLoadedObject($model)) {
                $this->setRedirectAlert($this->l('Record not found.'), 'error');
                $this->redirectAdmin(array('event' => 'htmlBlockList'));
            }
        }

        $html = "";
        $default_lang_id = (int) Configuration::get('PS_LANG_DEFAULT');

        if ($this->isPostRequest()) {
            $errors = $model->validateAndAssignModelAttributes();

            if (empty($errors)) {
                try {
                    $result = empty($model_id) ? $model->add() : $model->update();
                } catch (Exception $e) {
                    $this->setRedirectAlert($e->getMessage(), 'error');
                    $this->redirectAdmin(array(
                        'event' => 'htmlBlockUpdate',
                        'id_elegantalseoessentials_html' => $model->id,
                    ));
                }
                if ($result) {
                    if (Tools::getValue('hooks') && is_array(Tools::getValue('hooks'))) {
                        foreach (Tools::getValue('hooks') as $hook_register) {
                            $this->registerHook($hook_register);
                        }
                    }
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->setRedirectAlert($model->name . ': ' . $this->l('Record saved successfully.'), 'success');
                        $this->redirectAdmin(array(
                            'event' => 'htmlBlockUpdate',
                            'id_elegantalseoessentials_html' => $model->id,
                        ));
                    } else {
                        $this->setRedirectAlert($model->name . ': ' . $this->l('Record saved successfully.'), 'success');
                        $this->redirectAdmin(array('event' => 'htmlBlockList'));
                    }
                } else {
                    $html .= $this->displayError($this->l('Record could not be saved.'));
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $model->getAttributes();

        // Default values
        if (!$fields_value['id_elegantalseoessentials_html'] && !$this->isPostRequest()) {
            $fields_value['pages[]'] = array('all');
            $fields_value['hooks[]'] = array('displayHeader');
            $fields_value['position'] = 1;
            $fields_value['is_active'] = 1;

            if (Tools::getValue('facebook_tags')) {
                $fields_value['name'] = 'Facebook Open Graph Tags';
                $fields_value['pages[]'] = array('product');
                $fields_value['html'][$default_lang_id] = $this->display(__FILE__, 'views/templates/admin/facebook_open_graph.tpl');
            }
            if (Tools::getValue('twitter_cards')) {
                $fields_value['name'] = 'Twitter Card Meta Tags';
                $fields_value['pages[]'] = array('product');
                $fields_value['html'][$default_lang_id] = $this->display(__FILE__, 'views/templates/admin/twitter_cards.tpl');
            }
        } else {
            // This is needed for multiple select input
            $selected_pages = ElegantalSeoEssentialsTools::unserialize($fields_value['pages']);
            if (!empty($selected_pages)) {
                $fields_value['pages[]'] = $selected_pages;
            }
            $selected_hooks = ElegantalSeoEssentialsTools::unserialize($fields_value['hooks']);
            if (!empty($selected_hooks)) {
                $fields_value['hooks[]'] = $selected_hooks;
            }
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $fields_value['id_elegantalseoessentials_html'] ? $this->l('Edit HTML') : $this->l('Create HTML'),
                    'icon' => 'icon-code'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_elegantalseoessentials_html',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'name',
                        'required' => true,
                        'desc' => $this->l('Give a name for this HTML.') . ' ' . $this->l('This is for your reference only.'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('HTML Code'),
                        'name' => 'html',
                        'required' => true,
                        'lang' => true,
                        'cols' => 40,
                        'rows' => 20,
                        'class' => 'html_textarea',
                        'desc' => $this->l('You can use short-codes below to insert into your HTML code.') . ' ' . $this->l('NOTE') . ': ' . $this->l('GENERAL short-codes work on all pages, PRODUCT short-codes work only for product page, CATEGORY short-codes work only for category page and so on.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Pages'),
                        'name' => 'pages[]',
                        'required' => true,
                        'multiple' => true,
                        'options' => array(
                            'query' => $this->getPagesForSelect(),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select pages where this HTML code should exist.') . ' ' . $this->l('You can select multiple items with CTRL + MOUSE LEFT CLICK.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => 'Hooks',
                        'name' => 'hooks[]',
                        'required' => true,
                        'multiple' => true,
                        'options' => array(
                            'query' => $this->getHooksForSelect(),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('Select hooks where this HTML code should exist.') . ' ' . $this->l('You can select multiple items with CTRL + MOUSE LEFT CLICK.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'is_active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'htmlBlockList')),
                        'title' => $this->l('Back'),
                        'icon' => 'process-icon-back'
                    ),
                    array(
                        'title' => $this->l('Save and stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                )
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'htmlBlockUpdate';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'htmlBlockUpdate', 'id_elegantalseoessentials_html' => $model_id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->context->smarty->assign(array('shortcodes' => ElegantalSeoEssentialsHtml::$shortcodes));
        $html .= $this->display(__FILE__, 'views/templates/admin/shortcodes.tpl');

        return $html . $helper->generateForm(array($fields_form));
    }

    protected function htmlBlockChangeStatus()
    {
        $model = new ElegantalSeoEssentialsHtml(Tools::getValue('id_elegantalseoessentials_html'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'htmlBlockList'));
        }
        $model->is_active = $model->is_active == 1 ? 0 : 1;
        if ($model->update()) {
            $this->setRedirectAlert($model->name . ': ' . $this->l('Status changed successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Status could not be changed.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'htmlBlockList'));
    }

    protected function htmlBlockDuplicate()
    {
        $model = new ElegantalSeoEssentialsHtml(Tools::getValue('id_elegantalseoessentials_html'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'htmlBlockList'));
        }
        $model->id = null;
        $model->id_elegantalseoessentials_html = null;
        $model->name .= ' (Copy)';
        $model->position = $model->position + 1;
        if ($model->add()) {
            $this->setRedirectAlert($this->l('Record duplicated successfully.'), 'success');
            $this->redirectAdmin(array(
                'event' => 'htmlBlockUpdate',
                'id_elegantalseoessentials_html' => $model->id,
            ));
        } else {
            $this->setRedirectAlert($this->l('Rule could not be duplicated.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'htmlBlockList'));
    }

    protected function htmlBlockDelete()
    {
        $model = new ElegantalSeoEssentialsHtml(Tools::getValue('id_elegantalseoessentials_html'));
        if (!Validate::isLoadedObject($model)) {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
            $this->redirectAdmin(array('event' => 'htmlBlockList'));
        }
        if ($model->delete()) {
            $this->setRedirectAlert($model->name . ': ' . $this->l('Rule deleted successfully.'), 'success');
        } else {
            $this->setRedirectAlert($this->l('Rule could not be deleted.'), 'error');
        }
        $this->redirectAdmin(array('event' => 'htmlBlockList'));
    }

    protected function htmlBlockUpdatePositions()
    {
        $positions = Tools::getValue('positions');
        if ($positions) {
            $positions = explode('-', $positions);

            foreach ($positions as $position) {
                $data = explode('_', $position);
                if (isset($data[0]) && isset($data[1])) {
                    $model = new ElegantalSeoEssentialsHtml($data[0]);
                    if ($model) {
                        $model->position = $data[1];
                        $model->update();
                    }
                }
            }
        }
        die('Ok');
    }

    protected function htmlBlockFront($hook)
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $all_hooks = $this->getHooksForSelect();
        $hook_exists = false;
        foreach ($all_hooks as $hook_arr) {
            if (Tools::strtolower($hook_arr['key']) == Tools::strtolower($hook)) {
                $hook_exists = true;
                break;
            }
        }
        if (!$hook_exists) {
            return false;
        }

        $models = ElegantalSeoEssentialsHtml::model()->findAll(array(
            'condition' => array(
                'is_active' => 1,
            ),
            'order' => 'position'
        ));
        if (empty($models) || !is_array($models)) {
            return false;
        }

        $html = "";
        $controller = Dispatcher::getInstance()->getController();
        if (!empty($this->context->controller->php_self)) {
            $controller = $this->context->controller->php_self;
        }

        foreach ($models as $model) {
            $htmlBlock = new ElegantalSeoEssentialsHtml($model['id_elegantalseoessentials_html'], $id_lang, $id_shop);
            if (!Validate::isLoadedObject($htmlBlock)) {
                continue;
            }
            $htmlBlock->pages = ElegantalSeoEssentialsTools::unserialize($htmlBlock->pages);
            $htmlBlock->hooks = ElegantalSeoEssentialsTools::unserialize($htmlBlock->hooks);
            $htmlBlock->hooks = array_map('strtolower', $htmlBlock->hooks);
            if ((in_array('all', $htmlBlock->pages) || in_array($controller, $htmlBlock->pages) || ($controller == 'cms' && in_array('cms_' . Tools::getValue('id_cms'), $htmlBlock->pages))) &&
                in_array(Tools::strtolower($hook), $htmlBlock->hooks)) {
                $html .= $htmlBlock->renderHtml();
            }
        }

        return $html;
    }

    /**
     * Returns list of pages for select box
     * @return array
     */
    protected function getPagesForSelect()
    {
        $pages = array(array('key' => 'all', 'value' => $this->l('ALL PAGES')));

        // Add common pages
        $common_pages = array('index', 'product', 'category');
        foreach ($common_pages as $common_page) {
            $key = $value = $common_page;
            if ($key == 'index') {
                $value = 'Home Page';
            }
            $pages[] = array('key' => $key, 'value' => $value);
        }

        // Add meta pages
        $metas = Meta::getPages();
        foreach ($metas as $meta) {
            if (!in_array($meta, $common_pages)) {
                $pages[] = array('key' => $meta, 'value' => $meta);
            }
        }

        // Add cms pages
        $cmss = CMS::getCMSPages($this->context->language->id, null, true, $this->context->shop->id);
        foreach ($cmss as $cms) {
            $key = "cms_" . $cms['id_cms'];
            $value = $cms['meta_title'] ? $cms['meta_title'] : $key;
            $pages[] = array('key' => $key, 'value' => $value);
        }

        return $pages;
    }

    /**
     * Returns list of hooks for select box
     * @return array
     */
    protected function getHooksForSelect()
    {
        // Common hooks that exist both in 1.7 and 1.6
        $hooks = array(
            array('key' => 'displayTop', 'value' => 'displayTop'),
            array('key' => 'displayTopColumn', 'value' => 'displayTopColumn'),
            array('key' => 'displayNav', 'value' => 'displayNav'),
            array('key' => 'displayHome', 'value' => 'displayHome'),
            array('key' => 'displayLeftColumn', 'value' => 'displayLeftColumn'),
            array('key' => 'displayRightColumn', 'value' => 'displayRightColumn'),
            array('key' => 'displayFooter', 'value' => 'displayFooter'),
            array('key' => 'displayLeftColumnProduct', 'value' => 'displayLeftColumnProduct'),
            array('key' => 'displayRightColumnProduct', 'value' => 'displayRightColumnProduct'),
            array('key' => 'displayFooterProduct', 'value' => 'displayFooterProduct'),
            array('key' => 'displayShoppingCartFooter', 'value' => 'displayShoppingCartFooter'),
            array('key' => 'displayShoppingCart', 'value' => 'displayShoppingCart'),
            array('key' => 'displayBeforeCarrier', 'value' => 'displayBeforeCarrier'),
            array('key' => 'displayBeforeBodyClosingTag', 'value' => 'displayBeforeBodyClosingTag'),
            array('key' => 'displayCartExtraProductActions', 'value' => 'displayCartExtraProductActions'),
            array('key' => 'displayContentWrapperTop', 'value' => 'displayContentWrapperTop'),
            array('key' => 'displayCustomerAccount', 'value' => 'displayCustomerAccount'),
            array('key' => 'displayMaintenance', 'value' => 'displayMaintenance'),
            array('key' => 'displayProductAdditionalInfo', 'value' => 'displayProductAdditionalInfo'),
            array('key' => 'displayProductExtraContent', 'value' => 'displayProductExtraContent'),
            array('key' => 'displayReassurance', 'value' => 'displayReassurance'),
        );
        if (_PS_VERSION_ < '1.7') { // Only in 1.6
            $hooks[] = array('key' => 'displayHomeTab', 'value' => 'displayHomeTab');
            $hooks[] = array('key' => 'displayHomeTabContent', 'value' => 'displayHomeTabContent');
            $hooks[] = array('key' => 'displayProductTab', 'value' => 'displayProductTab');
            $hooks[] = array('key' => 'displayProductTabContent', 'value' => 'displayProductTabContent');
            $hooks[] = array('key' => 'displayProductButtons', 'value' => 'displayProductButtons');
            $hooks[] = array('key' => 'displayBanner', 'value' => 'displayBanner');
        } else { // Only in 1.7
            $hooks[] = array('key' => 'displayAfterBodyOpeningTag', 'value' => 'displayAfterBodyOpeningTag');
            $hooks[] = array('key' => 'displayAfterCarrier', 'value' => 'displayAfterCarrier');
            $hooks[] = array('key' => 'displayAfterProductThumbs', 'value' => 'displayAfterProductThumbs');
            $hooks[] = array('key' => 'displayCarrierExtraContent', 'value' => 'displayCarrierExtraContent');
            $hooks[] = array('key' => 'displayContentWrapperBottom', 'value' => 'displayContentWrapperBottom');
            $hooks[] = array('key' => 'displayFooterBefore', 'value' => 'displayFooterBefore');
            $hooks[] = array('key' => 'displayNav1', 'value' => 'displayNav1');
            $hooks[] = array('key' => 'displayNav2', 'value' => 'displayNav2');
            $hooks[] = array('key' => 'displayNavFullWidth', 'value' => 'displayNavFullWidth');
            $hooks[] = array('key' => 'displayWrapperBottom', 'value' => 'displayWrapperBottom');
            $hooks[] = array('key' => 'displayWrapperTop', 'value' => 'displayWrapperTop');
        }
        // Sort hooks alphabetically
        usort($hooks, function ($a, $b) {
            return strcmp($a['value'], $b['value']);
        });
        // Put displayHeader at the beginning
        array_unshift($hooks, array('key' => 'displayHeader', 'value' => 'displayHeader'));

        return $hooks;
    }

    /**
     * Action function to manage Canonical settings
     * @return string HTML
     */
    protected function editSettingsCanonical()
    {
        $html = "";

        // Process Form
        if ($this->isPostRequest()) {
            $errors = array();

            if (Tools::getValue('is_enable_canonical')) {
                $this->setSetting('is_enable_canonical', 1);
            } else {
                $this->setSetting('is_enable_canonical', 0);
            }

            if (empty($errors)) {
                $this->setRedirectAlert($this->l('Settings saved successfully.'), 'success');
                if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                    $this->redirectAdmin(array(
                        'event' => 'editSettingsCanonical',
                    ));
                } else {
                    $this->redirectAdmin();
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        // Render Form
        $fields_value = $this->getSettings();
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Canonical URL'),
                    'icon' => 'icon-link'
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => 'Enable Canonical URL',
                        'name' => 'is_enable_canonical',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_enable_canonical_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_enable_canonical_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Canonical URL tag prevents duplicate content issues.')
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'canonicalsList')),
                        'title' => $this->l('Custom Canonicals'),
                        'class' => 'pull-right',
                        'icon' => 'process-icon-edit'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'editSettingsCanonical';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'editSettingsCanonical'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $html .= $helper->generateForm(array($fields_form));

        return $html;
    }

    /**
     * Action function to manage Hreflang settings
     * @return string HTML
     */
    protected function editSettingsHreflang()
    {
        $html = "";

        // Process Form
        if ($this->isPostRequest()) {
            $errors = array();

            if (Tools::getValue('is_enable_hreflang')) {
                $this->setSetting('is_enable_hreflang', 1);
            } else {
                $this->setSetting('is_enable_hreflang', 0);
            }
            if (Tools::getValue('is_hreflang_use_multishop_domain')) {
                $this->setSetting('is_hreflang_use_multishop_domain', 1);
            } else {
                $this->setSetting('is_hreflang_use_multishop_domain', 0);
            }

            if (empty($errors)) {
                $this->setRedirectAlert($this->l('Settings saved successfully.'), 'success');
                if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                    $this->redirectAdmin(array(
                        'event' => 'editSettingsHreflang',
                    ));
                } else {
                    $this->redirectAdmin();
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        // Render Form
        $fields_value = $this->getSettings();
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Hreflang Tags'),
                    'icon' => 'icon-language'
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => 'Enable Hreflang Tags',
                        'name' => 'is_enable_hreflang',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_enable_hreflang_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_enable_hreflang_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Hreflang tags make search engines send people to the content in their own language')
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => 'Include Multishop Hreflang Tags',
                        'name' => 'is_hreflang_use_multishop_domain',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_hreflang_use_multishop_domain_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_hreflang_use_multishop_domain_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Hreflang tags from other shops will be added if there is a language enabled only for the other shop.') . ' ' . $this->l('For example:') . ' ' . $this->l('If your first shop has English and second shop has French, French hreflang tag will be added on the first shop and it will use the domain of the second shop.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'editSettingsHreflang';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'editSettingsHreflang'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $html .= $helper->generateForm(array($fields_form));

        return $html;
    }

    /**
     * Action to manage next/prev tag settings
     * @return string HTML
     */
    protected function editSettingsNextPrev()
    {
        $html = "";

        // Process Form
        if ($this->isPostRequest()) {
            $errors = array();

            if (Tools::getValue('is_enable_nextprev')) {
                $this->setSetting('is_enable_nextprev', 1);
            } else {
                $this->setSetting('is_enable_nextprev', 0);
            }

            if (empty($errors)) {
                $this->setRedirectAlert($this->l('Settings saved successfully.'), 'success');
                if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                    $this->redirectAdmin(array(
                        'event' => 'editSettingsNextPrev',
                    ));
                } else {
                    $this->redirectAdmin();
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        // Render Form
        $fields_value = $this->getSettings();
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Next/Prev Tags'),
                    'icon' => 'icon-copy'
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Enable Next/Prev Tags'),
                        'name' => 'is_enable_nextprev',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_enable_nextprev_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_enable_nextprev_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Enable rel="next" and rel="prev" tags on paginated pages to make search engines send users to the most relevant page')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'editSettingsNextPrev';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'editSettingsNextPrev'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $html . $helper->generateForm(array($fields_form));
    }

    /**
     * Action to manage Sitelinks Searchbox settings
     * @return string HTML
     */
    protected function editSettingsSitelinksSearchbox()
    {
        $html = "";

        // Process Form
        if ($this->isPostRequest()) {
            $errors = array();

            if (Tools::getValue('is_enable_sitelinks_searchbox')) {
                $this->setSetting('is_enable_sitelinks_searchbox', 1);
            } else {
                $this->setSetting('is_enable_sitelinks_searchbox', 0);
            }

            if (empty($errors)) {
                $this->setRedirectAlert($this->l('Settings saved successfully.'), 'success');
                if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                    $this->redirectAdmin(array(
                        'event' => 'editSettingsSitelinksSearchbox',
                    ));
                } else {
                    $this->redirectAdmin();
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        // Render Form
        $fields_value = $this->getSettings();
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => 'Google Sitelinks Searchbox',
                    'icon' => 'icon-google'
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Enable') . ' Google Sitelinks Searchbox',
                        'name' => 'is_enable_sitelinks_searchbox',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_enable_sitelinks_searchbox_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_enable_sitelinks_searchbox_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Enables search box that appears within search results on Google. It allows people to search on your shop from Google search results page.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'editSettingsSitelinksSearchbox';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'editSettingsSitelinksSearchbox'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $html . $helper->generateForm(array($fields_form));
    }

    /**
     * Render list of URL redirects
     * @return string HTML
     */
    protected function redirectsList()
    {
        // Pagination data
        $total = ElegantalSeoEssentialsRedirects::model()->countAll();
        $limit = 30;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'id_elegantalseoessentials_redirects',
            'old_url',
            'new_url',
            'redirect_type',
            'expires_at',
            'is_active',
        );
        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'id_elegantalseoessentials_redirects';
        $orderType = Tools::getValue('orderType') == 'asc' ? 'asc' : 'desc';

        $models = ElegantalSeoEssentialsRedirects::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'offset' => $offset,
            'limit' => $limit,
        ));

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'shop_url' => $this->getShopUrl(),
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/redirects.tpl');
    }

    /**
     * Renders and processes a form for URL redirect
     * @return string HTML
     */
    protected function redirectEdit()
    {
        $html = "";
        $shop_url = $this->getShopUrl();

        $model = null;
        $model_id = Tools::getValue('id_elegantalseoessentials_redirects');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsRedirects($model_id);
        }
        if (!$model || !Validate::isLoadedObject($model)) {
            $model = new ElegantalSeoEssentialsRedirects();
        }

        if ($this->isPostRequest()) {
            // Validate submitted data
            $errors = $model->validateAndAssignModelAttributes();
            if (Tools::getValue('old_url') && Validate::isAbsoluteUrl(Tools::getValue('old_url')) && Tools::substr(Tools::getValue('old_url'), 0, Tools::strlen($shop_url)) == $shop_url) {
                $model->old_url = Tools::substr(Tools::getValue('old_url'), Tools::strlen($shop_url));
            } elseif (Tools::getValue('old_url') && Validate::isAbsoluteUrl(Tools::getValue('old_url'))) {
                $errors[] = $this->l('Old URL should not start with domain.');
            } elseif (Tools::getValue('old_url') && Tools::substr(Tools::getValue('old_url'), 0, 1) != '/') {
                $errors[] = $this->l('Old URL should start with /');
            }
            if (Tools::getValue('new_url') && !Validate::isAbsoluteUrl(Tools::getValue('new_url'))) {
                $errors[] = $this->l('New URL must be absolute URL.') . ' ' . $this->l('For example:') . ' http://www.example.com';
            }
            if (!in_array(Tools::getValue('redirect_type'), $this->redirect_types)) {
                $errors[] = sprintf($this->l('Redirect type %s is not allowed. You may use only %s.'), Tools::getValue('redirect_type'), implode(', ', $this->redirect_types));
            }
            if (empty($errors)) {
                $result = empty($model_id) ? $model->add() : $model->update();
                if ($result) {
                    $this->setRedirectAlert($this->l('Redirect saved successfully.'), 'success');
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->redirectAdmin(array(
                            'event' => 'redirectEdit',
                            'id_elegantalseoessentials_redirects' => $model->id,
                        ));
                    } else {
                        $this->redirectAdmin(array(
                            'event' => 'redirectsList',
                        ));
                    }
                } else {
                    $html .= $this->displayError($this->l('Redirect could not be saved.'));
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $model->getAttributes();

        // Default values
        if (!$fields_value['id_elegantalseoessentials_redirects'] && !$this->isPostRequest()) {
            $fields_value['is_active'] = 1;
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $fields_value['id_elegantalseoessentials_redirects'] ? $this->l('Edit Redirect') : $this->l('New Redirect'),
                    'icon' => 'icon-random'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Old URL'),
                        'name' => 'old_url',
                        'required' => true,
                        'prefix' => $shop_url,
                        'placeholder' => '/example/old-url',
                        'desc' => $this->l('Enter existing URL within your shop which you want to redirect to another URL.') . ' ' . $this->l('NOTE:') . ' ' . $this->l('You should enter URL without domain of your shop and it should start with forward slash /'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('New URL'),
                        'name' => 'new_url',
                        'required' => true,
                        'placeholder' => 'http://www.example.com/new-url',
                        'desc' => $this->l('Enter New URL to which you want to redirect the old URL above.') . ' ' . $this->l('NOTE:') . ' ' . $this->l('You should enter absolute URL with domain.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Redirect Type'),
                        'name' => 'redirect_type',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('key' => '301', 'value' => $this->l('301 - URL permanently moved to a new location')),
                                array('key' => '302', 'value' => $this->l('302 - URL temporarily moved to a new location')),
                                array('key' => '303', 'value' => $this->l('303 - GET method used to retrieve information')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'desc' => $this->l('A 301 redirect means that the page has permanently moved to a new location. A 302 redirect means that the move is only temporary. 303 Redirect is a "see other" redirect status indicating that the resource has been replaced.'),
                    ),
                    array(
                        'type' => 'date',
                        'label' => $this->l('Expires at'),
                        'name' => 'expires_at',
                        'autocomplete' => false,
                        'desc' => $this->l('Specify date till which you want this redirect to be active. After the specified date, the redirection will not happen. Leave it empty in order to have this redirect all the time.'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'is_active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('You can enable or disable this URL. Only active redirects will work.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'redirectsList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'redirectEdit';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'redirectEdit', 'id_elegantalseoessentials_redirects' => $model_id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $html . $helper->generateForm(array($fields_form));
    }

    /**
     * Action to change redirect status
     */
    protected function redirectChangeStatus()
    {
        $model = null;
        $model_id = Tools::getValue('id_elegantalseoessentials_redirects');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsRedirects($model_id);
        }
        if ($model) {
            $model->is_active = $model->is_active == 1 ? 0 : 1;
            if ($model->update()) {
                $this->setRedirectAlert($this->l('Redirect status changed successfully.'), 'success');
            } else {
                $this->setRedirectAlert($this->l('Redirect status could not be changed.'), 'error');
            }
        } else {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
        }

        $this->redirectAdmin(array('event' => 'redirectsList'));
    }

    /**
     * Action to delete redirect
     */
    protected function redirectDelete()
    {
        $model = null;
        $model_id = Tools::getValue('id_elegantalseoessentials_redirects');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsRedirects($model_id);
        }
        if ($model) {
            if ($model->delete()) {
                $this->setRedirectAlert($this->l('Redirect deleted successfully.'), 'success');
            } else {
                $this->setRedirectAlert($this->l('Redirect could not be deleted.'), 'error');
            }
        } else {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
        }

        $this->redirectAdmin(array('event' => 'redirectsList'));
    }

    /**
     * Action to delete all redirects
     */
    protected function redirectDeleteAll()
    {
        $sql = "DELETE r, sh FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` r 
            INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` sh ON (r.`id_elegantalseoessentials_redirects` = sh.`id_elegantalseoessentials_redirects`) 
            WHERE sh.`id_shop` = " . (int) $this->context->shop->id;
        if (Db::getInstance()->execute($sql) == false) {
            $this->setRedirectAlert(Db::getInstance()->getMsgError(), 'error');
        } else {
            $this->setRedirectAlert($this->l('All redirects deleted successfully.'), 'success');
        }
        $this->redirectAdmin(array('event' => 'redirectsList'));
    }

    /**
     * Imports redirects from CSV
     * @return string HTML
     */
    protected function redirectsImport()
    {
        $html = "";

        if ($this->isPostRequest()) {
            $errors = array();
            // Validate upload
            if (!isset($_FILES['csv_file']["tmp_name"]) || empty($_FILES['csv_file']["tmp_name"]) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
                $errors[] = $this->l('File is not uploaded.');
            }
            // Validate file type
            $extension = Tools::strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
            $allowedFileTypes = array('csv');
            if (!in_array($extension, $allowedFileTypes)) {
                $errors[] = sprintf($this->l('The file type %s is not allowed. You may import only %s.'), $extension, implode(', ', $allowedFileTypes));
            }
            // If there is file size limit, check it and validate
            $max_file_size = $this->getSetting('max_csv_file_size');
            $file_size = $_FILES['csv_file']['size'];
            if ($max_file_size && $file_size > $max_file_size) {
                $errors[] = sprintf($this->l('Your file (%s) is exceeding the maximum file size (%s).'), ElegantalSeoEssentialsTools::displaySize($file_size), ElegantalSeoEssentialsTools::displaySize($max_file_size));
            }

            if (empty($errors)) {
                $file = $_FILES['csv_file']['tmp_name'];
                if (($handle = fopen($file, "r")) !== false) {
                    $count = 0;
                    $csv_delimiter = ElegantalSeoEssentialsTools::identifyCsvDelimiter($file);
                    $shop_url = $this->getShopUrl();

                    while (($data = fgetcsv($handle, 0, $csv_delimiter)) !== false) {
                        // Check columns
                        if (!isset($data[0]) || !$data[0] || !isset($data[1]) || !$data[1] || !isset($data[2]) || !$data[2]) {
                            continue;
                        }
                        // Check old URL
                        if (Validate::isAbsoluteUrl($data[0]) && Tools::substr($data[0], 0, Tools::strlen($shop_url)) == $shop_url) {
                            $data[0] = Tools::substr($data[0], Tools::strlen($shop_url));
                        }
                        if (Tools::substr($data[0], 0, 1) != '/') {
                            continue;
                        }
                        // Check new URL
                        if (!Validate::isAbsoluteUrl($data[1])) {
                            continue;
                        }
                        // Check redirect type
                        if (!in_array($data[2], $this->redirect_types)) {
                            continue;
                        }

                        $model = new ElegantalSeoEssentialsRedirects();
                        $model->old_url = $data[0];
                        $model->new_url = $data[1];
                        $model->redirect_type = $data[2];
                        $model->id_product = isset($data[3]) ? (int) $data[3] : 0;
                        $model->is_active = 1;
                        if ($model->add()) {
                            $count++;
                        }
                    }

                    fclose($handle);
                } else {
                    $errors[] = $this->l('Could not read CSV file.');
                }
            }

            if (empty($errors)) {
                $this->setRedirectAlert(sprintf($this->l('%d redirects were imported successfully.'), $count), 'success');
                $this->redirectAdmin(array('event' => 'redirectsList'));
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = array(
            'csv_file' => null,
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Import Redirects From CSV'),
                    'icon' => 'icon-upload'
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Select CSV File'),
                        'name' => 'csv_file',
                        'desc' => $this->l('Upload CSV file. Make sure you have Old URL, New URL and Redirect Type in the first 3 columns respectively'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Upload'),
                ),
                'buttons' => array(
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'redirectsList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitRedirectsImport';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'redirectsImport'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $html . $helper->generateForm(array($fields_form));
    }

    /**
     * Downloads CSV file with list of active redirects
     * @return int
     */
    protected function redirectsExport()
    {
        $filename = 'seo_redirects.csv';
        $file = ElegantalSeoEssentialsTools::getTempDir() . DIRECTORY_SEPARATOR . $filename;

        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` r 
            INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` sh ON (r.`id_elegantalseoessentials_redirects` = sh.`id_elegantalseoessentials_redirects`) 
            WHERE r.`is_active` = 1 AND sh.`id_shop` = " . (int) $this->context->shop->id . " 
            AND (r.`expires_at` < '1970-01-01 08:00:00' OR r.`expires_at` IS NULL OR r.`expires_at` > '" . pSQL(date('Y-m-d H:i:s')) . "') 
            ORDER BY r.`id_elegantalseoessentials_redirects` DESC";
        $redirects = Db::getInstance()->executeS($sql);

        if ($redirects && is_array($redirects)) {
            $handle = fopen($file, 'w');
            if ($handle) {
                //$csv_header = array("Old URL", "New URL", "Redirect Type", "Product ID");
                // fputcsv($handle, $csv_header, ';', '"');
                foreach ($redirects as $redirect) {
                    $redirect = array(
                        $redirect['old_url'],
                        $redirect['new_url'],
                        $redirect['redirect_type'],
                        $redirect['id_product'],
                    );
                    fputcsv($handle, $redirect, ';', '"');
                }
            } else {
                throw new Exception($this->l('Cannot open file for writing.') . ' ' . $file);
            }
            fclose($handle);

            $this->setRedirectAlert($this->l('Redirects have been exported to CSV successfully.') . ' <a href="' . $this->getModuleUrl() . 'tmp/' . $filename . '" target="_blank">' . $this->l('You can download it here.') . '</a>', 'success');
            $this->redirectAdmin(array(
                'event' => 'redirectsList'
            ));
        } else {
            $this->setRedirectAlert($this->l('Redirects were not found in your shop.'), 'error');
            $this->redirectAdmin(array(
                'event' => 'redirectsList'
            ));
        }
    }

    /**
     * Render list of manual canonical URLs
     * @return string HTML
     */
    protected function canonicalsList()
    {
        // Pagination data
        $total = ElegantalSeoEssentialsCanonicals::model()->countAll();
        $limit = 30;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'id_elegantalseoessentials_canonicals',
            'old_url',
            'new_url',
            'is_active',
        );
        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'id_elegantalseoessentials_canonicals';
        $orderType = Tools::getValue('orderType') == 'asc' ? 'asc' : 'desc';

        $models = ElegantalSeoEssentialsCanonicals::model()->findAll(array(
            'order' => $orderBy . ' ' . $orderType,
            'offset' => $offset,
            'limit' => $limit,
        ));

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'shop_url' => $this->getShopUrl(),
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/canonicals.tpl');
    }

    /**
     * Renders and processes a form for custom canonical
     * @return string HTML
     */
    protected function canonicalEdit()
    {
        $html = "";
        $shop_url = $this->getShopUrl();

        $model = null;
        $model_id = Tools::getValue('id_elegantalseoessentials_canonicals');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsCanonicals($model_id);
        }
        if (!$model || !Validate::isLoadedObject($model)) {
            $model = new ElegantalSeoEssentialsCanonicals();
        }

        if ($this->isPostRequest()) {
            // Validate submitted data
            $errors = $model->validateAndAssignModelAttributes();

            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $lang_prefix = Tools::strtoupper($lang['iso_code']) . ': ';
                $old_url = Tools::getValue('old_url_' . $lang['id_lang']);
                $new_url = Tools::getValue('new_url_' . $lang['id_lang']);
                if ($old_url && Validate::isAbsoluteUrl($old_url) && Tools::substr($old_url, 0, Tools::strlen($shop_url)) == $shop_url) {
                    $model->old_url = Tools::substr($old_url, Tools::strlen($shop_url));
                } elseif ($old_url && Validate::isAbsoluteUrl($old_url)) {
                    $errors[] = $lang_prefix . $this->l('Old URL should not start with domain.');
                } elseif ($old_url && Tools::substr($old_url, 0, 1) != '/') {
                    $errors[] = $lang_prefix . $this->l('Old URL should start with /');
                }
                if ($new_url && Validate::isAbsoluteUrl($new_url) && Tools::substr($new_url, 0, Tools::strlen($shop_url)) == $shop_url) {
                    $model->new_url = Tools::substr($new_url, Tools::strlen($shop_url));
                } elseif ($new_url && Validate::isAbsoluteUrl($new_url)) {
                    $errors[] = $lang_prefix . $this->l('New URL should not start with domain.');
                } elseif ($new_url && Tools::substr($new_url, 0, 1) != '/') {
                    $errors[] = $lang_prefix . $this->l('New URL should start with /');
                }
            }

            if (empty($errors)) {
                $result = empty($model_id) ? $model->add() : $model->update();
                if ($result) {
                    $this->setRedirectAlert($this->l('Canonical URL saved successfully.'), 'success');
                    if (Tools::isSubmit('submitAndStay') && !Tools::isSubmit('submitAndNext')) {
                        $this->redirectAdmin(array(
                            'event' => 'canonicalEdit',
                            'id_elegantalseoessentials_canonicals' => $model->id,
                        ));
                    } else {
                        $this->redirectAdmin(array(
                            'event' => 'canonicalsList',
                        ));
                    }
                } else {
                    $html .= $this->displayError($this->l('Canonical URL could not be saved.'));
                }
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $model->getAttributes();

        // Default values
        if (!$fields_value['id_elegantalseoessentials_canonicals'] && !$this->isPostRequest()) {
            $fields_value['is_active'] = 1;
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $fields_value['id_elegantalseoessentials_canonicals'] ? $this->l('Edit Canonical URL') : $this->l('New Canonical URL'),
                    'icon' => 'icon-link'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Old URL'),
                        'name' => 'old_url',
                        'required' => true,
                        'lang' => true,
                        'prefix' => $shop_url,
                        'placeholder' => '/example/old-url',
                        'desc' => $this->l('Enter existing URL within your shop for which you want to create custom canonical URL.') . ' ' . $this->l('NOTE:') . ' ' . $this->l('You should enter URL without domain of your shop and it should start with forward slash /'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('New URL'),
                        'name' => 'new_url',
                        'required' => true,
                        'lang' => true,
                        'prefix' => $shop_url,
                        'placeholder' => '/example/new-url',
                        'desc' => $this->l('Enter New URL that you want to use as canonical URL instead of Old URL above.') . ' ' . $this->l('NOTE:') . ' ' . $this->l('You should enter URL without domain of your shop and it should start with forward slash /'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'is_active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('You can enable or disable this URL. Only active canonicals will work.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAndNext',
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Save & Stay'),
                        'name' => 'submitAndStay',
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'icon' => 'process-icon-save'
                    ),
                    array(
                        'href' => $this->getAdminUrl(array('event' => 'canonicalsList')),
                        'title' => $this->l('Back'),
                        'class' => 'pull-left',
                        'icon' => 'process-icon-back'
                    ),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'canonicalEdit';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'canonicalEdit', 'id_elegantalseoessentials_canonicals' => $model_id));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $html . $helper->generateForm(array($fields_form));
    }

    /**
     * Action to change canonical status
     */
    protected function canonicalChangeStatus()
    {
        $model = null;
        $model_id = Tools::getValue('id_elegantalseoessentials_canonicals');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsCanonicals($model_id);
        }
        if ($model) {
            $model->is_active = $model->is_active == 1 ? 0 : 1;
            if ($model->update()) {
                $this->setRedirectAlert($this->l('Canonical URL status changed successfully.'), 'success');
            } else {
                $this->setRedirectAlert($this->l('Canonical URL status could not be changed.'), 'error');
            }
        } else {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
        }

        $this->redirectAdmin(array('event' => 'canonicalsList'));
    }

    /**
     * Action to delete canonical
     */
    protected function canonicalDelete()
    {
        $model = null;
        $model_id = Tools::getValue('id_elegantalseoessentials_canonicals');
        if ($model_id) {
            $model = new ElegantalSeoEssentialsCanonicals($model_id);
        }
        if ($model) {
            if ($model->delete()) {
                $this->setRedirectAlert($this->l('Canonical URL deleted successfully.'), 'success');
            } else {
                $this->setRedirectAlert($this->l('Canonical URL could not be deleted.'), 'error');
            }
        } else {
            $this->setRedirectAlert($this->l('Record not found.'), 'error');
        }

        $this->redirectAdmin(array('event' => 'canonicalsList'));
    }

    /**
     * Action to delete all canonicals
     */
    protected function canonicalDeleteAll()
    {
        $sql = "DELETE c, sh FROM `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals` c 
            INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_shop` sh ON (c.`id_elegantalseoessentials_canonicals` = sh.`id_elegantalseoessentials_canonicals`) 
            WHERE sh.`id_shop` = " . (int) $this->context->shop->id;
        if (Db::getInstance()->execute($sql) == false) {
            $this->setRedirectAlert(Db::getInstance()->getMsgError(), 'error');
        } else {
            $this->setRedirectAlert($this->l('All canonical URLs deleted successfully.'), 'success');
        }
        $this->redirectAdmin(array('event' => 'canonicalsList'));
    }

    /**
     * Returns current Canonical URL
     * @return string
     */
    protected function getCanonicalUrl($add_pagination_param = true)
    {
        $context = $this->context;
        $id_shop = $context->shop->id;
        $id_lang = $context->language->id;

        $controller = Dispatcher::getInstance()->getController();
        if (!empty($context->controller->php_self)) {
            $controller = $context->controller->php_self;
        }

        $canonical_url = "";

        // Check if there is custom Canonical URL set for this URL
        $current_url = $_SERVER['REQUEST_URI'];
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals` c 
            INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_shop` sh ON (sh.`id_elegantalseoessentials_canonicals` = c.`id_elegantalseoessentials_canonicals`) 
            INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_canonicals_lang` l ON (l.`id_elegantalseoessentials_canonicals` = c.`id_elegantalseoessentials_canonicals`) 
            WHERE c.`is_active` = 1 AND sh.`id_shop` = " . (int) $id_shop . " AND l.`id_lang` = " . (int) $id_lang . " AND l.`old_url` = '" . pSQL($current_url) . "' 
            ORDER BY c.`id_elegantalseoessentials_canonicals` DESC";
        $canonical = Db::getInstance()->getRow($sql);
        if ($canonical) {
            $canonical_url = $this->getShopUrl() . $canonical['new_url'];
        } elseif ($controller == "product" && Tools::getValue('id_product')) {
            $id_product = (int) Tools::getValue('id_product');
            $id_product_attribute = null;
            if (Combination::isFeatureActive() && Combination::isCurrentlyUsed()) {
                $product = new Product($id_product);
                if (property_exists($product, 'cache_default_attribute') && $product->cache_default_attribute > 0) {
                    $id_product_attribute = (int) $product->cache_default_attribute;
                }
            }
            $canonical_url = $context->link->getProductLink($id_product, null, null, null, $id_lang, $id_shop, $id_product_attribute);
        } elseif ($controller == "category" && Tools::getValue('id_category')) {
            $canonical_url = $context->link->getCategoryLink(Tools::getValue('id_category'), null, $id_lang);
        } elseif ($controller == "manufacturer" && Tools::getValue('id_manufacturer')) {
            $canonical_url = $context->link->getManufacturerLink(Tools::getValue('id_manufacturer'), null, $id_lang);
        } elseif ($controller == "supplier" && Tools::getValue('id_supplier')) {
            $canonical_url = $context->link->getSupplierLink(Tools::getValue('id_supplier'), null, $id_lang);
        } elseif ($controller == "cms" && Tools::getValue('id_cms')) {
            $canonical_url = $context->link->getCMSLink(Tools::getValue('id_cms'), null, null, $id_lang);
        } elseif ($controller == "cms" && Tools::getValue('id_cms_category')) {
            $canonical_url = $context->link->getCMSCategoryLink(Tools::getValue('id_cms_category'), null, $id_lang);
        } elseif (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'advancedtags' && $controller == 'browse' && method_exists('AdvancedTag', 'getModuleLink')) {
            $canonical_url = AdvancedTag::getModuleLink('advancedtags', 'browse', array('tag' => Tools::strtolower(Tools::getValue('tag'))), true, $id_lang);
        } elseif (Tools::getValue('fc') == 'module') {
            $module = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';
            if (!empty($module)) {
                $params = $_GET;
                unset($params['fc'], $params['module'], $params['id_lang'], $params['isolang']);
                $canonical_url = $context->link->getModuleLink($module, $controller, $params, null, $id_lang);
            }
        } elseif ($controller == "search" && Tools::getValue('search_query')) {
            $search_params = array('search_query' => Tools::getValue('search_query'));
            if (Tools::getValue('orderby')) {
                $search_params['orderby'] = Tools::getValue('orderby');
            }
            if (Tools::getValue('orderway')) {
                $search_params['orderway'] = Tools::getValue('orderway');
            }
            $canonical_url = $context->link->getPageLink($controller, null, $id_lang, $search_params);
        } else {
            $canonical_url = $context->link->getPageLink($controller, null, $id_lang);
        }

        // Add pagination page param p
        if ($add_pagination_param) {
            $current_url_params = array();
            parse_str(parse_url($canonical_url, PHP_URL_QUERY), $current_url_params);
            if (Tools::getValue('p') && !isset($current_url_params['p'])) {
                $canonical_url = ElegantalSeoEssentialsTools::addGetParamsToUrl($canonical_url, array('p' => Tools::getValue('p')));
            } elseif (Tools::getValue('page') && !isset($current_url_params['page'])) {
                $canonical_url = ElegantalSeoEssentialsTools::addGetParamsToUrl($canonical_url, array('page' => Tools::getValue('page')));
            }
        }

        return $canonical_url;
    }

    /**
     * Returns list of hreflangs for the current URL
     * @param bool $use_multishop_domain
     * @return array
     */
    protected function getHreflangs($use_multishop_domain = false)
    {
        $hreflangs = array();

        $controller = Dispatcher::getInstance()->getController();
        if (!empty($this->context->controller->php_self)) {
            $controller = $this->context->controller->php_self;
        }

        $default_lang_code = "";
        $default_lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(true, $this->context->shop->id);
        foreach ($languages as $language) {
            if ($language['id_lang'] == $default_lang_id) {
                $default_lang_code = $language['language_code'];
            }
            if (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'pm_advancedsearch4' && class_exists('AdvancedSearchSeoClass')) {
                $ObjAdvancedSearchSeoClass = new AdvancedSearchSeoClass((int) Tools::getValue('id_seo'), null);
                $hreflangs[$language['language_code']] = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int) $ObjAdvancedSearchSeoClass->id, 'seo_url' => $ObjAdvancedSearchSeoClass->seo_url[$language['id_lang']]), null, $language['id_lang']);
            } elseif (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'advancedtags' && $controller == 'browse' && method_exists('AdvancedTag', 'getModuleLink')) {
                $hreflangs[$language['language_code']] = AdvancedTag::getModuleLink('advancedtags', 'browse', array('tag' => Tools::strtolower(Tools::getValue('tag'))), true, $language['id_lang']);
            } else {
                $hreflangs[$language['language_code']] = $this->context->link->getLanguageLink($language['id_lang'], $this->context);
            }
        }
        if (!$default_lang_code) {
            $default_lang_code = $languages[0]['language_code'];
        }

        if ($use_multishop_domain && Shop::isFeatureActive()) {
            $current_shop = $this->context->shop;
            $shop_groups = Shop::getTree();
            foreach ($shop_groups as $shop_group) {
                foreach ($shop_group['shops'] as $shop) {
                    if ($shop['id_shop'] == $this->context->shop->id) {
                        continue;
                    }

                    $tmpContext = Context::getContext();
                    $tmpContext->shop = new Shop($shop['id_shop']);

                    $shop_languages = Language::getLanguages(true, $shop['id_shop']);
                    foreach ($shop_languages as $shlang) {
                        if (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'pm_advancedsearch4' && class_exists('AdvancedSearchSeoClass')) {
                            $ObjAdvancedSearchSeoClass = new AdvancedSearchSeoClass((int) Tools::getValue('id_seo'), null);
                            $hreflangs[$shlang['language_code']] = $tmpContext->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int) $ObjAdvancedSearchSeoClass->id, 'seo_url' => $ObjAdvancedSearchSeoClass->seo_url[$shlang['id_lang']]), null, $shlang['id_lang']);
                        } elseif (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'advancedtags' && $controller == 'browse' && method_exists('AdvancedTag', 'getModuleLink')) {
                            $hreflangs[$shlang['language_code']] = AdvancedTag::getModuleLink('advancedtags', 'browse', array('tag' => Tools::strtolower(Tools::getValue('tag'))), true, $shlang['id_lang']);
                        } else {
                            $hreflangs[$shlang['language_code']] = $tmpContext->link->getLanguageLink($shlang['id_lang'], $tmpContext);
                        }
                    }
                }
            }
            $this->context->shop = $current_shop;
        }
        $hreflangs['x-default'] = $hreflangs[$default_lang_code];

        // Add pagination page param p
        foreach ($hreflangs as &$url) {
            $url_params = array();
            parse_str(parse_url($url, PHP_URL_QUERY), $url_params);
            if (Tools::getValue('p') && !isset($url_params['p'])) {
                $url = ElegantalSeoEssentialsTools::addGetParamsToUrl($url, array('p' => Tools::getValue('p')));
            } elseif (Tools::getValue('page') && !isset($url_params['page'])) {
                $url = ElegantalSeoEssentialsTools::addGetParamsToUrl($url, array('page' => Tools::getValue('page')));
            }
        }

        return $hreflangs;
    }

    /**
     * Returns URLs for next/prev tags
     * @return array
     */
    protected function getNextPrevTags()
    {
        $next_prev_tags = array('next' => '', 'prev' => '');
        $controller = Dispatcher::getInstance()->getController();
        if (!empty($this->context->controller->php_self)) {
            $controller = $this->context->controller->php_self;
        }
        $settings = $this->getSettings();
        $id_lang = $this->context->language->id;
        $url = $this->getCanonicalUrl(false);

        $total_items = 0;

        if ($controller == "category" && Tools::getValue('id_category')) {
            $category = new Category(Tools::getValue('id_category'));
            $total_items = $category->getProducts(null, null, null, null, null, true);

            if (Module::isInstalled('pm_advancedsearch4') && class_exists('PM_AdvancedSearch4')) {
                $pm_advancedsearch4 = new PM_AdvancedSearch4();
                $total_items_advsearch = (int) $pm_advancedsearch4->getCategoryProducts(null, null, null, null, null, true);
                if ($total_items_advsearch > 0) {
                    $total_items = $total_items_advsearch;
                }
            }
        } elseif ($controller == "manufacturer" && Tools::getValue('id_manufacturer')) {
            $total_items = Manufacturer::getProducts(Tools::getValue('id_manufacturer'), null, null, null, null, null, true);
        } elseif ($controller == "manufacturer" && !Tools::getValue('id_manufacturer')) {
            $manufacturers = Manufacturer::getManufacturers();
            $total_items = count($manufacturers);
        } elseif ($controller == "supplier" && Tools::getValue('id_supplier')) {
            $total_items = Supplier::getProducts(Tools::getValue('id_supplier'), null, null, null, null, null, true);
        } elseif ($controller == "supplier" && !Tools::getValue('id_supplier')) {
            $suppliers = Supplier::getSuppliers();
            $total_items = count($suppliers);
        } elseif ($controller == "best-sales") {
            $total_items = (int) ProductSale::getNbSales();
        } elseif ($controller == "new-products") {
            $total_items = (int) Product::getNewProducts($id_lang, 0, 1000000, true);
        } elseif ($controller == "prices-drop") {
            $total_items = (int) Product::getPricesDrop($id_lang, 0, 1000000, true);
        } elseif ($controller == "search" && Tools::getValue('search_query')) {
            $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('search_query')));
            $search = Search::find($id_lang, $query);
            if (isset($search['total'])) {
                $total_items = $search['total'];
            }
        } elseif (Tools::getValue('fc') == 'module' &&
            Tools::getValue('module') == 'pm_advancedsearch4' &&
            Validate::isModuleName(Tools::getValue('module')) &&
            class_exists('AdvancedSearchSeoClass') &&
            class_exists('PM_AdvancedSearch4') &&
            class_exists('As4SearchEngine')) {
            // $resultSeoUrl = AdvancedSearchSeoClass::getSeoSearchByIdSeo((int) Tools::getValue('id_seo'), $id_lang);
            $resultSeoUrl = call_user_func(array('AdvancedSearchSeoClass', 'getSeoSearchByIdSeo'), (int) Tools::getValue('id_seo'), $id_lang);
            if ($resultSeoUrl && isset($resultSeoUrl[0]['id_search'])) {
                $idSearch = (int) $resultSeoUrl[0]['id_search'];
                $criterions = array();
                $criteria = unserialize($resultSeoUrl[0]['criteria']);
                if (is_array($criteria) && sizeof($criteria)) {
                    // $criterions = PM_AdvancedSearch4::getArrayCriteriaFromSeoArrayCriteria($criteria);
                    // $criterions = As4SearchEngine::cleanArrayCriterion($criterions);
                    $criterions = call_user_func(array('PM_AdvancedSearch4', 'getArrayCriteriaFromSeoArrayCriteria'), $criteria);
                    $criterions = call_user_func(array('As4SearchEngine', 'cleanArrayCriterion'), $criterions);
                }
                $searchQuery = implode('/', array_slice(explode('/', Tools::getValue('seo_url')), 1));
                // $criterionsList = As4SearchEngine::getCriterionsFromURL($idSearch, $searchQuery);
                $criterionsList = call_user_func(array('As4SearchEngine', 'getCriterionsFromURL'), $idSearch, $searchQuery);
                $criterions += $criterionsList;

                $url = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int) Tools::getValue('id_seo'), 'seo_url' => Tools::getValue('seo_url')), null, $id_lang);
                // $criteria_groups_type = As4SearchEngine::getCriterionGroupsTypeAndDisplay($idSearch, array_keys($criterions));
                // $total_items = As4SearchEngine::getProductsSearched($idSearch, $criterions, $criteria_groups_type, null, null, true);
                $criteria_groups_type = call_user_func(array('As4SearchEngine', 'getCriterionGroupsTypeAndDisplay'), $idSearch, array_keys($criterions));
                $total_items = call_user_func(array('As4SearchEngine', 'getProductsSearched'), $idSearch, $criterions, $criteria_groups_type, null, null, true);
            }
        } elseif (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'advancedtags' && $controller == 'browse' && method_exists('AdvancedTag', 'getModuleLink')) {
            $id_tag = AdvancedTag::getTagIdByName(Tools::getValue('tag'), $id_lang);
            $total_items = (int) AdvancedTag::searchTag($id_lang, (int) $id_tag, true, 0, 10, false, false);
            $url = AdvancedTag::getModuleLink('advancedtags', 'browse', array('tag' => Tools::strtolower(Tools::getValue('tag'))), true, $id_lang);
        }

        // Retrieve the current number of products per page (either the default, the GET parameter or the one in the cookie)
        $default_products_per_page = max(1, (int) Configuration::get('PS_PRODUCTS_PER_PAGE'));
        $n = $default_products_per_page;
        if (isset($this->context->cookie->nb_item_per_page)) {
            $n = (int) $this->context->cookie->nb_item_per_page;
        }
        if ((int) Tools::getValue('n')) {
            $n = (int) Tools::getValue('n');
        }
        if ($n != $default_products_per_page || isset($this->context->cookie->nb_item_per_page)) {
            $this->context->cookie->nb_item_per_page = $n;
        }

        // Retrieve the page number (either the GET parameter or the first page)
        $p = 1;
        if (Tools::isSubmit('p')) {
            $p = (int) Tools::getValue('p', 1);
        } elseif (Tools::isSubmit('page')) {
            $p = (int) Tools::getValue('page', 1);
        }
        if (!is_numeric($p) || $p < 1) {
            $p = 1;
        }

        $pages_nb = ceil($total_items / (int) $n);
        if ($p > $pages_nb && $total_items != 0) {
            $p = 1;
        }

        if ($p < $pages_nb) {
            $next_prev_tags['next'] = $this->context->link->goPage($url, $p + 1);
            if ($settings['page_param'] != 'p') {
                $next_prev_tags['next'] = str_replace('?p=', '?' . $settings['page_param'] . '=', $next_prev_tags['next']);
                $next_prev_tags['next'] = str_replace('&p=', '&' . $settings['page_param'] . '=', $next_prev_tags['next']);
            }
        }
        if ($p > 1) {
            $next_prev_tags['prev'] = $this->context->link->goPage($url, $p - 1);
            if ($settings['page_param'] != 'p') {
                $next_prev_tags['prev'] = str_replace('?p=', '?' . $settings['page_param'] . '=', $next_prev_tags['prev']);
                $next_prev_tags['prev'] = str_replace('&p=', '&' . $settings['page_param'] . '=', $next_prev_tags['prev']);
            }
        }

        return $next_prev_tags;
    }

    protected function pageNotFoundList()
    {
        $sql = "SELECT COUNT(DISTINCT pg.`request_uri`) 
            FROM `" . _DB_PREFIX_ . "pagenotfound` as pg 
            WHERE pg.`id_shop` = " . (int) $this->context->shop->id;
        $count_pages_404 = (int) Db::getInstance()->getValue($sql);

        // Pagination data
        $total = $count_pages_404;
        $limit = 30;
        $pages = ceil($total / $limit);
        $currentPage = (int) Tools::getValue('page', 1);
        $currentPage = ($currentPage > $pages) ? $pages : $currentPage;
        $halfVisibleLinks = 5;
        $offset = ($total > $limit) ? ($currentPage - 1) * $limit : 0;

        // Sorting records
        $sortableColumns = array(
            'request_uri',
            'http_referer',
            'date_add',
        );
        $orderBy = in_array(Tools::getValue('orderBy'), $sortableColumns) ? Tools::getValue('orderBy') : 'date_add';
        $orderType = Tools::getValue('orderType') == 'asc' ? 'asc' : 'desc';

        $sql = "SELECT pg.`request_uri`, pg.`http_referer`,  pg.`date_add` 
            FROM `" . _DB_PREFIX_ . "pagenotfound` as pg 
            WHERE pg.`id_shop` = " . (int) $this->context->shop->id . " 
            GROUP BY pg.`request_uri` 
            ORDER BY " . pSQL($orderBy) . " " . pSQL($orderType) . " 
            LIMIT " . (int) $offset . "," . (int) $limit;

        $models = Db::getInstance()->ExecuteS($sql);

        $this->context->smarty->assign(
            array(
                'models' => $models,
                'shop_url' => $this->getShopUrl(),
                'adminUrl' => $this->getAdminUrl(),
                'pages' => $pages,
                'currentPage' => $currentPage,
                'halfVisibleLinks' => $halfVisibleLinks,
                'orderBy' => $orderBy,
                'orderType' => $orderType,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/pagenotfound.tpl');
    }

    protected function pageNotFoundDownload()
    {
        $filename = 'pages_not_found.csv';
        $file = ElegantalSeoEssentialsTools::getTempDir() . DIRECTORY_SEPARATOR . $filename;

        $sql = "SELECT pg.`request_uri` 
            FROM `" . _DB_PREFIX_ . "pagenotfound` as pg 
            WHERE pg.`id_shop` = " . (int) $this->context->shop->id . " 
            GROUP BY pg.`request_uri` 
            ORDER BY pg.`date_add` DESC";
        $pages_404 = Db::getInstance()->ExecuteS($sql);

        if ($pages_404 && is_array($pages_404)) {
            $handle = fopen($file, 'w');
            if ($handle) {
                foreach ($pages_404 as $page) {
                    $row = array();
                    $row[] = $page['request_uri'];
                    $row[] = $this->getShopUrl();
                    $row[] = '301';
                    fputcsv($handle, $row, ';', '"');
                }
            } else {
                throw new Exception($this->l('Cannot open file for writing.') . ' ' . $file);
            }
            fclose($handle);

            $this->setRedirectAlert($this->l('404 error pages have been exported to CSV successfully.') . ' <a href="' . $this->getModuleUrl() . 'tmp/' . $filename . '" target="_blank">' . $this->l('You can download it here.') . '</a>', 'success');
            $this->redirectAdmin(array(
                'event' => 'pageNotFoundList'
            ));
        } else {
            $this->setRedirectAlert($this->l('404 error pages were not found in your shop.'), 'error');
            $this->redirectAdmin(array(
                'event' => 'pageNotFoundList'
            ));
        }
    }

    /**
     * Print tags on <head> section of the web page
     * @return string HTML
     */
    public function hookDisplayHeader()
    {
        $id_lang = $this->context->language->id;
        $controller = Dispatcher::getInstance()->getController();
        if (!empty($this->context->controller->php_self)) {
            $controller = $this->context->controller->php_self;
        }
        $settings = $this->getSettings();
        $canonical_url = $settings['is_enable_canonical'] ? $this->getCanonicalUrl() : "";
        $hreflangs = $settings['is_enable_hreflang'] ? $this->getHreflangs($settings['is_hreflang_use_multishop_domain']) : "";
        $next_prev_tags = $settings['is_enable_nextprev'] ? $this->getNextPrevTags() : array('next' => '', 'prev' => '');

        $search_params = array('controller' => 'search');
        if (_PS_VERSION_ < '1.7') {
            $search_params['orderby'] = 'position';
            $search_params['orderway'] = 'desc';
            $search_params['search_query'] = '{search_term_string}';
        } else {
            $search_params['s'] = '{search_term_string}';
        }
        $sitelinks_searchbox_target = $this->context->link->getPageLink('search', null, $id_lang);
        $sitelinks_searchbox_target = ElegantalSeoEssentialsTools::addGetParamsToUrl($sitelinks_searchbox_target, $search_params);
        $sitelinks_searchbox_target = urldecode($sitelinks_searchbox_target);

        $this->context->smarty->assign(array(
            'controller' => $controller,
            'canonical_url' => $canonical_url,
            'hreflangs' => $hreflangs,
            'next_prev_tags' => $next_prev_tags,
            'settings' => $settings,
            'shop_url' => $this->context->link->getPageLink('index', null, $id_lang),
            'sitelinks_searchbox_target' => $sitelinks_searchbox_target,
        ));

        return $this->display(__FILE__, 'front.tpl') . $this->htmlBlockFront('displayHeader');
    }

    /**
     * Hook action called when product is added
     * @param array $params
     * @throws Exception
     */
    public function hookActionProductAdd($params)
    {
        $this->hookActionProductUpdate($params);
    }

    /**
     * Hook action called when product is updated
     * @param array $params
     * @throws Exception
     */
    public function hookActionProductUpdate($params)
    {
        return true;
        try {
            $id_product = null;
            if (isset($params['id_product']) && $params['id_product']) {
                $id_product = $params['id_product'];
            } elseif (isset($params['product']) && $params['product']->id) {
                $id_product = $params['product']->id;
            } else {
                return;
            }

            $product = new Product($id_product);
            $product_categories = $product->getCategories();

            // Update product SEO meta tags
            $auto_meta_rules = ElegantalSeoEssentialsAutoMeta::model()->findAll(array(
                'condition' => array(
                    'is_active' => 1
                ),
                'order' => 'id_elegantalseoessentials_auto_meta', // Last rule should be applied last
            ));
            foreach ($auto_meta_rules as $auto_meta_rule) {
                $autoMetaRuleObj = new ElegantalSeoEssentialsAutoMeta($auto_meta_rule['id_elegantalseoessentials_auto_meta']);
                $auto_meta_rule_categories = ElegantalSeoEssentialsTools::unserialize($autoMetaRuleObj->category_ids);
                $auto_meta_common_categories = array_intersect($product_categories, $auto_meta_rule_categories);
                if (empty($auto_meta_rule_categories) || ($auto_meta_common_categories && is_array($auto_meta_common_categories) && count($auto_meta_common_categories) > 0)) {
                    $autoMetaRuleObj->applyRuleOnProduct($id_product);
                }
            }

            // Update product image alt tags
            $image_alt_rules = ElegantalSeoEssentialsImageAlt::model()->findAll(array(
                'condition' => array(
                    'is_active' => 1
                ),
                'order' => 'id_elegantalseoessentials_image_alt', // Last rule should be applied last
            ));
            foreach ($image_alt_rules as $image_alt_rule) {
                $imageAltRuleObj = new ElegantalSeoEssentialsImageAlt($image_alt_rule['id_elegantalseoessentials_image_alt']);
                $image_alt_rule_categories = ElegantalSeoEssentialsTools::unserialize($imageAltRuleObj->category_ids);
                $image_alt_common_categories = array_intersect($product_categories, $image_alt_rule_categories);
                if (empty($image_alt_rule_categories) || ($image_alt_common_categories && is_array($image_alt_common_categories) && count($image_alt_common_categories) > 0)) {
                    $imageAltRuleObj->applyRuleOnProduct($id_product);
                }
            }

            // Update product redirect
            $redirect = new ElegantalSeoEssentialsRedirects();
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` r 
            INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` sh ON (r.`id_elegantalseoessentials_redirects` = sh.`id_elegantalseoessentials_redirects`) 
            WHERE sh.`id_shop` = " . (int) $this->context->shop->id . " AND r.`id_product` = " . (int) $id_product . " 
            ORDER BY r.`id_elegantalseoessentials_redirects` DESC";
            $row = Db::getInstance()->getRow($sql);
            if ($row && $row['id_elegantalseoessentials_redirects']) {
                $redirect = new ElegantalSeoEssentialsRedirects($row['id_elegantalseoessentials_redirects']);
            }

            if (Tools::isSubmit('elegantal_new_url') && !Validate::isAbsoluteUrl(Tools::getValue('elegantal_new_url'))) {
                $this->context->controller->errors[] = $this->l('New URL must be absolute URL.') . ' ' . $this->l('For example:') . ' http://www.example.com';
            } elseif ((!Tools::isSubmit('elegantal_new_url') || !Tools::getValue('elegantal_new_url')) && Validate::isLoadedObject($redirect)) {
                $redirect->delete();
            } elseif (Tools::getValue('elegantal_new_url') && Validate::isAbsoluteUrl(Tools::getValue('elegantal_new_url'))) {
                $product_link = $this->context->link->getProductLink($product);
                $product_link_params = parse_url($product_link);
                $redirect->old_url = $product_link_params['path'];
                $redirect->new_url = Tools::getValue('elegantal_new_url');
                $redirect->redirect_type = Tools::getValue('elegantal_redirect_type');
                $redirect->id_product = $id_product;
                $redirect->expires_at = Tools::getValue('elegantal_expires_at');
                $redirect->is_active = Tools::getValue('elegantal_is_active');
                if (Validate::isLoadedObject($redirect)) {
                    $redirect->update();
                } else {
                    $redirect->add();
                }
            }
        } catch (Exception $e) {
            // echo '<pre>'.print_r($e->getMessage(), true).'</pre>';exit;
        }
    }

    public function hookActionProductDelete($params)
    {
        $id_product = null;
        if (isset($params['id_product']) && $params['id_product']) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product']) && $params['product']->id) {
            $id_product = $params['product']->id;
        }
        if ($id_product) {
            $sql = "DELETE FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` WHERE `id_product` = " . (int) $id_product;
            Db::getInstance()->execute($sql);
        }
    }

    /**
     * Hook that will add extra tab on product edit page
     * @return string
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $redirect = null;

        $id_product = (int) Tools::getValue('id_product');
        if (!$id_product && isset($params['id_product']) && $params['id_product']) {
            $id_product = (int) $params['id_product'];
        }
        if ($id_product) {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` r 
                INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` sh ON (r.`id_elegantalseoessentials_redirects` = sh.`id_elegantalseoessentials_redirects`) 
                WHERE sh.`id_shop` = " . (int) $this->context->shop->id . " AND r.`id_product` = " . (int) $id_product . " 
                ORDER BY r.`id_elegantalseoessentials_redirects` DESC";
            $redirect = Db::getInstance()->getRow($sql);
        }

        if (!$redirect) {
            $redirect = array(
                'new_url' => '',
                'redirect_type' => 301,
                'expires_at' => '',
                'is_active' => 1,
            );
        }

        if ($redirect['expires_at'] == '0000-00-00 00:00:00') {
            $redirect['expires_at'] = null;
        }

        $this->context->smarty->assign(array(
            'redirect' => $redirect,
            'ps_version' => _PS_VERSION_,
        ));

        return $this->display(__FILE__, 'product_extra.tpl');
    }
}
