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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__).'/classes/ArSeoProInstaller.php';
include_once dirname(__FILE__).'/classes/ArSeoProLogger.php';
include_once dirname(__FILE__).'/classes/ArSeoProUrls.php';
include_once dirname(__FILE__).'/classes/ArSeoProJsonLD.php';
include_once dirname(__FILE__).'/classes/ArSeoProSitemap.php';
include_once dirname(__FILE__).'/classes/ArSeoProRedirects.php';

include_once dirname(__FILE__).'/classes/ArSeoProFavicon.php';
include_once dirname(__FILE__).'/classes/ArSeoProCanonical.php';
include_once dirname(__FILE__).'/classes/canonical/ArSeoProCanonicalProduct.php';
include_once dirname(__FILE__).'/classes/canonical/ArSeoProCanonicalCategory.php';
include_once dirname(__FILE__).'/classes/ArSeoProTools.php';

include_once dirname(__FILE__).'/classes/redirects/models/ArSeoProRedirectTable.php';
include_once dirname(__FILE__).'/classes/meta/models/ArSeoProMetaTable.php';
include_once dirname(__FILE__).'/classes/meta/models/ArSeoProMetaData.php';

include_once dirname(__FILE__).'/classes/ArSeoFOCheck.php';

/**
 * @property ArSeoProUrls $urlConfig
 * @property ArSeoProSitemap $sitemapConfig
 * @property ArSeoProMeta $metaConfig
 */
class ArSeoPro extends Module
{
    const REMIND_TO_RATE = 259200; // 3 days
    const ADDONS_ID = 44489;
    const AUTHOR_ID = 675406;
    
    protected $html;
    protected $installer = null;
    
    protected $urlConfig;
    protected $jsonLDConfig;
    protected $sitemapConfig;
    protected $canonicalConfig;
    protected $metaConfig;
    protected $faviconConfig;
    protected $redirectsConfig;

    protected $productListJson = null;

    protected static $langs = null;
    
    protected $logger;

    public $max_image_size;
    
    protected $routesDisabled = false;

    public function __construct()
    {
        $this->name = 'arseopro';
        $this->tab = 'seo';
        $this->version = '1.9.5';
        $this->author = 'Areama';
        $this->controllers = array('ajax');
        $this->need_instance = 0;
        $this->bootstrap = true;
        if ($this->is17() || $this->is8x()) {
            $this->ps_versions_compliancy = array(
                'min' => '1.7',
                'max' => _PS_VERSION_
            );
        }
        $this->module_key = '93755410d6412f7524e43646ad0379bb';
        parent::__construct();

        $this->displayName = $this->l('SeoPro - All-In-One SEO');
        $this->description = $this->l('Improve your SEO - clean URLs, OpenGraph tags, Twitter tags, meta tags, redirects, sitemaps, JSON-LD microdata and much more!');
        $this->confirmUninstall = $this->l('Are you sure you want to delete all data?');
        
        $this->initConfig();
    }
    
    public function getOverridesVersion()
    {
        $versions = array();
        $dispatcher = Dispatcher::getInstance();
        if (method_exists($dispatcher, 'arSeoProOverrideVersion')) {
            $versions['Dispatcher'] = $dispatcher->arSeoProOverrideVersion();
        } else {
            $versions['Dispatcher'] = 'unknown';
        }
        $versions['FrontController'] = $this->version;
        
        $link = Context::getContext()->link;
        if (method_exists($link, 'arSeoProOverrideVersion')) {
            $versions['Link'] = $link->arSeoProOverrideVersion();
        } else {
            $versions['Link'] = 'unknown';
        }
        return $versions;
    }
    
    /**
     * 
     * @return ArSeoProLogger
     */
    public function getLogger()
    {
        if (empty($this->logger)) {
            $this->logger = ArSeoProLogger::getInstance();
        }
        return $this->logger;
    }
    
    protected function initConfig()
    {
        $this->urlConfig = new ArSeoProUrls($this);
        $this->jsonLDConfig = new ArSeoProJsonLD($this);
        $this->sitemapConfig = new ArSeoProSitemap($this);
        $this->faviconConfig = new ArSeoProFavicon($this, 'arsf_');
        $this->canonicalConfig = new ArSeoProCanonical($this, 'arscc_');
        $this->redirectsConfig = new ArSeoProRedirects($this, 'arsr_');
    }
    
    public function getController()
    {
        return $this->context->controller;
    }
    
    public function getControllerId()
    {
        return (isset($this->context->controller->php_self) && $this->context->controller->php_self)? $this->context->controller->php_self : null;
    }
    
    public static function getProductAnchor($id, $id_product_attribute, $with_id = false)
    {
        $attributes = Product::getAttributesParams($id, $id_product_attribute);
        $anchor = '#';
        $sep = Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR');
        foreach ($attributes as &$a) {
            foreach ($a as &$b) {
                $b = str_replace($sep, '_', Tools::link_rewrite($b));
            }
            $anchor .= '/' . ($with_id && isset($a['id_attribute']) && $a['id_attribute'] ? $a['id_attribute_group'] . '|' . (int)$a['id_attribute'] . $sep : '') . $a['group'] . $sep . $a['name'];
        }

        return $anchor;
    }
    
    public function assignAttributes()
    {
        if ($this->getControllerId() == 'product' && Tools::getValue('ajax', 'false') == 'false' && Tools::getValue('id_product', 'false') != 'false') {
            $attributes = Product::getProductAttributesIds(Tools::getValue('id_product'));
            if (is_array($attributes)) {
                if (count($attributes) > 0) {
                    $this->context->controller->addJS($this->_path.'views/js/script.js');
                    $json = array();
                    foreach ($attributes as $combination) {
                        $json[str_replace("#/", "", self::getProductAnchor(Tools::getValue('id_product'), (int)$combination['id_product_attribute'], false))] = str_replace("#/", "", self::getProductAnchor(Tools::getValue('id_product'), (int)$combination['id_product_attribute'], true));
                    }
                    Media::addJsDef(array(
                        'arSEOAttributes' => $json,
                        'arSEORemoveIdFromHash' => (int)$this->urlConfig->product->remove_anchor_id
                    ));
                }
            }
        }
    }
    
    public function hookDisplayHeader($params)
    {
        $content = '';
        $this->faviconConfig->loadFromConfig();
        $faviconFilename = pathinfo($this->faviconConfig->icon, PATHINFO_FILENAME);
        $faviconExt = pathinfo($this->faviconConfig->icon, PATHINFO_EXTENSION);
        $id_shop = Context::getContext()->shop->id;
        if ($this->getControllerId() == 'product' && ($this->is17() || $this->is8x())) {
            $this->assignAttributes();
            
        }
        
        /*if (empty($this->faviconConfig->icon)) {
            return null;
        }*/
        
        if (file_exists($this->getUploadPath() . 'manifest' . $id_shop . '.json')) {
            $manifestFileName = 'manifest' . $id_shop . '.json';
        } else {
            $manifestFileName = 'manifest.json';
        }
        
        $faviconVars = array(
            'moduleUploadUrl' => $this->getUploadsUrl(),
            'faviconConfig' => $this->faviconConfig,
            'faviconFilename' => $faviconFilename,
            'faviconExt' => $faviconExt,
            'faviconSizes' => $this->faviconConfig->faviconSizes(),
            'appleTouchSizes' => $this->faviconConfig->appleTouchIconSizes(),
            'msTileSizes' => $this->faviconConfig->msTileSizes(),
            'manifestFileName' => $manifestFileName,
            'id_shop' => $id_shop
        );
        
        $vars = array(
            'domain' => Tools::getShopDomainSsl(false),
            'url' => Tools::getShopDomainSsl(true) . $_SERVER['REQUEST_URI'],
            'sitename' => Configuration::get('PS_SHOP_NAME'),
            'faviconConfig' => $this->faviconConfig,
        );
        
        $content .= $this->render('head_favicon.tpl', $faviconVars);
        
        if ($this->getController() instanceof CategoryController || $this->getController() instanceof CategoryControllerCore) {
            if ($row = ArSeoProMetaTable::getRowByRelation($this->context->controller->getCategory()->id, Context::getContext()->language->id, 'category')) {
                $metaData = new ArSeoProMetaData($this, $row, $this->context->controller->getCategory(), Context::getContext()->language->id);
                $metaData->prepareData();
                $vars['metaData'] = $metaData;
                $content .= $this->render('head_meta.tpl', $vars);
            }
        } elseif ($this->getControllerId() == 'product') {
            if ($row = ArSeoProMetaTable::getRowByRelation($this->context->controller->getProduct()->id, Context::getContext()->language->id, 'product')) {
                $metaData = new ArSeoProMetaData($this, $row, $this->context->controller->getProduct(), Context::getContext()->language->id);
                $metaData->prepareData();
                $vars['metaData'] = $metaData;
                $content .= $this->render('head_meta.tpl', $vars);
            }
        } elseif ($this->getControllerId() == 'manufacturer') {
            $id = Tools::getValue('id_manufacturer');
            $brand = new Manufacturer($id, Context::getContext()->language->id);
            if ($row = ArSeoProMetaTable::getRowByRelation($id, Context::getContext()->language->id, 'brand')) {
                $metaData = new ArSeoProMetaData($this, $row, $brand, Context::getContext()->language->id);
                $metaData->prepareData();
                $vars['metaData'] = $metaData;
                $content .= $this->render('head_meta.tpl', $vars);
            }
        } else {
            $meta = Meta::getMetaByPage($this->getControllerId(), Context::getContext()->language->id);
            if (!empty($meta)) {
                if ($row = ArSeoProMetaTable::getRowByRelation($meta['id_meta'], Context::getContext()->language->id, 'metapage')) {
                    $model = new Meta($meta['id_meta'], Context::getContext()->language->id);
                    $metaData = new ArSeoProMetaData($this, $row, $model, Context::getContext()->language->id);
                    $metaData->prepareData();
                    $vars['metaData'] = $metaData;
                    $content .= $this->render('head_meta.tpl', $vars);
                }
            }
        }
        if ($this->getCanonicalConfig()->enable) {
            $content .= $this->getCanonicalConfig()->getCanonicalData($params);
        }
        return $content;
    }
    
    public function registerFilters()
    {
        $smarty = Context::getContext()->smarty;
        $smarty->registerFilter('output', array(Module::getInstanceByName($this->name), 'parseTemplate'));
    }
    
    public function parseTemplate($output, $smarty = null)
    {
        return $this->jsonLDConfig->cleanUpMicrodata($output);
    }
    
    public function hookDisplayFooter($params)
    {
        if ($this->is16()) {
            return $this->hookDisplayBeforeBodyClosingTag($params);
        }
    }
    
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        $content = array();
        $this->jsonLDConfig->loadAllFromConfig();
        
        if (!$this->jsonLDConfig->general->enable) {
            return null;
        }
        if ($this->jsonLDConfig->general->cleanup) {
            $this->registerFilters();
        }
        
        if ($this->jsonLDConfig->general->breadcrumbs && method_exists(Context::getContext()->controller, 'getBreadcrumbLinks') && is_callable(array(Context::getContext()->controller, 'getBreadcrumbLinks'))) {
            try {
                $breadcrumbs = Context::getContext()->controller->getBreadcrumbLinks();
                $breadcrumbsData = $this->jsonLDConfig->buildBreadcrumbsJson($breadcrumbs);
                $content[] = $this->render('jsonld.tpl', array(
                    'jsonData' => ArSeoProTools::jsonEncode($breadcrumbsData, JSON_UNESCAPED_UNICODE)
                ));
            } catch (Exception $e) {
                // some exception. Ignore it
            }
        }
        
        if ((Context::getContext()->controller instanceof ProductController || Context::getContext()->controller instanceof ProductControllerCore) && $this->jsonLDConfig->product->enable) {
            $product = Context::getContext()->controller->getProduct();
            if ($product) {
                $ipa = Tools::getValue('id_product_attribute')? Tools::getValue('id_product_attribute') : $product->cache_default_attribute;
                $jsonData = $this->jsonLDConfig->buildProductJson($product, $ipa);
                $content[] = $this->render('jsonld.tpl', array(
                    'jsonData' => ArSeoProTools::jsonEncode($jsonData, JSON_UNESCAPED_UNICODE)
                ));
            }
        }
        
        if ($this->is17() || $this->is8x()) {
            if (Context::getContext()->controller instanceof ProductListingFrontController && $this->jsonLDConfig->product->enable_list && !empty($this->productListJson)) {
                $content[] = $this->render('jsonld.tpl', array(
                    'jsonData' => ArSeoProTools::jsonEncode($this->productListJson, JSON_UNESCAPED_UNICODE)
                ));
            }
        } elseif ($this->is16()) {
            if (Context::getContext()->controller instanceof CategoryController && $this->jsonLDConfig->product->enable_list && !empty($this->productListJson)) {
                $content[] = $this->render('jsonld.tpl', array(
                    'jsonData' => ArSeoProTools::jsonEncode($this->productListJson, JSON_UNESCAPED_UNICODE)
                ));
            }
        }
        
        if ($this->jsonLDConfig->general->store) {
            $storeData = $this->jsonLDConfig->buildStoreJson();
            $content[] = $this->render('jsonld.tpl', array(
                'jsonData' => ArSeoProTools::jsonEncode($storeData)
            ));
        }
        return implode(PHP_EOL, $content);
    }
    
    public function hookActionProductSearchAfter($params)
    {
        if (($this->is17() || $this->is8x()) && isset($params['result'])) {
            $this->jsonLDConfig->loadAllFromConfig();
        
            if (!$this->jsonLDConfig->general->enable || !$this->jsonLDConfig->product->enable_list) {
                return null;
            }
            
            $this->productListJson = $this->jsonLDConfig->buildProductListJson($params['result']->getProducts());
        }
    }
    
    public function hookActionProductListModifier($params)
    {
        if ($this->is16() && isset($params['cat_products'])) {
            $this->jsonLDConfig->loadAllFromConfig();
        
            if (!$this->jsonLDConfig->general->enable) {
                return null;
            }
            
            $this->productListJson = $this->jsonLDConfig->buildProductListJson($params['cat_products']);
        }
    }
    
    public function hookActionAdminMetaAfterWriteRobotsFile($params)
    {
        if (isset($params['write_fd']) && $params['write_fd']) {
            if (!Shop::isFeatureActive()) {
                $host = Tools::getHttpHost(Configuration::get('PS_SSL_ENABLED'));
                fwrite($params['write_fd'], "\n" . 'Host: ' . $host . "\n");
            }
            $generator = new ArSeoProSitemapGenerator($this, Context::getContext()->shop->id);
            $generator->setIndexPath($this->getIndexSitemapPath(false));
            $generator->updateRobots();
        }
    }
    
    public function hookDisplayAdminNavBarBeforeEnd($params)
    {
        $moduleConfig = false;
        $productForm = false;
        $controller = $this->context->controller;
        if ($controller instanceof AdminModulesController || $controller instanceof AdminModulesControllerCore) {
            if (Tools::getValue('configure') == $this->name) {
                $moduleConfig = true;
            }
        }
        if (isset($controller->php_self) && $controller->php_self == 'AdminProducts') {
            $productForm = true;
        }
        return $this->render('admin_head.tpl', array(
            'path' => $this->getPath(),
            'arSEOAjaxURL' => $this->context->link->getAdminLink('AdminArSeoUrls'),
            'moduleConfig' => $moduleConfig,
            'productForm' => $productForm
        ));
        return null;
    }
    
    /**
     *
     * @return ArSeoProUrls
     */
    public function getUrlConfig()
    {
        return $this->urlConfig;
    }
    
    /**
     *
     * @return ArSeoProMeta
     */
    public function getMetaConfig()
    {
        return $this->metaConfig;
    }
    
    /**
     *
     * @return ArSeoProFavicon
     */
    public function getFaviconConfig()
    {
        return $this->faviconConfig;
    }
    
    /**
     *
     * @return ArSeoProRedirects
     */
    public function getRedirectsConfig()
    {
        if (!$this->redirectsConfig->isLoaded()) {
            $this->redirectsConfig->loadFromConfig();
        }
        return $this->redirectsConfig;
    }
    
    /**
     *
     * @return ArSeoProCanonical
     */
    public function getCanonicalConfig()
    {
        if (!$this->canonicalConfig->isLoaded()) {
            $this->canonicalConfig->loadFromConfig();
        }
        return $this->canonicalConfig;
    }

    public function hookActionDispatcher($params)
    {
        return $this->urlConfig->dispatcher($params);
    }
    
    public function hookModuleRoutes($params)
    {
        if (!$this->routesDisabled) {
            return $this->urlConfig->getRoutes($params);
        }
    }
    
    public function disableRoutes($value)
    {
        $this->routesDisabled = $value;
    }

    /**
     *
     * @return ArSeoProInstaller
     */
    public function getInstaller()
    {
        if (!$this->installer) {
            $this->installer = new ArSeoProInstaller($this);
        }
        return $this->installer;
    }


    public function install()
    {
        return $this->getInstaller()->prepareOverrides() && parent::install() && $this->getInstaller()->install();
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->getInstaller()->uninstall();
    }
    
    public function getForms()
    {
        return array(
            $this->urlConfig->general,
            $this->urlConfig->product,
            $this->urlConfig->category,
            $this->urlConfig->manufacturer,
            $this->urlConfig->supplier,
            $this->urlConfig->cms,
            $this->urlConfig->cmsCategory,
            
            $this->jsonLDConfig->general,
            $this->jsonLDConfig->product,
            $this->jsonLDConfig->advanced,
            
            $this->sitemapConfig->categories,
            $this->sitemapConfig->cms,
            $this->sitemapConfig->general,
            $this->sitemapConfig->manufacturers,
            $this->sitemapConfig->products,
            $this->sitemapConfig->suppliers,
            $this->sitemapConfig->meta,
            $this->sitemapConfig->smartblog,
            $this->sitemapConfig->prestablog,
            $this->sitemapConfig->simpleblog,
            $this->sitemapConfig->faqs,
            
            $this->faviconConfig,
            
            $this->canonicalConfig,
            
            $this->redirectsConfig
        );
    }
    
    public function getContent()
    {
        if ((bool)Tools::getValue('clearGlobalCache')) {
            $this->clearGlobalCache();
            $this->html .= $this->displayConfirmation($this->l('Cache cleared'));
        }
        if ($this->isSubmit()) {
            if ($this->postValidate()) {
                $this->postProcess();
            }
        }
        
        $this->html .= $this->renderForm();
        return $this->html;
    }
    
    public function isSubmit()
    {
        foreach ($this->getAllowedSubmits() as $submit) {
            if (Tools::isSubmit($submit)) {
                return true;
            }
        }
    }
    
    public function getAllowedSubmits()
    {
        $submits = array();
        foreach ($this->getForms() as $model) {
            $submits[] = get_class($model);
        }
        return $submits;
    }
    
    public function postProcess()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->populate();
                if ($model->saveToConfig()) {
                    $this->html .= $this->displayConfirmation($this->l('Settings updated'));
                } else {
                    $this->postValidate();
                }
            }
        }
    }
    
    public function postValidate()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->loadFromConfig();
                $model->populate();
                if (!$model->validate()) {
                    foreach ($model->getErrors() as $errors) {
                        foreach ($errors as $error) {
                            $this->html .= $this->displayError($error);
                        }
                    }
                    return false;
                }
                return true;
            }
        }
    }
    
    public function renderForm()
    {
        $this->max_image_size = (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE');
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', true).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'path' => $this->getPath(),
        );
        $helper->base_folder =  dirname(__FILE__);
        
        $helper->base_tpl = '/views/templates/admin/arseopro/helpers/form/form.tpl';
        
        $nflLastTime = null;
        if ($time = Configuration::get('ARSEO_NFL_TIME')) {
            $nflLastTime = date('Y-m-d H:i:s', $time);
        }
        
        $categoriesHelper = new HelperTreeCategories('arseo-categories');
        $categoriesHelper->setUseCheckBox(true);
        
        $sitemapCategoriesHelper = new HelperTreeCategories('arseo-sitemap-category-tree');
        $sitemapCategoriesHelper->setUseCheckBox(true); //->setUseSearch(true);
        
        $metaCategoriesHelper = new HelperTreeCategories('arseo-meta-categories');
        $metaCategoriesHelper->setUseCheckBox(true);
        
        $fbImageUploader = new HelperImageUploader('arseopro_fb_upload_image');
        $fbImageUploader
                ->setMultiple(false)
                ->setTitle($this->l('Select file'))
                ->setUseAjax(true)
                ->setMaxFiles(1)
                ->setTemplateDirectory($this->getPath(true) . 'views/templates/admin/arseopro/helpers/uploader/')
                ->setUrl(Context::getContext()->link->getAdminLink('AdminArSeoMeta').'&ajax=1&action=uploadFbCustomImage');
        
        $twImageUploader = new HelperImageUploader('arseopro_tw_upload_image');
        $twImageUploader
                ->setMultiple(false)
                ->setTitle($this->l('Select file'))
                ->setUseAjax(true)
                ->setMaxFiles(1)
                ->setTemplateDirectory($this->getPath(true) . 'views/templates/admin/arseopro/helpers/uploader/')
                ->setUrl(Context::getContext()->link->getAdminLink('AdminArSeoMeta').'&ajax=1&action=uploadTwCustomImage');
        
        $sql = 'SELECT id_category FROM `' . ArSeoProSitemapCategory::getTableName() . '` WHERE id_shop=' . (int)Context::getContext()->shop->id . ' AND `export`=1';
        $categories = Db::getInstance()->executeS($sql);
        $selectedCategories = array();
        foreach ($categories as $row) {
            $selectedCategories[] = $row['id_category'];
        }
        $sitemapCategoriesHelper->setSelectedCategories($selectedCategories);
        
        if (!$this->isDirectoryExists($this->getSitemapPath(Context::getContext()->shop->id))) {
            $this->createDirectory($this->getSitemapPath(Context::getContext()->shop->id));
        }
        
        $dispatcher = Dispatcher::getInstance();
        
        $context = Context::getContext();
        $shops = self::getShops();
        $routes = method_exists($dispatcher, 'getRoutes') ? $dispatcher->getRoutes() : false;
        $langs = array();
        foreach (Language::getLanguages() as $l) {
            $langs[$l['id_lang']] = $l;
        }
        
        if (!is_null($context) && isset($context->smarty) && !is_null($context->smarty)) {
            $context->smarty->assign(array(
                'form' => $helper,
                'urlGeneralFormParams' => array($this->getForm($this->urlConfig->general)),
                'urlProductFormParams' => array($this->getForm($this->urlConfig->product)),
                'urlCategoryFormParams' => array($this->getForm($this->urlConfig->category)),
                'urlManufacturerFormParams' => array($this->getForm($this->urlConfig->manufacturer)),
                'urlSupplierFormParams' => array($this->getForm($this->urlConfig->supplier)),
                'urlCMSFormParams' => array($this->getForm($this->urlConfig->cms)),
                'urlCMSCategoryFormParams' => array($this->getForm($this->urlConfig->cmsCategory)),

                'jsonldGeneralFormParams' => array($this->getForm($this->jsonLDConfig->general)),
                'jsonldProductFormParams' => array($this->getForm($this->jsonLDConfig->product)),
                'jsonldAdvancedFormParams' => array($this->getForm($this->jsonLDConfig->advanced)),

                'sitemapProductsFormParams' => array($this->getForm($this->sitemapConfig->products)),
                'sitemapCategoriesFormParams' => array($this->getForm($this->sitemapConfig->categories)),
                'sitemapManufacturersFormParams' => array($this->getForm($this->sitemapConfig->manufacturers)),
                'sitemapSuppliersFormParams' => array($this->getForm($this->sitemapConfig->suppliers)),
                'sitemapCMSFormParams' => array($this->getForm($this->sitemapConfig->cms)),
                'sitemapGeneralFormParams' => array($this->getForm($this->sitemapConfig->general)),
                'sitemapMetaFormParams' => array($this->getForm($this->sitemapConfig->meta)),
                'sitemapSmartblogFormParams' => ArSeoProSitemap::isSmartBlogInstalled()? array($this->getForm($this->sitemapConfig->smartblog)) : null,
                'sitemapPrestablogFormParams' => ArSeoProSitemap::isPrestaBlogInstalled()? array($this->getForm($this->sitemapConfig->prestablog)) : null,
                'sitemapSimpleblogFormParams' => ArSeoProSitemap::isSimpleBlogInstalled()? array($this->getForm($this->sitemapConfig->simpleblog)) : null,
                'sitemapFAQFormParams' => ArSeoProSitemap::isFAQsInstalled()? array($this->getForm($this->sitemapConfig->faqs)) : null,

                'smartblogEnabled' => ArSeoProSitemap::isSmartBlogInstalled(),
                'prestablogEnabled' => ArSeoProSitemap::isPrestaBlogInstalled(),
                'simpleblogEnabled' => ArSeoProSitemap::isSimpleBlogInstalled(),
                'FAQEnabled' => ArSeoProSitemap::isFAQsInstalled(),

                'faviconFormParams' => array($this->getForm($this->faviconConfig)),
                
                'canonicalFormParams' => array($this->getForm($this->canonicalConfig)),
                
                'redirectsFormParams' => array($this->getForm($this->redirectsConfig)),

                'sitemapConfig' => $this->sitemapConfig,
                'link' => $this->context->link,
                'path' => $this->getPath(),
                'name' => $this->displayName,
                'version' => $this->version,
                'activeTab' => $this->getActiveTab(),
                'activeSubTab' => $this->getActiveSubTab(),
                'ajaxUrl' => $this->getAjaxUrl(),
                'moduleUrl' => $this->getModuleBaseUrl(),
                'serverUrl' => Tools::getShopDomainSsl(true),
                'moduleId' => self::ADDONS_ID,
                'authorId' => self::AUTHOR_ID,
                'nflLastTime' => $nflLastTime,
                'multishop' => Shop::isFeatureActive(),
                'langs' => Language::getLanguages(),
                'categoriesTree' => $categoriesHelper->render(),
                'metaCategoriesTree' => $metaCategoriesHelper->render(),
                'sitemapCategoriesHelper' => $sitemapCategoriesHelper->render(),
                'id_shop' => Context::getContext()->shop->id,
                'shops' => $shops,
                'memoryLimit' => ini_get('memory_limit'),
                'maxExecutionTime' => ini_get('max_execution_time'),
                'target' => 'arseo-url-rule-form_rule',
                'fbImageUploader' => $fbImageUploader->render(),
                'twImageUploader' => $twImageUploader->render(),
                'keywords' => self::getProductKeywords(),
                'max_image_size' => (int)$this->max_image_size,
                'maxImageSize' => $this->formatBytes((int)$this->max_image_size),
                'twitterTypes' => $this->getTwitterTypes(),
                'languages' => $this->context->controller->getLanguages(),
                'defaultFormLanguage' => (int)(Configuration::get('PS_LANG_DEFAULT')),
                'sitemapDir' => $this->getSitemapPath(Context::getContext()->shop->id),
                'sitemapIndexDir' => $this->getIndexSitemapPath(),
                'sitemapDirWritable' => is_writable($this->getSitemapPath(Context::getContext()->shop->id)),
                'indexSitemapDirWriteable' => is_writable($this->getIndexSitemapPath(true)),
                'sitemapCronUrl' => $this->getSitemapCronUrl(Context::getContext()->shop->id),
                'multishop' => Shop::isFeatureActive(),
                'currentShopId' => Context::getContext()->shop->id,
                'currentShopSitemapToken' => Configuration::get('ARSEO_SITEMAP_TOKEN', null, null, Context::getContext()->shop->id),
                'metaPages' => $this->getMetaPages(),
                'isRootWriteable' => is_writable(_PS_ROOT_DIR_),
                'isRootSitemapWriteable' => is_writable($this->normalizePath(_PS_ROOT_DIR_ . '/sitemaps/')),
                'psRootDir' => _PS_ROOT_DIR_,
                'overridesDisabled' => Configuration::get('PS_DISABLE_OVERRIDES'),
                'preformanceSettingsUrl' => $this->context->link->getAdminLink('AdminPerformance'),
                'overridesVersion' => $this->getOverridesVersion(),
                'arSEOProVersion' => $this->version,
                'routes' => $routes,
                'langs' => $langs
            ));
            return $this->display(__FILE__, 'config.tpl');
        }
    }
    
    public function getMetaPages()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'meta` m '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'meta_lang` ml ON m.id_meta = ml.id_meta '
                . 'WHERE m.page NOT IN("pagenotfound", "addresses", "discount", "history", "identity", "my-account", "order-follow", "order-slip", "attachment") '
                . 'AND ((ml.url_rewrite IS NOT NULL AND ml.url_rewrite != "") OR m.page = "index")'
                . 'AND ml.id_lang = ' . (int)Context::getContext()->language->id . ' AND ml.id_shop = ' . (int)Context::getContext()->shop->id;
        $res = Db::getInstance()->executeS($sql);
        foreach ($res as $k => $page) {
            $res[$k]['url'] = Context::getContext()->link->getPageLink($page['page'], null, Context::getContext()->language->id);
        }
        return $res;
    }
    
    public function getShops()
    {
        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
        } else {
            $shops = array(Shop::getShop(Context::getContext()->shop->id));
        }
        $res = array();
        foreach ($shops as $k => $shop) {
            $res[$shop['id_shop']] = $shop;
            $res[$shop['id_shop']]['sitemapCronUrl'] = $this->getSitemapCronUrl($shop['id_shop']);
            $res[$shop['id_shop']]['sitemapUrl'] = $this->getIndexSitemapUrl($shop['id_shop']);
            $lastgen = Configuration::get('ARSEO_SITEMAP_GEN', null, null, $shop['id_shop']);
            $res[$shop['id_shop']]['sitemapLastegen'] = $lastgen? date('Y-m-d H:i:s', $lastgen) : null;
            $res[$shop['id_shop']]['sitemap_token'] = Configuration::get('ARSEO_SITEMAP_TOKEN', null, null, $shop['id_shop']);
        }
        return $res;
    }
    
    public function getLanguages($active = true, $id_shop = false)
    {
        if (self::$langs === null) {
            $languages = Language::getLanguages($active, $id_shop);
            self::$langs = array();
            if ($languages) {
                foreach ($languages as $language) {
                    self::$langs[] = (int)$language['id_lang'];
                }
            }
        }
        return self::$langs;
    }
    
    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    public function getRedirect($id)
    {
        return new ArSeoProRedirectTable($id);
    }
    
    public function redirect()
    {
        if (Configuration::get('ARSR_ENABLE')) {
            $uri = $_SERVER['REQUEST_URI'];
            $url = $this->getShopDomain() . $uri;
            $shopId = Context::getContext()->shop->id;
            $lang = Context::getContext()->language->iso_code;
            $sql = 'SELECT * FROM '._DB_PREFIX_.'arseopro_redirect '.
                'WHERE (`from` = "'.pSQL($uri).'" OR `from` = "' . pSQL($url) .  '") ' .
                'AND id_shop IN(0, '.(int)$shopId.') '.
                'AND status = 1 ' .
                'ORDER BY id_redirect DESC';
            if (!$redirect = Db::getInstance()->getRow($sql)) {
                $uri = preg_replace("{^/{$lang}/}is", '/{lang}/', $uri);
                $url = $this->getShopDomain() . $uri;
                $sql = 'SELECT * FROM '._DB_PREFIX_.'arseopro_redirect '.
                    'WHERE (`from` = "'.pSQL($uri).'" OR `from` = "' . pSQL($url) .  '") '.
                    'AND id_shop IN(0, '.(int)$shopId.') '.
                    'AND status = 1 ' .
                    'ORDER BY id_redirect DESC';
                $redirect = Db::getInstance()->getRow($sql);
            }
            
            if ($redirect) {
                $model = $this->getRedirect($redirect['id_redirect']);
                $headers = array(
                    $model->getRedirectHeader()
                );
                if (Configuration::get('ARSR_DEBUG')) {
                    echo 'Rule ID: <b>' . $model->id . '</b><br/>';
                    echo 'Rule: <b>' . $model->from . '</b><br/>';
                    echo 'Redirect type: <b>' . $model->getRedirectHeader() . '</b><br/>';
                    echo 'Redirect to: <a href="' . $model->getRedirectToUrl($lang) . '">' . $model->getRedirectToUrl($lang) . '</a>';
                    die();
                }
                $model->use_times ++;
                $model->last_used_at = date('Y-m-d H:i:s');
                Tools::redirect($model->getRedirectToUrl($lang), __PS_BASE_URI__, null, $headers);
            }
        }
    }
    
    public function render($file, $params = array())
    {
        $context = Context::getContext();
        if (!is_null($context) && isset($context->smarty) && !is_null($context->smarty)) {
            $context->smarty->assign($params);
            return $this->display(__FILE__, $file);
        }
    }
    
    public function getActiveTab()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                return $model::getConfigTab();
            }
        }
        if (Tools::getValue('activeTab')) {
            return Tools::getValue('activeTab');
        }
        return null;
    }
    
    public function getActiveSubTab()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                return get_class($model);
            }
        }
        return null;
    }
    
    public function getFormConfigs()
    {
        $configs = array();
        foreach ($this->getForms() as $form) {
            $configs[] = $this->getForm($form);
        }
        return $configs;
    }
    
    public function getForm($model)
    {
        $model->populate();
        $model->validate(false);
        $config = $model->getFormHelperConfig();
        return array(
            'form' => array(
                'name' => get_class($model),
                'legend' => array(
                    'title' => $model->getFormTitle(),
                    'icon' => $model->getFormIcon()
                ),
                'input' => $config,
                'submit' => array(
                    'name' => get_class($model),
                    'class' => $this->is15()? 'button' : null,
                    'title' => $this->l('Save'),
                )
            )
        );
    }
    
    public function getAjaxUrl()
    {
        return array(
            'default' => $this->context->link->getAdminLink('AdminArSeo'),
            'url' => $this->context->link->getAdminLink('AdminArSeoUrls'),
            'meta' => $this->context->link->getAdminLink('AdminArSeoMeta'),
            'redirect' => $this->context->link->getAdminLink('AdminArSeoRedirects'),
            'sitemap' => $this->context->link->getAdminLink('AdminArSeoSitemap'),
            'sitemapProduct' => $this->context->link->getAdminLink('AdminArSeoSitemapProduct'),
            'sitemapSupplier' => $this->context->link->getAdminLink('AdminArSeoSitemapSupplier'),
            'sitemapManufacturer' => $this->context->link->getAdminLink('AdminArSeoSitemapManufacturer'),
            'sitemapCms' => $this->context->link->getAdminLink('AdminArSeoSitemapCms'),
            'sitemapMeta' => $this->context->link->getAdminLink('AdminArSeoSitemapMeta'),
            'sitemapCategory' => $this->context->link->getAdminLink('AdminArSeoSitemapCategory'),
            'robots' => $this->context->link->getAdminLink('AdminArSeoRobots'),
            'sitemapGenerate' => $this->getSitemapCronUrl(null, true, true)
        );
    }
    
    public function getSitemapCronUrl($id_shop, $relative = false, $noToken = false)
    {
        if ($id_shop == null) {
            if ($noToken) {
                $url = Context::getContext()->link->getModuleLink($this->name, 'ajax');
            } else {
                $url = Context::getContext()->link->getModuleLink($this->name, 'ajax') . '?token=' . Configuration::get('ARSEO_SITEMAP_TOKEN', null, null, $id_shop);
            }
        } else {
            if ($noToken) {
                $url = Context::getContext()->link->getModuleLink($this->name, 'ajax') . '?id_shop=' . $id_shop;
            } else {
                $url = Context::getContext()->link->getModuleLink($this->name, 'ajax') . '?id_shop=' . $id_shop . '&token=' . ConfigurationCore::get('ARSEO_SITEMAP_TOKEN', null, null, $id_shop);
            }
        }
        if (!$relative) {
            return $url;
        }
        return preg_replace('{https?://.*?/}is', '/', $url);
    }
    
    public function getUploadPath()
    {
        $path = dirname(__FILE__) . '/uploads/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }
    
    public function getUploadsUrl()
    {
        return $this->getModuleBaseUrl() . 'uploads/';
    }
    
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getForms() as $model) {
            $model->loadFromConfig();
            $model->populate();
            foreach ($model->getAttributes() as $attr => $value) {
                $values[$model->getConfigAttribueName($attr)] = $value;
            }
        }
        return $values;
    }
    
    public function getTwitterTypes()
    {
        return array(
            'summary' => $this->l('Summary'),
            'summary_large_image' => $this->l('Summary with large image')
        );
    }
    
    public static function getProductKeywords()
    {
        return array(
            'name' => 'Product name',
            'description' => 'Product description',
            'description_short' => 'Product short description',
            'reference' => 'Product reference',
            'manufacturer' => 'Product manufacturer',
            'features' => 'Product features',
            'default_cat_name' => 'Product category name',
            'category_list' => 'All product categories',
            'mpn' => 'Product MPN',
            'ean13' => 'Prosuct EAN13',
            'price' => 'Product retail price',
            'reduce_price' => 'Product specific price',
            'price_wt' => 'Product pre-tax retail price',
            'reduce_price_wt' => 'Product pre-tax specific price',
            'reduction_percent' => 'Product reduction percent',
            'shop_name' => 'Shop name'
        );
    }
    
    public static function getCategoryKeywords()
    {
        return array(
            'name' => 'Category name',
            'description' => 'Category description',
            'meta_title' => 'Category meta title',
            'meta_description' => 'Category meta description',
            'meta_keywords' => 'Category meta keywords',
            'parent_category' => 'Parent category',
            'parent_categories' => 'All parent categories',
            'shop_name' => 'Shop name'
        );
    }
    
    public static function getMetaKeywords()
    {
        return array(
            'meta_title' => 'Page meta title',
            'meta_description' => 'Page meta description',
            'meta_keywords' => 'Page meta keywords',
            'shop_name' => 'Shop name'
        );
    }
    
    public static function getBrandKeywords()
    {
        return array(
            'meta_title' => 'Page meta title',
            'meta_description' => 'Page meta description',
            'meta_keywords' => 'Page meta keywords',
            'name' => 'Manufacturer name',
            'short_description' => 'Manufacturer short description',
            'description' => 'Manufacturer description',
            'shop_name' => 'Shop name'
        );
    }
    
    public function getShopDomain()
    {
        $ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        $shop = Context::getContext()->shop;
        
        $base = ($ssl ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        
        return $base;
    }
    
    public function hookActionObjectProductUpdateBefore($params)
    {
        if ($this->getRedirectsConfig()->auto_create) {
            if (isset($params['object']) && $params['object']) {
                $product = $params['object'];
                if ($product instanceof Product || $product instanceof ProductCore) {
                    $category = Category::getLinkRewrite((int) $product->id_category_default, (int)Context::getContext()->language->id);
                    $oldProduct = new Product($product->id, true);
                    $oldCategory = Category::getLinkRewrite((int) $oldProduct->id_category_default, (int)Context::getContext()->language->id);
                    $shopDomain = $this->getShopDomain();
                    foreach (Language::getLanguages() as $lang) {
                        $link = Context::getContext()->link->getProductLink($product, $product->link_rewrite[$lang['id_lang']], $category, null, (int)$lang['id_lang']);
                        $oldLink = Context::getContext()->link->getProductLink($oldProduct, $oldProduct->link_rewrite[$lang['id_lang']], $oldCategory, null, (int)$lang['id_lang']);
                        $link = str_replace($shopDomain, '', $link);
                        $oldLink = str_replace($shopDomain, '', $oldLink);
                        if ($link != $oldLink) {
                            $sql = 'DELETE FROM `' . _DB_PREFIX_ . ArSeoProRedirectTable::TABLE_NAME . '` WHERE `from` = "' . pSQL($link) . '"';
                            Db::getInstance()->execute($sql);
                            $model = new ArSeoProRedirectTable();
                            $model->from = pSQL($oldLink);
                            $model->to = pSQL($link);
                            $model->type = 302;
                            $model->status = 1;
                            $model->id_shop = Context::getContext()->shop->id;
                            $model->created_at = date('Y-m-d H:i:s');
                            $model->save();
                        }
                    }
                }
            }
        }
    }
    
    public function hookActionProductUpdate($params)
    {
        if ($this->getCanonicalConfig()->enable && $this->getCanonicalConfig()->product) {
            $id_product = (int) Tools::getValue('id_product');
            $urls = array();

            foreach (Language::getLanguages() as $language) {
                $urls[$language['id_lang']] = Tools::getValue('ARSEO_customCanonical_' . $language['id_lang']);
            }

            $active = Tools::getValue('ARSEO_customCanonicalActive');
            ArSeoProCanonicalProduct::deleteCanonicalURL($id_product);

            foreach ($urls as $id_lang => $url) {
                if (!empty($url)) {
                    ArSeoProCanonicalProduct::addCanonicalURL($id_product, $url, $active, $id_lang);
                }
            }
        }
    }
    
    public function hookDisplayAdminProductsSeoStepBottom($params)
    {
        if ($this->getCanonicalConfig()->enable && $this->getCanonicalConfig()->product) {
            if ($this->is17() || $this->is8x()) {
                $id_product = $params['id_product'];
            } else {
                $id_product = (int) Tools::getValue('id_product');
            }
            $active = 0;
            $rows = ArSeoProCanonicalProduct::getCanonicalURL($id_product);

            $productCanonical = array();

            foreach ($rows as $row) {
                $productCanonical[$row['id_lang']] = $row['url'];
                if ($row['active'] != 0) {
                    $active = 1;
                }
            }

            $data = array(
                'product_canonical' => $productCanonical,
                'active' => $active,
                'default_form_language' => $this->context->language->id,
                'languages' => Language::getLanguages(),
                'current_url' => $this->context->link->getAdminLink('AdminProducts'),
            );

            return $this->render('canonical_product.tpl', $data);
        }
    }
    
    public function hookDisplayBackOfficeCategory($params)
    {
        if ($this->getCanonicalConfig()->enable && $this->getCanonicalConfig()->category) {
            $id_category = Tools::getValue("id_category");

            if (($id_category == null || $id_category =='') && $params['request'] != null) {
                $id_category = (int)$params['category']->id_category != null ? (int)$params['category']->id_category : (int) Tools::getValue('id_category');
                if (!$id_category) {
                    $id_category = $params['request']->get('categoryId');
                }
            }

            if ($id_category != null && $id_category != '') {
                $rows = ArSeoProCanonicalCategory::getCanonicalURL($id_category);
            } else {
                $rows = array();
            }

            $active = 0;
            $category_canonical = array();

            foreach ($rows as $row) {
                $category_canonical[$row['id_lang']] = $row['url'];
                if ($row['active'] != 0) {
                    $active = 1;
                }
            }

            $language = new Language($this->context->controller->default_form_language);

            $data = array(
                'category_canonical' => $category_canonical,
                'active' => $active,
                'default_form_language' => $this->context->controller->default_form_language,
                'languages' => $this->context->controller->_languages,
                'languageObj' => $language,
            );

            if ($this->is176() || $this->is8x()) {
                return $this->render('canonical_category176.tpl', $data);
            } else {
                return $this->render('canonical_category.tpl', $data);
            }
        }
    }
    
    public function hookActionCategoryUpdate($params)
    {
        if ($this->getCanonicalConfig()->enable && $this->getCanonicalConfig()->category) {
            $id_category = (int)$params['category']->id_category != null ? (int)$params['category']->id_category : (int) Tools::getValue('id_category');

            $urls = array();
            foreach (Language::getLanguages() as $language) {
                $urls[$language['id_lang']] = Tools::getValue('ARSEO_customCanonical_' . $language['id_lang']);
            }

            $active = Tools::getValue('ARSEO_customCanonicalActive');
            ArSeoProCanonicalCategory::deleteCanonicalURL($id_category);

            foreach ($urls as $id_lang => $url) {
                if (!empty($url)) {
                    ArSeoProCanonicalCategory::addCanonicalURL($id_category, $url, $active, $id_lang);
                }
            }
        }
    }
    
    public function hookActionObjectProductAddAfter($params)
    {
        if (!$this->active) {
            return ;
        }
        if (!empty($params['object'])) {
            $product = $params['object'];
            $defCategory = $product->getDefaultCategory();
            $id_shop = Context::getContext()->shop->id;
            if ($rules = ArSeoProRuleTable::getRules($defCategory, null, $id_shop)) {
                foreach ($rules as $row) {
                    $rule = new ArSeoProRuleTable($row['id_rule']);
                    $this->generateProductRewrite($rule, $product->id);
                }
            }
            
            if ($metaRules = ArSeoProMetaTable::getRules('product', $defCategory, null, $id_shop)) {
                foreach ($metaRules as $row) {
                    $rule = new ArSeoProMetaTable($row['id_rule']);
                    $this->generateProductMeta($rule, $product->id);
                }
            }
        }
    }
    
    public function hookActionObjectProductUpdateAfter($params)
    {
        return $this->hookActionObjectProductAddAfter($params);
    }


    public function generateProductRewrite(ArSeoProRuleTable $rule, $id)
    {
        $meta = array();
        $langs = array();
        if ($rule->id_lang == 0) {
            foreach (Language::getLanguages() as $lang) {
                $langs[] = $lang['id_lang'];
            }
        } else {
            $langs[] = $rule->id_lang;
        }
        foreach ($langs as $id_lang) {
            $meta[$id_lang] = $this->generateProductRewriteForLang($rule, $id, $id_lang);
        }
        return $meta;
    }
    
    public function generateProductRewriteForLang(ArSeoProRuleTable $rule, $id, $id_lang)
    {
        $product = new Product($id, false, $id_lang);
        $metaData = new ArSeoProMetaData($this, get_object_vars($rule), $product, $id_lang);
        $metaData->prepareData(array('rule'), false);
        $link_rewrite = strip_tags($this->toLinkRewrite($metaData->rule));
        if ($product->link_rewrite != $link_rewrite) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . "product_lang` SET `link_rewrite` = '" . bqSQL($link_rewrite) . "' WHERE id_product = " .
                    (int)$id . ' AND id_lang = ' . (int)$id_lang . ' AND id_shop = ' . (int)$rule->id_shop;
            Db::getInstance()->execute($sql);
        }
        return $metaData;
    }
    
    public function toLinkRewrite($str)
    {
        return Tools::link_rewrite($str);
    }
    
    public function generateProductMeta(ArSeoProMetaTable $rule, $id)
    {
        $meta = array();
        $langs = array();
        if ($rule->id_lang == 0) {
            foreach (Language::getLanguages() as $lang) {
                $langs[] = $lang['id_lang'];
            }
        } else {
            $langs[] = $rule->id_lang;
        }
        foreach ($langs as $id_lang) {
            $meta[$id_lang] = $this->generateProductMetaForLang($rule, $id, $id_lang);
        }
        return $meta;
    }
    
    public function generateProductMetaForLang(ArSeoProMetaTable $rule, $id, $id_lang)
    {
        $product = new Product($id, false, $id_lang);
        
        $metaData = new ArSeoProMetaData($this, get_object_vars($rule), $product, $id_lang);
        $metaData->prepareData(array('meta_title', 'meta_description', 'meta_keywords'), false);
        
        if ($product->meta_title != bqSQL($metaData->meta_title) || $product->meta_description != bqSQL($metaData->meta_description) || $product->meta_keywords != bqSQL($metaData->meta_keywords)) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . "product_lang` SET `meta_title` = '" . bqSQL($metaData->meta_title) . "', `meta_description` = '"
                    . bqSQL($metaData->meta_description) . "', `meta_keywords` = '" . bqSQL($metaData->meta_keywords)
                    . "' WHERE id_product = " . (int)$id . ' AND id_lang = ' . (int)$id_lang . ' AND id_shop = ' . (int)$rule->id_shop;
            Db::getInstance()->execute($sql);
        }
        return $metaData;
    }
    
    public function generateCategoryMeta(ArSeoProMetaTable $rule, $id)
    {
        $meta = array();
        $langs = array();
        if ($rule->id_lang == 0) {
            foreach (Language::getLanguages() as $lang) {
                $langs[] = $lang['id_lang'];
            }
        } else {
            $langs[] = $rule->id_lang;
        }
        foreach ($langs as $id_lang) {
            $meta[$id_lang] = $this->generateCategoryMetaForLang($rule, $id, $id_lang);
        }
        return $meta;
    }
    
    public function generateMetaPageMeta(ArSeoProMetaTable $rule, $id)
    {
        $meta = array();
        $langs = array();
        if ($rule->id_lang == 0) {
            foreach (Language::getLanguages() as $lang) {
                $langs[] = $lang['id_lang'];
            }
        } else {
            $langs[] = $rule->id_lang;
        }
        foreach ($langs as $id_lang) {
            $meta[$id_lang] = $this->generateMetaPageMetaForLang($rule, $id, $id_lang);
        }
        return $meta;
    }
    
    public function generateMetaPageMetaForLang(ArSeoProMetaTable $rule, $id, $id_lang)
    {
        $meta = new Meta($id, $id_lang);
        $metaData = new ArSeoProMetaData($this, get_object_vars($rule), $meta, $id_lang);
        $metaData->prepareData(array('meta_title', 'meta_description', 'meta_keywords'), false);
        $update = array();
        if (!empty($metaData->meta_title)) {
            $update[] = "`title` = '" . bqSQL($metaData->meta_title) . "'";
        }
        if (!empty($metaData->meta_description)) {
            $update[] = "`description` = '" . bqSQL($metaData->meta_description) . "'";
        }
        if (!empty($metaData->meta_keywords)) {
            $update[] = "`keywords` = '" . bqSQL($metaData->meta_keywords) . "'";
        }
        if ($update) {
            if ($meta->title != bqSQL($metaData->meta_title) || $meta->description != bqSQL($metaData->meta_description) || $meta->keywords != bqSQL($metaData->meta_keywords)) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . "meta_lang` "
                        . "SET " . implode(', ', $update) . " "
                        . "WHERE id_meta = " . (int)$id . ' AND id_lang = ' . (int)$id_lang . ' AND id_shop = ' . (int)$rule->id_shop;
                Db::getInstance()->execute($sql);
            }
        }
        return $metaData;
    }
    
    public function generateBrandMeta(ArSeoProMetaTable $rule, $id)
    {
        $meta = array();
        $langs = array();
        if ($rule->id_lang == 0) {
            foreach (Language::getLanguages() as $lang) {
                $langs[] = $lang['id_lang'];
            }
        } else {
            $langs[] = $rule->id_lang;
        }
        foreach ($langs as $id_lang) {
            $meta[$id_lang] = $this->generateBrandMetaForLang($rule, $id, $id_lang);
        }
        return $meta;
    }
    
    public function generateBrandMetaForLang(ArSeoProMetaTable $rule, $id, $id_lang)
    {
        $brand = new Manufacturer($id, $id_lang);
        $metaData = new ArSeoProMetaData($this, get_object_vars($rule), $brand, $id_lang);
        $metaData->prepareData(array('meta_title', 'meta_description', 'meta_keywords'), false);
        $update = array();
        if (!empty($metaData->meta_title)) {
            $update[] = "`meta_title` = '" . bqSQL($metaData->meta_title) . "'";
        }
        if (!empty($metaData->meta_description)) {
            $update[] = "`meta_description` = '" . bqSQL($metaData->meta_description) . "'";
        }
        if (!empty($metaData->meta_keywords)) {
            $update[] = "`meta_keywords` = '" . bqSQL($metaData->meta_keywords) . "'";
        }
        if ($update) {
            if ($brand->meta_title != bqSQL($metaData->meta_title) || $brand->meta_description != bqSQL($metaData->meta_description) || $brand->meta_keywords != bqSQL($metaData->meta_keywords)) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . "manufacturer_lang` "
                        . "SET " . implode(', ', $update) . " "
                        . "WHERE id_manufacturer = " . (int)$id . ' AND id_lang = ' . (int)$id_lang;
                Db::getInstance()->execute($sql);
            }
        }
        return $metaData;
    }
    
    public function generateCategoryMetaForLang(ArSeoProMetaTable $rule, $id, $id_lang)
    {
        $category = new Category($id, $id_lang);
        $metaData = new ArSeoProMetaData($this, get_object_vars($rule), $category, $id_lang);
        $metaData->prepareData(array('meta_title', 'meta_description', 'meta_keywords'), false);
        if ($category->meta_title != bqSQL($metaData->meta_title) || $category->meta_description != bqSQL($metaData->meta_description) || $category->meta_keywords != bqSQL($metaData->meta_keywords)) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . "category_lang` SET `meta_title` = '" . bqSQL($metaData->meta_title) . "', `meta_description` = '"
                    . bqSQL($metaData->meta_description) . "', `meta_keywords` = '" . bqSQL($metaData->meta_keywords)
                    . "' WHERE id_category = " . (int)$id . ' AND id_lang = ' . (int)$id_lang . ' AND id_shop = ' . (int)$rule->id_shop;
            Db::getInstance()->execute($sql);
        }
        return $metaData;
    }
    
    
    public function is15()
    {
        if ((version_compare(_PS_VERSION_, '1.5.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.6.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is16()
    {
        if ((version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.7.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is8x()
    {
        if ((version_compare(_PS_VERSION_, '8.0.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '9.0.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is17()
    {
        if ((version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is174()
    {
        if ((version_compare(_PS_VERSION_, '1.7.4', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is176()
    {
        if ((version_compare(_PS_VERSION_, '1.7.6', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is178x()
    {
        if ((version_compare(_PS_VERSION_, '1.7.8', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function getSitemapPath($id_shop, $abs = true)
    {
        $path = $this->normalizePath($this->getSitemapBasePath($abs) . '/sitemaps/shop_' . $id_shop . '/');
        if ($abs && !is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        return $abs? $path : '/sitemaps/shop_' . $id_shop . '/';
    }
    
    public function normalizePath($path)
    {
        $path = preg_replace('{\/+}is', '/', $path);
        return str_replace(':/', '://', $path);
    }
    
    public function getIndexSitemapUrl($id_shop)
    {
        return $this->normalizePath($this->getBaseUrl($id_shop) . $this->getSitemapBasePath(false) . '/' . $this->getIndexSitemapPath(false) . 'sitemap_shop_' . $id_shop . '.xml');
    }
    
    public function getIndexSitemapPath($abs = true)
    {
        if (is_writable(_PS_ROOT_DIR_)) {
            return $abs? _PS_ROOT_DIR_ : '/';
        } else {
            return $abs? ($this->normalizePath($this->getPath(true) . '/sitemaps/')) : '/sitemaps/';
        }
    }
    
    public function getRootPath($abs = true)
    {
        return $abs? _PS_ROOT_DIR_ : '/';
    }
    
    public function getSitemapBasePath($abs)
    {
        $absPath = $this->normalizePath($this->getRootPath(true) . '/');
        $relPath = $this->normalizePath($this->getRootPath(false) . '/');
        if (!is_dir($absPath)) {
            mkdir($absPath, 0777, true);
        }
        if (is_writable($absPath)) {
            return $abs? $absPath : $relPath;
        }
        $absPath = $this->normalizePath($this->getPath(true) . '/');
        $relPath = $this->normalizePath($this->getPath(false) . '/');
        if (!is_dir($absPath)) {
            mkdir($absPath, 0777, true);
        }
        return $abs? $absPath : $relPath;
    }
    
    public function getPath($abs = false)
    {
        if ($abs) {
            return _PS_MODULE_DIR_ . $this->name . '/';
        }
        return $this->_path;
    }
    
    public function isWritable($dir)
    {
        return is_writable($this->getPath(true) . $dir);
    }
    
    public function isDirectoryExists($dir)
    {
        return is_dir($this->getPath(true) . $dir);
    }
    
    public function createDirectory($dir)
    {
        return mkdir($this->getPath(true) . $dir, 0777, true);
    }
    
    public function getBaseUrl($id_shop = null)
    {
        return ArSeoProTools::getShopDomainSsl($id_shop, true, true).__PS_BASE_URI__ . '/';
    }
    
    public function getModuleBaseUrl($id_shop = null)
    {
        return ArSeoProTools::getShopDomainSsl($id_shop, true, true).__PS_BASE_URI__ . 'modules/' . $this->name . '/';
    }
    
    public function smartyAssign($var)
    {
        $this->context->smarty->assign($var);
    }
    
    public function clearGlobalCache()
    {
        Tools::clearSmartyCache();
        Tools::clearXMLCache();
        Media::clearCache();
        if (method_exists('Tools', 'generateIndex')) {
            Tools::generateIndex();
        }
        return true;
    }
}
