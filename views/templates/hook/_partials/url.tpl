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
<div class="arseopro-config-panel" id="arseopro-url">
    <div class="bootstrap panel form-horizontal">
        <div class="panel-heading show-heading">
            <i class="icon-link"></i> {l s='URL Settings' mod='arseopro'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" onclick="arSEO.url.resetRoutes(); return false;" href="#">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Reset routes" data-html="true" data-placement="top">
                        <i class="process-icon-refresh"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="form-wrapper">
            <ul class="nav nav-tabs">
                <li class="{if $activeSubTab == 'ArSeoProURLGeneral' or empty($activeSubTab) or $activeTab != 'url'}active{/if}">
                    <a href="#arseopro-general" id="arseopro-general-tab" data-toggle="tab">{l s='General settings' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProURLProduct'}active{/if}">
                    <a href="#arseopro-product" id="arseopro-product-tab" data-toggle="tab">{l s='Product URLs' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProURLCategory'}active{/if}">
                    <a href="#arseopro-category" id="arseopro-category-tab" data-toggle="tab">{l s='Category URLs' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProURLManufacturer'}active{/if}">
                    <a href="#arseopro-manufacturer" id="arseopro-manufacturer-tab" data-toggle="tab">{l s='Manufacturer URLs' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProURLSupplier'}active{/if}">
                    <a href="#arseopro-supplier" id="arseopro-supplier-tab" data-toggle="tab">{l s='Supplier URLs' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProURLCMS'}active{/if}">
                    <a href="#arseopro-cms" id="arseopro-cms-tab" data-toggle="tab">{l s='CMS URLs' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProURLCMSCategory'}active{/if}">
                    <a href="#arseopro-cms-category" id="arseopro-cms-category-tab" data-toggle="tab">{l s='CMS Category URLs' mod='arseopro'}</a>
                </li>
                <li class="">
                    <a href="#arseopro-duplication" id="arseopro-duplication-tab" data-toggle="tab">{l s='URL Duplication' mod='arseopro'}</a>
                </li>
            </ul>
            <div class="tab-content">
                {include file="./url/general.tpl"}
                {include file="./url/product.tpl"}
                {include file="./url/category.tpl"}
                {include file="./url/manufacturer.tpl"}
                {include file="./url/supplier.tpl"}
                {include file="./url/cms.tpl"}
                {include file="./url/cms-category.tpl"}
                {include file="./url/duplication.tpl"}
            </div>
        </div>
    </div>
    {include file="./url/_modal.tpl"}
</div>