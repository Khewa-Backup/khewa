{*
*
* NOTICE OF LICENSE
*
*  @author    Khewa
*  @copyright 2024 Khewa
*  @license   Commercial License
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {l s='Reports' mod='khewareports'}
    </div>
    <div class="panel-body">
        <form method="post" action="{$action_url|escape:'html':'UTF-8'}" id="khewa-reports-form" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Select the start date for the report' mod='khewareports'}">
                        {l s='Date From' mod='khewareports'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" class="form-control datepicker" id="date_from" name="date_from" value="{$date_from|escape:'html':'UTF-8'}" placeholder="{l s='From' mod='khewareports'}" />
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Select the end date for the report' mod='khewareports'}">
                        {l s='Date To' mod='khewareports'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" class="form-control datepicker" id="date_to" name="date_to" value="{$date_to|escape:'html':'UTF-8'}" placeholder="{l s='To' mod='khewareports'}" />
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-9 col-lg-offset-3">
                    <button type="submit" name="submitKhewaReportsExport" id="submitKhewaReportsExport" class="btn btn-primary">
                        <i class="icon-download"></i> {l s='Export' mod='khewareports'}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        if ($(".datepicker").length > 0) {
            $(".datepicker").datepicker({
                prevText: '',
                nextText: '',
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                maxDate: new Date()
            });
            
            // Set min/max date constraints
            $('#date_from').on('change', function() {
                var fromDate = $(this).datepicker('getDate');
                if (fromDate) {
                    $('#date_to').datepicker('option', 'minDate', fromDate);
                }
            });
            
            $('#date_to').on('change', function() {
                var toDate = $(this).datepicker('getDate');
                if (toDate) {
                    $('#date_from').datepicker('option', 'maxDate', toDate);
                }
            });
        }
    });
</script>

