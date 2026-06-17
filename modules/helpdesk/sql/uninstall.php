<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright © 2022 FME Modules
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_priorities`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_departments`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_ticketstatus`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_tickets`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_tickets_responses`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_departments_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_departments_shop`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_emailtemp`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_emailtemp_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_premade`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_premade_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_priorities_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_ticketstatus_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fmm_hd_notes`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
return true;