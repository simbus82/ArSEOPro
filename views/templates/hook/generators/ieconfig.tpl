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
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
    <msapplication>
        <tile>
            {foreach $msTileSizes as $size}
                <square{$size|escape:'htmlall':'UTF-8'}logo src="{$moduleUploadUrl|escape:'htmlall':'UTF-8'}{$faviconFilename|escape:'htmlall':'UTF-8'}_{$size|escape:'htmlall':'UTF-8'}.{$faviconExt|escape:'htmlall':'UTF-8'}"/>
            {/foreach}
            <TileColor>{$metaConfig->ms_tile_color|escape:'htmlall':'UTF-8'}</TileColor>
        </tile>
    </msapplication>
</browserconfig>