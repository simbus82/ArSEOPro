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
<div class="arseopro-config-panel" id="arseopro-jsonld">
    <div class="bootstrap panel form-horizontal">
        <div class="panel-heading show-heading">
            <i class="icon-link"></i> {l s='JSON-LD microdata settings' mod='arseopro'}
        </div>
        <div class="form-wrapper">
            <ul class="nav nav-tabs">
                <li class="{if $activeSubTab == 'ArSeoProJsonLDGeneral' or empty($activeSubTab) or $activeTab != 'jsonld'}active{/if}">
                    <a href="#arseopro-json-general" id="arseopro-json-general-tab" data-toggle="tab">{l s='General settings' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProJsonLDProduct'}active{/if}">
                    <a href="#arseopro-json-product" id="arseopro-json-product-tab" data-toggle="tab">{l s='Product page' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProJsonLDAdvanced'}active{/if}">
                    <a href="#arseopro-json-advanced" id="arseopro-json-advanced-tab" data-toggle="tab">{l s='Advanced settings' mod='arseopro'}</a>
                </li>
            </ul>
            <div class="tab-content">
                {include file="./jsonld/general.tpl"}
                {include file="./jsonld/product.tpl"}
                {include file="./jsonld/advanced.tpl"}
            </div>
        </div>
    </div>
</div>