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

class FrontController extends FrontControllerCore
{
    protected static $moduleInstance;
    
    public function arSeoProOverrideVersion()
    {
        return '1.9.5';
    }

    /**
     *
     * @return ArSeoPro
     */
    public function getModuleInstance()
    {
        if (self::$moduleInstance == null) {
            self::$moduleInstance = Module::getInstanceByName('arseopro');
        }
        return self::$moduleInstance;
    }
    
    public function getTemplateVarShop()
    {
        $shop = parent::getTemplateVarShop();
        $shop['favicon'] = $this->getShopFavicon();

        return $shop;
    }
    
    public function getShopFavicon()
    {
        $icon = Configuration::get('PS_FAVICON');
        $favicon = ($icon) ? _PS_IMG_.$icon : '';
        if (Module::isEnabled('arseopro')) {
            $module = $this->getModuleInstance();
            $module->getFaviconConfig()->loadFromConfig();
            if ($module->getFaviconConfig()->icon) {
                $filename = pathinfo($module->getFaviconConfig()->icon, PATHINFO_FILENAME);
                $ext = pathinfo($module->getFaviconConfig()->icon, PATHINFO_EXTENSION);
                $favicon = $module->getUploadsUrl() . $filename . '_96x96.' . $ext;
            }
        }
        return $favicon;
    }
    
    public function initLogoAndFavicon()
    {
        $res = parent::initLogoAndFavicon();
        $res['favicon_url'] = $this->getShopFavicon();
        return $res;
    }

    protected function canonicalRedirection($canonical_url = '')
    {
        $params = array();
        if (Module::isEnabled('arseopro')) {
            $module = $this->getModuleInstance();
            $data = $module->getUrlConfig()->canonicalUrl($canonical_url);
            $canonical_url = $data['url'];
            if (isset($data['params']) && is_array($data['params']) && $data['params']) {
                $params = $data['params'];
            }
        }

        parent::canonicalRedirection($canonical_url);

        if ($params) {
            $_GET = array_merge($_GET, $params);
        }
    }

    protected function updateQueryString(array $extraParams = null)
    {
        if (!Module::isEnabled('arseopro')) {
            return parent::updateQueryString($extraParams);
        }
        $uriWithoutParams = explode('?', $_SERVER['REQUEST_URI']);
        if (isset($uriWithoutParams[0])) {
            $uriWithoutParams = $uriWithoutParams[0];
        }

        $url = Tools::getCurrentUrlProtocolPrefix().Tools::getHttpHost().$uriWithoutParams;

        if (Module::isEnabled('arseopro')) {
            $module = $this->getModuleInstance();
            $url = $module->getUrlConfig()->overrideUpdateQueryStringBaseUrl($url, $extraParams);
        }

        $params = array();
        parse_str($_SERVER['QUERY_STRING'], $params);

        if (null !== $extraParams) {
            foreach ($extraParams as $key => $value) {
                if (null === $value) {
                    unset($params[$key]);
                } else {
                    $params[$key] = $value;
                }
            }
        }

        ksort($params);

        if (null !== $extraParams) {
            foreach ($params as $key => $param) {
                if (null === $param || '' === $param) {
                    unset($params[$key]);
                }
            }
        } else {
            $params = array();
        }

        $queryString = str_replace('%2F', '/', http_build_query($params));

        return $url . ($queryString? "?{$queryString}" : '');
    }
}
