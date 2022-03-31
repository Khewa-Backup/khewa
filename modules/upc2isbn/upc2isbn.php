<?php
/**
 * 2016-2017 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2016-2017 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

/*if (!defined('_PS_VERSION_')) {
    exit;
}*/

class UPC2ISBN extends Module
{
    private $myError;
    private $mySuc;

    public function __construct()
    {
        $this->name = 'upc2isbn';
        $this->tab = 'other';
        $this->version = '1.0.0';
        $this->author = 'Leoné MusicReader B.V.';
        $this->module_key = 'a06117e97ebeb3c978a78e7118573972';

        $this->bootstrap=true;

        parent::__construct();

        $this->displayName = $this->l('Copy UPC values to isbn');
        $this->description =
            $this->l('Copy UPC values to isbn.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        Db::getInstance()->Execute("UPDATE ps_product SET isbn=upc WHERE isbn IS NULL OR isbn = '';");
        Db::getInstance()->Execute("UPDATE ps_product_attribute SET isbn=upc WHERE isbn IS NULL OR isbn = '';");

        return "UPC values copied to ISBN field.";
    }
}
