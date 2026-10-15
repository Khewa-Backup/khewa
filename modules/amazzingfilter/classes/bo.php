<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Bo extends AmazzingFilter
{
    public function extendGetContent()
    {
        $this->defineSettings();
        if ($view_log = Tools::getValue('viewLog')) {
            exit($this->log('view', '', $view_log));
        } elseif ($clear_log = Tools::getValue('clearLog')) {
            $this->log('clear', '', $clear_log);
            $this->context->controller->confirmations[] = 'Log cleared: ' . $clear_log;
        }
        $this->addConfigMedia();
        $this->setWarningsIfRequired();
    }

    public function addConfigMedia()
    {
        $this->addJquery();
        $this->context->controller->addJqueryUI('ui.sortable');
        foreach ($this->getConfigFiles('css') as $file) {
            $this->context->controller->css_files[$file . '?v=' . $this->version] = 'all';
        }
        foreach ($this->getConfigFiles('js') as $file) {
            $this->context->controller->js_files[] = $file . '?v=' . $this->version;
        }
        // mce
        $this->context->controller->addJS(__PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'js/admin/tinymce.inc.js');
        if (!empty($this->sp)) {
            $this->sp->addConfigData();
        }
    }

    public function getConfigFiles($type)
    {
        $file_name = 'back.' . $type;
        $dir = 'views/' . $type . '/';
        $files = [$this->_path . $dir . $file_name];
        foreach (glob($this->local_path . $dir . '*-' . $file_name) as $file) { // ***-back.css, ***-back.js
            $files[] = $this->_path . $dir . basename($file);
        }

        return $files;
    }

    public function addJquery()
    {
        if (empty($this->context->jqueryAdded)) {
            version_compare(_PS_VERSION_, '1.7.6.0', '>=') ? $this->context->controller->setMedia() :
            $this->context->controller->addJquery();
            $this->context->jqueryAdded = 1;
        }
    }

    public function setWarningsIfRequired()
    {
        if ($this->active) {
            foreach (['blocklayered', 'ps_facetedsearch'] as $module_name) {
                if (Module::isEnabled($module_name)) {
                    $txt = $this->l('Please, uninstall module %s in order to avoid possible interference', 'bo');
                    $this->context->controller->warnings[] = sprintf($txt, $module_name);
                }
            }
            if (Module::isEnabled('iqitthemeeditor')
                && Configuration::get('iqitthemeed_pl_infinity', null, null, $this->id_shop)) {
                // iqitthemeeditor settings can be configured/saved only in single shop context
                $iqit = 'IqitThemeEditor settings';
                if ($this->is_modern) {
                    $link = $this->context->link->getAdminLink('AdminIqitThemeEditor');
                    $iqit = '<a href="' . $link . '" target="_blank">' . $iqit . '</a>';
                }
                $txt = 'Set "Infinity scroll: NO" in ' . $iqit;
                if ($this->settings['general']['p_type'] < 2) {
                    $txt .= ', and then select "Pagination Type: Infinite scroll" in General settings below ↓';
                }
                $this->context->controller->warnings[] = $txt;
            }
            if ($this->is_modern && Module::isEnabled('stthemeeditor')) {
                $opt_names = [
                    1 => 'Infinite Scroll',
                    2 => 'Load more button',
                ];
                $theme_inf_scroll = Configuration::get('STSN_INFINITE_SCROLL', null, null, $this->id_shop);
                if (isset($opt_names[$theme_inf_scroll])) {
                    $url = $this->context->link->getAdminLink('AdminModules', true, [], [
                        'configure' => 'stthemeeditor',
                    ]);
                    $txt = 'If you want to use "' . $opt_names[$theme_inf_scroll] . '" with filter,
                        please go to <a href="' . $url . '" target="_blank">Theme Editor settings</a> and change
                        "Pagination" from "' . $opt_names[$theme_inf_scroll] . '" to "Pagination".';
                    if ($theme_inf_scroll == 1 && $this->settings['general']['p_type'] != 3) {
                        $txt .= '<br>Afer that select "Pagination Type: Infinite scroll" in General settings below ↓';
                    } elseif ($theme_inf_scroll == 2 && $this->settings['general']['p_type'] != 2) {
                        $txt .= '<br>Afer that select "Pagination Type: Load more button" in General settings below ↓';
                    }
                    $this->context->controller->warnings[] = $txt;
                }
            }
        }
    }

    public function getFilesUpdadeWarnings()
    {
        $warnings = $customizable_layout_files = [];
        $locations = [
            '/css/' => 'css',
            '/js/' => 'js',
            '/templates/admin/' => 'tpl',
            '/templates/hook/' => 'tpl',
            '/templates/front/' => 'tpl',
        ];
        foreach ($locations as $loc => $ext) {
            $loc = 'views' . $loc;
            $files = glob($this->local_path . $loc . '*.' . $ext);
            foreach ($files as $file) {
                $customizable_layout_files[] = '/' . $loc . basename($file);
            }
        }
        foreach ($customizable_layout_files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($file == '/views/css/custom.css' || $file == '/views/js/custom.js') {
                continue;
            }
            $subdir = $this->is_modern || $ext == 'tpl' ? '' : $ext . '/';
            $customized_file_path = _PS_THEME_DIR_ . $subdir . 'modules/' . $this->name . $file;
            if (file_exists($customized_file_path)) {
                $original_file_path = $this->local_path . $file;
                $original_rows = file($original_file_path);
                $original_identifier = trim(array_pop($original_rows));
                $customized_rows = file($customized_file_path);
                $customized_identifier = trim(array_pop($customized_rows));
                if ($original_identifier != $customized_identifier) {
                    $warnings[$file] = $original_identifier;
                }
            }
        }

        return $warnings;
    }

    public function customCode($action, $params = [])
    {
        $ret = true;
        switch ($action) {
            case 'get':
                $ret = $this->customCode('getTypes');
                foreach ($ret as $type => $code) {
                    $path = $this->customCode('getFilePath', ['type' => $type]);
                    if (file_exists($path)) {
                        $ret[$type] = Tools::file_get_contents($path);
                    }
                }
                if (isset($params['type'])) {
                    $ret = isset($ret[$params['type']]) ? $ret[$params['type']] : '';
                }
                break;
            case 'save':
                $available_types = $this->customCode('getTypes');
                $type = $params['type'];
                if (isset($available_types[$type]) && $file_path = $this->customCode('getFilePath', $params)) {
                    if ($code = rtrim($params['code'])) {
                        $ret = file_put_contents($file_path, $code . PHP_EOL); // add last empty line to r-trimmed code
                    } elseif (file_exists($file_path)) {
                        $ret = unlink($file_path);
                    }
                    Media::clearCache();
                }
                break;
            case 'getFilePath':
                $ret = $params['type'] ? $this->local_path . 'views/' . $params['type']
                    . '/custom.' . $params['type'] : '';
                break;
            case 'getTypes':
                $ret = ['css' => '', 'js' => ''];
                break;
        }

        return $ret;
    }
}
