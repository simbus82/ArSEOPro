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
<div class="row keywords-select-row" id="{$target|escape:'htmlall':'UTF-8'}-keyword-selector">
    <div class="col-sm-12">
        <ul class="list-unstyled keywords-select" data-target="{$target|escape:'htmlall':'UTF-8'}">
            {foreach from=$keywords item=title key=k}
                <li>
                    <a title="{$title|escape:'htmlall':'UTF-8'}" href="#" data-keyword="{$k|escape:'htmlall':'UTF-8'}">{literal}{{/literal}{$k|escape:'htmlall':'UTF-8'}{literal}}{/literal}</a> - {$title|escape:'htmlall':'UTF-8'}
                </li>
            {/foreach}
        </ul>
    </div>
</div>