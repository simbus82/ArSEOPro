{*
* 2022 Areama
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
*  @copyright  2022 Areama
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*}

<script type="text/javascript">
    window.addEventListener('load', function(){
        $('input[name=ARSEO_customCanonicalActive]').change(function () {
            if ($('#ARSEO_customCanonicalActive_on').is(':checked')) {
                $('#canonicalSection').show();
            } else {
                $('#canonicalSection').hide();
            }
        });
    });
</script>

<div class="form-group row">
    <label class="form-control-label col-lg-3">
        {l s='Do you want to use a custom canonical url for this category?' mod='arseopro'}
    </label>
    <div class="col-sm">
        <span class="ps-switch switch prestashop-switch fixed-width-lg">
            <input autocomplete="off" class="" type="radio" name="ARSEO_customCanonicalActive" id="ARSEO_customCanonicalActive_off" value="0" {if $active == 0}checked="checked"{/if}>
            <label for="ARSEO_customCanonicalActive_off" class="radioCheck" onclick="">
                {l s='No' mod='arseopro'}
            </label>
            <input autocomplete="off" class="" type="radio" name="ARSEO_customCanonicalActive" id="ARSEO_customCanonicalActive_on" value="1" {if $active == 1}checked="checked"{/if}>
            <label for="ARSEO_customCanonicalActive_on" class="radioCheck" onclick="">
                {l s='Yes' mod='arseopro'}
            </label>
            <a class="slide-button btn"></a>
        </span>
        <small class="form-text">
            {l s='No = Canonical urls for this category will be by default.' mod='arseopro'}
        </small>
    </div>
</div>

<div class="form-group row" id="canonicalSection" {if $active != 1}style="display:none;"{/if}>
    <label for="category_meta_keyword" class="form-control-label">
        {l s='Canonical url' mod='arseopro'}
    </label>
    
    <div class="col-sm"> 
        <div class="input-group locale-input-group js-locale-input-group d-flex">
            {foreach $languages as $language}
                <div class="js-locale-input js-locale-{$language['iso_code']} {if $default_form_language != $language['id_lang']}d-none{/if}" style="flex-grow: 1;">
                    <input type="text" id="ARSEO_customCanonical_{$language['id_lang']}" name="ARSEO_customCanonical_{$language['id_lang']}" class="form-control" value="{if isset($category_canonical[$language['id_lang']])}{$category_canonical[$language['id_lang']]}{/if}" > 
                </div>
            {/foreach}

            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle js-locale-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="category_meta_keyword">
                    {$languageObj->iso_code}
                </button>
                <div class="dropdown-menu" aria-labelledby="category_meta_keyword">
                    {foreach $languages as $language}
                        <span class="dropdown-item js-locale-item" data-locale="{$language['iso_code']}">{$language['name']}</span>
                    {/foreach}
                </div>
            </div>
        </div>

        <small class="form-text">
            {l s='Type here the canonical url for this category' mod='arseopro'}
        </small>
    </div>
</div>









