<?php


class ObjectModel extends ObjectModelCore
{
    public function __construct($id = null, $id_lang = null, $id_shop = null, $translator = null)
    {
        $result = parent::__construct($id, $id_lang, $id_shop, $translator);

        //if (is_object(Context::getContext()->employee) && Context::getContext()->employee->id) { //Module::isEnabled('bestkit_log')
            require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

            BestkitLog::putToRegistry(BestkitLog::getRegistryName($this), $this);
        //}

        return $result;
    }
}