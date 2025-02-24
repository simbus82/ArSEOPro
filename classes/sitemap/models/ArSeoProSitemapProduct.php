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

class ArSeoProSitemapProduct extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_sitemap_product';
    
    public $id_sitemap;
    public $id_product;
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
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
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
            `id_product` INT(11) UNSIGNED NOT NULL,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `export` TINYINT(1) UNSIGNED NOT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_sitemap`),
            INDEX `id_product` (`id_product`),
            INDEX `id_shop` (`id_shop`),
            INDEX `export` (`export`)
        )
        COLLATE='utf8_general_ci'";
        
        return Db::getInstance()->execute($sql);
    }
    
    public static function getCount($params = array())
    {
        $query = new DbQuery();
        $query->from('product', 'p');
        $query->select('COUNT(1) c');
        $query->join('LEFT JOIN ' . self::getTableName() . ' sp ON sp.id_product = p.id_product');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.id_product = p.id_product');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            'pl.id_lang = ' . (int)Context::getContext()->language->id,
            'pl.id_shop = ' . (int)Context::getContext()->shop->id,
            'ps.id_shop = ' . (int)Context::getContext()->shop->id,
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
        $query->from('product', 'p');
        $query->limit($pageSize, $offset);
        $query->join('LEFT JOIN ' . self::getTableName() . ' sp ON sp.id_product = p.id_product');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.id_product = p.id_product');
        $query->select('p.id_product, pl.name, pl.id_shop, p.reference, IF(sp.export IS NOT NULL, sp.export, 0) AS export, sp.id_sitemap');
        $query->orderBy('p.id_product ASC');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            'pl.id_lang = ' . (int)Context::getContext()->language->id,
            'pl.id_shop = ' . (int)Context::getContext()->shop->id,
            'ps.id_shop = ' . (int)Context::getContext()->shop->id,
        ));
        $query->where($where);
        return Db::getInstance()->executeS($query);
    }
    
    public static function processFilters($params, $initParams = array())
    {
        $where = $initParams;
        $model = new self();
        foreach ($params as $k => $value) {
            if (property_exists($model, $k) && $value != '') {
                if ($k == 'id') {
                    $k = 'id_product';
                    if (strpos($value, '%') !== false) {
                        $where[] = "p.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "p.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
                if ($k == 'export') {
                    if ($value) {
                        $where[] = "sp.`" . $k . "` = '" . pSQL($value) . "'";
                    } else {
                        $where[] = "(sp.`" . $k . "` IS NULL OR sp.`" . $k . "` = 0)";
                    }
                }
            } elseif (in_array($k, array('id_product', 'name'))) {
                if (!empty($value)) {
                    if (strpos($value, '%') !== false) {
                        $where[] = "pl.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "pl.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
            } elseif (in_array($k, array('reference'))) {
                if (!empty($value)) {
                    if (strpos($value, '%') !== false) {
                        $where[] = "p.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "p.`" . $k . "` = '" . pSQL($value) . "'";
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
