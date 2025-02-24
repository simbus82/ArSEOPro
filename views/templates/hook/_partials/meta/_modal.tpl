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
<div class="modal fade" id="arseo-meta-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-hg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='New meta rule' mod='arseopro'}</div>
            </div>
            <form class="form-horizontal form" id="arseo-meta-rule-form" onsubmit="arSEO.meta.save(false); return false;">
                <input type="hidden" id="arseo-meta-rule-form_id" value="" data-default="">
                <div class="modal-body">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#arseopro-meta-general" id="arseopro-product-tab" data-toggle="tab">{l s='General' mod='arseopro'}</a>
                        </li>
                        <li class="">
                            <a href="#arseopro-meta-meta" id="arseopro-product-tab" data-toggle="tab">{l s='Meta tags' mod='arseopro'}</a>
                        </li>
                        <li class="">
                            <a href="#arseopro-meta-fb" id="arseopro-category-tab" data-toggle="tab">{l s='Facebook tags' mod='arseopro'}</a>
                        </li>
                        <li class="">
                            <a href="#arseopro-meta-tw" id="arseopro-manufacturer-tab" data-toggle="tab">{l s='Twitter tags' mod='arseopro'}</a>
                        </li>
                    </ul>
                        
                    <div class="tab-content">
                        <div class="tab-pane active" id="arseopro-meta-general">
                            <div class="panel">
                                <div class="form-group">
                                    <label class="control-label required col-sm-2">{l s='Rule type' mod='arseopro'}</label>
                                    <div class="col-sm-10">
                                        <select disabled="" onchange="arSEO.meta.updateKeywords()" class="form-control" name="rule_type" id="arseo-meta-rule-form_rule_type" data-serializable="true" data-default="product">
                                            <option value="product">{l s='Product' mod='arseopro'}</option>
                                            <option value="category">{l s='Category' mod='arseopro'}</option>
                                            <option value="metapage">{l s='Meta page' mod='arseopro'}</option>
                                            <option value="brand">{l s='Manufacturer page' mod='arseopro'}</option>
                                        </select>
                                        <div class="errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2">{l s='Rule name' mod='arseopro'}</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" placeholder="{l s='Leave this field blank to auto-generate rule name' mod='arseopro'}" id="arseo-meta-rule-form_name" name="name" data-serializable="true" data-default="">
                                        <div class="errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label required col-sm-2">{l s='Language' mod='arseopro'}</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" name="id_lang" id="arseo-meta-rule-form_id_lang" data-serializable="true" data-default="0">
                                            <option value="0">{l s='All languages' mod='arseopro'}</option>
                                            {foreach $langs as $lang}
                                                <option value="{$lang.id_lang|escape:'htmlall':'UTF-8'}">{$lang.name|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                        <div class="errors"></div>
                                    </div>
                                </div>
                                <div class="form-group form_group_categories">
                                    <label class="control-label required col-sm-2">{l s='Categories' mod='arseopro'}</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" name="id_category" id="arseo-meta-rule-form_id_category" data-serializable="true" data-default="0">
                                            <option value="0">{l s='All categories' mod='arseopro'}</option>
                                            <option value="1">{l s='Selected categories' mod='arseopro'}</option>
                                        </select>
                                        <div class="hidden" id="arseo-meta-categories-container">
                                            {$metaCategoriesTree nofilter}
                                        </div>
                                        <div class="errors"></div>
                                    </div>
                                </div>
                                <div class="form-group form_group_metapages">
                                    <label class="control-label required col-sm-2">{l s='Meta pages' mod='arseopro'}</label>
                                    <div class="col-sm-10">
                                        <div id="arseo-meta-pages-container">
                                            <ul class="list-unstyled" id="arseo-meta-rule-form_id_meta">
                                            {foreach $metaPages as $page}
                                                <li>
                                                    <label style="font-weight: normal">
                                                        <input type="checkbox" name="meta[]" data-serializable="true" value="{$page.id_meta|escape:'htmlall':'UTF-8'}" data-default="0" class="noborder" />
                                                        <a href="{$page.url|escape:'htmlall':'UTF-8'}" target="_blank">
                                                        {if $page.page == 'index'}
                                                            <b>{l s='Home page' mod='arseopro'}</b>
                                                        {else}
                                                            {$page.title|escape:'htmlall':'UTF-8'}
                                                        {/if}
                                                        </a>
                                                    </label>
                                                </li>
                                            {/foreach}
                                            </ul>
                                            <div class="errors"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="arseopro-meta-meta">
                            <div class="panel">
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Meta title' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_meta_title" placeholder="{l s='Leave this field blank if you dont want to update meta title' mod='arseopro'}" name="meta_title" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Meta description' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_meta_description" placeholder="{l s='Leave this field blank if you dont want to update meta description' mod='arseopro'}" name="meta_description" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Meta keywords' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_meta_keywords" placeholder="{l s='Leave this field blank if you dont want to update meta keywords' mod='arseopro'}" name="meta_keywords" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 keywords-container">
                                        
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="tab-pane" id="arseopro-meta-fb">
                            <div class="panel">
                                <div id="arseopro-meta-fb-alert">
                                    
                                </div>
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Admin account IDs' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="arseo-meta-rule-form_fb_admins" placeholder="{l s='Comma-separated list of Facebook user IDs of administrators or moderators of this page' mod='arseopro'}" name="fb_admins" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Facebook app ID' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="arseo-meta-rule-form_fb_app" placeholder="{l s='Facebook application ID applicable for this site' mod='arseopro'}" name="fb_app" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Title' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_fb_title" name="fb_title" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Description' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_fb_description" name="fb_description" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Image' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="fb_image" id="arseo-meta-rule-form_fb_image" data-serializable="true" data-default="1">
                                                    <option value="1">{l s='Cover image' mod='arseopro'}</option>
                                                    <option value="2">{l s='All images' mod='arseopro'}</option>
                                                    <option value="3">{l s='Custom image' mod='arseopro'}</option>
                                                </select>
                                                <div class="errors"></div>
                                            </div>
                                        </div>

                                        <div class="form-group" id="arseo-fb-custom-image">
                                            <label class="control-label col-sm-3">{l s='Custom image' mod='arseopro'}</label>
                                            <input type="hidden" name="fb_custom_image" id="arseo-meta-rule-form_fb_custom_image" data-serializable="true" data-default="" />
                                            <div class="col-sm-9">
                                                {$fbImageUploader nofilter}
                                                <div id="arseopro_fb_upload_image_list"></div>
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 keywords-container">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="arseopro-meta-tw">
                            <div class="panel">
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Type' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="tw_type" id="arseo-meta-rule-form_tw_type" onclick="arSEO.meta.changeTwitterType()" data-serializable="true" data-default="summary">
                                                    {foreach from=$twitterTypes item=twType key=key}
                                                        <option value="{$key|escape:'htmlall':'UTF-8'}">{$twType|escape:'htmlall':'UTF-8'}</option>
                                                    {/foreach}
                                                </select>
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Twitter account' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="arseo-meta-rule-form_tw_account" placeholder="{l s='Site twitter account. For example @userName' mod='arseopro'}" name="tw_account" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Title' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_tw_title" name="tw_title" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Description' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control has-keywords" id="arseo-meta-rule-form_tw_description" name="tw_description" data-serializable="true" data-default="">
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">{l s='Image' mod='arseopro'}</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="tw_image" id="arseo-meta-rule-form_tw_image" data-serializable="true" data-default="1">
                                                    <option value="1">{l s='Cover image' mod='arseopro'}</option>
                                                    <option value="3">{l s='Custom image' mod='arseopro'}</option>
                                                </select>
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                        <div class="form-group" id="arseo-tw-custom-image">
                                            <label class="control-label col-sm-3">{l s='Custom image' mod='arseopro'}</label>
                                            <input type="hidden" name="tw_custom_image" id="arseo-meta-rule-form_tw_custom_image" data-serializable="true" data-default="" />
                                            <div class="col-sm-9">
                                                {$twImageUploader nofilter}
                                                <div id="arseopro_tw_upload_image_list"></div>
                                                <div class="errors"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 keywords-container">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                    <button class="btn btn-success" type="submit">{l s='Save' mod='arseopro'}</button>
                    <button class="btn btn-primary" type="button" onclick="arSEO.meta.save(true)">{l s='Save and stay' mod='arseopro'}</button>
                </div>
            </form>
        </div>
    </div>
</div>