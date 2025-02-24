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

class ArSeoProTools
{
    public static function getShopDomainSsl($id_shop = null, $http = false, $entities = false)
    {
        if ($id_shop == null) {
            $id_shop = Context::getContext()->shop->id;
        }
        if (!$domain = ShopUrl::getMainShopDomainSSL($id_shop)) {
            $domain = Tools::getHttpHost();
        }
        if ($entities) {
            $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
        }
        if ($http) {
            $domain = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$domain;
        }
        return $domain;
    }
    
    public static function isColumnExists($table, $column)
    {
        $sql = 'DESCRIBE `' . $table . '`';
        $data = Db::getInstance()->executeS($sql);
        foreach ($data as $col) {
            if ($col['Field'] == $column) {
                return true;
            }
        }
        return false;
    }
    
    public static function jsonEncode($data)
    {
        if (function_exists('json_encode')) {
            return json_encode($data);
        }
        if (method_exists(Tools, 'jsonEncode')) {
            return Tools::jsonEncode($data);
        }
        throw new Exception('No JSON encode method found');
    }
    
    public static function jsonDecode($json)
    {
        if (function_exists('json_decode')) {
            return json_decode($json);
        }
        if (method_exists(Tools, 'jsonDecode')) {
            return Tools::jsonDecode($json);
        }
        throw new Exception('No JSON decode method found');
    }
}
