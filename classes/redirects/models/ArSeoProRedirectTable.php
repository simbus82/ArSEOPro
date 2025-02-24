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

class ArSeoProRedirectTable extends ArSeoProTableAbstract
{
    const TABLE_NAME = 'arseopro_redirect';
    
    public $id;
    public $from;
    public $to;
    public $type;
    public $status;
    public $id_shop;
    public $selected;
    public $created_at;
    public $use_times;
    public $last_used_at;
    public $create_type;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_redirect',
        'multilang' => false,
        'fields' => array(
            'from' =>               array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'to' =>                 array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'type' =>               array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'status' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'use_times' =>          array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'create_type' =>        array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'selected' =>           array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'created_at' =>         array('type' => self::TYPE_STRING),
            'last_used_at' =>         array('type' => self::TYPE_STRING),
        ),
    );
    
    public static function uninstallTable()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::TABLE_NAME . '`');
    }
    
    public static function installTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . self::TABLE_NAME . "` (
            `id_redirect` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `from` VARCHAR(512) NOT NULL,
            `to` TEXT NOT NULL,
            `type` INT(10) UNSIGNED NOT NULL,
            `status` TINYINT(3) UNSIGNED NOT NULL,
            `id_shop` INT(10) UNSIGNED NOT NULL,
            `selected` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
            `use_times` INT(10) UNSIGNED NULL DEFAULT '0',
            `create_type` TINYINT(3) UNSIGNED NULL DEFAULT '0',
            `created_at` DATETIME NOT NULL,
            `last_used_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id_redirect`)
        )
        COLLATE='utf8_general_ci'";
        
        return Db::getInstance()->execute($sql);
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
    
    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . self::TABLE_NAME . '`');
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
                    $k = 'id_redirect';
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
    
    public function getRedirectHeader()
    {
        return ArSeoHelpers::getResponseHeader($this->type);
    }
    
    public function getRedirectToUrl($lang = null)
    {
        if (self::isAbsoluteUrl($this->to)) {
            return $this->to;
        } else {
            if ($this->id_shop) {
                $shop = Shop::getShop($this->id_shop);
                $domain = Configuration::get('PS_SSL_ENABLED')? $shop['domain_ssl'] : $shop['domain'];
                $protocol = Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://';
                $domain = $protocol . $domain;
            } else {
                $domain = Tools::getHttpHost(true);
            }
        }
        $url = $domain . $this->prepareUrl($lang);
        $url = preg_replace('{/+}is', '/', $url);
        return str_replace(':/', '://', $url);
    }
    
    public function prepareUrl($lang = null)
    {
        if (strpos($this->to, '{lang}') === false && strpos($this->to, '{default_lang}') === false) {
            return $this->to;
        }
        $defaultLangId = Configuration::get('PS_LANG_DEFAULT');
        $defaultLang = Language::getIsoById($defaultLangId);
        return strtr($this->to, array(
            '{lang}' => $lang,
            '{default_lang}' => $defaultLang
        ));
    }
    
    public static function isAbsoluteUrl($url)
    {
        return preg_match('{^https?://}is', $url);
    }
    
    public function validateFields($die = true, $error_return = false)
    {
        $parent = parent::validateFields($die, $error_return);
        $fromValidation = $this->validateFromUrl();
        $toValidation = $this->validateToUrl();
        if ($fromValidation !== true) {
            if (is_array($parent)) {
                $parent['from'] = $fromValidation;
            } else {
                $parent = array(
                    'from' => $fromValidation
                );
            }
        }
        if ($toValidation !== true) {
            if (is_array($parent)) {
                $parent['to'] = $toValidation;
            } else {
                $parent = array(
                    'to' => $toValidation
                );
            }
        }
        if ($this->from == $this->to) {
            if (is_array($parent)) {
                $parent['to'] = 'These fields should not be equals';
                $parent['from'] = 'These fields should not be equals';
            } else {
                $parent = array(
                    'to' => 'These fields should not be equals',
                    'from' => 'These fields should not be equals'
                );
            }
        }
        return $parent;
    }
    
    public function validateFromUrl()
    {
        return self::validateFromUrlStaic($this->from);
    }
    
    public function validateToUrl()
    {
        return self::validateToUrlStatic($this->to);
    }
    
    public static function validateToUrlStatic($url)
    {
        if (empty($url)) {
            return 'Field "Redirect to" is required';
        }
        if (preg_match('{https?://}is', $url)) {
            if (!Validate::isAbsoluteUrl($url)) {
                return 'Not valid url';
            }
        } else {
            if (strpos($url, '/') !== 0) {
                return 'Please start with "/" sign';
            }
        }
        if (preg_match('{\s+}is', $url) || preg_match('{#+}is', $url)) {
            return 'URL contains disallowed characters';
        }
        if (!self::checkTags($url, array('{lang}', '{default_lang}'))) {
            return 'URL contains disallowed tags';
        }
        return true;
    }
    
    public static function validateFromUrlStaic($url)
    {
        if (empty($url)) {
            return 'Field "Redirect from" is required';
        }
        if (strpos($url, '/') !== 0) {
            return 'Please start with "/" sign';
        }
        if (preg_match('{\s+}is', $url) || preg_match('{#+}is', $url)) {
            return 'URL contains disallowed characters';
        }
        if (!self::checkTags($url, array('{lang}'))) {
            return 'URL contains disallowed tags';
        }
        return true;
    }
    
    public static function checkTags($string, $allowedTags = array())
    {
        $matches = array();
        if (preg_match_all('/({.*?})/is', $string, $matches)) {
            $data = $matches[0];
            foreach ($data as $tag) {
                if (!in_array($tag, $allowedTags)) {
                    return false;
                }
            }
        }
        return true;
    }
}
