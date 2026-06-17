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
<script type="text/javascript">
    var ets_solo_matches = window.opener !== null ? decodeURIComponent(window.opener.location.href).match(/^(?:https:\/\/|http:\/\/)(.+)\?back\=(https:\/\/|http:\/\/)(.+)$/) : false;
    if (ets_solo_matches) {
        //window.opener.location.href = ets_solo_matches[2] + ets_solo_matches[3];
        window.parent.location.href = ets_solo_matches[2] + ets_solo_matches[3];
        window.close();
    } else if (window.parent.opener !== null) {
        //window.opener.location.reload();
        window.parent.opener.location.reload();
        //window.top.opener.location.reload();
    } else
        location.reload();
    //window.close();
</script>