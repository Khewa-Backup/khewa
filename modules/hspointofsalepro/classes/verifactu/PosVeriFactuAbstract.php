<?php
/**
 * RockPOS VeriFacturacion
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class PosVeriFactuAbstract
{

    abstract public function getQRCodeUrl($params);

    abstract public function getTextConfigurations($id_shop);

    /**
     *
     * @param string $module_name
     * @return bool
     */
    protected function isValidModule()
    {
        return Module::isEnabled($this->module_name) && Module::getInstanceByName($this->module_name);
    }
}
