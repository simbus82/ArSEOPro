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
<div class="modal fade" id="arseo-url-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='New URL rule' mod='arseopro'}</div>
            </div>
            <form class="form-horizontal form" id="arseo-url-rule-form" onsubmit="arSEO.url.save(false); return false;">
                <input type="hidden" id="arseo-url-rule-form_id" value="" data-default="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-sm-2">{l s='Rule name' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" placeholder="{l s='Leave this field blank to auto-generate rule name' mod='arseopro'}" id="arseo-url-rule-form_name" name="name" data-serializable="true" data-default="">
                            <div class="errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Language' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="id_lang" id="arseo-url-rule-form_id_lang" data-serializable="true" data-default="0">
                                <option value="0">{l s='All languages' mod='arseopro'}</option>
                                {foreach $langs as $lang}
                                    <option value="{$lang.id_lang|intval}">{$lang.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Categories' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="id_category" id="arseo-url-rule-form_id_category" data-serializable="true" data-default="0">
                                <option value="0">{l s='All categories' mod='arseopro'}</option>
                                <option value="1">{l s='Selected categories' mod='arseopro'}</option>
                            </select>
                            <div class="hidden" id="arseo-categories-container">
                                {$categoriesTree nofilter}
                            </div>
                            <div class="errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='URL rule' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="arseo-url-rule-form_rule" name="rule" data-serializable="true" data-default="">
                            <div class="errors"></div>
                            {include file="./_keywords.tpl"}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                    <button class="btn btn-success" type="submit">{l s='Save' mod='arseopro'}</button>
                    <button class="btn btn-primary" type="button" onclick="arSEO.url.save(true)">{l s='Save and stay' mod='arseopro'}</button>
                </div>
            </form>
        </div>
    </div>
</div>