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
<meta http-equiv="x-dns-prefetch-control" value="on" />
<meta name="HandheldFriendly" content="true" />
<meta name="apple-mobile-web-app-capable" content="yes" />
{if $metaData && $metaData->meta_title}
    <meta name="apple-mobile-web-app-title" content="{$metaData->meta_title|escape:'html':'UTF-8'}">
{/if}
<link rel="dns-prefetch" href="//www.google-analytics.com" />
<link rel="dns-prefetch" href="//twitter.com" />
<link rel="dns-prefetch" href="//facebook.com" />
<link rel="dns-prefetch" href="//apis.google.com" />
<link rel="dns-prefetch" href="//fonts.googleapis.com" />
<link rel="dns-prefetch" href="//ssl.gstatic.com" />
<link rel="dns-prefetch" href="//{$domain|escape:'htmlall':'UTF-8'}" />

<meta property="og:type" content="website" />
<meta property="og:url" content="{$url nofilter}" />
<meta property="og:site_name" content="{$sitename|escape:'htmlall':'UTF-8'}" />

{if $metaData}
    {if $metaData->fb_admins}
        <meta property="fb:admins" content="{$metaData->fb_admins|escape:'htmlall':'UTF-8'}" />
    {/if}
    {if $metaData->fb_app}
        <meta property="fb:app_id" content="{$metaData->fb_app|escape:'htmlall':'UTF-8'}" />
    {/if}
    {if $metaData->fb_image == 3 and $metaData->fb_custom_image}
        <meta property="og:image" content="{$metaData->fb_custom_image|escape:'htmlall':'UTF-8'}" />
    {elseif $metaData->fb_image == 1 and $metaData->fbImageUrl}
        <meta property="og:image" content="{$metaData->fbImageUrl|escape:'htmlall':'UTF-8'}" />
    {elseif $metaData->fb_image == 2 and $metaData->fbImageUrl}
        {foreach $metaData->fbImageUrl as $img}
            <meta property="og:image" content="{$img|escape:'htmlall':'UTF-8'}" />
        {/foreach}
    {/if}
    {if $metaData->fb_title}
        <meta property="og:title" content="{$metaData->fb_title|escape:'htmlall':'UTF-8'}" />
    {/if}
    {if $metaData->fb_description}
        <meta property="og:description" content="{$metaData->fb_description|escape:'htmlall':'UTF-8'}" />
    {/if}

    {if $metaData->tw_type}
        <meta property="twitter:card" content="{$metaData->tw_type|escape:'htmlall':'UTF-8'}" />
        {if $metaData->tw_title}
        <meta property="twitter:title" content="{$metaData->tw_title|escape:'htmlall':'UTF-8'}" />
        {/if}
        {if $metaData->tw_description}
            <meta property="twitter:description" content="{$metaData->tw_description|escape:'htmlall':'UTF-8'}" />
        {/if}
        {if $metaData->tw_account}
            <meta property="twitter:site" content="{$metaData->tw_account|escape:'htmlall':'UTF-8'}" />
        {/if}
        {if $metaData->tw_type == 'player'}
            <meta property="twitter:player" content="{$metaData->tw_video_url|escape:'htmlall':'UTF-8'}" />
            <meta property="twitter:player:width" content="{$metaData->tw_video_width|escape:'htmlall':'UTF-8'}" />
            <meta property="twitter:player:height" content="{$metaData->tw_video_height|escape:'htmlall':'UTF-8'}" />
        {/if}
        {if $metaData->tw_image == 3 and $metaData->tw_custom_image}
            <meta property="twitter:image" content="{$metaData->tw_custom_image|escape:'htmlall':'UTF-8'}" />
        {elseif $metaData->tw_image == 1 and $metaData->twImageUrl}
            <meta property="twitter:image" content="{$metaData->twImageUrl|escape:'htmlall':'UTF-8'}" />
        {elseif $metaData->fb_image == 2 and $metaData->twImageUrl}
            {foreach $metaData->twImageUrl as $img}
                <meta property="twitter:image" content="{$img|escape:'htmlall':'UTF-8'}" />
            {/foreach}
        {/if}
    {/if}
{/if}
