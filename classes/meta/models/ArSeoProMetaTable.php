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

class ArSeoProMetaTable extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_meta';
    const REL_TABLE_NAME = 'arseopro_meta_rel';
    
    public $id;
    public $id_lang;
    public $id_shop;
    public $name;
    public $rule_type;
    
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    
    public $fb_admins;
    public $fb_app;
    public $fb_title;
    public $fb_description;
    public $fb_type;
    public $fb_image;
    public $fb_custom_image;
    
    public $tw_type;
    public $tw_account;
    public $tw_title;
    public $tw_description;
    public $tw_image;
    public $tw_custom_image;
    public $tw_ch1;
    public $tw_ch2;
    
    public $status;
    public $created_at;
    public $updated_at;
    public $last_applied_at;
    
    /**
     * Virtual fields
     */
    public $id_category;
    public $categories;
    public $fb_custom_image_url;
    public $tw_custom_image_url;
    
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
            'rule_type' =>          array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            
            'meta_title' =>         array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'meta_description' =>   array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'meta_keywords' =>      array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            
            'fb_admins' =>          array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'fb_app' =>             array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'fb_title' =>           array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'fb_description' =>     array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'fb_type' =>            array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'fb_image' =>           array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'fb_custom_image' =>    array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            
            'tw_type' =>            array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tw_account' =>         array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tw_title' =>           array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tw_description' =>     array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tw_image' =>           array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'tw_custom_image' =>    array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tw_ch1' =>             array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tw_ch2' =>             array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            
            'status' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'created_at' =>         array('type' => self::TYPE_STRING),
            'updated_at' =>         array('type' => self::TYPE_STRING),
            'last_applied_at' =>    array('type' => self::TYPE_STRING),
        ),
    );
    
    public function validateFields($die = true, $error_return = false)
    {
        $parent = parent::validateFields($die, $error_return);
        $parent = $this->addError($parent, 'fb_image', $this->validateFbCustomImage());
        $parent = $this->addError($parent, 'tw_image', $this->validateTwCustomImage());
        foreach (array('meta_title', 'meta_description', 'meta_keywords', 'fb_title', 'fb_description', 'tw_title', 'tw_description') as $field) {
            $parent = $this->addError($parent, $field, $this->validateRuleField($field));
        }
        return $parent;
    }
    
    public static function getRowByRelation($id, $id_lang, $type = 'product')
    {
        $sql = new DbQuery();
        $sql->from(self::getTableName(false, false), 't');
        $sql->select('t.*');
        $sql->leftJoin(self::getTableName(true, false), 'mr', 't.id_rule = mr.id_rule');
        $sql->where("mr.rel_object IN (0, '" . pSQL($id) . "') AND t.id_lang IN(0, " . (int)$id_lang . ") AND t.rule_type = '" . pSQL($type) . "' AND t.status = 1");
        return Db::getInstance()->getRow($sql);
    }
    
    public function validateRuleField($field)
    {
        switch ($this->rule_type) {
            case 'product':
                $allowedTags = array_keys(ArSeoPro::getProductKeywords());
                break;
            case 'metapage':
                $allowedTags = array_keys(ArSeoPro::getMetaKeywords());
                break;
            case 'brand':
                $allowedTags = array_keys(ArSeoPro::getbrandKeywords());
                break;
            default:
                $allowedTags = array_keys(ArSeoPro::getCategoryKeywords());
        }
        $matches = array();
        $errors = array();
        if (preg_match_all('/{[_a-zA-Z0-9-\pL]+[|}]/is', $this->$field, $matches)) {
            if (isset($matches[0]) && is_array($matches[0])) {
                foreach ($matches[0] as $tag) {
                    $keyword = str_replace(array('{', '}', '|'), '', $tag);
                    if (!in_array($keyword, $allowedTags)) {
                        $errors[] = sprintf('Tag "%s" is not exists', "{{$keyword}}");
                    }
                }
            }
        }
        return $errors? $errors : true;
    }
    
    public function validateFbCustomImage()
    {
        if ($this->fb_image == 3 && empty($this->fb_custom_image)) {
            return 'Please upload image';
        }
        return true;
    }
    
    public function validateTwCustomImage()
    {
        if ($this->tw_image == 3 && empty($this->tw_custom_image)) {
            return 'Please upload image';
        }
        return true;
    }
    
    public function addError($errors, $field, $message)
    {
        if ($message && $message !== true) {
            if (is_array($errors)) {
                $errors[$field] = $message;
            } else {
                $errors = array(
                    $field => $message
                );
            }
        }
        return $errors;
    }
    
    public static function uninstallTable()
    {
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . self::getTableName() . '`');
        return $res && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . self::getTableName(true) . '`');
    }
    
    public static function installTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getTableName() . "` (
            `id_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lang` INT(10) UNSIGNED NULL DEFAULT NULL,
            `id_shop` INT(10) UNSIGNED NULL DEFAULT NULL,
            `name` VARCHAR(50) NULL DEFAULT NULL,
            `rule_type` VARCHAR(50) NULL DEFAULT NULL,
            `meta_title` VARCHAR(255) NULL DEFAULT NULL,
            `meta_description` VARCHAR(255) NULL DEFAULT NULL,
            `meta_keywords` VARCHAR(255) NULL DEFAULT NULL,
            `fb_admins` VARCHAR(255) NULL DEFAULT NULL,
            `fb_app` VARCHAR(255) NULL DEFAULT NULL,
            `fb_title` VARCHAR(255) NULL DEFAULT NULL,
            `fb_description` VARCHAR(255) NULL DEFAULT NULL,
            `fb_type` VARCHAR(255) NULL DEFAULT NULL,
            `fb_image` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
            `fb_custom_image` VARCHAR(50) NULL DEFAULT NULL,
            `tw_type` VARCHAR(255) NULL DEFAULT NULL,
            `tw_account` VARCHAR(255) NULL DEFAULT NULL,
            `tw_title` VARCHAR(255) NULL DEFAULT NULL,
            `tw_description` VARCHAR(255) NULL DEFAULT NULL,
            `tw_image` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
            `tw_custom_image` VARCHAR(50) NULL DEFAULT NULL,
            `tw_ch1` VARCHAR(255) NULL DEFAULT NULL,
            `tw_ch2` VARCHAR(255) NULL DEFAULT NULL,
            `status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
            `created_at` DATETIME NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            `last_applied_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_rule`)
        )
        COLLATE='utf8_general_ci'";
        
        $res = Db::getInstance()->execute($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::getTableName(true) . "` (
            `id_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `rel_object` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id_rule`, `rel_object`)
        )
        COLLATE='utf8_general_ci'";
        
        return $res && Db::getInstance()->execute($sql);
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
        $query->limit($pageSize, $offset);
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
    
    public function clearRelations()
    {
        return Db::getInstance()->execute('DELETE FROM `' . self::getTableName(true) . '` WHERE id_rule=' . (int)$this->id);
    }
    
    public function addRelation($id_category)
    {
        return Db::getInstance()->execute('INSERT INTO `' . self::getTableName(true) . '` (`id_rule`, `rel_object`) VALUES (' . (int)$this->id . ", '" . pSQL($id_category) . "')");
    }
    
    public function getRelations($nonZero = false)
    {
        $sql = new DbQuery();
        $sql->from(self::getTableName(true, false), 't');
        $sql->select('t.rel_object');
        $sql->where('id_rule=' . (int)$this->id);
        if ($nonZero) {
            $sql->where('id_rule=' . (int)$this->id . ' AND rel_object != "" AND rel_object != "0" AND rel_object IS NOT NULL');
        }
        $return = array();
        if ($res = Db::getInstance()->executeS($sql)) {
            foreach ($res as $row) {
                $return[] = pSQL($row['rel_object']);
            }
        }
        return $return;
    }
    
    public function getRelatedProductsCount()
    {
        $categories = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $categories[] = $rel;
            }
        }
        if ($categories) {
            $sql = 'SELECT COUNT(DISTINCT(ps.id_product)) FROM `' . _DB_PREFIX_ . 'product_shop` ps '
                    . ' LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.id_product = ps.id_product '
                    . ' WHERE (cp.id_category IN (' . implode(',', $categories) . ')) AND id_shop = ' . (int)$this->id_shop;
        } else {
            $sql = 'SELECT COUNT(id_product) FROM `' . _DB_PREFIX_ . 'product_shop` WHERE id_shop = ' . (int)$this->id_shop;
        }
        return (int)Db::getInstance()->getValue($sql);
    }
    
    public function getRelatedProductIds($limit, $offset)
    {
        $categories = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $categories[] = $rel;
            }
        }
        if ($categories) {
            $sql = 'SELECT DISTINCT(ps.id_product) FROM `' . _DB_PREFIX_ . 'product_shop` ps '
                . ' LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.id_product = ps.id_product '
                . ' WHERE (cp.id_category IN (' . implode(',', $categories) . ')) AND id_shop = ' . (int)$this->id_shop . ' LIMIT ' . (int)$offset . ', ' . (int)$limit;
        } else {
            $sql = 'SELECT id_product FROM `' . _DB_PREFIX_ . 'product_shop` WHERE id_shop = ' . (int)$this->id_shop . ' LIMIT ' . (int)$offset . ', ' . (int)$limit;
        }
        $return = array();
        if ($res = Db::getInstance()->executeS($sql)) {
            foreach ($res as $row) {
                $return[] = $row['id_product'];
            }
        }
        return $return;
    }
    
    public static function getRules($type, $id_category, $id_lang, $id_shop)
    {
        $where = array();
        if ($id_category) {
            $where[] = 'amr.rel_object IN (0, ' . (int)$id_category . ')';
        }
        if ($id_lang) {
            $where[] = 'am.id_lang IN (0, ' . (int)$id_lang . ')';
        }
        if ($id_shop) {
            $where[] = 'am.id_shop = ' . (int)$id_shop;
        }
        if ($type) {
            $where[] = 'am.rule_type = "' . pSQL($type) . '"';
        }
        $sql = 'SELECT am.* FROM `' . self::getTableName() . '` am ' .
            'LEFT JOIN `' . self::getTableName(true) . '` amr ON amr.id_rule = am.id_rule ' .
            'WHERE ' . implode(' AND ', $where);
        return Db::getInstance()->executeS($sql);
    }
    
    public function getRelatedCategories($limit, $offset)
    {
        $categories = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $categories[] = $rel;
            }
        }
        if (empty($categories)) {
            $sql = 'SELECT id_category FROM `' . _DB_PREFIX_ . 'category` LIMIT ' . (int)$offset . ', ' . (int)$limit;
            $cats = Db::getInstance()->executeS($sql);
            $return  = array();
            foreach ($cats as $cat) {
                $return[] = $cat['id_category'];
            }
            return $return;
        } else {
            return $categories;
        }
    }
    
    public function getRelatedCategoriesCount()
    {
        $categories = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $categories[] = $rel;
            }
        }
        if (empty($categories)) {
            $sql = 'SELECT COUNT(1) FROM `' . _DB_PREFIX_ . 'category`';
            return (int)Db::getInstance()->getValue($sql);
        } else {
            return count($categories);
        }
    }
    
    public function getRelatedMetaPagesCount()
    {
        $meta = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $meta[] = $rel;
            }
        }
        if (empty($meta)) {
            $sql = 'SELECT COUNT(1) FROM `' . _DB_PREFIX_ . 'meta`';
            return (int)Db::getInstance()->getValue($sql);
        } else {
            return count($meta);
        }
    }
    
    public function getRelatedMetaPages($limit, $offset)
    {
        $meta = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $meta[] = $rel;
            }
        }
        if (empty($meta)) {
            $sql = 'SELECT id_meta FROM `' . _DB_PREFIX_ . 'meta` LIMIT ' . (int)$offset . ', ' . (int)$limit;
            $pages = Db::getInstance()->executeS($sql);
            $return  = array();
            foreach ($pages as $page) {
                $return[] = $page['id_meta'];
            }
            return $return;
        } else {
            return $meta;
        }
    }
    
    public function getRelatedBrandPagesCount()
    {
        $brands = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $brands[] = $rel;
            }
        }
        if (empty($brands)) {
            $sql = 'SELECT COUNT(1) FROM `' . _DB_PREFIX_ . 'manufacturer`';
            return (int)Db::getInstance()->getValue($sql);
        } else {
            return count($brands);
        }
    }
    
    public function getRelatedBrandPages($limit, $offset)
    {
        $brands = array();
        foreach ($this->getRelations() as $rel) {
            if ($rel != 0) {
                $brands[] = $rel;
            }
        }
        if (empty($brands)) {
            $sql = 'SELECT id_manufacturer FROM `' . _DB_PREFIX_ . 'manufacturer` LIMIT ' . (int)$offset . ', ' . (int)$limit;
            $pages = Db::getInstance()->executeS($sql);
            $return  = array();
            foreach ($pages as $page) {
                $return[] = $page['id_manufacturer'];
            }
            return $return;
        } else {
            return $brands;
        }
    }
    
    public static function getTableName($relTable = false, $withPrefix = true)
    {
        if ($withPrefix) {
            return $relTable? (_DB_PREFIX_ . self::REL_TABLE_NAME) : (_DB_PREFIX_ . self::TABLE_NAME);
        }
        return $relTable? self::REL_TABLE_NAME : self::TABLE_NAME;
    }
}
