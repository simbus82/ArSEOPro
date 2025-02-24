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

<div class="row">
  <div class="col-md-12">
    <h2>{l s='Custom canonical URL' mod='arseopro'}</h2>
    <p class="subtitle">{l s='Use a custom canonical URL for this product?' mod='arseopro'}</p>
  </div>
</div>
<div class="row">
    <div class="col-md-8 form-group">
        <span class="ps-switch prestashop-switch fixed-width-lg">
            
            <input type="radio" name="ARSEO_customCanonicalActive" id="ARSEO_customCanonicalActive_off" autocomplete="off" value="0" {if $active == 0}checked="checked"{/if}>
            <label for="ARSEO_customCanonicalActive_off" class="radioCheck" onclick="">
                {l s='No' mod='arseopro'}
            </label>
            <input type="radio" name="ARSEO_customCanonicalActive" id="ARSEO_customCanonicalActive_on" autocomplete="off" value="1" {if $active == 1}checked="checked"{/if}>
            <label for="ARSEO_customCanonicalActive_on" class="radioCheck" onclick="">
                {l s='Yes' mod='arseopro'}
            </label>
            <a class="slide-button btn"></a>
        </span>
        <span class="help-box mb-3" data-toggle="popover" data-content="{l s='If disabled, canonical URL will be default for this product.' mod='arseopro'}" data-original-title="" title=""></span>
        
    </div>
</div>

<div class="row" id="canonicalSection" {if $active == false} style="display: none" {/if}>
    <fieldset class="col-md-9 form-group">
        <label class="form-control-label">
            {l s='Canonical URL for this product' mod='arseopro'}
        </label>

        <div class="translations tabbable" id="form_step5_ARSEO_customCanonical">
            <div class="translationsFields tab-content">
                {foreach $languages as $language}
                    <div data-locale="{$language['iso_code']}" class="translationsFields-form_step5_ARSEO_customCanonical_{$language['id_lang']} tab-pane translation-field {if $language['id_lang'] == $default_form_language} show active {/if}  translation-label-{$language['iso_code']}">
                        <input maxlength="200" autocomplete="off" type="text" name="ARSEO_customCanonical_{$language['id_lang']}" value="{if isset($product_canonical[$language['id_lang']])}{$product_canonical[$language['id_lang']]}{/if}" counter="200" counter_type="recommended" class="serp-watched-title form-control">
                        <small class="form-text text-muted text-right maxLength ">
                            <em>
                                <span class="currentLength"></span> {l s='of' mod='arseopro'} <span class="currentTotalMax">200</span> {l s='characters used (recommended)' mod='arseopro'}
                            </em>
                        </small>
                    </div>
                {/foreach}
            </div>
        </div>
    </fieldset>
</div>




