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

class ArSeoProRuleTable extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_rule';
    const REL_TABLE_NAME = 'arseopro_rule_category';
    
    public $id;
    public $id_lang;
    public $id_shop;
    public $name;
    public $rule;
    public $status;
    public $created_at;
    public $updated_at;
    public $last_applied_at;
    
    public $id_category;
    public $categories;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_rule',
        'multilang' => false,
        'fields' => array(
            'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'name' =>               array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'rule' =>               array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'status' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'created_at' =>         array('type' => self::TYPE_STRING),
            'updated_at' =>         array('type' => self::TYPE_STRING),
            'last_applied_at' =>         array('type' => self::TYPE_STRING),
        ),
    );
    
    public static function uninstallTable()
    {
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . self::getTableName() . '`');
        return $res && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . self::getTableName(true) . '`');
    }
    
    public static function installTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getTableName() . "` (
            `id_rule` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lang` INT(11) UNSIGNED NOT NULL,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `rule` VARCHAR(255) NOT NULL,
            `status` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
            `created_at` DATETIME NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            `last_applied_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_rule`),
            INDEX `id_lang` (`id_lang`),
            INDEX `id_shop` (`id_shop`)
        )
        COLLATE='utf8_general_ci'";
        
        $res = Db::getInstance()->execute($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getTableName(true) . "` (
            `id_rule` INT(10) UNSIGNED NOT NULL,
            `id_category` INT(10) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_rule`, `id_category`)
        )
        COLLATE='utf8_general_ci'";
        
        return $res && Db::getInstance()->execute($sql);
    }
    
    public function validateFields($die = true, $error_return = false)
    {
        $parent = parent::validateFields($die, $error_return);
        $ruleValidation = $this->validateRuleField();
        if ($ruleValidation && $ruleValidation !== true) {
            if (is_array($parent)) {
                $parent['rule'] = $ruleValidation;
            } else {
                $parent = array(
                    'rule' => $ruleValidation
                );
            }
        }
        return $parent;
    }
    
    public function validateRuleField()
    {
        $allowedTags = array_keys(ArSeoPro::getProductKeywords());
        $matches = array();
        $errors = array();
        if (preg_match_all('/{[_a-zA-Z0-9-\pL]+}/is', $this->rule, $matches)) {
            if (isset($matches[0]) && is_array($matches[0])) {
                foreach ($matches[0] as $tag) {
                    $keyword = str_replace(array('{', '}'), '', $tag);
                    if (!in_array($keyword, $allowedTags)) {
                        $errors[] = sprintf('Tag "%s" is not exists', $tag);
                    }
                }
            }
        }
        return $errors? $errors : true;
    }
    
    public static function generateRuleName()
    {
        $count = self::getCount();
        return 'Rule ' . ($count + 1);
    }
    
    public static function getCount($params = array())
    {
        $query = new DbQuery();
        $query->from(self::TABLE_NAME);
        $query->select('COUNT(1) c');
        if (isset($params['filter'])) {
            $where = self::processFilters($params['filter']);
            $query->where($where);
        }
        $res = Db::getInstance()->getRow($query);
        return $res['c'];
    }
    
    public static function getAll($params = array())
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 50;
        $page = isset($params['page'])? $params['page'] - 1 : 0;
        $offset = isset($params['page'])? $pageSize * $page : 0;
        $query = new DbQuery();
        $query->from(self::TABLE_NAME);
        $query->limit((int)$pageSize, (int)$offset);
        if (isset($params['filter'])) {
            $where = self::processFilters($params['filter']);
            $query->where($where);
        }
        return Db::getInstance()->executeS($query);
    }
    
    public static function processFilters($params)
    {
        $where = array();
        $model = new self();
        foreach ($params as $k => $value) {
            if (property_exists($model, $k) && $value != '') {
                if ($k == 'id') {
                    $k = 'id_rule';
                }
                if (strpos($value, '%') !== false) {
                    $where[] = "`" . $k . "` LIKE '" . pSQL($value) . "'";
                } else {
                    $where[] = "`" . $k . "` = '" . pSQL($value) . "'";
                }
            }
        }
        return implode(' AND ', $where);
    }
    
    public static function truncate()
    {
        Db::getInstance()->execute('TRUNCATE `' . self::getTableName(true) . '`');
        return Db::getInstance()->execute('TRUNCATE `' . self::getTableName() . '`');
    }
    
    public function clearCategories()
    {
        return Db::getInstance()->execute('DELETE FROM `' . self::getTableName(true) . '` WHERE id_rule=' . (int)$this->id);
    }
    
    public function addCategory($id_category)
    {
        return Db::getInstance()->execute('INSERT INTO `' . self::getTableName(true) . '` (`id_rule`, `id_category`) VALUES (' . (int)$this->id . ', ' . (int)$id_category .')');
    }
    
    public function getCategories($nonZero = false)
    {
        $sql = new DbQuery();
        $sql->from(self::getTableName(true, false), 't');
        $sql->select('t.id_category');
        $sql->where('id_rule=' . (int)$this->id);
        if ($nonZero) {
            $sql->where('id_rule=' . (int)$this->id . ' AND id_category != 0');
        }
        $return = array();
        if ($res = Db::getInstance()->executeS($sql)) {
            foreach ($res as $row) {
                $return[] = $row['id_category'];
            }
        }
        return $return;
    }
    
    public function getRelatedProductsCount()
    {
        $categories = array();
        foreach ($this->getCategories() as $rel) {
            if ($rel != 0) {
                $categories[] = (int)$rel;
            }
        }
        if ($categories) {
            $sql = 'SELECT COUNT(id_product) FROM `' . _DB_PREFIX_ . 'product_shop` WHERE id_category_default IN (' . implode(',', $categories) . ') AND id_shop = ' . (int)$this->id_shop;
        } else {
            $sql = 'SELECT COUNT(id_product) FROM `' . _DB_PREFIX_ . 'product_shop` WHERE id_shop = ' . (int)$this->id_shop;
        }
        return (int)Db::getInstance()->getValue($sql);
    }
    
    public function getRelatedProductIds($limit, $offset, $id_product = null)
    {
        $categories = array();
        foreach ($this->getCategories() as $rel) {
            if ($rel != 0) {
                $categories[] = $rel;
            }
        }
        $where = array();
        if ($categories) {
            $where[] = 'id_category_default IN (' . implode(',', $categories) . ')';
        }
        $where[] = 'id_shop = ' . (int)$this->id_shop;
        if ($id_product) {
            $where[] = ' id_product = ' . (int)$id_product;
        }
        $sql = 'SELECT id_product FROM `' . _DB_PREFIX_ . 'product_shop` WHERE ' . implode(' AND ', $where) . ' LIMIT ' . (int)$offset . ', ' . (int)$limit;
        $return = array();
        if ($res = Db::getInstance()->executeS($sql)) {
            foreach ($res as $row) {
                $return[] = $row['id_product'];
            }
        }
        return $return;
    }
    
    public static function getRules($id_category, $id_lang, $id_shop)
    {
        $where = array();
        if ($id_category) {
            $where[] = 'arc.id_category IN (0, ' . (int)$id_category . ')';
        }
        if ($id_lang) {
            $where[] = 'ar.id_lang IN (0, ' . (int)$id_lang . ')';
        }
        if ($id_shop) {
            $where[] = 'ar.id_shop = ' . (int)$id_shop;
        }
        $sql = 'SELECT ar.* FROM `' . self::getTableName() . '` ar ' .
            'LEFT JOIN `' . self::getTableName(true) . '` arc ON arc.id_rule = ar.id_rule ' .
            'WHERE ' . implode(' AND ', $where);
        return Db::getInstance()->executeS($sql);
    }
    
    public static function getTableName($relTable = false, $withPrefix = true)
    {
        if ($withPrefix) {
            return $relTable? (_DB_PREFIX_ . self::REL_TABLE_NAME) : (_DB_PREFIX_ . self::TABLE_NAME);
        }
        return $relTable? self::REL_TABLE_NAME : self::TABLE_NAME;
    }
}
