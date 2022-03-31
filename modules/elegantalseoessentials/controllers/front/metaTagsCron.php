<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
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
