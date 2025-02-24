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

function upgrade_module_1_3_2($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'arseopro_redirect`
	ADD COLUMN `use_times` INT UNSIGNED NULL DEFAULT "0" AFTER `selected`,
	ADD COLUMN `create_type` TINYINT UNSIGNED NULL DEFAULT "0" AFTER `use_times`,
	ADD COLUMN `last_used_at` DATETIME NULL AFTER `created_at`';
    Db::getInstance()->execute($sql);
    
    Configuration::updateValue('ARSEO_REDIRECTS_LOG', 1);
    
    return true;
}
