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

class ArSeoProSitemapCategory extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_sitemap_category';
    
    public $id_sitemap;
    public $id_category;
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
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
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
            `id_category` INT(11) UNSIGNED NOT NULL,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `export` TINYINT(1) UNSIGNED NOT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_sitemap`),
            INDEX `id_category` (`id_category`),
            INDEX `id_shop` (`id_shop`),
            INDEX `export` (`export`)
        )
        COLLATE='utf8_general_ci'";
        
        return Db::getInstance()->execute($sql);
    }
    
    public static function getCount($params = array())
    {
        $query = new DbQuery();
        $query->from('category', 'c');
        $query->select('COUNT(1) c');
        $query->join('LEFT JOIN ' . self::getTableName() . ' sc ON sc.id_category = c.id_category');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON cl.id_category = c.id_category');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            'cl.id_lang = ' . (int)Context::getContext()->language->id,
            'cs.id_shop = ' . (int)Context::getContext()->shop->id,
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
        $query->from('category', 'c');
        $query->limit($pageSize, $offset);
        $query->join('LEFT JOIN ' . self::getTableName() . ' sc ON sc.id_category = c.id_category');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON cl.id_category = c.id_category');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category');
        $query->select('c.id_category, cl.name, c.reference, IF(sc.export IS NOT NULL, sc.export, 0) AS export, sc.id_sitemap');
        $query->orderBy('c.id_category ASC');
        $filters = isset($params['filter'])? $params['filter'] : array();
        $where = self::processFilters($filters, array(
            'cl.id_lang = ' . (int)Context::getContext()->language->id,
            'cs.id_shop = ' . (int)Context::getContext()->shop->id,
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
                    $k = 'id_category';
                    if (strpos($value, '%') !== false) {
                        $where[] = "c.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "c.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
                if ($k == 'export') {
                    if ($value) {
                        $where[] = "sc.`" . $k . "` = '" . pSQL($value) . "'";
                    } else {
                        $where[] = "(sc.`" . $k . "` IS NULL OR sc.`" . $k . "` = 0)";
                    }
                }
            } elseif (in_array($k, array('id_category', 'name'))) {
                if (!empty($value)) {
                    if (strpos($value, '%') !== false) {
                        $where[] = "cl.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "cl.`" . $k . "` = '" . pSQL($value) . "'";
                    }
                }
            } elseif (in_array($k, array('reference'))) {
                if (!empty($value)) {
                    if (strpos($value, '%') !== false) {
                        $where[] = "c.`" . $k . "` LIKE '" . pSQL($value) . "'";
                    } else {
                        $where[] = "c.`" . $k . "` = '" . pSQL($value) . "'";
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
