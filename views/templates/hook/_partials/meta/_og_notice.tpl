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
<div class="alert alert-warning">
    <span style="font-size: 15px">
        {l s='Your theme already contains opengraph tags for facebook in template [1]%s[/1].' mod='arseopro' tags=['<b>'] sprintf=[$relativePath]}
        {l s='These tags can not be overriden by this module.' mod='arseopro'}
        {if $fileWritable}
        {else}
            {l s='To use this section of module please remove following lines from your theme template:' mod='arseopro'}
            <ul class="arseo-file-content">
                <li><span class="arseo-line-nubmer">...</span></li>
                {foreach from=$lines key=k item=line}
                <li>
                    <span class="arseo-line-nubmer">{$k|intval}</span>
                    <span class="arseo-line">{$line|escape:'htmlall':'UTF-8'}</span>
                </li>
                {/foreach}
                <li><span class="arseo-line-nubmer">...</span></li>
            </ul>
        {/if}
    </span>
</div>