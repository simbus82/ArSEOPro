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
{if $faviconConfig->icon}
    <link rel="manifest" href="{$moduleUploadUrl|escape:'htmlall':'UTF-8'}{$manifestFileName|escape:'htmlall':'UTF-8'}">
    {foreach $faviconSizes as $size}
        <link rel="icon" type="image/png" href="{$moduleUploadUrl|escape:'htmlall':'UTF-8'}{$faviconConfig->getFavicon('filename')|escape:'htmlall':'UTF-8'}_{$size|escape:'htmlall':'UTF-8'}.{$faviconConfig->getFavicon('ext')|escape:'htmlall':'UTF-8'}" sizes="{$size|escape:'htmlall':'UTF-8'}">
    {/foreach}

    {foreach $appleTouchSizes as $size}
        <link rel="apple-touch-icon" sizes="{$size|escape:'htmlall':'UTF-8'}" href="{$moduleUploadUrl|escape:'htmlall':'UTF-8'}{$faviconConfig->getIOSFavicon('filename')|escape:'htmlall':'UTF-8'}_{$size|escape:'htmlall':'UTF-8'}.{$faviconConfig->getIOSFavicon('ext')|escape:'htmlall':'UTF-8'}">
    {/foreach}

    <meta name="msapplication-TileColor" content="{$faviconConfig->ms_tile_color|escape:'htmlall':'UTF-8'}">
    <meta name="msapplication-config" content="{$moduleUploadUrl|escape:'htmlall':'UTF-8'}ieconfig.xml" />
    
    <meta name="theme-color" content="{$faviconConfig->android_theme|escape:'htmlall':'UTF-8'}">
    
    {if $faviconConfig->mac_icon}
        <link rel="mask-icon" href="{$moduleUploadUrl|escape:'htmlall':'UTF-8'}{$faviconConfig->mac_icon|escape:'htmlall':'UTF-8'}" color="{$faviconConfig->mac_theme|escape:'htmlall':'UTF-8'}">
    {/if}
{/if}