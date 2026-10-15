{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}
{if isset($mappedGroupAgent) && isset($groupInfo)}
<div id="mp-container-customer">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel clearfix">
				{if isset($groupInfo)}
					<div class="panel-heading">
						<i class="icon-user"></i>
						{$groupInfo.group_name}
						<div class="panel-heading-action">
							<a href="{$current}&amp;updatewk_hd_group&amp;id={$groupInfo.id|intval}&amp;token={$token}" class="btn btn-default">
								<i class="icon-edit"></i>
								{l s='Edit' mod='wkhelpdesk'}
							</a>
						</div>
					</div>
					<div class="form-horizontal">
						<div class="row">
							<label class="control-label col-lg-3">{l s='Mapped Agent(s)' mod='wkhelpdesk'} :</label>
							<div class="col-lg-9">
								{foreach $mappedGroupAgent as $group_agent}
									<p class="form-control-static">{$group_agent.name}({$group_agent.email})</p>
								{/foreach}
							</div>
						</div>

						<div class="row">
							<label class="control-label col-lg-3">{l s='Status' mod='wkhelpdesk'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">
									{if $groupInfo.active}
										<span class="label label-success">
											<i class="icon-check"></i>
											{l s='Active' mod='wkhelpdesk'}
										</span>
									{else}
										<span class="label label-danger">
											<i class="icon-remove"></i>
											{l s='Inactive' mod='wkhelpdesk'}
										</span>
									{/if}
								</p>
							</div>
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
</div>
{else}
<div class="alert alert-danger">
	<p>{l s='Agent information not found.' mod='wkhelpdesk'}</p>
</div>
{/if}