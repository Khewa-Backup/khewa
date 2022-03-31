{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
    * @copyright 2019 Knowband
    * @license   see file: LICENSE.txt
    *
    * Description
    *
    * Admin List Action tpl file
    *}

{$list} {*Variable contains HTML, can not escape*}

<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Select Etsy category to continue' mod='kbetsy'}</h4>
            </div>
            <div class="modal-body">
                {if $categories_imported == 'yes'}
                <p class="help-block" style="margin-top: 0px; margin-bottom: 20px;">{l s='Choose an Etsy category to leaf level & press the select category button (Will be populated on the leaf category selection) to continue.' mod='kbetsy'}</p>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='Select Category' mod='kbetsy'}
                    </label>

                    <div class="col-lg-3">
                        <select name="categories[]" class=" fixed-width-xl" id="etsy_category">
                            {foreach $etsy_categories as $etsy_category}
                                <option value="{$etsy_category['id_etsy_categories']|escape:'htmlall':'UTF-8'}">{$etsy_category['category_name']|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div style="clear: both; padding-bottom: 15px"></div>
                {else}
                {l s='Etsy categories is not imported yet.' mod='kbetsy'} <a href=''>{l s='Click here' mod='kbetsy'}</a> {l s='import the etsy categories. After import, Refresh the page to continue.' mod='kbetsy'}
                {/if}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='kbetsy'}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        /* Below code is no longer requured. It was added to display category selection on the Popup
        $("#page-header-desc-etsy_profiles-new_template").bind("click", function () {
            $("#myModal").modal();
            return false;
        });
        
        $("#etsy_category").bind("change", function() {
              $.ajax({
                url: '{$ajax_category_action}', {*Variable contains url, can not escape*}
                data: 'id_etsy_categories=' + $(this).val() + "&action=getEtsyCategory",
                type: 'post',
                dataType: 'json',
                success: function (json) {
                    
                }
            });
        });
        */
    });
</script>