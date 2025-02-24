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

include_once dirname(__FILE__).'/../classes/ArSeoProCanonical.php';
include_once dirname(__FILE__).'/../classes/canonical/ArSeoProCanonicalProduct.php';
include_once dirname(__FILE__).'/../classes/canonical/ArSeoProCanonicalCategory.php';

function upgrade_module_1_8_1($module)
{
    $return = true;
    $return = $return && $module->uninstallOverrides();
    $return = $return && $module->getInstaller()->prepareOverrides();
    $return = $return && $module->installOverrides();
    
    $config = new ArSeoProCanonical($module, 'arscc_');
    $config->loadDefaults();
    $config->saveToConfig(false);
    
    $module->registerHook('actionProductUpdate');
    $module->registerHook('actionCategoryUpdate');
    $module->registerHook('displayAdminProductsSeoStepBottom');
    $module->registerHook('displayBackOfficeCategory');
    $module->registerHook('actionObjectProductUpdateBefore');
    
    if (Configuration::get('ARSEO_REDIRECTS')) {
        Configuration::updateValue('ARSR_ENABLE', 1);
    }
    if (Configuration::get('ARSEO_REDIRECTS_LOG')) {
        Configuration::updateValue('ARSR_LOG', 1);
    }
    
    $return = $return && ArSeoProCanonicalProduct::installTable();
    $return = $return && ArSeoProCanonicalCategory::installTable();
    
    return $return;
}
