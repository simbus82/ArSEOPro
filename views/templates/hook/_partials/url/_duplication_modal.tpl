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
<div class="modal fade" id="arseo-duplication-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='New duplication rule' mod='arseopro'}</div>
            </div>
            <form class="form-horizontal form" id="arseo-duplication-form" onsubmit="arSEO.url.duplication.save(); return false;">
                <div class="modal-body">
                    <input type="hidden" id="arseo-duplication-form_id" value="" data-default="" />
                    <input type="hidden" id="arseo-duplication-form_type" value="" data-default="" />
                    <div class="link-rewrite-group">
                        <div class="form-group link-rewrite">
                            <label class="control-label col-lg-2">{l s='For all languages' mod='arseopro'}</label>
                            <div class="col-lg-10">
                                <input name="link_rewrite_0" id="arseo-duplication-form_link_rewrite_0" 
                                       data-lang="0" value="" data-serializable="true" data-default="" type="text" />
                                <div class="errors"></div>
                            </div>
                        </div>
                        {foreach $languages as $language}
                            <div class="form-group link-rewrite-lang" id="link-rewrite-lang-{$language.id_lang|escape:'htmlall':'UTF-8'}">
                                <label class="control-label col-lg-2">{$language.iso_code|escape:'htmlall':'UTF-8'}</label>
                                <div class="col-lg-10">
                                    <input name="link_rewrite_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="arseo-duplication-form_link_rewrite_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
                                           data-lang="{$language.id_lang|escape:'htmlall':'UTF-8'}" value="" data-serializable="true" data-default="" type="text" class="arseo-link-rewrite" />
                                    <p class="actual-rewrite"></p>
                                    <div class="errors"></div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                    {if $multishop}
                        <div class="form-group">
                            <label class="control-label required col-sm-2">{l s='Shop' mod='arseopro'}</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="id_shop" id="arseo-duplication-form_id_shop" data-serializable="true" data-default="0">
                                    <option value="0">{l s='All shops' mod='arseopro'}</option>
                                    {foreach $shops as $shop}
                                        <option value="{$shop.id_shop|intval}">{$shop.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                                <div class="errors"></div>
                            </div>
                        </div>
                    {else}
                        <input type="hidden" value="{$id_shop|intval}" name="id_shop" id="arseo-duplication-form_id_shop" data-serializable="true" data-default="{$id_shop|intval}" />
                    {/if}
                    <div class="form-group name-group">
                        <label class="control-label required col-sm-2">{l s='Name' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="arseo-duplication-form_name" name="name" data-serializable="true" data-default="" />
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                    <button class="btn btn-success" type="submit">{l s='Save' mod='arseopro'}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.link-rewrite input').keyup(function(data){
        if (data.keyCode != 9 && data.keyCode != 16){
            var val = $(this).val();
            if (val.trim() != ''){
                $('.link-rewrite-lang input').val(val);
                arSEO.url.duplication.updateLinkRewrite(val, this);
            }
        }
    });
    $('.link-rewrite-lang input').keyup(function(data){
        console.log(data);
        if (data.keyCode != 9 && data.keyCode != 16){
            var val = $(this).val();
            arSEO.url.duplication.updateLinkRewrite(val, this);
        }
    });
</script>