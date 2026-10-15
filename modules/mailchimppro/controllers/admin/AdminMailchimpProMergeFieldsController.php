<?php
/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Read-only browser for the audience's merge fields (label, tag, type).
 * Scoped to the current store's configured list.
 */
class AdminMailchimpProMergeFieldsController extends PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
    public $entityPlural = 'merge_fields';

    public $entitySingular = 'merge_field';

    /** Audiences cap at 30 merge fields, so one page always holds the full set. */
    protected $entitiesPerPage = 50;

    /** @var string */
    public $list_id;

    public function init()
    {
        parent::init();
        $this->list_id = Tools::getValue('list_id') ?: $this->getListIdFromStore();
    }

    protected function getListApiEndpointUrl()
    {
        return "/lists/{$this->list_id}/merge-fields";
    }

    protected function getEntities()
    {
        $entities = parent::getEntities();
        usort($entities, function ($a, $b) {
            return (int) $b['merge_id'] - (int) $a['merge_id'];
        });

        // Free cache refresh — but only when viewing the store's own list;
        // a foreign list's fields say nothing about our audience.
        if ($this->list_id == $this->getListIdFromStore()) {
            PrestaChamps\MailchimpPro\CompanyMergeField::updateStatusFromFields($entities);
        }

        return $entities;
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "/lists/{$this->list_id}/merge-fields/{$entityId}";
    }
}
