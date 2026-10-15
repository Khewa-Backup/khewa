{*
* NOTICE OF LICENSE
*
* This source file is subject to a trade license awarded by
* Garamo Online L.T.D.
*
* Any use, reproduction, modification or distribution
* of this source file without the written consent of
* Garamo Online L.T.D It Is prohibited.
*
* @author    ReactionCode <info@reactioncode.com>
* @copyright 2015-2021 Garamo Online L.T.D
* @license   Commercial license
*}
<div id="module-caveat-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 class="modal-title">{l s='New GTM Workspace' mod='rc_pgtagmanager'}</h3>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                <p>{l s='This new module version' mod='rc_pgtagmanager'} <strong>{l s='requires to upgrade the Google Tag Manager Workspace' mod='rc_pgtagmanager'}</strong> {l s='to enable the latest features' mod='rc_pgtagmanager'}.</p>
                <p>{l s='Please, download the new workspace file from the online guide and import it on GTM' mod='rc_pgtagmanager'}</p>
                <p>{l s='If you click on' mod='rc_pgtagmanager'} "{l s='I updated the workspace' mod='rc_pgtagmanager'}", {l s='this message will don\'t show again' mod='rc_pgtagmanager'}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Remind later' mod='rc_pgtagmanager'}</button>
                <button id="module-caveat-done" type="button" class="btn btn-default" data-dismiss="modal">{l s='I updated the workspace' mod='rc_pgtagmanager'}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->