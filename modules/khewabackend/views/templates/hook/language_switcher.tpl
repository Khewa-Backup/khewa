{*<div class="card">
    <h3 class="card-header">
        <i class="material-icons">language</i>
        {l s='Language Switcher' mod='khewabackend'}
    </h3>
    <div class="card-body">
        <p>{l s='Current language:' mod='khewabackend'} <strong>{$current_lang}</strong></p>
        <a href="{$switch_url}" class="btn btn-primary">
            <i class="material-icons">sync</i>
            {l s='Switch to' mod='khewabackend'} {$new_lang}
        </a>
    </div>
</div>
*}
<div class="component" id="header-product-lang-sw-container">
    <div class="shop-list">
        <a class="link" href="{$switch_url}" >
            {*<p>{l s='Current language:' mod='khewabackend'} <strong>{$current_lang}</strong></p>*}
            <i class="material-icons">sync</i>
            {l s='Switch to' mod='khewabackend'} {$new_lang}
        </a>
    </div>
</div>