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
<div class="form-control" id="arseo_{$ruleId|escape:'htmlall':'UTF-8'}" readonly="">
    {$schema|escape:'htmlall':'UTF-8'}
</div>
<p class="help-block">
    {if $is16}
        {l s='You can edit scheme in [1]"Preferences" -> "SEO & URLs" -> "Schema of URLs"[/1] panel! ' mod='arseopro' tags=["<a href='{$link}' target='_blank'>"]}
    {elseif $is174}
        {l s='You can edit scheme in [1]"Preferences" -> "URLs"[/1] panel! ' mod='arseopro' tags=["<a href='{$link}' target='_blank'>"]}
    {else}
        {l s='You can edit scheme in [1]"Shop parameters" -> "Traffic & SEO"[/1] panel! ' mod='arseopro' tags=["<a href='{$link}' target='_blank'>"]}
    {/if}
</p>