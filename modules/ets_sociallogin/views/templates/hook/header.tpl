{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}

<!--Module: ets_socicallogin -->
<script type="text/javascript">
{if isset($AMAZONE_CLIENTID) && $AMAZONE_CLIENTID}
    var AMAZONE_CLIENTID = '{$AMAZONE_CLIENTID|escape:'quotes':'UTF-8'}';
    var ETS_SOLO_AMAZON_APP_SECRET = '{$ETS_SOLO_AMAZON_APP_SECRET|escape:'quotes':'UTF-8'}';
{/if}
{if isset($callback) && $callback}
    var ets_solo_callbackUrl = '{$callback|escape:'html':'UTF-8'}';
{/if}
{if isset($ETS_SL_CONFIG) && $ETS_SL_CONFIG}
    {foreach from=$ETS_SL_CONFIG item='val' key='variableName'}
        var {$variableName|escape:'html':'UTF-8'} = {if $val.type=='string'}'{$val.value|escape:'html':'UTF-8'}'{else}{$val.value|intval}{/if};
    {/foreach}
{/if}
{if isset($transJs) && $transJs}
    {foreach from=$transJs item='val' key='variableName'}
        var {$variableName|escape:'html':'UTF-8'} = '{$val|escape:'html':'UTF-8'}';
    {/foreach}
{/if}
</script>
{if isset($base_dir) && $base_dir}
    <script src="{$base_dir|escape:'quotes':'UTF-8'}views/js/front.js"></script>
{/if}
{if isset($html) && $html}
    {$html nofilter}
{/if}
<!--/Module: ets_socicallogin-->