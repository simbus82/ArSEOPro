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

include_once dirname(__FILE__).'/../../classes/sitemap/ArSeoProSitemapGenerator.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

/**
 * @property ArSeoPro $module
 */
class ArSeoProAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    
    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        $time = microtime(true);
        $id_shop = (int)Tools::getValue('id_shop');
        $token = Tools::getValue('token');
        if ($token != Configuration::get('ARSEO_SITEMAP_TOKEN', null, null, $id_shop)) {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 0,
                'error' => $this->module->l('Wrong security token')
            )));
        }
        
        $step = (int)Tools::getValue('step');
        $totalCount = (int)Tools::getValue('totalCount');
        
        $redirect = (int)Tools::getValue('redirect', 1);
        
        $page = (int)Tools::getValue('page');
        
        $generator = new ArSeoProSitemapGenerator($this->module, $id_shop);
        $path = $this->module->getSitemapPath($id_shop);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $generator->setPath($this->module->getSitemapPath($id_shop, false))->setIndexPath($this->module->getIndexSitemapPath(false));
        if ($step == 0) {
            $generator->clearSitemapDir();
            $generator->generateAll();
        } elseif ($step == 1) {
            $generator->generateProducts($page);
        }
        
        $end = microtime(true);
        $realCount = $generator->getRealCount();
        $count = $generator->getCount();
        $continue = ($step == 1 && $realCount['product'] != 0) || $step == 0;
        $linkParams = http_build_query(array(
            'page' => $step == 0? 0 : $page + 1,
            'step' => 1,
            'redirect' => $redirect? 1 : 0
        ));
        if ($redirect) {
            if ($continue) {
                Tools::redirect($this->module->getSitemapCronUrl($id_shop) . '&' . $linkParams);
            }
        }
        if (!$continue) {
            $lastgen = time();
            $generator->generateIndexSitemap();
            Configuration::updateValue('ARSEO_SITEMAP_GEN', $lastgen, false, null, $id_shop);
            $config = new ArSeoProSitemapGeneral($this->module);
            $config->loadFromConfig();
            $ping = array();
            if ($config->ping_google) {
                $ping['google'] = 'https://www.google.com/webmasters/sitemaps/ping?sitemap=';
            }
            if ($config->ping_bing) {
                $ping['bing'] = 'https://www.bing.com/webmaster/ping.aspx?siteMap=';
            }
            if ($config->ping_yandex) {
                $ping['yandex'] = 'https://ping.blogs.yandex.ru/ping?sitemap=';
            }
            $pingRes = array();
            if ($ping) {
                $sitemapUrl = $generator->getIndexFileUrl($generator->getIndexFileName());
                foreach ($ping as $k => $url) {
                    $url .= urlencode($sitemapUrl);
                    $pingRes[$k] = Tools::file_get_contents($url);
                }
            }
        }
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'id_shop' => $id_shop,
            'time' => round($end - $time, 2),
            'memory' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'step' => 1,
            'page' => $step == 0? 0 : $page + 1,
            'nextUrl' => $continue? ($this->module->getSitemapCronUrl($id_shop) . '&' . $linkParams) : null,
            'continue' => $continue? 1 : 0,
            'count' => $count,
            'realCount' => $realCount,
            'lastgen' => isset($lastgen)? date('Y-m-d H:i:s', $lastgen) : null,
            'totalCount' => $step == 0? $generator->getTotalCount() : $totalCount,
            'ping' => isset($pingRes)? $pingRes : null,
            'token' => $token
        )));
    }
}
