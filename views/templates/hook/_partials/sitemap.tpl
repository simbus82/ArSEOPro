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
<div class="arseopro-config-panel hidden" id="arseopro-sitemap">
    <div class="bootstrap panel form-horizontal">
        <h3><i class="icon-code"></i> {l s='Sitemap Settings' mod='arseopro'}</h3>
        <div class="form-wrapper">
            <ul class="nav nav-tabs">
                <li class="{if $activeSubTab == 'ArSeoProSitemapGeneral' or empty($activeSubTab) or $activeTab != 'sitemap'}active{/if}">
                    <a href="#arseopro-sitemap-general" id="arseopro-sitemap-general-tab" data-toggle="tab">{l s='General' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProSitemapProducts'}active{/if}">
                    <a href="#arseopro-sitemap-products" id="arseopro-sitemap-products-tab" data-toggle="tab">{l s='Products' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProSitemapCategories'}active{/if}">
                    <a href="#arseopro-sitemap-categories" id="arseopro-sitemap-categories-tab" data-toggle="tab">{l s='Categories' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProSitemapSuppliers'}active{/if}">
                    <a href="#arseopro-sitemap-suppliers" id="arseopro-sitemap-suppliers-tab" data-toggle="tab">{l s='Suppliers' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProSitemapManufacturers'}active{/if}">
                    <a href="#arseopro-sitemap-manufacturers" id="arseopro-sitemap-manufacturers-tab" data-toggle="tab">{l s='Manufacturers' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProSitemapCmsConfig'}active{/if}">
                    <a href="#arseopro-sitemap-cms" id="arseopro-sitemap-cms-tab" data-toggle="tab">{l s='CMS' mod='arseopro'}</a>
                </li>
                <li class="{if $activeSubTab == 'ArSeoProSitemapMeta'}active{/if}">
                    <a href="#arseopro-sitemap-meta" id="arseopro-sitemap-meta-tab" data-toggle="tab">{l s='Meta pages' mod='arseopro'}</a>
                </li>
                {if $smartblogEnabled}
                    <li class="{if $activeSubTab == 'ArSeoProSitemapSmartblog'}active{/if}">
                        <a href="#arseopro-sitemap-smartblog" id="arseopro-sitemap-smartblog-tab" data-toggle="tab">{l s='SmartBlog pages' mod='arseopro'}</a>
                    </li>
                {/if}
                {if $prestablogEnabled}
                    <li class="{if $activeSubTab == 'ArSeoProSitemapPrestablog'}active{/if}">
                        <a href="#arseopro-sitemap-prestablog" id="arseopro-sitemap-prestablog-tab" data-toggle="tab">{l s='PrestaBlog pages' mod='arseopro'}</a>
                    </li>
                {/if}
                {if $simpleblogEnabled}
                    <li class="{if $activeSubTab == 'ArSeoProSitemapSimpleblog'}active{/if}">
                        <a href="#arseopro-sitemap-simpleblog" id="arseopro-sitemap-simpleblog-tab" data-toggle="tab">{l s='SimpleBlog pages' mod='arseopro'}</a>
                    </li>
                {/if}
                {if $FAQEnabled}
                    <li class="{if $activeSubTab == 'ArSeoProSitemapFAQs'}active{/if}">
                        <a href="#arseopro-sitemap-faq" id="arseopro-sitemap-faq-tab" data-toggle="tab">{l s='FAQ pages' mod='arseopro'}</a>
                    </li>
                {/if}
                <li class="{if $activeSubTab == 'ArSeoProSitemapGenerate'}active{/if}">
                    <a href="#arseopro-sitemap-generate" id="arseopro-sitemap-generate-tab" data-toggle="tab">{l s='Generate sitemap' mod='arseopro'}</a>
                </li>
            </ul>
            <div class="tab-content">
                {include file="./sitemap/general.tpl"}
                {include file="./sitemap/products.tpl"}
                {include file="./sitemap/categories.tpl"}
                {include file="./sitemap/manufacturers.tpl"}
                {include file="./sitemap/suppliers.tpl"}
                {include file="./sitemap/cms.tpl"}
                {include file="./sitemap/meta.tpl"}
                {include file="./sitemap/generate.tpl"}
                {if $smartblogEnabled}
                    {include file="./sitemap/smartblog.tpl"}
                {/if}
                {if $prestablogEnabled}
                    {include file="./sitemap/prestablog.tpl"}
                {/if}
                {if $simpleblogEnabled}
                    {include file="./sitemap/simpleblog.tpl"}
                {/if}
                {if $FAQEnabled}
                    {include file="./sitemap/faqs.tpl"}
                {/if}
            </div>
        </div>
    </div>
</div>