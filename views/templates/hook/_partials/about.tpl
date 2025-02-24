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

<div class="arseopro-config-panel hidden" id="arseopro-about" style="font-size: 15px">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-info"></i> {l s='About' mod='arseopro'}
        </div>
        <div class="form-wrapper text-center">
            <p>
                <a href="https://addons.prestashop.com/en/product.php?id_product={$moduleId|intval}" target="_blank">
                    <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/logo.png" alt="Areama" />
                </a>
            </p>
            <h2>
                {$name|escape:'htmlall':'UTF-8'}
            </h2>
            <p class="text-muted">
                {l s='Version' mod='arseopro'} {$version|escape:'htmlall':'UTF-8'}
            </p>
            <p>
                {l s='Improve your SEO all the way! Clean URLs, generate meta tags, setup redirects and much more!!!' mod='arseopro'} 
            </p>
            <p>
                {l s='We hope you would find this module useful and would have 1 minute to [1]give us excellent rating[/1], this encourage our support and developers.' mod='arseopro' tags=['<a href="https://addons.prestashop.com/en/ratings.php" target="_blank">']}
            </p>
            <p class="text-center" style="">
                <a href="https://addons.prestashop.com/en/ratings.php" target="_blank">
                    <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/5-stars.png" alt="5 stars" />
                </a>
            </p>
            <p>
                {l s='If you have any questions or suggestions about this module, please' mod='arseopro'} <a href="https://addons.prestashop.com/en/contact-us?id_product={$moduleId|intval}" target="_blank">{l s='contact us' mod='arseopro'}</a>.
            </p>
            <p>
                {l s='Also please checkout our other modules that can help improve your store and increase sales!' mod='arseopro'}<br/>
                <a target="_blank" href="https://addons.prestashop.com/en/2_community-developer?contributor={$authorId|intval}">{l s='View all our modules' mod='arseopro'} >>></a>
            </p>
        </div>
    </div>
</div>