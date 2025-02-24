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

<div class="form-group">
    <label class="control-label col-lg-3">
        {l s='Custom canonical URL' mod='arseopro'}
    </label>
    <div class="col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
            <input autocomplete="off" type="radio" name="ARSEO_customCanonicalActive" id="ARSEO_customCanonicalActive_on" value="1" {if $active == 1}checked="checked"{/if}>
            <label for="ARSEO_customCanonicalActive_on" class="radioCheck" onclick="">
                {l s='Yes' mod='arseopro'}
            </label>
            <input autocomplete="off" type="radio" name="ARSEO_customCanonicalActive" id="ARSEO_customCanonicalActive_off" value="0" {if $active == 0}checked="checked"{/if}>
            <label for="ARSEO_customCanonicalActive_off" class="radioCheck" onclick="">
                {l s='No' mod='arseopro'}
            </label>
            <a class="slide-button btn"></a>
        </span>
        <p class="help-block">
            {l s='If disabled, canonical URL will be default for this category.' mod='arseopro'}
        </p>
    </div>
</div>
<div class="form-group" id="canonicalSection" {if $active != 1}style="display:none;"{/if}>
    <label class="control-label col-lg-3">
        {l s='Canonical URL' mod='arseopro'}
    </label>
    <div class="col-lg-9">
        <div class="row">
            {foreach $languages as $language}
                <div class="translatable-field lang-{$language['id_lang']}" style="display: {if $language['id_lang'] == $default_form_language}block{else}none{/if};">
                    <div class="col-lg-9">
                        <input type="text" size="64" name="ARSEO_customCanonical_{$language['id_lang']}" value="{if isset($category_canonical[$language['id_lang']])}{$category_canonical[$language['id_lang']]}{/if}" />
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                            {$language['iso_code']}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach $languages as $language}
                                <li><a href="javascript:hideOtherLanguage({$language['id_lang']});" tabindex="-1">{$language['name']}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/foreach}
            <div class="col-lg-9">
                <p class="help-block">{l s='Type here the canonical url for this category' mod='arseopro'}</p>
            </div>
        </div>
    </div>
</div>








