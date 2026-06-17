{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer tohttp://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Header tabs file
*}
<script>
var action_get_custom_details = '{$action_get_custom_details}';
</script>
<div class="modal fade" id="modal_details" tabindex="-1" aria-hidden="true" aria-labelledby="modal_reason">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="text-align: center;">
        <button type="button" class="close" onclick="closePopupTemplate()">
          <span aria-hidden="true">&times;</span>
          <span class="sr-only">Close</span>
        </button>
        <h4 class="modal-title velsof_modal_title" id="modal-reason">{$custom_product_information|escape:'html':'UTF-8'}</h4>
      </div>
      <div class="modal-body" style="min-height: 100px;">
        <form id="modal_details_editor">
          <div style="overflow-y:auto !important;">
            <table class="list form" style="width: calc(100% - 5px);">
            <colgroup>
        <col style="width: 138px;">
        <col style="width: auto;">
          </colgroup>
              <!-- REMINDER NAME -->
              <tbody>
                <input type="hidden" id="id_etsy_profile_product_custom" class="required_entry" name="id_etsy_profile_product_custom" value="">

                <tr>
                    <td class="ac_modal_form_label ac_modal_form_field" colspan="2" style="padding-bottom: 10px; text-align: center;">
                        <label style="margin-right: 60px;">
                            <input type="checkbox" id="enableInputsCheckbox" style="margin-right: 10px;">{$default_product_details_text|escape:'html':'UTF-8'}
                        </label>
                    </td>
                </tr>
                <tr>
                  <td class="ac_modal_form_label ac_modal_form_field" style="padding-bottom: 10px;">
                    <span class="control-label" data-toggle="tooltip" data-placement="top" data-original-title="Enter the Title">{$custom_details_title|escape:'html':'UTF-8'}
                     </span>
                  </td>
                  <td class="ac_modal_form_field" style="padding-bottom: 10px;">
                    <div class="span">
                      <input class="required_entry form-control" type="text" name="product_custom_title" value="" disabled>
                    </div>
                  </td>
                </tr>
                <br></br>
                <tr style="margin-top: 20px;"></tr>
                <tr>
                  <td class="ac_modal_form_label ac_modal_form_field">
                    <span class="control-label" data-toggle="tooltip" data-placement="top" data-original-title="Enter the Custom Description">{$custom_details_description|escape:'html':'UTF-8'}
                      </span>
                  </td>
                  <td class="ac_modal_form_field">
                    <div class="span">
                      <textarea class="required_entry form-control" name="product_custom_description" rows="4" cols="60" disabled></textarea>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closePopupTemplate();" class="btn btn-default">Close</button>
        <button type="button" onclick="saveCustomDetails(this);" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>
