{*
 * Action audit details.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 *}

<div class="col-lg-12 bestkit-log-details">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-globe"></i> {l s='Log details' mod='bestkit_log'}
        </div>

        <div class="panel-title">
            <div>
                <strong>{l s='Object:' mod='bestkit_log'}</strong>
                {$bestkit_log.diffObj->object|escape:'html':'UTF-8'}
            </div>
            <div>
                <strong>{l s='Path:' mod='bestkit_log'}</strong>
                {$bestkit_log.diffObj->path|escape:'html':'UTF-8'}
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th><span class="title_box ">{l s='Id object' mod='bestkit_log'}</span></th>
                    <th><span class="title_box ">{l s='Field' mod='bestkit_log'}</span></th>
                    <th><span class="title_box ">{l s='Old value' mod='bestkit_log'}</span></th>
                    <th><span class="title_box ">{l s='New value' mod='bestkit_log'}</span></th>
                    <th><span class="title_box ">{l s='Diff' mod='bestkit_log'}</span></th>
                </tr>
            </thead>
            <tbody>
                {foreach $bestkit_log.details as $detail}
                    <tr>
                        <td>{$bestkit_log.diffObj->id_object|intval}</td>
                        <td>{$detail.name|escape:'html':'UTF-8'}</td>
                        <td>{$detail.old_value|escape:'html':'UTF-8'}</td>
                        <td>{$detail.new_value|escape:'html':'UTF-8'}</td>
                        <td>
                            {foreach $detail.diff_parts as $part}
                                {if $part.type == 'delete'}
                                    <del>{$part.value|escape:'html':'UTF-8'}</del>
                                {elseif $part.type == 'insert'}
                                    <ins>{$part.value|escape:'html':'UTF-8'}</ins>
                                {else}
                                    {$part.value|escape:'html':'UTF-8'}
                                {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>

{literal}
<style type="text/css">
    ins {
        color: green;
        background: #dfd;
        text-decoration: none;
    }
    del {
        color: red;
        background: #fdd;
        text-decoration: none;
    }
    code {
        font-size: smaller;
    }
    #params {
        margin: 1em 0;
        font: 14px sans-serif;
    }
    .code {
        margin-left: 2em;
        font: 12px monospace;
    }
    .ins {
        background:#dfd;
    }
    .del {
        background:#fdd;
    }
    .rep {
        color: #008;
        background: #eef;
    }
    .panecontainer {
        display: inline-block;
        width: 49.5%;
        vertical-align: top;
    }
    .panecontainer > p {
        margin: 0;
        border: 1px solid #bcd;
        border-bottom: none;
        padding: 1px 3px;
        background: #def;
        font: 14px sans-serif
    }
    .panecontainer > p + div {
        margin: 0;
        padding: 2px 0 2px 2px;
        border: 1px solid #bcd;
        border-top: none;
    }
    .pane {
        margin: 0;
        padding: 0;
        border: 0;
        width: 100%;
        min-height: 20em;
        overflow:auto;
        font: 12px monospace;
    }
    #htmldiff.onlyDeletions ins {display:none}
    #htmldiff.onlyInsertions del {display:none}
</style>
{/literal}
