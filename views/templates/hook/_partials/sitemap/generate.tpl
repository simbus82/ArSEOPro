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
<div class="tab-pane {if $activeSubTab == 'ArSeoProSitemapGenerate'}active{/if}" id="arseopro-sitemap-generate">
    <div class="panel">
        <div class="form-wrapper">
            <ul class="list-unstyled" style="font-size: 15px">
                <li>
                    <div class="row">
                        <div class="col-sm-2 text-right">
                            {l s='Memory limit:' mod='arseopro'}
                        </div>
                        <div class="col-sm-9">
                            {$memoryLimit|escape:'htmlall':'UTF-8'}
                        </div>
                    </div>
                </li>
                <li>
                    <div class="row">
                        <div class="col-sm-2 text-right">
                            {l s='Maximum execution time:' mod='arseopro'}
                        </div>
                        <div class="col-sm-9">
                            {$maxExecutionTime|escape:'htmlall':'UTF-8'} {l s='seconds' mod='arseopro'}
                        </div>
                    </div>
                </li>
                <li>
                    <div class="row">
                        <div class="col-sm-2 text-right">
                            {l s='Sitemap index directory:' mod='arseopro'}
                        </div>
                        <div class="col-sm-9 {if !$sitemapIndexDir}text-danger{/if}">
                            {$sitemapIndexDir|escape:'htmlall':'UTF-8'}
                            {if $indexSitemapDirWriteable}
                                <i style="color: #72C279" title="{l s='Directory is writeable' mod='arseopro'}" class="icon-check"></i>
                            {else}
                                <i style="color: #E08F95" title="{l s='Directory is not writeable! Please check permissions' mod='arseopro'}" class="icon-remove"></i>
                            {/if}
                        </div>
                    </div>
                </li>
                <li>
                    <div class="row">
                        <div class="col-sm-2 text-right">
                            {l s='Sitemap directory:' mod='arseopro'}
                        </div>
                        <div class="col-sm-9 {if !$sitemapDirWritable}text-danger{/if}">
                            {$sitemapDir|escape:'htmlall':'UTF-8'} 
                            {if $sitemapDirWritable}
                                <i style="color: #72C279" title="{l s='Directory is writeable' mod='arseopro'}" class="icon-check"></i>
                            {else}
                                <i style="color: #E08F95" title="{l s='Directory is not writeable! Please check write permissions!' mod='arseopro'}" class="icon-remove"></i>
                            {/if}
                        </div>
                    </div>
                </li>
                {if !$isRootWriteable}
                    <li>
                        <div class="row">
                            <div class="col-sm-9 col-sm-offset-2" style="font-size: 13px">
                                <div class="alert alert-danger">
                                    {l s='It\'s strongly recomended to place sitemap files in the root of your store.' mod='arseopro'}<br/>
                                    {l s='Please check write permissions to your root folder:' mod='arseopro'} <b>{$psRootDir nofilter}</b>
                                </div>
                            </div>
                        </div>
                    </li>
                {/if}
                <li>
                    <div class="row">
                        <div class="col-sm-2 text-right">
                            {l s='Sitemap file URL:' mod='arseopro'}
                        </div>
                        <div class="col-sm-9">
                            {foreach $shops as $shop}
                                <a href="{$shop.sitemapUrl|escape:'htmlall':'UTF-8'}" target="_blank">{$shop.sitemapUrl|escape:'htmlall':'UTF-8'}</a> <small id="arseopro-sitemap-lastgen-{$shop.id_shop|escape:'htmlall':'UTF-8'}">{$shop.sitemapLastegen|escape:'htmlall':'UTF-8'}</small><br/>
                            {/foreach}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading show-heading">
            <i class="icon-cog"></i> {l s='Generate sitemap' mod='arseopro'}
        </div>
        <div class="form-wrapper">
            <div id="arseo-sitemap-progress-container" class="">
                <div class="progress" id="arseo-sitemap-progress">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        0%
                    </div>
                </div>
                <ul class="list-unstyled" style="font-size: 15px;">
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Total time spend:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-time"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Peak memory usage:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-memory"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Products:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-products-count"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Product images:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-images-count"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Categories:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-categories-count"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Manufacturers:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-manufacturers-count"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Suppliers:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-suppliers-count"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='Meta pages (+index page):' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-meta-count"></div>
                        </div>
                    </li>
                    <li>
                        <div class="row">
                            <div class="col-sm-3">{l s='CMS pages:' mod='arseopro'}</div>
                            <div class="col-sm-9" id="arseopro-sitemap-cms-count"></div>
                        </div>
                    </li>
                    {if $smartblogEnabled}
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='SmartBlog pages:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-smartblog-pages-count"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='SmartBlog categories:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-smartblog-categories-count"></div>
                            </div>
                        </li>
                    {/if}
                    {if $prestablogEnabled}
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='PrestaBlog pages:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-prestablog-pages-count"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='PrestaBlog categories:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-prestablog-categories-count"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='PrestaBlog authors:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-prestablog-authors-count"></div>
                            </div>
                        </li>
                    {/if}
                    {if $simpleblogEnabled}
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='SimpleBlog pages:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-simpleblog-pages-count"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='SimpleBlog categories:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-simpleblog-categories-count"></div>
                            </div>
                        </li>
                    {/if}
                    {if $FAQEnabled}
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='FAQ pages:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-faq-pages-count"></div>
                            </div>
                        </li>
                        <li>
                            <div class="row">
                                <div class="col-sm-3">{l s='FAQ categories:' mod='arseopro'}</div>
                                <div class="col-sm-9" id="arseopro-sitemap-faq-categories-count"></div>
                            </div>
                        </li>
                    {/if}
                </ul>
                {if $multishop}
                <div class="btn-group">
                    <a href="#" onclick="arSEO.sitemap.generate({$currentShopId|intval}, 0, 0, 0, '{$currentShopSitemapToken|escape:'htmlall':'UTF-8'}'); return false;" class="btn btn-default">
                        <i class="icon-refresh"></i> {l s='Generate for current shop' mod='arseopro'}
                    </a>
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-caret-down"></i>&nbsp;
                    </button>
                    <ul class="dropdown-menu">
                        {foreach $shops as $shop}
                        <li>
                            <a href="#" onclick="arSEO.sitemap.generate({$shop.id_shop|intval}, 0, 0, 0, '{$shop.sitemap_token|escape:'htmlall':'UTF-8'}'); return false;">
                                <i class="icon-refresh"></i> {l s='Generate for ' mod='arseopro'} {$shop.name|escape:'htmlall':'UTF-8'}
                            </a>
                        </li>
                        {/foreach}
                    </ul>
                </div>
                {else}
                    <button class="btn btn-export btn-success" type="button" onclick="arSEO.sitemap.generate({$currentShopId|intval}, 0, 0, 0, '{$currentShopSitemapToken|escape:'htmlall':'UTF-8'}');">{l s='Generate' mod='arseopro'}</button>
                {/if}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading show-heading">
            <i class="icon-cog"></i> {l s='Cron' mod='arseopro'}
        </div>
        <div class="form-wrapper" style="font-size: 15px;">
            <div class="alert alert-info" style="margin-bottom: 0">
                {l s='Use this link to generate sitemap using cron:' mod='arseopro'}<br/>
                {foreach $shops as $shop}
                    <a href="{$shop.sitemapCronUrl nofilter}" target="_blank">{$shop.sitemapCronUrl nofilter}</a><br/>
                {/foreach}
            </div>
        </div>
    </div>
</div>