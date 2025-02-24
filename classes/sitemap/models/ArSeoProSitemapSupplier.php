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

include_once dirname(__FILE__).'/../../ArSeoProTableAbstract.php';

class ArSeoProSitemapSupplier extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_sitemap_supplier';
    
    public $id_sitemap;
    public $id_supplier;
    public $id_shop;
    public $export;
    public $updated_at;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_sitemap',
        'multilang' => false,
        'fields' => array(
            'id_sitemap' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_supplier' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'export' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'updated_at' =>         array('type' => self::TYPE_STRING)
        ),
    );
    
    public static function getTableName($withPrefix = true)
    {
        return $withPrefix? (_DB_PREFIX_ . self::TABLE_NAME) : self::TABLE_NAME;
    }
    
    public static function uninstallTable()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . self::getTableName() . '`');
    }
    
    public static function installTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getTableName() . "` (
            `id_sitemap` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_supplier` INT(11) UNSIGNED NOT NULL,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `export` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_sitemap`),
            INDEX `id_supplier` (`id_supplier`),
            INDEX `id_shop` (`id_shop`),
            INDEX `export` (`export`)
        )
        COLLATE='utf8_general_ci'";
        
        return Db::getInstance()->execute($sql);
    }
    
    public static function getCount($params = array())
    {
        $query = new DbQuery();
        $query->from('supplier', 's');
        $query->select('COUNT(1) c');
        $query->join('LEFT JOIN ' . self::getTableName() . ' ss ON ss.id_supplier = s.id_supplier');
        $query->join('LEFT JOIN ' . _DB_PREFIX_ . 'supplier_shop sh ON sh.id_supplier = s.id_supplier');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            'sh.id_shop = ' . (int)Context::getContext()->shop->id
        ));
        $query->where($where);
        $res = Db::getInstance()->getRow($query);
        return $res['c'];
    }
    
    public static function getAll($params = array())
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 50;
        $page = isset($params['page'])? $params['page'] - 1 : 0;
        $offset = isset($params['page'])? $pageSize * $page : 0;
        $query = new DbQuery();
        $query->from('supplier', 's');
        $query->limit($pageSize, $offset);
        $query->join('LEFT JOIN ' . self::getTableName() . ' ss ON ss.id_supplier = s.id_supplier');
        $query->join('LEFT JOIN ' . _DB_PREFIX_ . 'supplier_shop sh ON sh.id_supplier = s.id_supplier');
        $query->select('s.id_supplier, s.name, s.id_supplier, sh.id_shop, IF(ss.export IS NOT NULL, ss.export, 0) AS export, ss.id_sitemap');
        $query->orderBy('s.id_supplier ASC');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            'sh.id_shop = ' . (int)Context::getContext()->shop->id
        ));
        $query->where($where);
        return Db::getInstance()->executeS($query);
    }
    
    public static function processFilters($params, $initPrams = array())
    {
        $where = $initPrams;
        $model = new self();
        foreach ($params as $k => $value) {
            if (property_exists($model, $k) && $value != '') {
                if ($k == 'id') {
                    $k = 'id_supplier';
                    if (strpos($value, '%') !== false) {
                        $where[] = "s.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "s.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
                if ($k == 'export') {
                    if ($value) {
                        $where[] = "ss.`" . $k . "` = '" . pSQL($value) . "'";
                    } else {
                        $where[] = "(ss.`" . $k . "` IS NULL OR ss.`" . $k . "` = 0)";
                    }
                }
            } elseif (in_array($k, array('id_supplier', 'name'))) {
                if (!empty($value)) {
                    if (strpos($value, '%') !== false) {
                        $where[] = "s.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "s.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
            }
        }
        return implode(' AND ', $where);
    }
    
    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE `' . self::getTableName() . '`');
    }
}
