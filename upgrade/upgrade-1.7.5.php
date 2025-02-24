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

function upgrade_module_1_7_5($module)
{
    $return = true;
    $return = $return && $module->uninstallOverrides();
    $return = $return && $module->getInstaller()->prepareOverrides();
    $return = $return && $module->installOverrides();
    
    $sqls = array(
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_meta`
            COLLATE='utf8mb4_general_ci',
            CHANGE COLUMN `name` `name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `id_shop`,
            CHANGE COLUMN `rule_type` `rule_type` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `name`,
            CHANGE COLUMN `meta_title` `meta_title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `rule_type`,
            CHANGE COLUMN `meta_description` `meta_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `meta_title`,
            CHANGE COLUMN `meta_keywords` `meta_keywords` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `meta_description`,
            CHANGE COLUMN `fb_admins` `fb_admins` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `meta_keywords`,
            CHANGE COLUMN `fb_app` `fb_app` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `fb_admins`,
            CHANGE COLUMN `fb_title` `fb_title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `fb_app`,
            CHANGE COLUMN `fb_description` `fb_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `fb_title`,
            CHANGE COLUMN `fb_type` `fb_type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `fb_description`,
            CHANGE COLUMN `fb_custom_image` `fb_custom_image` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `fb_image`,
            CHANGE COLUMN `tw_type` `tw_type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `fb_custom_image`,
            CHANGE COLUMN `tw_account` `tw_account` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `tw_type`,
            CHANGE COLUMN `tw_title` `tw_title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `tw_account`,
            CHANGE COLUMN `tw_description` `tw_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `tw_title`,
            CHANGE COLUMN `tw_custom_image` `tw_custom_image` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `tw_image`,
            CHANGE COLUMN `tw_ch1` `tw_ch1` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `tw_custom_image`,
            CHANGE COLUMN `tw_ch2` `tw_ch2` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `tw_ch1`;",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_meta_rel`
            COLLATE='utf8mb4_general_ci',
            CHANGE COLUMN `rel_object` `rel_object` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `id_rule`;",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_redirect`
            COLLATE='utf8mb4_general_ci',
            CHANGE COLUMN `from` `from` VARCHAR(1024) NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `id_redirect`,
            CHANGE COLUMN `to` `to` TEXT NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `from`;",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_rule`
            COLLATE='utf8mb4_general_ci',
            CHANGE COLUMN `name` `name` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `id_shop`,
            CHANGE COLUMN `rule` `rule` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `name`;",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_rule_category`
            COLLATE='utf8mb4_general_ci';",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_sitemap_category`
            COLLATE='utf8mb4_general_ci';",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_sitemap_cms`
            COLLATE='utf8mb4_general_ci';",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_sitemap_manufacturer`
            COLLATE='utf8mb4_general_ci';",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_sitemap_meta`
            COLLATE='utf8mb4_general_ci';",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_sitemap_product`
            COLLATE='utf8mb4_general_ci';",
        "ALTER TABLE `" . _DB_PREFIX_ . "arseopro_sitemap_supplier`
            COLLATE='utf8mb4_general_ci';"
    );
    
    foreach ($sqls as $sql) {
        Db::getInstance()->execute($sql);
    }
    
    return $return;
}
