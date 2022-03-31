<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0($object)
{
    $shipping_methods = array(
        'DOM' => array(
            'DOM.RP' => 'Regular',
            'DOM.EP' => 'Expedited',
            'DOM.XP' => 'Xpresspost',
            'DOM.PC' => 'Priority',
        ),
        'USA' => array(
            'USA.EP' => 'Expedited Parcel USA',
            'USA.XP' => 'Xpresspost USA',
            'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
            'USA.PW.PAK' => 'Priority Worldwide pak USA',
            'USA.SP.AIR' => 'Small Packet USA Air (less than 1kg)',
            'USA.TP' => 'Tracked Packet USA',
        ),
        'INT' => array(
            'INT.PW.ENV' => 'Priority Worldwide Envelope Int’l',
            'INT.PW.PAK' => 'Priority Worldwide pak Int’l',
            'INT.PW.PARCEL' => 'Priority Worldwide parcel Int’l',
            'INT.XP' => 'Xpresspost International',
            'INT.SP.AIR' => 'Small Packet Int’l Air (less than 2kg)',
            'INT.SP.SURF' => 'Small Packet Int’l Surface (less than 2kg)',
            'INT.TP' => 'Tracked Packet – Int’l',
        ),
    );

    /* Update */
    Configuration::updateValue('CPL_ADDWEIGHT', false);
    Configuration::updateValue('CPL_RATE_SIGNATURE', false);
    Configuration::updateValue('CPL_CARRIER_IMAGE', true);
    Configuration::updateValue('CPL_DEFAULT_WEIGHT', '1.000');
    Configuration::updateValue('CPL_TOKEN_REQUEST', 'token_request_ps');
    Configuration::updateValue('CPL_VE', true);
    Configuration::updateValue('CPL_VS', true);
    Configuration::updateValue('CPL_UPDATE_2_0', true);

    /* Delete */
    Configuration::deleteByName('CPL_VERIFIED_SERIAL');
    Configuration::deleteByName('CPL_VERIFIED_EMAIL');


    return ($object->registerHook('updateCarrier') && $object->registerHook('actionCartSave') && installTabs($object) && installMethodsDb() && installMethods($shipping_methods) && editBoxesDb());
}

function installTabs($object)
{
    $tab = new Tab();
    $tab->class_name = 'AdminCanadaPostLabels';
    $tab->module = $object->name;
    $tab->id_parent = Tab::getIdFromClassName('AdminShipping');
    $tab->active = 1;

    foreach (Language::getLanguages(false) as $lang) {
        $tab->name[(int) $lang['id_lang']] = 'Canada Post Labels';
    }

    if (!$tab->add()) {
        return false;
    } else {
        return true;
    }
}

/* Install DB table with Canada Post service methods */
function installMethodsDb()
{
    return Db::getInstance()->Execute('
	CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cpl_methods` (
	`id_method` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`id_carrier` int(10) NOT NULL,
	`id_carrier_history` text NOT NULL,
	`name` varchar(255) NOT NULL,
	`code` varchar(16) NOT NULL,
	`group` varchar(16) NOT NULL,
	`active` tinyint(1) NOT NULL,
	UNIQUE(`name`, `code`),
	PRIMARY KEY (`id_method`))
	ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
}

/* Add column to db */
function editBoxesDb()
{
    return Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'cpl_boxes` ADD `weight` decimal(10,3) NOT NULL AFTER `length`');
}

/* Populate Methods DB with Canada Post service methods */
function installMethods($shipping_methods)
{
    foreach ($shipping_methods as $type => $methods) {
        foreach ($methods as $k => $v) {
            if (!Db::getInstance()->insert('cpl_methods', array(
                'name'      => pSQL($v),
                'code'      => pSQL($k),
                'group'      => pSQL($type),
                'active'      => 0,
            ))) {
                return false;
            }
        }
    }
    return true;
}
