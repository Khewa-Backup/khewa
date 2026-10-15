{*
*  2019 Zack Hussain
*
*  @author      Zack Hussain <me@zackhussain.ca>
*  @copyright   2019 Zack Hussain
*
*  DISCLAIMER
*
*  Do not redistribute without my permission. Feel free to modify the code as needed.
*  Modifying the code may break future PrestaShop updates.
*  Do not remove this comment containing author information and copyright.
*
*}

{extends file=$extendFormFile}

{block name="defaultForm"}
    {if isset($formTabs) && $formTabs|count}
        <script type="text/javascript">
            var formTabs = {$formTabs|json_encode};
        </script>
    {/if}
    {$smarty.block.parent}
{/block}

{block name="input_row"}
    {if isset($input.tab) && $input.tab != false}
        <span class="form-group-tab" data-tab-id="{$input.tab|escape:'html':'UTF-8'}">
            {$smarty.block.parent}
        </span>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="field"}
    {if $input.type == 'file'}
        {$smarty.block.parent}
        <div class="col-lg-9 col-lg-offset-3">
            <div class="form-group">
                {if isset($fields_value[$input.name]) && $fields_value[$input.name] != ''}
                    <div id="{$input.name|escape:'html':'UTF-8'}-images-thumbnails" class="col-lg-12">
                        <img src="{$uri|escape:'html':'UTF-8'}views/img/uploads/{$fields_value[$input.name|escape:'html':'UTF-8']|escape:'html':'UTF-8'}" class="img-thumbnail" style="max-width:100px;" />
                    </div>
                {/if}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
