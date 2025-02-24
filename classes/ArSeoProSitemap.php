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

include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapCategories.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapCmsConfig.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapGeneral.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapManufacturers.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapMeta.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapProducts.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapSuppliers.php';

include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapSmartblog.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapPrestablog.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapSimpleblog.php';
include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapFAQs.php';

/**
 * @property ArSeoProSitemapCategories $categories
 * @property ArSeoProSitemapCmsConfig $cms
 * @property ArSeoProSitemapGeneral $general
 * @property ArSeoProSitemapManufacturers $manufacturers
 * @property ArSeoProSitemapMeta $meta
 * @property ArSeoProSitemapProducts $products
 * @property ArSeoProSitemapSuppliers $suppliers
 * @property ArSeoProSitemapSmartblog $smartblog
 * @property ArSeoProSitemapPrestablog $prestablog
 * @property ArSeoProSitemapSimpleblog $simpleblog
 * @property ArSeoProSitemapFAQs $faqs
 */
class ArSeoProSitemap
{
    public $products;
    public $categories;
    public $cms;
    public $cmsCategory;
    public $suppliers;
    public $manufacturers;
    public $general;
    public $meta;
    public $smartblog;
    public $prestablog;
    public $simpleblog;
    public $faqs;
    
    public static $faqRewriteSettings = null;
    public static $faqHomePageId = null;
    
    public function __construct($module)
    {
        $this->products = new ArSeoProSitemapProducts($module);
        $this->categories = new ArSeoProSitemapCategories($module);
        $this->cms = new ArSeoProSitemapCmsConfig($module);
        $this->suppliers = new ArSeoProSitemapSuppliers($module);
        $this->manufacturers = new ArSeoProSitemapManufacturers($module);
        $this->general = new ArSeoProSitemapGeneral($module);
        $this->meta = new ArSeoProSitemapMeta($module);
        $this->smartblog = new ArSeoProSitemapSmartblog($module);
        $this->prestablog = new ArSeoProSitemapPrestablog($module);
        $this->simpleblog = new ArSeoProSitemapSimpleblog($module);
        $this->faqs = new ArSeoProSitemapFAQs($module);
    }
    
    public function loadAllFromConfig()
    {
        if (!$this->general->isLoaded()) {
            $this->general->loadFromConfig();
        }
        if (!$this->products->isLoaded()) {
            $this->products->loadFromConfig();
        }
        if (!$this->categories->isLoaded()) {
            $this->categories->loadFromConfig();
        }
        if (!$this->manufacturers->isLoaded()) {
            $this->manufacturers->loadFromConfig();
        }
        if (!$this->suppliers->isLoaded()) {
            $this->suppliers->loadFromConfig();
        }
        if (!$this->cms->isLoaded()) {
            $this->cms->loadFromConfig();
        }
        if (!$this->meta->isLoaded()) {
            $this->meta->loadFromConfig();
        }
        if (self::isSmartBlogInstalled()) {
            if (!$this->smartblog->isLoaded()) {
                $this->smartblog->loadFromConfig();
            }
        }
        
        if (self::isPrestaBlogInstalled()) {
            if (!$this->prestablog->isLoaded()) {
                $this->prestablog->loadFromConfig();
            }
        }
        
        if (self::isSimpleBlogInstalled()) {
            if (!$this->simpleblog->isLoaded()) {
                $this->simpleblog->loadFromConfig();
            }
        }
        
        if (self::isFAQsInstalled()) {
            if (!$this->faqs->isLoaded()) {
                $this->faqs->loadFromConfig();
            }
        }
    }
    
    public static function isSimpleBlogInstalled()
    {
        if (Module::isEnabled('ph_simpleblog')) {
            $module = Module::getInstanceByName('ph_simpleblog');
            if ($module->author == 'PrestaHome') {
                return true;
            }
        }
        return false;
    }
    
    public static function isPrestaBlogInstalled()
    {
        if (Module::isEnabled('prestablog')) {
            $module = Module::getInstanceByName('prestablog');
            if ($module->author == 'Prestablog') {
                return true;
            }
        }
        return false;
    }
    
    public static function isSmartBlogInstalled()
    {
        return Module::isEnabled('smartblog');
    }
    
    public static function isSmartBlog2()
    {
        if ($module = Module::getInstanceByName('smartblog')) {
            $version = $module->version;
            if ((version_compare($version, '2.0.0', '>=') === true) && (version_compare($version, '3.0.0', '<') === true)) {
                return true;
            }
        }
        return false;
    }
    
    public static function isSmartBlog3()
    {
        if ($module = Module::getInstanceByName('smartblog')) {
            $version = $module->version;
            if ((version_compare($version, '3.0.0', '>=') === true) && (version_compare($version, '4.0.0', '<') === true)) {
                return true;
            }
        }
        return false;
    }
    
    public static function isFAQsInstalled()
    {
        return Module::isEnabled('faqs');
    }
    
    public static function getFAQUrl($id_lang, $rewrite, $categoryRewrite)
    {
        $faqURL = self::getFAQBaseUrl($id_lang);
        if (self::getFAQRewriteSettings()) {
            return $faqURL . $categoryRewrite . '/' . $rewrite . '.html';
        } else {
            return $faqURL . '&category=' . $categoryRewrite . '&question=' . $rewrite;
        }
    }
    
    public static function getFAQCategoryUrl($id_lang, $rewrite)
    {
        $faqURL = self::getFAQBaseUrl($id_lang);
        if ($rewrite) {
            if (self::getFAQRewriteSettings()) {
                return $faqURL . $rewrite . '.html';
            } else {
                return $faqURL . '&category=' . $rewrite;
            }
        }
        return $faqURL;
    }
    
    public static function getFAQBaseUrl($id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        $language = new Language($id_lang);
        if (!self::getFAQRewriteSettings()) {
            if (Language::isMultiLanguageActivated()) {
                $baseUrl = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=faqs&controller=display&id_lang=' . $id_lang;
            } else {
                $baseUrl = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=faqs&controller=display';
            }
        } else {
            if (Language::isMultiLanguageActivated()) {
                $baseUrl = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . $language->iso_code . '/'.self::getFAQHomePageId().'/';
            } else {
                $baseUrl = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . self::getFAQHomePageId().'/';
            }
        }

        return $baseUrl;
    }
    
    public static function getFAQRewriteSettings()
    {
        if (self::$faqRewriteSettings === null) {
            self::$faqRewriteSettings = (int)Configuration::get('PS_REWRITING_SETTINGS');
        }
        return self::$faqRewriteSettings;
    }
    
    public static function getFAQHomePageId()
    {
        if (self::$faqHomePageId === null) {
            self::$faqHomePageId = Configuration::get('FAQS_SEO_HOME_PAGE') != false ? Configuration::get('FAQS_SEO_HOME_PAGE') : 'faqs';
        }
        return self::$faqHomePageId;
    }
}
