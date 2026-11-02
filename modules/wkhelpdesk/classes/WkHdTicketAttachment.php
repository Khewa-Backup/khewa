<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.txt
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to a newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright Since 2010 Webkul
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class WkHdTicketAttachment extends ObjectModel
{
    public $id;
    public $hd_id_msg;
    public $attachment_name;
    public $attachment_token;

    public static $definition = [
        'table' => 'wk_hd_ticket_attachment',
        'primary' => 'id',
        'fields' => [
            'hd_id_msg' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'attachment_token' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 128],
            'attachment_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isMailSubject',
                'required' => true,
                'size' => 128,
            ],
        ],
    ];

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_ticket_attachment', ['type' => 'shop', 'primary' => 'id']);
    }

    public function getAttachmentByIdMsg($idMsg)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'wk_hd_ticket_attachment` wta '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_attachment', 'wta') . ' WHERE wta.`hd_id_msg` = '
            . (int) $idMsg . ' GROUP BY wta.`id`'
        );
    }

    public function getAttachmentByIdAndToken($id, $token)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'wk_hd_ticket_attachment` wta '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_ticket_attachment', 'wta') . '
            WHERE wta.`id` = ' . (int) $id . '
            AND wta.`attachment_token` = \'' . pSQL($token) . '\' GROUP BY wta.`id`');
    }

    public static function getAttachmentToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16)); // 16 is length of token
    }

    /**
     * Khewa PS 9.2: last check before a file is saved in ticketattachments/. Only plain document, image,
     * archive and video types are ever stored, whatever the settings say, and the type is saved in lower case.
     * Returns the extension, or false if the file must not be saved.
     */
    public static function khewaSafeExtension($fileName, $checkSettings = true)
    {
        $ext = Tools::strtolower((string) pathinfo((string) $fileName, PATHINFO_EXTENSION));
        $safe = ['txt', 'rtf', 'doc', 'docx', 'pdf', 'zip', 'rar', 'png', 'gif', 'jpg', 'jpeg', 'mp4', 'avi', 'mkv', 'flv', 'mov', 'wmv'];
        if ($ext === '' || !in_array($ext, $safe, true)) {
            return false;
        }
        if ($checkSettings) {
            $selected = array_map('strtolower', (array) WkHdTicket::getSelectedFileExtension());
            if (!in_array($ext, $selected, true)) {
                return false;
            }
        }

        return $ext;
    }

    public static function uploadTicketAttachment($ticketattachment, $idMsg)
    {
        $fileExtension = self::khewaSafeExtension($ticketattachment['name']); // Khewa PS 9.2: safe types only
        if ($fileExtension) {
            $uploadPath = _PS_MODULE_DIR_ . 'wkhelpdesk/ticketattachments/';
            $fileName = $idMsg . '_1.' . $fileExtension; // _1 because this is msg mail attachment
            if (Tools::copy($ticketattachment['tmp_name'], $uploadPath . $fileName)) {
                $obj_msg_attachment = new WkHdTicketAttachment();
                $obj_msg_attachment->hd_id_msg = (int) $idMsg;
                $obj_msg_attachment->attachment_name = pSQL($fileName);
                $obj_msg_attachment->attachment_token = pSQL(WkHdTicket::getToken());
                $obj_msg_attachment->save();
            }
        }
    }

    public static function uploadTicketOtherAttachment($ticketOtherAttachment, $idMsg)
    {
        foreach ($ticketOtherAttachment['name'] as $key => $otherAttachment) {
            $fileExtension = self::khewaSafeExtension($otherAttachment); // Khewa PS 9.2: safe types only
            if ($fileExtension) {
                $uploadPath = _PS_MODULE_DIR_ . 'wkhelpdesk/ticketattachments/';
                $fileName = $idMsg . '_' . ($key + 2) . '.' . $fileExtension;
                if (Tools::copy($ticketOtherAttachment['tmp_name'][$key], $uploadPath . $fileName)) {
                    $obj_msg_attachment = new WkHdTicketAttachment();
                    $obj_msg_attachment->hd_id_msg = (int) $idMsg;
                    $obj_msg_attachment->attachment_name = pSQL($fileName);
                    $obj_msg_attachment->attachment_token = pSQL(WkHdTicket::getToken());
                    $obj_msg_attachment->save();
                }
            }
        }
    }
}
