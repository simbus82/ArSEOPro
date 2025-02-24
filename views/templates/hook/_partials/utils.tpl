{*
* 2018 Areama
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Areama <contact@areama.net>
*  @copyright  2018 Areama
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*}

<div class="modal fade" id="arseo-oldroutes-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='Old routes editor' mod='arseopro'}</div>
            </div>
            <form class="form-horizontal form" id="arseo-oldroutes-form" onsubmit="arSEO.utils.saveOldRoutes(); return false;">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        {l s='Please be careful using this tool. You can break functionality of the old routes.' mod='arseopro'}
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Shop' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="id_shop" id="arseo-oldroutes-form_id_shop" onchange="arSEO.utils.loadOldRoutes()" data-serializable="true" data-default="0">
                                <option value="0">{l s='-- select --' mod='arseopro'}</option>
                                {foreach $shops as $shop}
                                    <option value="{$shop.id_shop|intval}">{$shop.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                    <div class="form-group hidden" id="arseo-old-routes-field">
                        <label class="control-label required col-sm-2">{l s='Old routes' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <textarea rows="20" id="arseo-old-routes"></textarea>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                    <button class="btn btn-success" disabled type="submit">{l s='Save' mod='arseopro'}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="arseopro-config-panel hidden" id="arseopro-utils" style="font-size: 15px">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-info"></i> {l s='Utilities' mod='arseopro'}
        </div>
        <div class="form-wrapper text-center">
            <p>
                {l s='Current version of overrides: ' mod='arseopro'}<br/>
            </p>
            <ul class="list-unstyled arseo-overrides">
                {foreach $overridesVersion as $k => $v}
                    <li id="arseo-override-{$k|escape:'htmlall':'UTF-8'}" class="{if $v == $arSEOProVersion}arseo-success{else}arseo-fail{/if}"><i class="icon-check"></i><i class="icon-close"></i> {$k|escape:'htmlall':'UTF-8'}: <span>{$v|escape:'htmlall':'UTF-8'}</span></li>
                {/foreach}
            </ul>
            <p>
                {l s='Current version of the module: ' mod='arseopro'} {$arSEOProVersion|escape:'htmlall':'UTF-8'}
            </p>
            <button type="button" class="btn btn-default" onclick="arSEO.utils.reInstallOverrides()">
                {l s='Re-install overrides' mod='arseopro'}
            </button>
            <hr/>
            <button type="button" class="btn btn-default" onclick="arSEO.url.resetRoutes()">
                {l s='Reset routes' mod='arseopro'}
            </button>
            <button type="button" class="btn btn-default" onclick="arSEO.url.resetOldRoutes()">
                {l s='Reset old routes' mod='arseopro'}
            </button>
            <button type="button" class="btn btn-default" onclick="arSEO.utils.editOldRoutes()">
                {l s='Edit old routes' mod='arseopro'}
            </button>
            <hr/>
            <p>
                {l s='Actual loaded routes:' mod='arseopro'}
            </p>
            {if $routes == false}
                <div class="alert alert-danger">
                    {l s='Method not found. Please re-install overrides.' mod='arseopro'}
                </div>
            {else}
                <table class="table table-bordered">
                    {foreach $routes as $k => $langRoutes}
                        {foreach $langRoutes as $l => $routesList}
                            {if isset($shops[$k]) and isset($langs[$l])}
                                <tr>
                                    <th colspan="2" style="text-align: center">
                                        {l s='Shop: ' mod='arseopro'} {$shops[$k].name|escape:'htmlall':'UTF-8'} - {$langs[$l].iso_code|escape:'htmlall':'UTF-8'}
                                    </th>
                                </tr>
                                {foreach $routesList as $rule => $route}
                                    <tr>
                                        <td style="text-align: left; color: #000000; padding: 6px 7px">{$rule|escape:'htmlall':'UTF-8'}</td>
                                        <td style="text-align: left; padding: 6px 7px">{$route.rule|escape:'htmlall':'UTF-8'}</td>
                                    </tr>
                                {/foreach}
                            {/if}
                        {/foreach}
                    {/foreach}
                </table>
            {/if}
        </div>
    </div>
</div>