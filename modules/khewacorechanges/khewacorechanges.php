<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Khewacorechanges extends Module
{
    public function __construct()
    {
        $this->name = 'khewacorechanges';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Khewa';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Khewa Core Changes');
        $this->description = $this->l('Holds Khewa\'s custom overrides/core changes so they survive PrestaShop and module updates.');
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}
