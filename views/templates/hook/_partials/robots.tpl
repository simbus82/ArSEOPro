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
<div class="arseopro-config-panel hidden" id="arseopro-robots">
    <form method="POST" id="arseopro-robots-form" onsubmit="arSEO.robots.save(); return false;" class="defaultForm form-horizontal">
        <div class="panel">
            <div class="panel-heading show-heading">
                <i class="icon-code"></i> {l s='Robots.txt Editor' mod='arseopro'}
                <span class="panel-heading-action">
                    <a class="list-toolbar-btn" onclick="arSEO.robots.defaults(); return false;" href="#">
                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Load defaults" data-html="true" data-placement="top">
                            <i class="process-icon-reset"></i>
                        </span>
                    </a>
                    <a class="list-toolbar-btn" onclick="arSEO.robots.reload(true); return false;" href="#">
                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Refresh" data-html="true" data-placement="top">
                            <i class="process-icon-refresh"></i>
                        </span>
                    </a>
                </span>
            </div>
            <div class="form-wrapper">
                <div style="font-size: 15px">
                    <p>
                        <b>robots.txt</b> {l s='is a text file you put on your site to tell search robots which pages you would like them not to visit.' mod='arseopro'}
                    </p>
                    <p>
                        {l s='This file will be ignored unless it is at the root of your host:' mod='arseopro'}<br/>
                        <span class="label label-success">{l s='Used:' mod='arseopro'}</span> <a href="{$serverUrl|escape:'htmlall':'UTF-8'}/robots.txt" target="_blank">{$serverUrl|escape:'htmlall':'UTF-8'}/robots.txt</a><br/>
                        <span class="label label-danger">{l s='Ignored:' mod='arseopro'}</span>  {$serverUrl|escape:'htmlall':'UTF-8'}/subfolder/robots.txt
                    </p>
                    <p>
                        {l s='For more information about the robots.txt standard, see:' mod='arseopro'}<br/>
                        <a href="http://www.robotstxt.org/robotstxt.html" target="_blank">http://www.robotstxt.org/robotstxt.html</a><br/>
                        <a href="https://developers.google.com/search/reference/robots_txt?hl=en" target="_blank">https://developers.google.com/search/reference/robots_txt?hl=en</a>
                    </p>
                </div>
                <div class="form-group">
                    <textarea name="robots" rows="20"></textarea>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" value="1" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Save' mod='arseopro'}
                </button>
                <button type="button" value="1" class="btn btn-default pull-right" onclick="arSEO.robots.reload(true);">
                    <i class="process-icon-reset"></i> {l s='Cancel' mod='arseopro'}
                </button>
            </div>
        </div>
    </form>
</div>