{**
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
*}

<div class="row" style="margin-bottom: 20px;">
    <div class="col-xs-0 col-sm-2 hidden-xs-down wk_hd_clearfix"></div>
    <div class="col-xs-2 col-sm-1 wk_hd_sender_name_div" style="float:right;">
        <div class="wk_hd_name_initial" style="background:#B6E9FB;">
            <p class="h4" style="line-height:30px;color:#008DBA;text-align:center;">{$name|substr:0:1}</p>
        </div>
    </div>
    <div class="col-xs-8 col-sm-9 wk_hd_msg_div_sent" style="float:right;">
        {$ticket_msg.message|stripslashes nofilter}
        {if $is_attachment}
            <br />
            {foreach $objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id) as $attachment}
                <a class="btn btn-default wk-hd-download-attachment" href="{$link->getModuleLink('wkhelpdesk', 'downloadattachment', ['token' => $attachment.attachment_token, 'id' => $attachment.id])}" target="_blank" title="{$attachment.attachment_name}"><i class="material-icons">&#xE2C4;</i> {l s='Download file' mod='wkhelpdesk'}</a>
            {/foreach}
        {/if}
    </div>
</div>
<div class="row">
    <div class="col-xs-8 col-sm-9 wk_hd_datetime" style="margin-left:45px;">
        <span class="text-muted" style="float: none;"><i>{$name}</i>, {$ticket_msg.date_add|date_format:"%d-%b-%Y"}, {$ticket_msg.date_add|date_format:"%r"|substr:0:11}</span>
    </div>
</div>