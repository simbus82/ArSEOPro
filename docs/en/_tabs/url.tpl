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
<div class="tab-pane active" id="arseo-help-url-settings">
    <div class="panel">
        <p>
            In this section you can clean URL's (remove ids from URL's) on diffrent URL types (product, category, manufacturer, cms, etc...). You can configure url's separately for each URL type.
        </p>
        <div class="panel-group" role="tablist" id="arseo-help-url-accordion" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-product" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-product" class="collapsed">Product URL's</a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" role="tabpanel" id="arseo-help-url-accordion-product" aria-labelledby="headingOne" aria-expanded="false" style="">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can configure URL's for product page.
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-1.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-1.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <h4>Options:</h4>
                                <ul>
                                    <li>
                                        <code>Remove ID from product URL</code> - enable this option if you want to remove ID's from product URL's.
                                    </li>
                                    <li>
                                        <code>Include default category to URL</code> - if enabled product URL's should look like this: {$serverUrl|escape:'htmlall':'UTF-8'}/<b>default-category</b>/product-rewrite.html. 
                                        This option can't be used with <code>Include parent category to URL</code>.
                                    </li>
                                    <li>
                                        <code>Include parent category to URL</code> - if enabled product URL's should look like this: {$serverUrl|escape:'htmlall':'UTF-8'}/<b>parent-category</b>/<b>children-category</b>/product-rewrite.html.
                                        This option can't be used with <code>Include default category to URL</code>.
                                    </li>
                                    <li>
                                        <code>Redirect type if product not found</code> - if product not found (disabled or removed) system will try to redirect visitor chosen way:
                                        <ul>
                                            <li>
                                                <code>None</code> - redirects will not happen
                                            </li>
                                            <li>
                                                <code>Redirect to category</code> - visitor will be redirected to parent or default category page.
                                            </li>
                                            <li>
                                                <code>Redirect to page not found</code> - visitor will be redirected to {$serverUrl|escape:'htmlall':'UTF-8'}/<b>notfound</b> page.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <code>Disable old routes</code> - if this option is not enabled and visitor try to open old product URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/category/<b>123</b>-product-rewrite.html</i> then visitor will be automatically redirected to new product URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/category/product-rewrite.html</i>. 
                                        <b>Please disable old routes only if you have just started website and don't have visitors yet.</b>
                                    </li>
                                    <li>
                                        <code>Current Product URL scheme</code> - this option is read-only. It displays current product URL scheme. You can edit this scheme <a href="{$schemeUrl|escape:'htmlall':'UTF-8'}" target="_blank">"Preferences" -> "SEO & URLs" -> "Schema of URLs"</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingTwo">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-category" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-category">
                            Category URL's
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-category" aria-labelledby="headingTwo" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can configure URL's for category page.
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-2.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-2.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <h4>Options:</h4>
                                <ul>
                                    <li>
                                        <code>Remove ID from category URL</code> - enable this option if you want to remove ID's from category URL's.
                                    </li>
                                    <li>
                                        <code>Include parent category to URL</code> - if enabled category URL's should look like this: {$serverUrl|escape:'htmlall':'UTF-8'}/<b>parent-category</b>/<b>children-category</b>/category-rewrite/
                                    </li>
                                    <li>
                                        <code>Redirect type if product not found</code> - if category not found (disabled or removed) system will try to redirect visitor chosen way:
                                        <ul>
                                            <li>
                                                <code>None</code> - redirects will not happen
                                            </li>
                                            <li>
                                                <code>Redirect to parent category</code> - visitor will be redirected to parent category page.
                                            </li>
                                            <li>
                                                <code>Redirect to page not found</code> - visitor will be redirected to {$serverUrl|escape:'htmlall':'UTF-8'}/<b>notfound</b> page.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <code>Disable old routes</code> - if this option is not enabled and visitor try to open old category URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/<b>123</b>-category-rewrite</i> then visitor will be automatically redirected to new category URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/category-rewrite</i>. 
                                        <b>Please disable old routes only if you have just started website and don't have visitors yet.</b>
                                    </li>
                                    <li>
                                        <code>Current Category URL scheme</code> - this option is read-only. It displays current category URL scheme. You can edit this scheme <a href="{$schemeUrl|escape:'htmlall':'UTF-8'}" target="_blank">"Preferences" -> "SEO & URLs" -> "Schema of URLs"</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingThree">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-manufacturer" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-manufacturer">
                            Manufacturer URL's
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-manufacturer" aria-labelledby="headingThree" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can configure URL's for manufacturer page.
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-3.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-3.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <h4>Options:</h4>
                                <ul>
                                    <li>
                                        <code>Remove ID from manufacturer URL</code> - enable this option if you want to remove ID's from manufacturer URL's.
                                    </li>
                                    <li>
                                        <code>Redirect type if manufacturer not found</code> - if manufacturer not found (disabled or removed) system will try to redirect visitor chosen way:
                                        <ul>
                                            <li>
                                                <code>None</code> - redirects will not happen
                                            </li>
                                            <li>
                                                <code>Redirect to manufacturer list</code> - visitor will be redirected to manufacturer list page.
                                            </li>
                                            <li>
                                                <code>Redirect to page not found</code> - visitor will be redirected to {$serverUrl|escape:'htmlall':'UTF-8'}/<b>notfound</b> page.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <code>Disable old routes</code> - if this option is not enabled and visitor try to open old manufacturer URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/<b>123</b>_rewrite</i> then visitor will be automatically redirected to new manufacturer URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/manufacturer/rewrite.html</i>. 
                                        <b>Please disable old routes only if you have just started website and don't have visitors yet.</b>
                                    </li>
                                    <li>
                                        <code>Current Manufacturer URL scheme</code> - this option is read-only. It displays current manufacturer URL scheme. You can edit this scheme <a href="{$schemeUrl|escape:'htmlall':'UTF-8'}" target="_blank">"Preferences" -> "SEO & URLs" -> "Schema of URLs"</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFour">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-supplier" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-supplier">
                            Supplier URL's
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-supplier" aria-labelledby="headingFour" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can configure URL's for supplier page.
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-4.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-4.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <h4>Options:</h4>
                                <ul>
                                    <li>
                                        <code>Remove ID from supplier URL</code> - enable this option if you want to remove ID's from supplier URL's.
                                    </li>
                                    <li>
                                        <code>Redirect type if supplier not found</code> - if supplier not found (disabled or removed) system will try to redirect visitor chosen way:
                                        <ul>
                                            <li>
                                                <code>None</code> - redirects will not happen
                                            </li>
                                            <li>
                                                <code>Redirect to supplier list</code> - visitor will be redirected to supplier list page.
                                            </li>
                                            <li>
                                                <code>Redirect to page not found</code> - visitor will be redirected to {$serverUrl|escape:'htmlall':'UTF-8'}/<b>notfound</b> page.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <code>Disable old routes</code> - if this option is not enabled and visitor try to open old supplier URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/<b>123</b>__rewrite</i> then visitor will be automatically redirected to new supplier URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/supplier/rewrite.html</i>. 
                                        <b>Please disable old routes only if you have just started website and don't have visitors yet.</b>
                                    </li>
                                    <li>
                                        <code>Current supplier URL scheme</code> - this option is read-only. It displays current supplier URL scheme. You can edit this scheme <a href="{$schemeUrl|escape:'htmlall':'UTF-8'}" target="_blank">"Preferences" -> "SEO & URLs" -> "Schema of URLs"</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFour">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-cms" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-cms">
                            CMS URL's
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-cms" aria-labelledby="headingFour" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can configure URL's for CMS page.
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-5.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-5.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <h4>Options:</h4>
                                <ul>
                                    <li>
                                        <code>Remove ID from CMS URL</code> - enable this option if you want to remove ID's from CMS URL's.
                                    </li>
                                    <li>
                                        <code>Include parent category to URL</code> - if enabled CMS URL's should look like this: {$serverUrl|escape:'htmlall':'UTF-8'}/content/<b>parent-category</b>/<b>children-category</b>/page-rewrite.html
                                    </li>
                                    <li>
                                        <code>Redirect type if CMS page not found</code> - if CMS page not found (disabled or removed) system will try to redirect visitor chosen way:
                                        <ul>
                                            <li>
                                                <code>None</code> - redirects will not happen
                                            </li>
                                            <li>
                                                <code>Redirect to category</code> - visitor will be redirected to parent CMS category.
                                            </li>
                                            <li>
                                                <code>Redirect to page not found</code> - visitor will be redirected to {$serverUrl|escape:'htmlall':'UTF-8'}/<b>notfound</b> page.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <code>Disable old routes</code> - if this option is not enabled and visitor try to open old CMS URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/content/<b>123</b>-rewrite</i> then visitor will be automatically redirected to new CMS URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/content/rewrite.html</i>. 
                                        <b>Please disable old routes only if you have just started website and don't have visitors yet.</b>
                                    </li>
                                    <li>
                                        <code>Current CMS Page URL scheme</code> - this option is read-only. It displays current CMS URL scheme. You can edit this scheme <a href="{$schemeUrl|escape:'htmlall':'UTF-8'}" target="_blank">"Preferences" -> "SEO & URLs" -> "Schema of URLs"</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFour">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-cms-cat" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-cms-cat">
                            CMS Category URL's
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-cms-cat" aria-labelledby="headingFour" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can configure URL's for CMS category page.
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-6.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-6.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <h4>Options:</h4>
                                <ul>
                                    <li>
                                        <code>Remove ID from CMS category URL</code> - enable this option if you want to remove ID's from CMS category URL's.
                                    </li>
                                    <li>
                                        <code>Include parent category to URL</code> - if enabled CMS category URL's should look like this: {$serverUrl|escape:'htmlall':'UTF-8'}/content/<b>parent-category</b>/<b>children-category</b>/
                                    </li>
                                    <li>
                                        <code>Redirect type if CMS category not found</code> - if CMS category not found (disabled or removed) system will try to redirect visitor chosen way:
                                        <ul>
                                            <li>
                                                <code>None</code> - redirects will not happen
                                            </li>
                                            <li>
                                                <code>Redirect to category</code> - visitor will be redirected to parent CMS category.
                                            </li>
                                            <li>
                                                <code>Redirect to page not found</code> - visitor will be redirected to {$serverUrl|escape:'htmlall':'UTF-8'}/<b>notfound</b> page.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <code>Disable old routes</code> - if this option is not enabled and visitor try to open old CMS category URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/content/category/<b>123</b>-rewrite/</i> then visitor will be automatically redirected to new CMS category URL 
                                        <i>{$serverUrl|escape:'htmlall':'UTF-8'}/content/rewrite/</i>. 
                                        <b>Please disable old routes only if you have just started website and don't have visitors yet.</b>
                                    </li>
                                    <li>
                                        <code>Current CMS category Page URL scheme</code> - this option is read-only. It displays current CMS category URL scheme. You can edit this scheme <a href="{$schemeUrl|escape:'htmlall':'UTF-8'}" target="_blank">"Preferences" -> "SEO & URLs" -> "Schema of URLs"</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFive">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-duplication" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-duplication">
                            URL duplication
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-duplication" aria-labelledby="headingFive" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    In this tab you can find duplicate URL's
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-7.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-7.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <p>
                                    You need to fix all duplicates if any. You can use <code>Edit</code> button to change URL for each item.
                                </p>
                                <p>
                                    Also you can regenerate all product URL's using 
                                    <a  href="#" data-tab="#arseo-help-url-settings-tab" data-accordion="#arseo-help-url-accordion-generator" class="arseo-help-link">Product rewrite generator</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingSix">
                    <h4 class="panel-title">
                        <a href="#arseo-help-url-accordion-generator" class="collapsed" role="button" data-toggle="collapse" data-parent="#arseo-help-url-accordion" aria-expanded="false" aria-controls="arseo-help-url-accordion-generator">
                            Product rewrite generator
                        </a>
                    </h4>
                </div>
                <div class="collapse panel-collapse" role="tabpanel" id="arseo-help-url-accordion-generator" aria-labelledby="headingFix" aria-expanded="false">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p>
                                    Here you can create rules for product rewrite URL's 
                                </p>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-8.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-8.png" />
                                </a>
                                <a href="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-9.png" class="fancybox">
                                    <img class="img-responsive" src="{$path|escape:'htmlall':'UTF-8'}views/img/docs/{$lang|escape:'htmlall':'UTF-8'}/url-9.png" />
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <p>
                                    You can create custom friendly URL's for all products or products in some categories. 
                                </p>
                                <p>
                                    To create new URL rule please follow these steps:
                                </p>
                                <ol>
                                    <li>
                                        Click <code>Create new rule</code>
                                    </li>
                                    <li>
                                        Fill <code>Rule name</code> or leave nlank for auto-generated rule name
                                    </li>
                                    <li>
                                        Select <code>Language</code> for rule. You can choose one language or choose <code>All languages</code>. You can create diffrent URL rules for diffrent languages
                                    </li>
                                    <li>
                                        Select <code>Categories</code> to apply the friendly URL rule for. Choose <code>All categories</code> if you'd like to apply the same URL structure to all your products.
                                    </li>
                                    <li>
                                        Configure <code>URL rule</code> using variables you see bellow. All spaces between variables will be replaced by <code>-</code> symbol.
                                    </li>
                                    <li>
                                        Click <code>Save</code> to save rule and close window or click <code>Save and stay</code> if you want save rule and create another one.
                                    </li>
                                    <li>
                                        After you have created all needed rules, click <code>Apply all rules</code>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>