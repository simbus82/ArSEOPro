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
<div class="arseopro-config-panel hidden" id="arseopro-meta">
    <div class="panel">
        <div class="panel-heading show-heading">
            <i class="icon-cog"></i> {l s='Meta tags rules' mod='arseopro'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" onclick="arSEO.meta.reload(); return false;" href="#">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Refresh list" data-html="true" data-placement="top">
                        <i class="process-icon-refresh"></i>
                    </span>
                </a>
                <a class="list-toolbar-btn" onclick="arSEO.meta.clear(); return false;" href="#">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Clear list" data-html="true" data-placement="top">
                        <i class="process-icon-trash icon-trash"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="form-wrapper">
            <div id="form-meta-list-container">
                <div id="form-meta-list" class="arseo-placeholder">
                    <input type="hidden" name="page" value="1" />
                </div>
            </div>
            <div class="text-right">
                <div class="btn-group">
                    <a href="#" onclick="arSEO.meta.newRule('product'); return false;" title="{l s='New rule' mod='arseopro'}" class="edit btn btn-default">
                        <i class="icon-plus"></i> {l s='New product rule' mod='arseopro'}
                    </a>
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-caret-down"></i>&nbsp;
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" onclick="arSEO.meta.newRule('category'); return false;" title="{l s='New category rule' mod='arseopro'}">
                                <i class="icon-plus"></i> {l s='New category rule' mod='arseopro'}
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="arSEO.meta.newRule('metapage'); return false;" title="{l s='New meta-page rule' mod='arseopro'}">
                                <i class="icon-plus"></i> {l s='New meta-page rule' mod='arseopro'}
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="arSEO.meta.newRule('brand'); return false;" title="{l s='New manufacturer page rule' mod='arseopro'}">
                                <i class="icon-plus"></i> {l s='New manufacturer page rule' mod='arseopro'}
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn btn-primary" onclick="arSEO.meta.applyRule(0, 0, 0, 1)">
                    {l s='Apply all rules' mod='arseopro'}
                </button>
                <button type="button" class="btn btn-default" onclick="arSEO.meta.clear()">
                    <i class="icon icon-trash"></i> {l s='Clear meta rules list' mod='arseopro'}
                </button>
            </div>
        </div>
    </div>
    {include file="./meta/_modal.tpl"}
</div>