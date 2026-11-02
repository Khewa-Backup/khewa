<?php
/**
 * Khewa PS 9.2: new file.
 *
 * PrestaShop 9 ships modules/.htaccess, which blocks direct calls to any .php file inside modules/.
 * So rc_pgtagmanager-ajax.php now answers "403 Forbidden" and the product data for the data layer
 * (list views, product clicks, add to cart, product page) and the "order sent" record never arrive.
 * This front controller does the same job as rc_pgtagmanager-ajax.php, at
 * index.php?fc=module&module=rc_pgtagmanager&controller=ajax
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Rc_PgTagManagerAjaxModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function initContent()
    {
        // Khewa PS 9.2: same checks as rc_pgtagmanager-ajax.php
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Tools::getValue('token') || !Module::isEnabled('rc_pgtagmanager')) {
            http_response_code(404);
            exit;
        }

        try {
            /** @var Rc_PgTagManager $module */
            $module = $this->module;

            if (Tools::getValue('token') === $module->secret_key) {
                $data = (
                    Tools::getIsset('data') ?
                    json_decode(rawurldecode(Tools::getValue('data')), true) :
                    null
                );

                if (is_array($data) && !empty($data)) {
                    // ajaxCall() prints the JSON answer and stops
                    $module->ajaxCall($data);
                } else {
                    throw new Exception('Invalid data');
                }
            } else {
                throw new Exception('Invalid token');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo 'Error: ', $e->getMessage();
        }
        exit;
    }
}
