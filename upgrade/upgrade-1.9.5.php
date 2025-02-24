<?php
/**
* 2012-2023 Areama
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

function upgrade_module_1_9_5($module)
{
    $return = true;

    $qs = [
        'ALTER TABLE `' . _DB_PREFIX_ . 'product_lang` ADD INDEX `link_rewrite` (`link_rewrite`);',
        'ALTER TABLE `' . _DB_PREFIX_ . 'category_lang` ADD INDEX `link_rewrite` (`link_rewrite`);',
        'ALTER TABLE `' . _DB_PREFIX_ . 'manufacturer` ADD INDEX `name` (`name`);'
    ];

    foreach ($qs as $q) {
        try{
            Db::getInstance()->execute($q);
        } catch (\Exception $e) {
            $module->getLogger()->log('Error executing query "' . $q . '"');
        }
    }

    $module->getLogger()->log('Upgrade to 1.9.5');
    
    $res = $module->uninstallOverrides();
    $module->getLogger()->log('Uninstall overrides: ' . (int)$res);
    $return = $return && $res;
    
    $res = $module->getInstaller()->prepareOverrides();
    $module->getLogger()->log('Preparing overrides: ' . (int)$res);
    $return = $return && $res;
    
    $res = $module->installOverrides();
    $module->getLogger()->log('Install overrides: ' . (int)$res);
    $return = $return && $res;
    
    $module->getLogger()->log('Upgrade complete' . PHP_EOL);
    return $return;
}
