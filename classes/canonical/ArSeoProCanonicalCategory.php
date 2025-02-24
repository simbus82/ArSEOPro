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

include_once dirname(__FILE__).'/../ArSeoProTableAbstract.php';

class ArSeoProCanonicalCategory extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_canonical_category';
    
    public static function addCanonicalURL($id_category, $url, $active, $id_lang)
    {
        Db::getInstance()->insert(self::getTableName(false), array(
            'id_category' => (int)$id_category,
            'url' => pSQL($url),
            'active' => (int)$active,
            'id_lang' => (int)$id_lang,
            'id_shop' => (int)Context::getContext()->shop->id
        ));
    }
    
    public static function deleteCanonicalURL($id_category)
    {
        return Db::getInstance()->execute('DELETE FROM `' . self::getTableName() . '` WHERE `id_category`=' . (int) $id_category . ' AND id_shop = '. (int)Context::getContext()->shop->id);
    }
    
    public static function getCanonicalURL($id_category)
    {
        return Db::getInstance()->executeS('SELECT * FROM `' . self::getTableName() . '` WHERE `id_category`=' . (int) $id_category . ' AND `id_shop` = '.(int)Context::getContext()->shop->id);
    }

    public static function getCanonicalURLByIdLang($id_category, $id_lang)
    {
        return Db::getInstance()->getRow('SELECT * FROM `' . self::getTableName() . '` WHERE `id_category`=' . (int) $id_category . ' AND `id_lang`=' . (int) $id_lang . ' AND `id_shop` = '.(int)Context::getContext()->shop->id);
    }
    
    public static function installTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getTableName() . "` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `id_category` INT(10) NOT NULL,
            `url` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
            `active` INT(3) NULL DEFAULT NULL,
            `id_lang` INT(3) NOT NULL,
            `id_shop` INT(3) NULL DEFAULT NULL,
            PRIMARY KEY (`id`, `id_lang`) USING BTREE
        )
        COLLATE='utf8_general_ci';";
        
        return Db::getInstance()->execute($sql);
    }
    
    public static function uninstallTable()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . self::getTableName() . '`');
    }
    
    public static function getTableName($withPrefix = true)
    {
        return $withPrefix? (_DB_PREFIX_ . self::TABLE_NAME) : (self::TABLE_NAME);
    }
}
