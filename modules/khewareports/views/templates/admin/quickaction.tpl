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
        <i class="icon-bolt"></i> {l s='Quick Action' mod='khewareports'}
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <p>{l s='Click the button below to export reports based on your saved quick export settings.' mod='khewareports'}</p>
            <p><strong>{l s='Current Setting:' mod='khewareports'}</strong> {$current_period|escape:'html':'UTF-8'}</p>
        </div>
        <a href="{$export_url|escape:'html':'UTF-8'}" class="btn btn-primary btn-lg">
            <i class="icon-download"></i> {l s='Export Quick' mod='khewareports'}
        </a>
    </div>
</div>

