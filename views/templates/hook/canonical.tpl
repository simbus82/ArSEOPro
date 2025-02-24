{*
* 2022 Areama
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
*  @copyright  2022 Areama
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*}

<!-- arseopro canonical -->
{if isset($page.canonical_seo)}
    <link rel="canonical" href="{$page.canonical_seo|escape:'htmlall':'UTF-8'}" />
{/if}

{if $page_name != 'category' && $page_name != 'product' && $page_name != 'cms' && $otherActive}
    <link rel="canonical" href="{$canonicalURL|escape:'htmlall':'UTF-8'}" />
{/if}
{if $page_name == 'product' and $productActive}
    <link rel="canonical" href="{$canonicalURL|escape:'htmlall':'UTF-8'}" />
{/if}
{if $page_name == 'cms' and $cmsActive} 
    <link rel="canonical" href="{$canonicalURL|escape:'htmlall':'UTF-8'}" />
{/if}
{if $page_name == 'category' and $categoryActive}
    {if $primero == 'NO'}
        {if $canonicalURLPrev != $canonicalURL}
            {if $actual != 1}
                <link rel="prev" href="{$canonicalURLPrev|escape:'htmlall':'UTF-8'}" />
            {/if}
        {/if}
    {/if}
    {if $actual < ($max)}
        <link rel="next" href="{$canonicalURLNext|escape:'htmlall':'UTF-8'}" />
    {/if}
{/if}
<!-- /arseopro canonical -->
















