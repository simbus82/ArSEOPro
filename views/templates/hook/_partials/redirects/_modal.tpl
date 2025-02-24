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
<div class="modal fade" id="arseo-redirect-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='New redirect rule' mod='arseopro'}</div>
            </div>
            <form class="form-horizontal form" id="arseo-redirect-form" onsubmit="arSEO.redirect.save(); return false;">
                <div class="modal-body">
                    <input type="hidden" id="arseo-redirect-form_id" value="" data-default="" />
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Redirect from' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    {$serverUrl|escape:'htmlall':'UTF-8'}
                                </span>
                                <input type="text" placeholder="/route/to/old-url" id="arseo-redirect-form_from" class="form-control" name="from" data-serializable="true" data-default="" />
                            </div>
                        </div>
                        <div class="col-sm-10 col-sm-offset-2">
                            <div class="errors"></div>
                            <p class="help-block">
                                {l s='Start with "/" sign.' mod='arseopro'}<br/>
                                {l s='You can use tag {lang} which means all the languages.' mod='arseopro'}
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Redirect to' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="arseo-redirect-form_to" placeholder="{$serverUrl|escape:'htmlall':'UTF-8'}/new-url.html or /new-url.html" name="to" data-serializable="true" data-default="" />
                            <div class="errors"></div>
                            <p class="help-block">
                                {l s='You can write absolute or relative URL here.' mod='arseopro'}<br/>
                                {l s='You can use tag {lang} which will be replaced to same language from source URL.' mod='arseopro'}<br/>
                                {l s='You can use tag {default_lang} which will be replaced to default shop language.' mod='arseopro'}
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Redirect type' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="type" id="arseo-redirect-form_type" data-serializable="true" data-default="301">
                                <option value="301">301 - {l s='Moved Permanently' mod='arseopro'}</option>
                                <option value="302">302 - {l s='Moved Temporarily' mod='arseopro'}</option>
                                <option value="303">303 - {l s='See Other' mod='arseopro'}</option>
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                    {if $multishop}
                        <div class="form-group">
                            <label class="control-label required col-sm-2">{l s='Shop' mod='arseopro'}</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="id_shop" id="arseo-redirect-form_id_shop" data-serializable="true" data-default="0">
                                    <option value="0">{l s='All shops' mod='arseopro'}</option>
                                    {foreach $shops as $shop}
                                        <option value="{$shop.id_shop|intval}">{$shop.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                                <div class="errors"></div>
                            </div>
                        </div>
                    {else}
                        <input type="hidden" value="{$id_shop|intval}" name="id_shop" id="arseo-redirect-form_id_shop" data-serializable="true" data-default="{$id_shop|intval}" />
                    {/if}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                    <button class="btn btn-success" type="submit">{l s='Save' mod='arseopro'}</button>
                    <button class="btn btn-primary" type="button" onclick="arSEO.redirect.save(true)">{l s='Save and stay' mod='arseopro'}</button>
                </div>
            </form>
        </div>
    </div>
</div>