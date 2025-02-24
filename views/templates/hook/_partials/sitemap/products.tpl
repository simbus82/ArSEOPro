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
<div class="tab-pane {if $activeSubTab == 'ArSeoProSitemapProducts'}active{/if}" id="arseopro-sitemap-products">
    {$form->generateForm($sitemapProductsFormParams) nofilter}{* HTML content generated by HelperForm, no escape necessary *}
    <div class="panel {if $sitemapConfig->products->all}hidden{/if}">
        <div class="panel-heading show-heading">
            <i class="icon-cog"></i> {l s='Products to export' mod='arseopro'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" onclick="arSEO.sitemap.product.reload(); return false;" href="#">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Refresh product list" data-html="true" data-placement="top">
                        <i class="process-icon-refresh"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="form-wrapper">
            <div id="form-sitemap-products-container">
                <div id="form-sitemap-products" class="arseo-placeholder">
                    <input type="hidden" name="page" value="1" />
                </div>
            </div>
        </div>
    </div>
</div>
{if $activeSubTab == 'ArSeoProSitemapProducts' and !$sitemapConfig->products->all}
    <script type="text/javascript">
        window.addEventListener('load', function(){
            arSEO.sitemap.product.reload();
        });
    </script>
{/if}