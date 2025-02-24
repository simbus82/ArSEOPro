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
<link rel="stylesheet" href="{$moduleUrl|escape:'htmlall':'UTF-8'}views/css/admin.css" type="text/css" media="all" />
<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/admin.js"></script>
{if $overridesDisabled}
    <div class="alert alert-danger" style="font-size: 15px">
        <b>{l s='Overrides are disabled.' mod='arseopro'}</b>
        {l s='This module cannot work correctly without overrides.' mod='arseopro'}
        Please enable overrides in the <a href="{$preformanceSettingsUrl|escape:'htmlall':'UTF-8'}" target="_blank">Performance settings</a>.
    </div>
{/if}
<div class="row" id="arseopro-config">
    <div class="col-lg-2 col-md-3">
        <div class="list-group arseoproTabs">
            <a class="list-group-item {if empty($activeTab) or $activeTab == 'url'}active{/if}" data-target="arseopro-url" href="#">
                <i class="icon-link"></i> {l s='URL settings' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'canonical'}active{/if}" data-tab="3" data-target="arseopro-canonical" href="#">
                <i class="icon-link"></i> {l s='Canonical' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'favicon'}active{/if}" data-tab="1" data-target="arseopro-favicon" href="#">
                <i class="icon-cog"></i> {l s='Favicon' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'meta'}active{/if}" data-tab="1" data-target="arseopro-meta" href="#">
                <i class="icon-cog"></i> {l s='Meta tags' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'redirects'}active{/if}" data-target="arseopro-redirect" href="#">
                <i class="icon-cog"></i> {l s='Redirects' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'jsonld'}active{/if}" data-target="arseopro-jsonld" href="#">
                <i class="icon-cog"></i> {l s='JSON-LD microdata' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'sitemap'}active{/if}" data-tab="3" data-target="arseopro-sitemap" href="#">
                <i class="icon-code"></i> {l s='Sitemap' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'robots'}active{/if}" data-tab="3" data-target="arseopro-robots" href="#">
                <i class="icon-code"></i> {l s='Robots.txt editor' mod='arseopro'}
            </a>
            <a class="list-group-item {if $activeTab == 'utils'}active{/if}" data-tab="3" data-target="arseopro-utils" href="#">
                <i class="icon-cog"></i> {l s='Utilities' mod='arseopro'}
            </a>
            <a class="list-group-item" data-tab="10" data-target="arseopro-about" href="#">
                <i class="icon-info"></i> {l s='About' mod='arseopro'}
            </a>
        </div>
    </div>
    <div class="col-lg-10 col-md-9" id="arseopro-config-tabs">
        {include file="./_partials/url.tpl"}
        {include file="./_partials/favicon.tpl"}
        {include file="./_partials/canonical.tpl"}
        {include file="./_partials/meta.tpl"}
        {include file="./_partials/redirects.tpl"}
        {include file="./_partials/jsonld.tpl"}
        {include file="./_partials/sitemap.tpl"}
        {include file="./_partials/robots.tpl"}
        {include file="./_partials/help.tpl"}{* This is for future updates *}
        {include file="./_partials/utils.tpl"}
        {include file="./_partials/about.tpl"}
    </div>
    {include file="./_partials/_rule_progress_modal.tpl"}
</div>
<script type="text/javascript">        
    var max_image_size = {$max_image_size|escape:'htmlall':'UTF-8'};
    
    arSEO.errorMessage = "{l s='Operation failed' mod='arseopro'}";
    arSEO.successMessage = "{l s='Operation complete' mod='arseopro'}";
    
    arSEO.ajaxUrl = '{$ajaxUrl.default nofilter}';
    
    arSEO.redirect.ajaxUrl = '{$ajaxUrl.redirect nofilter}';
    arSEO.url.ajaxUrl = '{$ajaxUrl.url nofilter}';
    arSEO.meta.ajaxUrl = '{$ajaxUrl.meta nofilter}';
    arSEO.sitemap.ajaxUrl = '{$ajaxUrl.sitemap nofilter}';
    arSEO.sitemap.product.ajaxUrl = '{$ajaxUrl.sitemapProduct nofilter}';
    arSEO.sitemap.supplier.ajaxUrl = '{$ajaxUrl.sitemapSupplier nofilter}';
    arSEO.sitemap.manufacturer.ajaxUrl = '{$ajaxUrl.sitemapManufacturer nofilter}';
    arSEO.sitemap.cms.ajaxUrl = '{$ajaxUrl.sitemapCms nofilter}';
    arSEO.sitemap.meta.ajaxUrl = '{$ajaxUrl.sitemapMeta nofilter}';
    arSEO.sitemap.category.ajaxUrl = '{$ajaxUrl.sitemapCategory nofilter}';
    arSEO.sitemap.generateUrl = '{$ajaxUrl.sitemapGenerate nofilter}';
    arSEO.robots.ajaxUrl = '{$ajaxUrl.robots nofilter}';
    
    arSEO.redirect.createTitle = "{l s='New redirect rule' mod='arseopro'}";
    arSEO.redirect.editTitle = "{l s='Edit redirect rule' mod='arseopro'}";
    
    arSEO.clearConfirmation = "{l s='Clear all items?' mod='arseopro'}";
    arSEO.removeConfirmation = "{l s='Remove selected items?' mod='arseopro'}";
    arSEO.deleteItemConfirmation = "{l s='Are you sure you want to delete item?' mod='arseopro'}";
    arSEO.activateConfirmation = "{l s='Activate selected items?' mod='arseopro'}";
    arSEO.deactivateConfirmation = "{l s='Deactivate selected items?' mod='arseopro'}";
    arSEO.noItemsSelected = "{l s='No items selected' mod='arseopro'}";
    
    arSEO.redirect.saveSuccess = "{l s='Rule saved' mod='arseopro'}";
    arSEO.redirect.saveError = "{l s='Error. Rule not saved' mod='arseopro'}";
    
    arSEO.init();
</script>
<script src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/admin_scripts.js" type="text/javascript"></script>