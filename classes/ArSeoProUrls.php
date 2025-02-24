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

include_once dirname(__FILE__).'/url/ArSeoProURLGeneral.php';
include_once dirname(__FILE__).'/url/ArSeoProURLProduct.php';
include_once dirname(__FILE__).'/url/ArSeoProURLCategory.php';
include_once dirname(__FILE__).'/url/ArSeoProURLCMS.php';
include_once dirname(__FILE__).'/url/ArSeoProURLCMSCategory.php';
include_once dirname(__FILE__).'/url/ArSeoProURLSupplier.php';
include_once dirname(__FILE__).'/url/ArSeoProURLManufacturer.php';
include_once dirname(__FILE__).'/url/ArSeoProDispatcherResponse.php';
include_once dirname(__FILE__).'/ArSeoHelpers.php';
include_once dirname(__FILE__).'/ArSeoProTools.php';

/**
 * @property ArSeoProURLProduct $product
 * @property ArSeoProURLCategory $category
 * @property ArSeoProURLCMS $cms
 * @property ArSeoProURLCMSCategory $cmsCategory
 * @property ArSeoProURLSupplier $supplier
 * @property ArSeoProURLManufacturer $manufacturer
 */
class ArSeoProUrls
{
    public $general;
    public $product;
    public $category;
    public $cms;
    public $cmsCategory;
    public $supplier;
    public $manufacturer;
    
    protected $preDispatchers = array();
    protected $module;
    protected $context;
    protected $routes = array();
    
    public function __construct($module)
    {
        $this->module = $module;
        $this->general = new ArSeoProURLGeneral($module, null, $this);
        $this->product = new ArSeoProURLProduct($module, null, $this);
        $this->category = new ArSeoProURLCategory($module, null, $this);
        $this->cms = new ArSeoProURLCMS($module, null, $this);
        $this->cmsCategory = new ArSeoProURLCMSCategory($module, null, $this);
        $this->supplier = new ArSeoProURLSupplier($module, null, $this);
        $this->manufacturer = new ArSeoProURLManufacturer($module, null, $this);
        
        $this->context = Context::getContext();
    }
    
    public function getIsoLang($id_lang)
    {
        $iso = Language::getIsoById($id_lang);
        
        return $iso? $iso : sprintf($this->l('Deleted - ID: %s'), $id_lang);
    }
    
    public function controllerMap()
    {
        return array(
            'ProductController' => 'product',
            'CategoryController' => 'category',
            'ManufacturerController' => 'manufacturer',
            'SupplierController' => 'supplier'
        );
    }
    
    public function ruleNameMap()
    {
        return array(
            'category_rule' => 'category',
            'layered_rule' => 'category',
            'supplier_rule' => 'supplier',
            'manufacturer_rule' => 'manufacturer',
            'cms_rule' => 'cms',
            'cms_category_rule' => 'cmsCategory',
            'product_rule' => 'product'
        );
    }
    
    public function getClassByController($controllerId)
    {
        if (empty($controllerId)) {
            return null;
        }
        $map = $this->controllerMap();
        return isset($map[$controllerId])? $map[$controllerId] : null;
    }
    
    public function dispatcher($params)
    {
        if ($params['controller_type'] == 1) {
            $dispatcher = Dispatcher::getInstance();
            if (!Tools::isCallable(array($dispatcher, 'getRoutes')) ||
                !Tools::isCallable(array($dispatcher, 'getRequestUri'))) {
                return false;
            }

            $controllerId = $params['controller_class'];
            if ($class = $this->getClassByController($controllerId)) {
                $this->$class->dispatch();
            } else {
                if ($controllerId == 'CmsController') {
                    if (Tools::getValue('ars_rewrite_cms')) {
                        $this->cms->dispatch();
                    } elseif (Tools::getValue('ars_rewrite_cms_category')) {
                        $this->cmsCategory->dispatch();
                    }
                } elseif ($controllerId == 'PageNotFoundController') {
                    if (strpos(ArSeoHelpers::getCurrentUrl(), 'index.php?controller=404') !== false) {
                        if (str_replace('https://', 'http://', ArSeoHelpers::getCurrentUrl()) != str_replace('https://', 'http://', $this->getPageNotFound())) {
                            $this->redirectToNotFound();
                        }
                    }
                }
            }
        }
    }
    
    public function getContext()
    {
        return $this->context;
    }
    
    public function canonicalUrl($url)
    {
        $return = array('url' => $url, 'params' => array());
        $controllerId = isset($this->context->controller->php_self)? $this->context->controller->php_self : null;
        
        if ($controllerId && in_array($controllerId, array('category'))) {
            if ($filters = Tools::getValue('selected_filters')) {
                if ($id_category = Tools::getValue('id_category')) {
                    $return['url'] = $this->context->link->getCategoryLink($id_category, null, null, $filters);

                    if (Tools::isSubmit('selected_filters')) {
                        $return['params']['selected_filters'] = Tools::getValue('selected_filters');
                        unset($_GET['selected_filters']);
                    }

                    if (Tools::isSubmit('q')) {
                        $return['params']['q'] = Tools::getValue('q');
                        unset($_GET['q']);
                    }
                }
            }
        }

        if ($controllerId && in_array($controllerId, array('cms'))) {
            if (Tools::isSubmit('id_cms_category')) {
                $return['params']['id_cms_category'] = Tools::getValue('id_cms_category');
                unset($_GET['id_cms_category']);
            }
        }
        return $return;
    }

    public function overrideUpdateQueryStringBaseUrl($url, &$extraParams)
    {
        if (Tools::isCallable(array($this->context->controller, 'getCategory'))) {
            $category = $this->context->controller->getCategory();
            if (isset($extraParams['q'])) {
                if ($this->category->enable_layered) {
                    $url = $this->context->link->getCategoryLink($category, null, null, $extraParams['q']);
                    unset($extraParams['q']);
                }
            } elseif (Tools::getValue('q') && (isset($extraParams['page']) || isset($extraParams['order']))) {
                if ($this->category->enable_layered) {
                    $url = $this->context->link->getCategoryLink($category, null, null, Tools::getValue('q'));
                }
            } else {
                $url = $this->context->link->getCategoryLink($category);
            }
        }

        return $url;
    }
    
    public function getPageNotFound()
    {
        return version_compare(_PS_VERSION_, '1.6.0.11', '>=') ? 'pagenotfound' : '404';
    }
    
    public function redirectToNotFound()
    {
        $this->redirect($this->context->link->getPageLink($this->getPageNotFound()), ArSeoHelpers::getResponseHeader(404));
    }
    
    public function redirect($url, $headers)
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        if (Configuration::get('ARSR_LOG')) {
            $shopId = Context::getContext()->shop->id;
            $source = $this->getRequestURI();
            $dest = $this->getRequestURI($url);
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'arseopro_redirect` '.
                    'WHERE `from` = "'.pSQL($source).'" '.
                    'AND id_shop IN(0, '.(int)$shopId.') '.
                    'ORDER BY id_redirect DESC';
            if ($row = Db::getInstance()->getRow($sql)) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'arseopro_redirect` SET `last_used_at` = "' . date('Y-m-d H:i:s') . '", `use_times` = ' . ((int)$row['use_times'] + 1) . ' WHERE id_redirect = ' . (int)$row['id_redirect'];
                Db::getInstance()->execute($sql);
            } else {
                $redirect = new ArSeoProRedirectTable();
                $redirect->from = $source;
                $redirect->to = $dest;
                $redirect->status = 0;
                $redirect->id_shop = $shopId;
                $redirect->type = 301;
                $redirect->created_at = date('Y-m-d H:i:s');
                $redirect->last_used_at = date('Y-m-d H:i:s');
                $redirect->use_times = 1;
                $redirect->create_type = 1;
                $redirect->save();
            }
        }
        return Tools::redirect($url, _PS_BASE_URL_, null, $headers);
    }
    
    public function getRequestURI($url = null)
    {
        if ($url) {
            return parse_url($url, PHP_URL_PATH);
        }
        return $_SERVER['REQUEST_URI'];
    }
    
    public function loadAllFromConfig()
    {
        if (!$this->general->isLoaded()) {
            $this->general->loadFromConfig();
        }
        if (!$this->product->isLoaded()) {
            $this->product->loadFromConfig();
        }
        if (!$this->category->isLoaded()) {
            $this->category->loadFromConfig();
        }
        if (!$this->manufacturer->isLoaded()) {
            $this->manufacturer->loadFromConfig();
        }
        if (!$this->supplier->isLoaded()) {
            $this->supplier->loadFromConfig();
        }
        if (!$this->cms->isLoaded()) {
            $this->cms->loadFromConfig();
        }
        if (!$this->cmsCategory->isLoaded()) {
            $this->cmsCategory->loadFromConfig();
        }
    }
    
    public function getRoutes($params)
    {
        $this->loadAllFromConfig();
        
        $context = $this->context;
        if (!is_null($context) && isset($context->smarty) && !is_null($context->smarty)) {
            $context->smarty->assign('params_hash', sha1(ArSeoProTools::jsonEncode($params)));
        }

        $rules = array(
            'index_rule' => array(
                'controller' => 'index',
                'rule' => '/',
                'keywords' => array(),
                'params' => array()
            )
        );

        if ($this->manufacturer->enable) {
            $this->addRoute('manufacturer_rule');
            $rules['manufacturer_rule'] = $this->manufacturer->getRoute();
        }

        if ($this->supplier->enable) {
            $this->addRoute('supplier_rule');
            $rules['supplier_rule'] = $this->supplier->getRoute();
        }

        if ($this->cmsCategory->enable) {
            $this->addRoute('cms_category_rule');
            $rules['cms_category_rule'] = $this->cmsCategory->getRoute();
        }

        if ($this->cms->enable) {
            $this->addRoute('cms_rule');
            $rules['cms_rule'] = $this->cms->getRoute();
        }

        if ($this->category->enable) {
            $this->addRoute('category_rule');
            $rules['category_rule'] = $this->category->getRoute();
            if ($this->category->enable_layered) {
                $this->addRoute('layered_rule');
                $rules['layered_rule'] = $this->category->getLayeredRoute();
            }
        }

        if ($this->product->enable) {
            $this->addRoute('product_rule');
            $rules['product_rule'] = $this->product->getRoute();
        }
        return $this->addOldRules($rules);
    }
    
    public function addOldRules($rules)
    {
        $oldRules = Configuration::get('ARSEO_OLD_ROUTES');
        $disableOld = false;
        if (Shop::isFeatureActive()) {
            $oldRules = Configuration::get('ARSEO_OLD_ROUTES', false, null, Context::getContext()->shop->id);
        }
        if ($oldRules && !$disableOld) {
            $oldRules = ArSeoProTools::jsonDecode($oldRules, true);
            if (is_array($oldRules)) {
                foreach ($oldRules as $rule => $route) {
                    $classes = $this->ruleNameMap();
                    
                    $class = null;
                    if (isset($classes[$rule])) {
                        $class = $classes[$rule];
                    }
                    if (($class && !$this->$class->disable_old) || $class == null) {
                        $rules['old_'.$rule] = $route;
                        $rules['old_'.$rule]['params'] = array();
                    }
                }
            }
        }
        return $rules;
    }
    
    public function doNothing($param)
    {
        $this->module->smartyAssign(array('ars_do_nothing' => sha1(ArSeoProTools::jsonEncode($param))));
    }
    
    public function getLanguageIDs($active = true, $id_shop = false)
    {
        $languages = Language::getLanguages($active, $id_shop);
        $ids = array();
        if ($languages) {
            foreach ($languages as $language) {
                $ids[] = $language['id_lang'];
            }
        }
        return $ids;
    }
    
    public function dispatcherLoadRoutes($routes, $dispatcher = null)
    {
        $context = Context::getContext();

        $language_ids = $this->getLanguageIDs(false);
        if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
            $language_ids[] = (int)$context->language->id;
        }

        if (Tools::isCallable(array($dispatcher, 'getRoutes'))) {
            $routes = $dispatcher->getRoutes();
            
            foreach ($routes as $id_shop => $shop_routes) {
                foreach ($shop_routes as $id_lang => $lang_routes) {
                    foreach ($lang_routes as $route_name => $one_lang_routes) {
                        if (in_array($route_name, array(
                            'product_rule',
                            'category_rule',
                            'layered_rule',
                            'manufacturer_rule',
                            'supplier_rule',
                            'cms_rule',
                            'cms_category_rule'
                        ))) {
                            $route_data = $dispatcher->default_routes[$route_name];
                            $route_data['rule'] = $one_lang_routes['rule'];

                            if (ArSeoHelpers::endWith(trim($route_data['rule']), '/')) {
                                $route_data['rule'] = Tools::substr($route_data['rule'], 0, -1);
                                $dispatcher->addRoute(
                                    $route_name.'_2',
                                    $route_data['rule'],
                                    $route_data['controller'],
                                    $id_lang,
                                    $route_data['keywords'],
                                    isset($route_data['params']) ? $route_data['params'] : array(),
                                    $id_shop
                                );
                                $this->addRoute($route_name.'_2');
                            }
                        }
                        
                        if (in_array($route_name, array(
                            'product_rule',
                            'category_rule',
                            'layered_rule',
                            'manufacturer_rule',
                            'supplier_rule',
                            'cms_rule',
                            'cms_category_rule'
                        ))) {
                            $route_data = $dispatcher->default_routes[$route_name];
                            $route_data['rule'] = $one_lang_routes['rule'];

                            if (ArSeoHelpers::endWith(trim($route_data['rule']), '}')) {
                                if (!$dispatcher->hasKeyword($route_name, $id_lang, 'categories', $id_shop)) {
                                    $route_data['rule'] = $route_data['rule'].'/';
                                    $dispatcher->addRoute(
                                        $route_name.'_2',
                                        $route_data['rule'],
                                        $route_data['controller'],
                                        $id_lang,
                                        $route_data['keywords'],
                                        isset($route_data['params']) ? $route_data['params'] : array(),
                                        $id_shop
                                    );
                                    $this->addRoute($route_name.'_2');
                                }
                            }
                        }
                    }
                }
            }

            $routes = $dispatcher->getRoutes();

            foreach ($routes as $id_shop => $shop_routes) {
                foreach ($shop_routes as $id_lang => $lang_routes) {
                    foreach ($lang_routes as $route_id => $one_lang_routes) {
                        $module = null;
                        if (isset($one_lang_routes['params']['ars_pre_dispatcher_module']) &&
                            $one_lang_routes['params']['ars_pre_dispatcher_module']) {
                            $module = $one_lang_routes['params']['ars_pre_dispatcher_module'];
                            unset($routes[$id_shop][$id_lang][$route_id]['params']['ars_pre_dispatcher_module']);
                        }

                        $function = null;
                        if (isset($one_lang_routes['params']['ars_pre_dispatcher_function']) &&
                            $one_lang_routes['params']['ars_pre_dispatcher_function']) {
                            $function = $one_lang_routes['params']['ars_pre_dispatcher_function'];
                            unset($routes[$id_shop][$id_lang][$route_id]['params']['ars_pre_dispatcher_function']);
                        }

                        if ($module && $function) {
                            $this->addPreDispatcher($route_id, $module, $function);
                            $this->addRoute($route_id);
                        }
                    }
                }
            }
        }

        $id_shop = (int)$context->shop->id;
        foreach ($language_ids as $id_lang) {
            $tmp = array();
            if (isset($routes[$id_shop]) && isset($routes[$id_shop][$id_lang])) {
                if ($route_name = Configuration::get('ARS_ROUTE_FRONT')) {
                    $tmp[$route_name] = $routes[$id_shop][$id_lang][$route_name];
                    unset($routes[$id_shop][$id_lang][$route_name]);
                }
                foreach ($routes[$id_shop][$id_lang] as $route_name => $route) {
                    if (!ArSeoHelpers::startWith(trim($route['rule']), '{')) {
                        $tmp[$route_name] = $route;
                        unset($routes[$id_shop][$id_lang][$route_name]);
                    }
                }

                $routes[$id_shop][$id_lang] = $tmp + $routes[$id_shop][$id_lang];

                if (Configuration::get('ARS_MODULE_ROUTE_END')) {
                    $route = $routes[$id_shop][$id_lang]['module'];
                    unset($routes[$id_shop][$id_lang]['module']);
                    $routes[$id_shop][$id_lang]['module'] = $route;
                }
            }
        }
        return $routes;
    }
    
    public function normalizeRegexResult($m)
    {
        if (isset($m['ars_rewrite_categories']) && $m['ars_rewrite_categories']) {
            if (isset($m['ars_rewrite_category']) && !$m['ars_rewrite_category']) {
                $categories = explode('/', $m['ars_rewrite_categories']);
                $m['ars_rewrite_category'] = array_pop($categories);
                $m['ars_rewrite_categories'] = implode('/', $categories);
            }

            if (isset($m['ars_rewrite_product']) && !$m['ars_rewrite_product']) {
                $categories = explode('/', $m['ars_rewrite_categories']);
                $m['ars_rewrite_product'] = array_pop($categories);
                $m['ars_rewrite_categories'] = implode('/', $categories);
            }
        }

        if (isset($m['ars_rewrite_category']) && $m['ars_rewrite_category']) {
            if (isset($m['ars_rewrite_product']) && !$m['ars_rewrite_product']) {
                $m['ars_rewrite_product'] = $m['ars_rewrite_category'];
                $m['ars_rewrite_category'] = '';
            }
        }

        if (isset($m['ars_rewrite_cms_categories']) && $m['ars_rewrite_cms_categories']) {
            if (isset($m['ars_rewrite_cms_category']) && !$m['ars_rewrite_cms_category']) {
                $categories = explode('/', $m['ars_rewrite_cms_categories']);
                $m['ars_rewrite_cms_category'] = array_pop($categories);
                $m['ars_rewrite_cms_categories'] = implode('/', $categories);
            }

            if (isset($m['ars_rewrite_cms']) && !$m['ars_rewrite_cms']) {
                $categories = explode('/', $m['ars_rewrite_cms_categories']);
                $m['ars_rewrite_cms'] = array_pop($categories);
                $m['ars_rewrite_cms_categories'] = implode('/', $categories);
            }
        }

        return $m;
    }
    
    public function addPreDispatcher($route_id, $module, $function)
    {
        $this->preDispatchers[$route_id] = array('module' => $module, 'function' => $function);
    }
    
    public function getRoutePreDispatcher($route_id)
    {
        if (isset($this->preDispatchers[$route_id])) {
            $module = null;
            if (isset($this->preDispatchers[$route_id]['module']) &&
                $this->preDispatchers[$route_id]['module']) {
                $module = Module::getInstanceByName($this->preDispatchers[$route_id]['module']);
            }

            $function = null;
            if (isset($this->preDispatchers[$route_id]['function']) &&
                $this->preDispatchers[$route_id]['function']) {
                $function = $this->preDispatchers[$route_id]['function'];
            }

            if ($module && $function) {
                return array('module' => $module, 'function' => $function);
            }
        }

        return false;
    }
    
    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop)
    {
        $return = $this->getEmptyPreDispatcherResponse();
        
        $dispatcher = Dispatcher::getInstance();
        if (!Tools::isCallable(array($dispatcher, 'getRoutes')) ||
            !Tools::isCallable(array($dispatcher, 'getRequestUri'))) {
            return $return;
        }
        
        switch ($route_id) {
            case 'product_rule':
            case 'product_rule_2':
                $return = $this->product->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'category_rule':
            case 'category_rule_2':
            case 'layered_rule':
            case 'layered_rule_2':
                $return = $this->category->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'manufacturer_rule':
            case 'manufacturer_rule_2':
                $return = $this->manufacturer->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;
                
            case 'supplier_rule':
            case 'supplier_rule_2':
                $return = $this->supplier->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'cms_rule':
            case 'cms_rule_2':
                $return = $this->cms->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;

            case 'cms_category_rule':
            case 'cms_category_rule_2':
                $return = $this->cmsCategory->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                break;
        }

        return $return;
    }

    public function getEmptyPreDispatcherResponse()
    {
        return new ArSeoProDispatcherResponse();
    }
    
    public function addRoute($id)
    {
        if (!$this->isRouteExists($id)) {
            $this->routes[] = $id;
        }
    }

    public function isRouteExists($id)
    {
        return in_array($id, $this->routes);
    }
}
