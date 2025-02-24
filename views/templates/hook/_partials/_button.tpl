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

<a href="{$href|escape:'htmlall':'UTF-8'}" onclick="{$onclick|escape:'htmlall':'UTF-8'}" title="{$title|escape:'htmlall':'UTF-8'}" class="{$class|escape:'htmlall':'UTF-8'}" {if $target}target="{$target|escape:'htmlall':'UTF-8'}"{/if}>
    <i class="{$icon|escape:'htmlall':'UTF-8'}"></i> {$title|escape:'htmlall':'UTF-8'}
</a>