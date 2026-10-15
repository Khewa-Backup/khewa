<?php
/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    PrestaChamps <leo@prestachamps.com>
 * @copyright PrestaChamps
 * @license   commercial
 *
 * Class AdminMailchimpProListMembersController
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\PrestaShop\Helpers\LinkHelper;

class AdminMailchimpProListMembersController extends PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
    public $entityPlural = 'members';

    public $entitySingular = 'member';

    public $list_id;

    public function init()
    {
        parent::init();
        $this->list_id = Tools::getValue('list_id');
    }

    protected function getListApiEndpointUrl()
    {
        return "/lists/{$this->list_id}/members";
    }

    protected function renderEntityList()
    {
        $this->context->smarty->assign(['merge_field_defs' => $this->getMergeFieldDefinitions()]);
        parent::renderEntityList();
    }

    /**
     * Field definitions drive the dynamic merge-value columns; ascending
     * merge_id mirrors the Mailchimp contacts-table column order.
     */
    protected function getMergeFieldDefinitions()
    {
        $result = $this->mailchimp->get(
            "/lists/{$this->list_id}/merge-fields",
            ['count' => 50, 'fields' => 'merge_fields.merge_id,merge_fields.name,merge_fields.tag,merge_fields.type']
        );
        if (!$this->mailchimp->success() || empty($result['merge_fields'])) {
            return [];
        }
        $fields = $result['merge_fields'];
        usort($fields, function ($a, $b) {
            return (int) $a['merge_id'] - (int) $b['merge_id'];
        });

        // Free cache refresh — only for the store's own list (see MergeFields controller).
        if ($this->list_id == $this->getListIdFromStore()) {
            PrestaChamps\MailchimpPro\CompanyMergeField::updateStatusFromFields($fields);
        }

        return $fields;
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "/lists/{$this->list_id}/members/{$entityId}";
    }

    protected function renderPaginationQ()
    {
        $this->context->smarty->assign([
            'controller_name' => $this->controller_name,
            'totalEntities' => $this->totalEntities,
            'offset' => $this->offset,
            'totalPageNumber' => $this->totalPageNumber,
            'currentPage' => $this->currentPage,
            'count' => $this->entitiesPerPage,
            'list_id' => Tools::getValue('list_id'),
        ]);
        $this->content .= $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/entity_list/_pagination_list-members.tpl'
        );
    }

    protected function getPaginationPageLinkTemplate()
    {
        return urldecode(
            LinkHelper::getAdminLink(
                $this->controller_name,
                true,
                [],
                [
                    'action' => 'page',
                    'page' => '(:num)',
                    'list_id' => $this->list_id,
                ]
            )
        );
    }
}
