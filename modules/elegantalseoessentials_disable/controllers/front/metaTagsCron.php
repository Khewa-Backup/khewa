<?php
/**
 * @author    ELEGANTAL <info@elegantal.com>
 * @copyright (c) 2023, ELEGANTAL <www.elegantal.com>
 * @license   Proprietary License - It is forbidden to resell or redistribute copies of the module or modified copies of the module.
 */

/**
 * This is controller for CRON job for meta tags
 */
class ElegantalSeoEssentialsMetaTagsCronModuleFrontController extends ModuleFrontController
{

    public function display()
    {
        $id = Tools::getValue('id');
        if (empty($id)) {
            die("ID is required.");
        }

        $model = new ElegantalSeoEssentialsAutoMeta($id);
        if (!Validate::isLoadedObject($model)) {
            die("Record not found.");
        }
        if (!$model->is_active) {
            die("The rule is disabled.");
        }

        $success_count = 0;
        $fail_count = 0;

        $product_ids = $model->getProductIds();
        foreach ($product_ids as $product_id) {
            if ($model->applyRuleOnProduct($product_id)) {
                $success_count++;
            } else {
                $fail_count++;
            }
        }

        $model->applied_at = date('Y-m-d H:i:s');
        $model->update();

        $message = "Meta Tags Rule #" . (int) $id . " was applied to " . (int) $success_count . " products.";
        if ($fail_count) {
            $message .= " Total number of failed products: " . (int) $fail_count . ".";
        }

        die($message);
    }
}
