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

include_once dirname(__FILE__).'/ArSeoProURLAbstract.php';

class ArSeoProURLCategory extends ArSeoProURLAbstract
{
    const CONFIG_PREFIX = 'arsc_';
    
    public $enable;
    public $keep_id;
    public $enable_layered;
    public $parent_cat;
    public $redirect;
    public $redirect_code;
    public $disable_old;
    
    public function getRuleId()
    {
        return 'category_rule';
    }
    
    public function getDuplicates()
    {
        $langs = $this->module->getLanguages();
        $return = array();
        $limit = ' LIMIT 5';
        $id_shop = Context::getContext()->shop->id;

        $sql = 'SELECT cl.`id_category`, cl.`link_rewrite`, cl.`id_shop`, cl.`id_lang`, c.`id_parent` FROM `'._DB_PREFIX_.'category_lang` cl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON cl.`id_category` = c.`id_category` '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category '
                . 'WHERE cl.`id_lang` IN ('.implode(',', $langs).') AND cl.id_shop = ' . (int)$id_shop . ' AND cs.id_shop = ' . (int)$id_shop . ' '
                . 'GROUP BY cl.`id_shop`, cl.`id_lang`, cl.`link_rewrite`';
        if ($this->parent_cat) {
            $sql .= ', c.`id_parent`';
        }
        $sql .= ' HAVING count(cl.`link_rewrite`) > 1 ORDER BY cl.`id_shop` ASC' . pSQL($limit);
        $duplicates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($duplicates) {
            foreach ($duplicates as $duplicate) {
                $sql2 = 'SELECT cl.`id_category`, cl.`link_rewrite`, cl.`id_shop`, cl.`id_lang`, c.`id_parent`, cl.`name` FROM `'._DB_PREFIX_.'category_lang` cl '
                        . 'LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON cl.`id_category` = c.`id_category` '
                        . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category '
                        . 'WHERE cl.`id_shop` = ' . (int)($duplicate['id_shop']) . ' AND cl.id_shop = ' . (int)$id_shop . ' AND cs.id_shop = ' . (int)$id_shop . ' '
                        . 'AND cl.`link_rewrite` = "' . pSQL($duplicate['link_rewrite']) . '" '
                        . 'AND cl.`id_lang` = ' . (int)($duplicate['id_lang']);
                if ($this->parent_cat) {
                    $sql2 .= ' AND c.`id_parent` = ' . (int)($duplicate['id_parent']);
                }
                $sql2 .= ' GROUP BY cl.`id_category`' . pSQL($limit);

                $more = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql2);
                foreach ($more as $info) {
                    $row = array();
                    $row['id'] = 'category_'.$info['id_category'];
                    $row['id_arseopro'] = 'category_'.$info['id_category'];
                    $row['id_object'] = $info['id_category'];
                    $row['id_type'] = 'category';
                    $row['type'] = 'Category';
                    $row['name'] = $info['name'];
                    $row['link_rewrite'] = $info['link_rewrite'];
                    $row['id_lang'] = $info['id_lang'];
                    $row['lang'] = $this->owner->getIsoLang($info['id_lang']);
                    $row['shop'] = '';
                    if ($shop = Shop::getShop($info['id_shop'])) {
                        $row['shop'] = $shop['name'];
                    }

                    $return[] = $row;
                }
            }
        }

        return $return;
    }
    
    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop)
    {
        $return = $this->owner->getEmptyPreDispatcherResponse();
        $context = $this->owner->getContext();
        $routes = Dispatcher::getInstance()->getRoutes();
        $rewrite = '';
        
        if (isset($m['ars_rewrite_category'])) {
            $rewrite = $m['ars_rewrite_category'];
        }
        
        $sql = 'SELECT cl.`id_category`, c.is_root_category FROM `'._DB_PREFIX_.'category_lang` cl '
                . 'LEFT JOIN `'._DB_PREFIX_.'category` c ON cl.id_category = c.id_category '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category '
                . 'WHERE cl.`id_shop` = '.(int)($id_shop) . ' AND cs.id_shop = ' . (int)$id_shop . ' '
                . 'AND cl.`link_rewrite` = "'.pSQL($rewrite).'"';
        
        $result = Db::getInstance()->getRow($sql.' AND cl.`id_lang` = ' . (int)$id_lang);
        $id_category = null;
        if (isset($m['ars_id_category']) && $m['ars_id_category']) {
            $id_category = $m['ars_id_category'];
        } else {
            $id_category = null;
        }
        if ($result || $id_category) {
            $return->controllerMatched = true;
            if (!$id_category) {
                $sql = 'SELECT cl.`id_category` FROM `'._DB_PREFIX_.'category_lang` cl '
                        . 'LEFT JOIN `'._DB_PREFIX_.'category` c ON cl.id_category = c.id_category '
                        . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category '
                        . 'WHERE cl.`id_shop` = ' . (int)($id_shop) . ' AND cs.id_shop = ' . (int)$id_shop . ' '
                        . 'AND cl.`link_rewrite` = "' . pSQL($rewrite) . '" ';
                if ($this->parent_cat && !$result['is_root_category']) {
                    $id_parent = $this->getLastIdFromCategoriesRewrite(
                        $this->getFromParamsCategories($m),
                        $context->shop->id_category,
                        $id_lang,
                        $id_shop,
                        false
                    );
                    if ($id_parent && is_array($id_parent)) {
                        $sql .= ' AND c.id_parent IN (' . implode(',', $id_parent) . ')';
                    } elseif ($id_parent && !is_array($id_parent)) {
                        $sql .= ' AND c.id_parent = ' . (int)$id_parent;
                    }
                }

                $id_category = Db::getInstance()->getValue($sql . ' AND cl.`id_lang` = '.(int)($id_lang));
                if (!$id_category) {
                    $id_category = Db::getInstance()->getValue($sql);
                }
            }

            if (!$id_category) {
                if (isset($routes[$id_shop][$id_lang]['old_category_rule'])) {
                    $dispatcher = Dispatcher::getInstance();
                    $request_uri = $dispatcher->getRequestUri();
                    if (Tools::substr($request_uri, -1) == '/') {
                        $request_uri = Tools::substr($dispatcher->getRequestUri(), 0, -1);
                    }

                    if (preg_match(
                        $routes[$id_shop][$id_lang]['old_category_rule']['regexp'],
                        $request_uri,
                        $m_sub
                    )) {
                        if (isset($m_sub['id_category'])) {
                            $id_category = $m_sub['id_category'];
                        }
                    }
                }
            }

            if ($id_category) {
                $c = new Category($id_category);
                if (!$c->active) {
                    $id_category = 0;
                }
            }

            if ($id_category) {
                $return->id = $id_category;
                $return->property = 'id_category';
            } else {
                $return->controllerMatched = false;
            }
        }

        if (!$id_category) {
            $id_category = $this->getLastIdFromCategoriesRewrite(
                $this->getFromParamsCategories($m),
                $context->shop->id_category,
                $id_lang,
                $id_shop
            );

            if ($this->redirect == self::REDIRECT_NONE) {
                $return->useIfProbably = false;
            }
            if ($id_category && $id_category != $context->shop->id_category) {
                $return->controllerProbably = true;

                if ((in_array($route_id, array('layered_rule', 'layered_rule_2'))) && $this->redirect == 'best') {
                    $selected_filters = $this->getLayeredParamFromUri($uri, $id_category);
                    if ($selected_filters) {
                        $_GET['selected_filters_maybe'] = $selected_filters;
                    }

                    $return->id = $id_category;
                    $return->property = 'id_category';
                    $return->controllerMatched = true;
                }
            }
        } else {
            if ((in_array($route_id, array('layered_rule', 'layered_rule_2'))) && $this->redirect == 'best') {
                $selected_filters = $this->getLayeredParamFromUri($uri, $id_category);
                if ($selected_filters) {
                    $_GET['selected_filters_maybe'] = $selected_filters;
                }
            }
        }
        
        return $return;
    }
    
    public function getLayeredParamFromUri($uri, $id_category)
    {
        $common_part = $this->getLongestMatchingSubstring($this->context->link->getCategoryLink($id_category), $uri);

        $selected_filters = '';
        if ($common_part) {
            $selected_filters = str_replace($common_part, '', $uri);
        }

        if ($selected_filters) {
            if (ArSeoHelpers::endWith(trim($selected_filters), '/')) {
                $selected_filters = Tools::substr($selected_filters, 0, -1);
            }
            if (ArSeoHelpers::startWith(trim($selected_filters), '/')) {
                $selected_filters = Tools::substr($selected_filters, 1);
            }

            $filter_helper = array();
            $selected_filters_array = explode('/', $selected_filters);
            foreach ($selected_filters_array as $selected_filter) {
                if (ArSeoHelpers::contains($selected_filter, '-')) {
                    $filter_helper[] = $selected_filter;
                }
            }

            if ($filter_helper) {
                $selected_filters = implode('/', $filter_helper);
            }
        }

        return $selected_filters;
    }
    
    public function getLongestMatchingSubstring($str1, $str2)
    {
        $len_1 = Tools::strlen($str1);
        $longest = '';
        for ($i = 0; $i < $len_1; $i++) {
            for ($j = $len_1 - $i; $j > 0; $j--) {
                $sub = Tools::substr($str1, $i, $j);
                if (Tools::strpos($str2, $sub) !== false && Tools::strlen($sub) > Tools::strlen($longest)) {
                    $longest = $sub;
                    break;
                }
            }
        }
        return $longest;
    }
    
    public function dispatch()
    {
        $context = $this->owner->getContext();
        $id_shop = $context->shop->id;
        $id_lang = $context->language->id;
        if (Tools::getValue('ars_rewrite_category')) {
            $id_category = Tools::getValue('id_category');
            if (!$id_category) {
                if ($this->redirect == ArSeoProURLCategory::REDIRECT_PARENT) {
                    $id_parent = $this->getLastIdFromCategoriesRewrite(
                        Tools::getValue('ars_rewrite_categories'),
                        $context->shop->id_category,
                        $id_lang,
                        $id_shop
                    );

                    if ($id_parent != $context->shop->id_category) {
                        $redirect_url = $context->link->getCategoryLink($id_parent);
                        $this->owner->redirect($redirect_url, ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                    }

                    $this->owner->redirect($context->link->getPageLink('index'), ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                } elseif ($this->redirect == self::REDIRECT_404) {
                    $this->owner->redirectToNotFound();
                }

                $this->owner->redirectToNotFound();
            }

            if (Tools::getValue('selected_filters_maybe')) {
                $_GET['selected_filters'] = Tools::getValue('selected_filters_maybe');
                unset($_GET['selected_filters_maybe']);
            }

            if (($this->module->is17() || $this->module->is8x()) && Tools::getValue('selected_filters')) {
                $_GET['q'] = Tools::getValue('selected_filters');
            }

            $_GET['id_category'] = $id_category;
            if (ArSeoHelpers::getIsset('ars_rewrite_category')) {
                unset($_GET['ars_rewrite_category']);
            }
            if (ArSeoHelpers::getIsset('ars_rewrite_categories')) {
                unset($_GET['ars_rewrite_categories']);
            }
            if (ArSeoHelpers::getIsset('fc')) {
                unset($_GET['fc']);
            }
        }
    }
    
    public function getLastIdFromCategoriesRewrite($rewrite_categories, $id_parent, $id_lang, $id_shop, $returnAll = false)
    {
        $res = array();
        if ($rewrite_categories) {
            $rewrite_categories = explode('/', $rewrite_categories);
            
            foreach ($rewrite_categories as $rewrite_category) {
                $sql = 'SELECT cl.`id_category` FROM `'._DB_PREFIX_.'category_lang` cl '
                        . 'LEFT JOIN `'._DB_PREFIX_.'category` c ON cl.id_category = c.id_category '
                        . 'WHERE cl.`id_shop` = '.(int)($id_shop) . ' '
                        . 'AND cl.`link_rewrite` = "' . pSQL($rewrite_category) . '" '
                        . 'AND c.id_parent = '.(int)($id_parent);
                if ($returnAll) {
                    $result = Db::getInstance()->executeS($sql.' AND cl.`id_lang` = '.(int)($id_lang));
                    
                    foreach ($result as $row) {
                        $res[] = $row['id_category'];
                    }
                }
                $result = Db::getInstance()->getValue($sql.' AND cl.`id_lang` = '.(int)($id_lang));

                if ($result) {
                    $id_parent = $result;
                }

                if (!$id_parent) {
                    $result = Db::getInstance()->getValue($sql);
                    if ($result) {
                        $id_parent = $result;
                    }
                }
            }
        }
        if ($returnAll) {
            return $res;
        }
        return $id_parent;
    }
    
    public function getFromParamsCategories($m)
    {
        return isset($m['ars_rewrite_categories'])? $m['ars_rewrite_categories'] : '';
    }
    
    public function getIdFromCategoryRewrite($rewrite_category, $id_category, $id_lang, $id_shop, $returnAll = false)
    {
        $res = array();
        if ($rewrite_category) {
            $sql = 'SELECT cl.`id_category` FROM `'._DB_PREFIX_.'category_lang` cl '
                    . 'LEFT JOIN `'._DB_PREFIX_.'category` c ON cl.id_category = c.id_category '
                    . 'WHERE cl.`id_shop` = '.(int)($id_shop).' '
                    . 'AND cl.`link_rewrite` = "'.pSQL($rewrite_category).'"';
            
            if ($returnAll) {
                $result = Db::getInstance()->executeS($sql.' AND cl.`id_lang` = '.(int)($id_lang));
                foreach ($result as $row) {
                    $res[] = $row['id_category'];
                }
                return $res;
            } else {
                $result = Db::getInstance()->getValue($sql.' AND cl.`id_lang` = '.(int)($id_lang));
            }
            
            

            if ($result) {
                $id_category = $result;
            }

            if (!$id_category) {
                if ($result = Db::getInstance()->getValue($sql)) {
                    $id_category = $result;
                }
            }
        }

        return $id_category;
    }
    
    public function getFromParamsCategory($m)
    {
        return isset($m['ars_rewrite_category'])? $m['ars_rewrite_category'] : '';
    }
    
    public function getDefaultRoute()
    {
        $route = array();
        if ($this->parent_cat) {
            $route[] = '{categories:/}';
        }
        if ($this->keep_id) {
            $route[] = '{id}-';
        }
        $route[] = '{rewrite}';
        
        return implode('', $route) . '/';
    }
    
    public function getRoute()
    {
        $route = array(
            'controller' => 'category',
            'rule' => $this->getDefaultRoute(),
            'keywords' => array(
                'id' => $this->keep_id? $this->regexp('[0-9]+', 'ars_id_category') : $this->regexp('[0-9]+'),
                'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_category'),
                'categories' => $this->regexp('[/_a-zA-Z0-9-\pL]*', 'ars_rewrite_categories'),
                'meta_keywords' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
                'meta_title' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            ),
            'params' => array(
                'fc' => 'controller'
            )
        );

        if (!$this->parent_cat) {
            unset($route['keywords']['categories']['param']);
        }
        return $route;
    }
    
    public function getLayeredRoute()
    {
        $route = array(
            'controller' => 'category',
            'rule' => '{categories:/}{rewrite}/{/:selected_filters}/',
            'keywords' => array(
                'id' => $this->keep_id? $this->regexp('[0-9]+', 'ars_id_category') : $this->regexp('[0-9]+'),
                'selected_filters' => $this->regexp('.*', 'selected_filters'),
                'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_category'),
                'categories' => $this->regexp('[/_a-zA-Z0-9-\pL]*', 'ars_rewrite_categories'),
                'meta_keywords' => $this->regexp('[_a-zA-Z0-9-\pL]*'),
                'meta_title' => $this->regexp('[_a-zA-Z0-9-\pL]*'),
            ),
            'params' => array(
                'fc' => 'controller'
            )
        );

        if (!$this->parent_cat) {
            $route['rule'] = '{rewrite}{/:selected_filters}';
            unset($route['keywords']['categories']['param']);
        }
        
        return $route;
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
    
    public function rules()
    {
        return array(
            array(
                array(
                    'enable',
                    'keep_id',
                    'enable_layered',
                    'parent_cat',
                    'redirect',
                    'redirect_code',
                    'schema',
                    'keywords',
                    'disable_old'
                ), 'safe'
            )
        );
    }
    
    public function redirectSelectOptions()
    {
        return array(
            array(
                'id' => self::REDIRECT_NONE,
                'name' => $this->l('None', 'ArSeoProURLCategory')
            ),
            array(
                'id' => self::REDIRECT_PARENT,
                'name' => $this->l('Redirect to parent category', 'ArSeoProURLCategory')
            ),
            array(
                'id' => self::REDIRECT_404,
                'name' => $this->l('Redirect to page not found', 'ArSeoProURLCategory')
            )
        );
    }
    
    public function attributeDescriptions()
    {
        $domain = $this->getBaseLink();
        $desc = parent::attributeDescriptions();
        return array_merge($desc, array(
            'parent_cat' => sprintf($this->l('Example: %sparent-category/children-category/category-rewrite/', 'ArSeoProURLCategory'), $domain),
            'redirect' => $this->l('Redirect type if category not found', 'ArSeoProURLCategory')
        ));
    }
    
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        return array_merge($labels, array(
            'enable' => $this->l('Enable this tab functionality', 'ArSeoProURLCategory'),
            'keep_id' => $this->l('Keep category ID in the URL', 'ArSeoProURLCategory'),
            'enable_layered' => $this->l('Handle faceted search URLs', 'ArSeoProURLCategory'),
            'parent_cat' => $this->l('Include parent category to URL', 'ArSeoProURLCategory'),
            'redirect' => $this->l('Redirect type if category not found', 'ArSeoProURLCategory'),
            'schema' => $this->l('Current Category URL scheme', 'ArSeoProURLCategory'),
        ));
    }
    
    public function attributeTypes()
    {
        $types = parent::attributeTypes();
        return array_merge($types, array(
            'enable_layered' => 'switch',
            'parent_cat' => 'switch',
            'redirect' => 'select',
        ));
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'enable_layered' => 0,
            'parent_cat' => 1,
            'redirect' => self::REDIRECT_PARENT
        );
    }
    
    public function afterSave()
    {
        if ($rule = Configuration::get('PS_ROUTE_category_rule')) {
            if ($this->hasKeyword($rule, 'categories') && !$this->parent_cat) {
                $rule = str_replace('{categories:/}', '', $rule);
            } elseif (!$this->hasKeyword($rule, 'categories') && $this->parent_cat) {
                $rule = '{categories:/}' . $rule;
            }
            if (!$this->hasKeyword($rule, 'id') && $this->keep_id) {
                $rule = str_replace('{rewrite}', '{id}-{rewrite}', $rule);
            } elseif ($this->hasKeyword($rule, 'id') && !$this->keep_id) {
                $rule = str_replace('{id}-{rewrite}', '{rewrite}', $rule);
            }
            
            ConfigurationCore::updateValue('PS_ROUTE_category_rule', $rule);
        }
        return parent::afterSave();
    }
}
