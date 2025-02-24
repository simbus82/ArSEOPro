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

include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

/**
 * @property ArSeoPro $module
 */
abstract class AdminArSeoControllerAbstract extends ModuleAdminController
{
    public static $shopCache = null;
    
    public function __construct()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('SeoPro');
    }
    
    public function renderTable()
    {
        return null;
    }
    
    public function getListId()
    {
        return null;
    }

    public function yesNoList()
    {
        return array(
            '0' => $this->l('No'),
            '1' => $this->l('Yes')
        );
    }

    public function ajaxProcessReload()
    {
        $params = $this->getParams($this->getListId());
        die(ArSeoProTools::jsonEncode(array(
            'content' => $this->renderTable($params)
        )));
    }
    
    public function getParams($listId)
    {
        $data = Tools::getValue('data');
        if (empty($data)) {
            return array();
        }
        $params = array(
            'resetFilter' => 0
        );
        foreach ($data as $param) {
            $name = str_replace($listId, '', $param['name']);
            if (strpos($name, 'Filter') === 0) {
                $name = str_replace('Filter_', '', $name);
                $params['filter'][$name] = $param['value'];
            } elseif ($name == 'submit') {
                if (strpos($param['value'], 'submitReset') !== false) {
                    $params['resetFilter'] = 1;
                    return array();
                }
            } else {
                $params[$name] = $param['value'];
            }
        }
        return $params;
    }
    
    protected function filterIdList($ids)
    {
        $res = array();
        foreach ($ids as $id) {
            $res[] = (int)$id;
        }
        return $res;
    }
    
    public function getShopList()
    {
        if (empty(self::$shopCache)) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                self::$shopCache[$shop['id_shop']] = $shop['name'];
            }
        }
        $shops = self::$shopCache;
        $shops[0] = $this->l('[all shops]');
        ksort($shops);
        return $shops;
    }
    
    public function shopTableValue($cellValue, $row)
    {
        if (empty(self::$shopCache)) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                self::$shopCache[$shop['id_shop']] = $shop['name'];
            }
        }
        if ($row['id_shop'] == 0) {
            return $this->l('[all shops]');
        }
        return isset(self::$shopCache[$row['id_shop']])? self::$shopCache[$row['id_shop']] : $this->l('[deleted]');
    }
    
    public function ajaxProcessHelp()
    {
        $lang = Language::getIsoById(Context::getContext()->language->id);
        $defLang = 'en';
        $path = $this->module->getPath(true) . 'docs/' . $lang . '/';
        $file = 'docs/' . $lang . '/index.tpl';
        if (!file_exists($path)) {
            $lang = $defLang;
            $path = $this->module->getPath(true) . 'docs/' . $defLang . '/';
            $file = 'docs/' . $defLang . '/index.tpl';
        }
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'lang' => $lang,
            'content' => $this->module->render($file, array(
                'path' => $this->module->getPath(),
                'lang' => $lang,
                'moduleUrl' => $this->module->getModuleBaseUrl(),
                'serverUrl' => Tools::getShopDomainSsl(true),
                'schemeUrl' => Context::getContext()->link->getAdminLink('AdminMeta'),
            ))
        )));
    }
}
