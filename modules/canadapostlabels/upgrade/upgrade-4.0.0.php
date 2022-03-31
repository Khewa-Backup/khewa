<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_0_0($object)
{
    require dirname(__FILE__) . '/../sql/sql_install.php';

    if (version_compare(_PS_VERSION_, '1.7.1.0') < 0) {
        $object->installOverrides();
    }

    \CanadaPostPs\Tools::migrateLegacyTables();

    $object->installTables();

    $object->registerHooks();
    $object->uninstallTabs();
    $object->installTabs();

    Configuration::updateValue('CPL_CONTENTS_VALUE', 1);
    Configuration::updateValue('CPL_ESTIMATES', 1);
    Configuration::updateValue('CPL_MAX_BOXES', 1);
    Configuration::updateValue('CPL_UPDATE_TRACKING_NUMBER', true);
    Configuration::updateValue('CPL_UPDATE_ORDER_STATUS', true);
    Configuration::updateValue('CPL_ORDER_STATUS', Configuration::get('PS_OS_SHIPPING'));
    Configuration::updateValue('CPL_TAX', 1);
    Configuration::updateValue('CPL_CARRIER_IMAGE', 0);
    Configuration::updateValue('CPL_CARRIER_LOGO_FILE', 'logo_40.png');
    Configuration::updateValue('CPL_SPLIT_TYPE', 2);
    Configuration::updateValue('CPL_LABELS_ORDER_BY', 'id_order');
    Configuration::updateValue('CPL_LABELS_ORDER_WAY', 'DESC');
    Configuration::updateValue('CPL_ACCOUNT_TYPE', (Configuration::get('CPC_CONTRACT') ? 2 : 1));
    Configuration::updateValue('CPL_LABEL_DELAY', 1000000);
    Configuration::updateValue('CPL_REQUESTED_SHIPPING_POINT', Configuration::get('CPL_POSTAL_CODE'));
    Configuration::updateValue('CPL_OPEN_LABEL_ON_CREATION', 1);
    Configuration::updateValue('CPL_TRACK_ORDER_STATUSES', Configuration::get('PS_OS_SHIPPING'));
    Configuration::updateValue('CPL_DELIVERED_ORDER_STATUS', Configuration::get('PS_OS_DELIVERED'));

    Configuration::updateValue('CPL_DOWNLOAD_ID', '2300');

    $customerVars = array('CONTRACT', 'CUSTOMER_NUMBER', 'PROD_API_USER', 'PROD_API_PASS');
    foreach ($customerVars as $customerVar) {
        if (Configuration::get('CPR_'.$customerVar)) {
            Configuration::updateValue('CPL_'.$customerVar, Configuration::get('CPR_'.$customerVar));
        }
        if (Configuration::get('CPC_'.$customerVar)) {
            Configuration::updateValue('CPL_'.$customerVar, Configuration::get('CPC_'.$customerVar));
        }
    }
    if (Configuration::get('CPL_SPLIT') && Configuration::get('CPL_SPLIT') == true) {
        Configuration::updateValue('CPL_MAX_BOXES', 5);
    }

    try {
        $Address = \CanadaPostPs\Address::getOriginAddress();
        if ($Address instanceof \CanadaPostPs\Address && Configuration::get('CPL_ADDRESS1')) {
            $Address->name = Configuration::get('CPL_COMPANY');
            $Address->company = Configuration::get('CPL_COMPANY');
            $Address->phone = Configuration::get('CPL_PHONE');
            $Address->address1 = Configuration::get('CPL_ADDRESS1');
            $Address->address2 = Configuration::get('CPL_ADDRESS2');
            $Address->city = Configuration::get('CPL_CITY');
            $Address->postcode = Configuration::get('CPL_POSTAL_CODE');
            $Address->id_state = State::getIdByIso(Configuration::get('CPL_PROVINCE'));
            $Address->id_country = Country::getByIso('CA');
            $Address->origin = (int)1;
            $Address->active = (int)1;
            $Address->save();
        }
    } catch (Exception $e) {
        return true;
    }

    $object->enable();

    return true;
}
