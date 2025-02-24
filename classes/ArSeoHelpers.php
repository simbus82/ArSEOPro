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

class ArSeoHelpers
{
    public static function endWith($string, $needle)
    {
        return preg_match("|{$needle}$|is", $string);
    }
    
    public static function startWith($string, $needle)
    {
        return preg_match("|^{$needle}|is", $string);
    }
    
    public static function contains($haystack, $needle)
    {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
        return false;
    }
    
    public static function getCurrentUrl()
    {
        return Tools::getShopDomainSsl(true).$_SERVER['REQUEST_URI'];
    }
    
    public static function getResponseHeader($code)
    {
        $headers = array(
            404 => 'HTTP/1.1 404 Not Found',
            301 => 'HTTP/1.1 301 Moved Permanently',
            302 => 'HTTP/1.1 302 Moved Temporarily'
        );
        return $headers[$code];
    }
    
    public static function getIsset($param)
    {
        $value = Tools::getValue($param, null);
        if (!is_null($value)) {
            return true;
        }
        return false;
    }
}
