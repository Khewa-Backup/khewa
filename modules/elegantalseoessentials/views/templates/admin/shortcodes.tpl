{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantal_shortcodes">
    <div class="elegantal_shortcodes_wrapper">
        {foreach from=$shortcodes item=group_shortcodes key=group}
            {if count($shortcodes) > 1}
                <h3>{$group|escape:'html':'UTF-8'}</h3>
            {/if}
            <ul class="row elegantal_shortcodes_list">
                {foreach from=$group_shortcodes item=shortcode}
                    <li class="col-xs-12 col-md-6" data-shortcode="{$shortcode|escape:'html':'UTF-8'}">
                        <span>{$shortcode|escape:'html':'UTF-8'}</span>
                    </li>
                {/foreach}
            </ul>
        {/foreach}
    </div>
</div>