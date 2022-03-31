{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Buy-addons <contact@buy-addons.com>
*  @copyright  2007-2021 Buy-addons
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<form  method="POST" accept-charset="utf-8" class="form-horizontal clearfix">
	<div class="panel col-lg-12">
		<div class="panel-heading">
			{l s='Cron Job' mod='reportsale'}
		</div>
		<div>
		    <div class="form-group row">
	            <label class="control-label col-lg-3 text-right">{l s='Task frequency' mod='reportsale'}</label>
	            <div class="col-lg-9 ">
	                <select name="barpcronj[hour]" class=" fixed-width-xl" id="hour">
	                    <option {if $basettgcronj->hour == -1}selected{/if} value="-1">{l s='Every hour' mod='reportsale'}</option>
	                    {for $foo=0 to 23}
	                    <option {if $basettgcronj->hour == $foo}selected{/if} value="{$foo|escape:'htmlall':'UTF-8'}">{$foo|escape:'htmlall':'UTF-8'}:00</option>
	                    {/for}
	                </select>
	                <p class="help-block">
					{l s='At what time should this task be executed? Now is Friday, 2018-12-07 13:56' mod='reportsale'}</p>
	            </div>
	        </div>
	        <div class="form-group row">
	            <div class="col-lg-9 col-lg-offset-3">
	                <select name="barpcronj[day]" class=" fixed-width-xl" id="hour">
	                    <option {if $basettgcronj->day == -1}selected{/if} value="-1">{l s='Every day of the month' mod='reportsale'}</option>
	                    {for $foo1=1 to 31}
	                    <option {if $basettgcronj->day == $foo1}selected{/if} value="{$foo1|escape:'htmlall':'UTF-8'}">{$foo1|escape:'htmlall':'UTF-8'}</option>
	                    {/for}
	                </select>
	                <p class="help-block">
					{l s='On which day of the month should this task be executed?' mod='reportsale'}</p>
	            </div>
	        </div>
	        <div class="form-group row">
	            <div class="col-lg-9 col-lg-offset-3">
	                <select name="barpcronj[month]" class=" fixed-width-xl" id="month">
	                        <option {if $basettgcronj->month == -1}selected{/if} value="-1">{l s='Every month' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 1}selected{/if} value="1">{l s='January' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 2}selected{/if} value="2">{l s='February' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 3}selected{/if} value="3">{l s='March' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 4}selected{/if} value="4">{l s='April' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 5}selected{/if} value="5">{l s='May' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 6}selected{/if} value="6">{l s='June' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 7}selected{/if} value="7">{l s='July' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 8}selected{/if} value="8">{l s='August' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 9}selected{/if} value="9">{l s='September' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 10}selected{/if} value="10">{l s='October' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 11}selected{/if} value="11">{l s='November' mod='reportsale'}</option>
	                        <option {if $basettgcronj->month == 12}selected{/if} value="12">{l s='December' mod='reportsale'}</option>
	                </select>
	                <p class="help-block">{l s='On what month should this task be executed?' mod='reportsale'}</p>
	            </div>
	        </div>
	        <div class="form-group row">
	            <div class="col-lg-9 col-lg-offset-3">     
	                <select name="barpcronj[day_of_week]" class=" fixed-width-xl" id="day_of_week">
	                    <option {if $basettgcronj->day_of_week == -1}selected{/if} value="-1">{l s='Every day of the week' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 1}selected{/if} value="1">{l s='Monday' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 2}selected{/if} value="2">{l s='Tuesday' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 3}selected{/if} value="3">{l s='Wednesday' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 4}selected{/if} value="4">{l s='Thursday' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 5}selected{/if} value="5">{l s='Friday' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 6}selected{/if} value="6">{l s='Saturday' mod='reportsale'}</option>
	                    <option {if $basettgcronj->day_of_week == 7}selected{/if} value="7">{l s='Sunday' mod='reportsale'}</option>
	                </select>                                                                            
	                <p class="help-block">{l s='On which day of the week should this task be executed?' mod='reportsale'}</p>                            
	            </div>        
	        </div>
	        <div class="form-group row">
	        	<label class="control-label col-lg-3 text-right">{l s='Task frequency' mod='reportsale'}</label>
	            <div class="col-lg-9">
			        <select class="fixed-width-xl" name="barpcronj[tableex][]" multiple>
			        	<option {if in_array(1,$basettgcronj->tableex)}selected{/if} value="1">{l s='Basic' mod='reportsale'}</option>
			        	<option {if in_array(2,$basettgcronj->tableex)}selected{/if} value="2">{l s='Taxes' mod='reportsale'}</option>
			        	<option {if in_array(3,$basettgcronj->tableex)}selected{/if} value="3">{l s='Revenue' mod='reportsale'}</option>
			        	<option {if in_array(4,$basettgcronj->tableex)}selected{/if} value="4">{l s='All' mod='reportsale'}</option>
			        	<option {if in_array(5,$basettgcronj->tableex)}selected{/if} value="5">{l s='Product' mod='reportsale'}</option>
			        	<option {if in_array(6,$basettgcronj->tableex)}selected{/if} value="6">{l s='Manufacturers' mod='reportsale'}</option>
			        	<option {if in_array(7,$basettgcronj->tableex)}selected{/if} value="7">{l s='Supplier' mod='reportsale'}</option>
			        	<option {if in_array(8,$basettgcronj->tableex)}selected{/if} value="8">{l s='Category' mod='reportsale'}</option>
			        	<option {if in_array(9,$basettgcronj->tableex)}selected{/if} value="9">{l s='Client' mod='reportsale'}</option>
			        	<option {if in_array(10,$basettgcronj->tableex)}selected{/if} value="10">{l s='Credit Slips' mod='reportsale'}</option>
			        </select>
	            </div>
	        </div>
	        <div class="panel-footer">
	        	<button type="submit" class="btn btn-default pull-right" name="submit_cronjob" value="1">
	                <i class="process-icon-save "></i> <span>Save</span>
	            </button>
	        </div>	
		</div>
	</div>
</form>