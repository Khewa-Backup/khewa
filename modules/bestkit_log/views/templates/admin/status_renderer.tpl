{*
 * Action status label.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 *}

<span class="label {if $bestkit_points.status.name == 'update'}label-warning{elseif $bestkit_points.status.name == 'add'}label-success{else}label-danger{/if}">
    {$bestkit_points.status.name|escape:'html':'UTF-8'}
</span>
