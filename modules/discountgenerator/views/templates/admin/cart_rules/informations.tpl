{**
 * DiscountGenerator Prestashop Module
 *
 * @author iRessources <support-prestashop@iressources.com>
 * @link http://www.iressources.com/
 * @copyright Copyright &copy; 2015-2019 iRessources
 * @version 1.4.1
 *}
<div class="form-group">
    <label class="control-label col-lg-3 required">
		<span class="label-tooltip" data-toggle="tooltip"
              title="{l s='This will be displayed in the cart summary, as well as on the invoice.' mod='discountgenerator'}">
			{l s='Name' mod='discountgenerator'}
		</span>
    </label>
    <div class="col-lg-8">
        {foreach from=$languages item=language}
            {if $languages|count > 1}
                <div class="row">
                <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $id_lang_default}style="display:none"{/if}>
                <div class="col-lg-9">
            {/if}
            <input id="name_{$language.id_lang|intval}" type="text"  name="name_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, 'name', $language.id_lang|intval)|escape:'htmlall':'UTF-8'}">
            {if $languages|count > 1}
                </div>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        {$language.iso_code}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        {foreach from=$languages item=language}
                            <li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
                        {/foreach}
                    </ul>
                </div>
                </div>
                </div>
            {/if}
        {/foreach}
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
              title="{l s='For your eyes only. This will never be displayed to the customer.' mod='discountgenerator'}">
			{l s='Description' mod='discountgenerator'}
		</span>
    </label>
    <div class="col-lg-8">
        <textarea name="description" rows="2" class="textarea-autosize">{$currentTab->getFieldValue($currentObject, 'description')|escape}</textarea>
    </div>
</div>

<!-- Custom block starts -->
<div class="form-group">
    <label class="control-label col-lg-3">
        <span class="label-tooltip" data-toggle="tooltip"
              title="{l s='Generate many unique vouchers' mod='discountgenerator'}">
            {l s='Generate many unique vouchers' mod='discountgenerator'}
        </span>
    </label>
    <div class="col-lg-8">
        <p class="checkbox">
            <label>
                <input type="checkbox" name="show_group_discount" id="show_group_discount" value="1"
                       {if isset($show_group_discount) && $show_group_discount == 1}checked="checked"{/if} />
            </label>
        </p>
    </div>
</div>
<div id="group_discount_properties" style="{if !isset($show_group_discount) || $show_group_discount == 0}display:none{/if}">
    <div class="form-group">
        <label class="control-label col-lg-3 required" required>
            <span class="label-tooltip" data-toggle="tooltip"
                  title="{l s='Total number of unique vouchers' mod='discountgenerator'}">
                {l s='Total number of unique vouchers' mod='discountgenerator'}
            </span>
        </label>
        <div class="col-lg-9">
            <div class="input-group col-lg-4">
                <input type="text" size="15" name="coupon_quantity" id="coupon_quantity" value="{if isset($coupon_quantity)}{$coupon_quantity|escape:'htmlall':'UTF-8'}{/if}" />
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3 required" required>
            <span class="label-tooltip" data-toggle="tooltip"
                  title="{l s='Code configurations' mod='discountgenerator'}">
                {l s='Code configurations' mod='discountgenerator'}
            </span>
        </label>
        <div class="col-lg-9">
            <div class="input-group col-lg-4" style="width:400px;">
                <input style="width:100px;float:left;" type="text" placeholder="Prefix" size="15" name="code_prefix" value="{if isset($code_prefix)}{$code_prefix|escape:'htmlall':'UTF-8'}{/if}" onclick="" />
                <input style="width:100px;float:left;" type="text" placeholder="Code mask" size="15" name="code_mask" value="{if isset($code_mask)}{$code_mask|escape:'htmlall':'UTF-8'}{/if}" onclick=""  />
                <div style="clear:both;"></div><p class="clear">{l s='Code generation rules (mask: x - digit, y - letter, for example "xxxyy" )' mod='discountgenerator'}</p>
            </div>
        </div>
    </div>
</div>
<!-- Custom block ends -->

<div id="single_code_field" class="form-group" style="{if isset($show_group_discount) && $show_group_discount == 1}display:none{/if}">
    <label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
              title="{l s='This is the code users should enter to apply the voucher to a cart. Either create your own code or generate one by clicking on "Generate".' mod='discountgenerator'}">
			{l s='Code' mod='discountgenerator'}
		</span>
    </label>
    <div class="col-lg-9">
        <div class="input-group col-lg-4">
            <input type="text" id="code" name="code" value="{$currentTab->getFieldValue($currentObject, 'code')|escape}" />
            <span class="input-group-btn">
				<a href="javascript:gencode(8);" class="btn btn-default"><i class="icon-random"></i> {l s='Generate' mod='discountgenerator'}</a>
			</span>
        </div>
        <span class="help-block">{l s='Caution! If you leave this field blank, the rule will automatically be applied to benefiting customers.' mod='discountgenerator'}</span>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
              title="{l s='If the voucher is not yet in the cart, it will be displayed in the cart summary.' mod='discountgenerator'}">
			{l s='Highlight' mod='discountgenerator'}
		</span>
    </label>
    <div class="col-lg-9">
		<span class="switch prestashop-switch fixed-width-lg">
			<input type="radio" name="highlight" id="highlight_on" value="1" {if $currentTab->getFieldValue($currentObject, 'highlight')|intval}checked="checked"{/if}/>
			<label for="highlight_on">{l s='Yes' mod='discountgenerator'}</label>
			<input type="radio" name="highlight" id="highlight_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'highlight')|intval}checked="checked"{/if} />
			<label for="highlight_off">{l s='No' mod='discountgenerator'}</label>
			<a class="slide-button btn"></a>
		</span>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
              title="{l s='Only applicable if the voucher value is greater than the cart total.' mod='discountgenerator'}
		{l s='If you do not allow partial use, the voucher value will be lowered to the total order amount. If you allow partial use, however, a new voucher will be created with the remainder.' mod='discountgenerator'}">
			{l s='Partial use' mod='discountgenerator'}
		</span>
    </label>
    <div class="col-lg-9">
		<span class="switch prestashop-switch fixed-width-lg">
			<input type="radio" name="partial_use" id="partial_use_on" value="1" {if $currentTab->getFieldValue($currentObject, 'partial_use')|intval}checked="checked"{/if} />
			<label class="t" for="partial_use_on">{l s='Yes' mod='discountgenerator'}</label>
			<input type="radio" name="partial_use" id="partial_use_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'partial_use')|intval}checked="checked"{/if} />
			<label class="t" for="partial_use_off">{l s='No' mod='discountgenerator'}</label>
			<a class="slide-button btn"></a>
		</span>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip"
              title="{l s='Cart rules are applied by priority. A cart rule with a priority of "1" will be processed before a cart rule with a priority of "2".' mod='discountgenerator'}">
			{l s='Priority' mod='discountgenerator'}
		</span>
    </label>
    <div class="col-lg-1">
        <input type="text" class="input-mini" name="priority" value="{$currentTab->getFieldValue($currentObject, 'priority')|intval}" />
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-3">{l s='Status' mod='discountgenerator'}</label>
    <div class="col-lg-9">
		<span class="switch prestashop-switch fixed-width-lg">
			<input type="radio" name="active" id="active_on" value="1" {if $currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
			<label class="t" for="active_on">{l s='Yes' mod='discountgenerator'}</label>
			<input type="radio" name="active" id="active_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
			<label class="t" for="active_off">{l s='No' mod='discountgenerator'}</label>
			<a class="slide-button btn"></a>
		</span>
    </div>
</div>
<script type="text/javascript">
    $(".textarea-autosize").autosize();
</script>

<!-- Custom block starts -->
<script type="text/javascript">
    $(document).ready(function(){
        $("#show_group_discount").change(function(){
            if($(this).is(":checked")) {
                $("#group_discount_properties").fadeIn().find("input").removeAttr("disabled");
                $("#single_code_field").fadeOut().find("input").attr("disabled","disabled");
            } else {
                $("#group_discount_properties").fadeOut().find("input").attr("disabled","disabled");
                $("#single_code_field").fadeIn().find("input").removeAttr("disabled");
            }
        });
    });
</script>
<!-- Custom block ends -->
