<?php
/**
 * khewacorechanges — override of HTMLTemplate::getTemplate()
 *
 * Carries CORE_CHANGES.md #5 (footer tax info) and #10 (invoice discount
 * tabs) in an update-proof way: every PDF template is first looked for in
 * modules/khewacorechanges/pdf/, before the theme's pdf/ folder and before
 * core pdf/. The customized templates live inside this module, so neither a
 * PrestaShop update nor a theme update can lose them.
 *
 * All HTML PDF templates (invoice, order slip, delivery slip, ...) inherit
 * from this class, so one override covers them all.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class HTMLTemplate extends HTMLTemplateCore
{
    protected function getTemplate($template_name)
    {
        $moduleTemplate = _PS_MODULE_DIR_ . 'khewacorechanges/pdf/' . $template_name . '.tpl';
        if (file_exists($moduleTemplate)) {
            return $moduleTemplate;
        }

        return parent::getTemplate($template_name);
    }
}
