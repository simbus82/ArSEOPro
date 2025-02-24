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

class ArSeoProSitemapCms extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_sitemap_cms';
    
    public $id_sitemap;
    public $id_cms;
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
            'id_cms' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
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
            `id_cms` INT(11) UNSIGNED NOT NULL,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `export` TINYINT(1) UNSIGNED NOT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_sitemap`),
            INDEX `id_cms` (`id_cms`),
            INDEX `id_shop` (`id_shop`),
            INDEX `export` (`export`)
        )
        COLLATE='utf8_general_ci'";
        
        return Db::getInstance()->execute($sql);
    }
    
    public static function getCount($params = array())
    {
        $query = new DbQuery();
        $query->from('cms', 'c');
        $query->select('COUNT(1) c');
        $query->join('LEFT JOIN ' . self::getTableName() . ' sc ON sc.id_cms = c.id_cms');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` cl ON cl.id_cms = c.id_cms');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'cms_shop` cs ON cs.id_cms = c.id_cms');
        $filters = isset($params['filter'])? $params['filter'] : array();
        if (ArSeoProTools::isColumnExists(_DB_PREFIX_ . 'cms_lang', 'id_shop')) {
            $where = self::processFilters($filters, array(
                'cl.id_lang = ' . (int)Context::getContext()->language->id,
                'cs.id_shop = ' . (int)Context::getContext()->shop->id,
                'cl.id_shop = ' . (int)Context::getContext()->shop->id
            ));
        } else {
            $where = self::processFilters($filters, array(
                'cl.id_lang = ' . (int)Context::getContext()->language->id,
                'cs.id_shop = ' . (int)Context::getContext()->shop->id
            ));
        }
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
        $query->from('cms', 'c');
        $query->limit($pageSize, $offset);
        $query->join('LEFT JOIN ' . self::getTableName() . ' sc ON sc.id_cms = c.id_cms');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` cl ON cl.id_cms = c.id_cms');
        $query->join('LEFT JOIN `' . _DB_PREFIX_ . 'cms_shop` cs ON cs.id_cms = c.id_cms');
        $query->select('c.id_cms, cl.meta_title, cs.id_shop, IF(sc.export IS NOT NULL, sc.export, 0) AS export, sc.id_sitemap');
        $query->orderBy('c.id_cms ASC');
        
        $filters = isset($params['filter'])? $params['filter'] : array();
        
        if (ArSeoProTools::isColumnExists(_DB_PREFIX_ . 'cms_lang', 'id_shop')) {
            $where = self::processFilters($filters, array(
                'cl.id_lang = ' . (int)Context::getContext()->language->id,
                'cs.id_shop = ' . (int)Context::getContext()->shop->id,
                'cl.id_shop = ' . (int)Context::getContext()->shop->id
            ));
        } else {
            $where = self::processFilters($filters, array(
                'cl.id_lang = ' . (int)Context::getContext()->language->id,
                'cs.id_shop = ' . (int)Context::getContext()->shop->id
            ));
        }
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
                    $k = 'id_cms';
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
            } elseif (in_array($k, array('id_cms', 'name'))) {
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
