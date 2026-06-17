<?php
/**
 * 2010-2021 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2021 Webkul IN
 * @license LICENSE.txt
*/
class Attribute extends AttributeCore
{
    public function delete()
    {
        if (Module::isEnabled('wktrash')) {
            require_once _PS_MODULE_DIR_ . '/wktrash/classes/WkTrashRequiredClasses.php';
            $objDeletedAttribute = new WkDeletedAttribute();
            $objDeletedAttribute->getAttributeDetailBeforeDelete($this->id);

            return parent::delete();
        }
    }
}
