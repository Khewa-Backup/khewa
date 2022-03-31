{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-search"></i> {l s='SEO Meta Tag Rules' mod='elegantalseoessentials'}
        </div>
        <div class="panel-body">
            <div class="row elegantal_panel elegantal_autometatags_apply_panel" data-id="{$model->id_elegantalseoessentials_auto_meta|intval}" data-lang="{$lang_id|intval}" data-offset="{$offset|intval}" data-limit="{$limit|intval}" data-requests="{$requests|intval}" data-reloadmsg="{l s='The process has not finished yet.' mod='elegantalseoessentials'}">
                <div class="col-xs-12 col-md-offset-2 col-md-8">
                    <div class="bootstrap elegantal_hidden elegantal_error">
                        <div class="module_error alert alert-danger">
                            <span class="elegantal_error_txt"></span>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-heading">
                            <i class="icon-edit"></i> {l s='Applying rule "%1$s" on %2$s products...' sprintf=[$model->name, $total] mod='elegantalseoessentials'}
                        </div>
                        <div class="panel-body">
                            <br><br>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="progress">
                                        <div class="elegantal_progress_bar progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="min-width: 3em; width: 0%;">
                                            0%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 text-center">
                                    {l s='Please wait. It may take a few minutes.' mod='elegantalseoessentials'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="elegantalseoessentialsJsDef" data-adminurl="{$adminUrl|escape:'html':'UTF-8'}"></div>
</div>