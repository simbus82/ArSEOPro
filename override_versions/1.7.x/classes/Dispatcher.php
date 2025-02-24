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

class Dispatcher extends DispatcherCore
{
    protected static $moduleInstance;
    
    public function arSeoProOverrideVersion()
    {
        return '1.9.5';
    }
    
    public function getModuleInstance()
    {
        if (self::$moduleInstance == null) {
            self::$moduleInstance = Module::getInstanceByName('arseopro');
        }
        return self::$moduleInstance;
    }
    
    public function getRoutes()
    {
        return $this->routes;
    }

    public function getRequestUri()
    {
        return $this->request_uri;
    }

    protected function loadRoutes($id_shop = null)
    {
        parent::loadRoutes($id_shop);
        if (Module::isEnabled('arseopro')) {
            $module = Module::getInstanceByName('arseopro');
            $this->routes = $module->getUrlConfig()->dispatcherLoadRoutes($this->routes, $this);
        }
    }

    protected function setRequestUri()
    {
        parent::setRequestUri();
        $remove_enabled = Configuration::get('ARS_REMOVE_DEF_LANG');
        $current_iso_lang = Tools::getValue('isolang');
        if ($this->use_routes && Language::isMultiLanguageActivated() && !$current_iso_lang && $remove_enabled) {
            $_GET['isolang'] = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        }
    }

    public function arSEOredirect()
    {
        if (Module::isInstalled('arseopro') && Module::isEnabled('arseopro') && Configuration::get('ARSR_ENABLE')) {
            $module = $this->getModuleInstance();
            $module->redirect();
        }
    }
    
    public function getController($id_shop = null)
    {
        if (!Module::isEnabled('arseopro')) {
            return parent::getController($id_shop);
        }
        
        if (defined('_PS_ADMIN_DIR_')) {
            $_GET['controllerUri'] = Tools::getvalue('controller');
        }
        
        $this->arSEOredirect();
        
        if ($this->controller) {
            $_GET['controller'] = $this->controller;
            return $this->controller;
        }

        if (isset(Context::getContext()->shop) && $id_shop === null) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        $id_lang = Context::getContext()->language->id;

        $controller = Tools::getValue('controller');

        if (isset($controller) && is_string($controller) && preg_match('/^([0-9a-z_-]+)\?(.*)=(.*)$/Ui', $controller, $m)) {
            $controller = $m[1];
            // need to detect controller index in GET and POST separately, so Tools::getIsset is not fit to these needs
            if (isset($_GET['controller'])) {
                $_GET[$m[2]] = $m[3];
            } elseif (isset($_POST['controller'])) {
                $_POST[$m[2]] = $m[3];
            }
        }

        if (!Validate::isControllerName($controller)) {
            $controller = false;
        }

        if ($this->use_routes && !$controller && !defined('_PS_ADMIN_DIR_')) {
            if (!$this->request_uri) {
                return Tools::strtolower($this->controller_not_found);
            }
            $controller = $this->controller_not_found;
            $test_request_uri = preg_replace('/(=http:\/\/)/', '=', $this->request_uri);

            if (!preg_match('/\.(gif|jpe?g|png|css|js|ico)$/i', parse_url($test_request_uri, PHP_URL_PATH))) {
                if ($this->empty_route) {
                    $this->addRoute(
                        $this->empty_route['routeID'],
                        $this->empty_route['rule'],
                        $this->empty_route['controller'],
                        $id_lang,
                        array(),
                        array(),
                        $id_shop
                    );
                }

                list($uri) = explode('?', $this->request_uri);

                if (Tools::file_exists_cache(_PS_ROOT_DIR_.$uri)) {
                    return $controller;
                }

                if (isset($this->routes[$id_shop][$id_lang])) {
                    $maybe = array();
                    $lastRoute = array();
                    foreach ($this->routes[$id_shop][$id_lang] as $route_id => $route) {
                        if (preg_match($route['regexp'], $uri, $m)) {
                            if (Module::isEnabled('arseopro')) {
                                $module = Module::getInstanceByName('arseopro');
                                if ($module->getUrlConfig()->isRouteExists($route_id)) {
                                    $m = $module->getUrlConfig()->normalizeRegexResult($m);
                                    $preDispatcher = $module->getUrlConfig()->getRoutePreDispatcher($route_id);
                                    if ($preDispatcher && Tools::isCallable(array($preDispatcher['module'], $preDispatcher['function']))) {
                                        $modulePreDispatcher = call_user_func(array(
                                            $preDispatcher['module'],
                                            $preDispatcher['function']
                                        ), $uri, $route_id, $route, $m, $id_lang, $id_shop);

                                        $info = $module->getUrlConfig()->getEmptyPreDispatcherResponse();
                                        if (is_array($modulePreDispatcher)) {
                                            $info = array_merge($info, $modulePreDispatcher);
                                        }
                                    } else {
                                        $info = $module->getUrlConfig()->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
                                    }

                                    $lastRoute = array(
                                        'route_id' => $route_id,
                                        'm' => $m,
                                        'route' => $route,
                                        'useIfProbably' => $info->useIfProbably
                                    );
                                    
                                    if ($info->controllerMatched) {
                                        if ($info->id && $info->property) {
                                            $_GET[$info->property] = $info->id;
                                        }
                                    } else {
                                        if ($info->controllerProbably) {
                                            $maybe[$route_id] = array(
                                                'm' => $m,
                                                'route' => $route,
                                                'useIfProbably' => $info->useIfProbably
                                            );
                                        }
                                        continue;
                                    }
                                }
                            }
                            
                            $maybe = array();
                            $lastRoute = array();

                            foreach ($m as $k => $v) {
                                if (!is_numeric($k)) {
                                    $_GET[$k] = $v;
                                }
                            }

                            $controller = $route['controller'] ? $route['controller'] : Tools::getValue('controller');
                            if (!empty($route['params'])) {
                                foreach ($route['params'] as $k => $v) {
                                    $_GET[$k] = $v;
                                }
                            }

                            if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9_]+)$#i', $controller, $m)) {
                                $_GET['module'] = $m[1];
                                $_GET['fc'] = 'module';
                                $controller = $m[2];
                            }

                            if (Tools::getValue('fc') == 'module') {
                                $this->front_controller = self::FC_MODULE;
                            }
                            break;
                        }
                    }
                    
                    if (!$maybe && $lastRoute) {
                        $maybe[$lastRoute['route_id']] = $lastRoute;
                    }

                    if ($maybe) {
                        foreach ($maybe as $routeData) {
                            $m = $routeData['m'];
                            $route = $routeData['route'];

                            if ($routeData['useIfProbably']) {
                                foreach ($m as $k => $v) {
                                    if (!is_numeric($k)) {
                                        $_GET[$k] = $v;
                                    }
                                }

                                $controller = $route['controller'] ? $route['controller'] : Tools::getValue('controller');
                                if (!empty($route['params'])) {
                                    foreach ($route['params'] as $k => $v) {
                                        $_GET[$k] = $v;
                                    }
                                }

                                if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9_]+)$#i', $controller, $m)) {
                                    $_GET['module'] = $m[1];
                                    $_GET['fc'] = 'module';
                                    $controller = $m[2];
                                }

                                if (Tools::getValue('fc') == 'module') {
                                    $this->front_controller = self::FC_MODULE;
                                }

                                break;
                            }
                        }
                    }
                }
            }

            if ($controller == 'index' || preg_match('/^\/index.php(?:\?.*)?$/', $this->request_uri)) {
                $controller = $this->useDefaultController();
            }
        }

        $this->controller = str_replace('-', '', $controller);
        $_GET['controller'] = $this->controller;
        return $this->controller;
    }
}
