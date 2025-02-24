<?php
/**
* 2012-2018 Areama
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
*  @author    Areama <contact@areama.net>
*  @copyright 2018 Areama
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__).'/../classes/jsonld/ArSeoProJsonLDGeneral.php';
include_once dirname(__FILE__).'/../classes/jsonld/ArSeoProJsonLDProduct.php';
include_once dirname(__FILE__).'/../classes/jsonld/ArSeoProJsonLDAdvanced.php';

function upgrade_module_1_6_2($module)
{
    $config = new ArSeoProJsonLDGeneral($module);
    $config->loadDefaults();
    $config->saveToConfig();
    
    $config = new ArSeoProJsonLDProduct($module);
    $config->loadDefaults();
    $config->saveToConfig();
    
    $config = new ArSeoProJsonLDAdvanced($module);
    $config->loadDefaults();
    $config->saveToConfig();
    
    $module->registerHook('displayBeforeBodyClosingTag');
    return true;
}
