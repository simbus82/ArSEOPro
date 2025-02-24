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

class ArSeoProURLProduct extends ArSeoProURLAbstract
{
    const CONFIG_PREFIX = 'arsp_';
    
    public $enable;
    public $keep_id;
    public $default_cat;
    public $parent_cat;
    public $redirect;
    public $redirect_code;
    public $redirect_not_active;
    public $redirect_not_active_code;
    public $disable_old;
    
    public $disable_anchor;
    public $disable_default_attr_anchor;
    public $remove_anchor_id;
    public $enable_attr;
    public $disable_default_attr;
    
    public function getRuleId()
    {
        return 'product_rule';
    }
    
    public function getDuplicates()
    {
        $langs = $this->module->getLanguages();
        $return = array();
        $limit = ' LIMIT 5';

        $id_shop = Context::getContext()->shop->id;
        
        $sql = 'SELECT pl.`id_product`, pl.`link_rewrite`, pl.`id_shop`, pl.`id_lang`, p.`id_category_default`, COUNT(pl.`id_product`) as count FROM `'._DB_PREFIX_.'product_lang` pl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON pl.`id_product` = p.`id_product` '
                . 'WHERE pl.`id_lang` IN ('.implode(',', $langs).') AND p.active = 1 AND pl.id_shop = ' . (int)$id_shop . ' '
                . 'GROUP BY pl.`id_shop`, pl.`id_lang`, pl.`link_rewrite`';
        if ($this->default_cat || $this->parent_cat) {
            $sql .= ', p.`id_category_default`';
        }
        $sql .= ' HAVING count(pl.`link_rewrite`) > 1 ORDER BY pl.`id_shop` ASC' . pSQL($limit);
        
        $duplicates = Db::getInstance()->executeS($sql);

        if ($duplicates) {
            foreach ($duplicates as $duplicate) {
                $sql2 = 'SELECT pl.`id_product`, pl.`link_rewrite`, pl.`id_shop`, pl.`id_lang`, p.`id_category_default`, pl.`name` FROM `'._DB_PREFIX_.'product_lang` pl '
                        . 'LEFT JOIN `' . _DB_PREFIX_.'product` p ON pl.`id_product` = p.`id_product` WHERE pl.`id_shop` = ' . (int)$duplicate['id_shop'] . ' '
                        . 'AND pl.`link_rewrite` = "' . pSQL($duplicate['link_rewrite']) . '" '
                        . 'AND pl.`id_lang` = ' . (int)$duplicate['id_lang'];
                if ($this->default_cat || $this->parent_cat) {
                    $sql2 .= ' AND p.`id_category_default` = ' . (int)($duplicate['id_category_default']);
                }
                $sql2 .= ' GROUP BY pl.`id_product` ORDER BY pl.`id_product` ASC' . pSQL($limit);
                $more = Db::getInstance()->executeS($sql2);
                foreach ($more as $info) {
                    $row = array();
                    $row['id_arseopro'] = 'product_'.$info['id_product'];
                    $row['id'] = 'product_'.$info['id_product'];
                    $row['id_object'] = $info['id_product'];
                    $row['id_type'] = 'product';
                    $row['type'] = 'Product';
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
    
    public function getQuery($rewrite, $id_lang = null, $id_shop = null, $id_parent = false, $activeOnly = false)
    {
        $sql = new DbQuery();
        $sql->from('product_lang', 't');
        $sql->join("LEFT JOIN `" . _DB_PREFIX_ . "product_shop` ps ON t.`id_product` = ps.`id_product` AND t.`id_shop` = ps.`id_shop`");
        $where = array("t.`link_rewrite` = '" . pSQL($rewrite) . "'");
        if ($id_lang) {
            $where[] = "t.`id_lang` = " . (int)$id_lang;
        }
        if ($id_shop) {
            $where[] = "ps.`id_shop` = " . (int)$id_shop;
        }
        if (!empty($id_parent)) {
            if (is_array($id_parent)) {
                $where[] = "ps.`id_category_default` IN (" . implode(',', $id_parent) . ")";
            } else {
                $where[] = "ps.`id_category_default` = " . (int)$id_parent;
            }
        }
        if ($activeOnly) {
            $where[] = "ps.`active` = 1";
        }
        $sql->where(implode(' AND ', $where));
        
        return $sql;
    }
    
    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop, $activeOnly = true)
    {
        $return = $this->owner->getEmptyPreDispatcherResponse();
        $context = $this->owner->getContext();
        $routes = Dispatcher::getInstance()->getRoutes();
        $rewrite = '';
        
        if (isset($m['ars_rewrite_product'])) {
            $rewrite = $m['ars_rewrite_product'];
        }
        $result = Db::getInstance()->getRow($this->getQuery($rewrite, $id_lang, $id_shop, false, $activeOnly));

        if (isset($m['ars_id_product']) && $m['ars_id_product']) {
            $id_product = $m['ars_id_product'];
        } else {
            $id_product = null;
        }
        if ($result || $id_product) {
            $return->controllerMatched = true;
            if (!$id_product) {
                $id_parent = $this->owner->category->getLastIdFromCategoriesRewrite(
                    $this->owner->category->getFromParamsCategories($m),
                    $context->shop->id_category,
                    $id_lang,
                    $id_shop,
                    true
                );

                $id_parent = $this->owner->category->getIdFromCategoryRewrite(
                    $this->owner->category->getFromParamsCategory($m),
                    $id_parent,
                    $id_lang,
                    $id_shop,
                    true
                );

                if ($this->default_cat || $this->parent_cat) {
                    $sql = $this->getQuery($rewrite, $id_lang, $id_shop, $id_parent, $activeOnly);
                } else {
                    $sql = $this->getQuery($rewrite, $id_lang, $id_shop, false, $activeOnly);
                }
                
                $id_product = Db::getInstance()->getValue($sql);

                if (!$id_product) {
                    if ($this->default_cat || $this->parent_cat) {
                        $id_product = Db::getInstance()->getValue($this->getQuery($rewrite, null, $id_shop, $id_parent, $activeOnly));
                    } else {
                        $id_product = Db::getInstance()->getValue($this->getQuery($rewrite, null, $id_shop, false, $activeOnly));
                    }
                }
            }

            if (!$id_product) {
                if (isset($routes[$id_shop][$id_lang]['old_product_rule'])) {
                    $dispatcher = Dispatcher::getInstance();
                    if (preg_match($routes[$id_shop][$id_lang]['old_product_rule']['regexp'], $dispatcher->getRequestUri(), $m_sub)) {
                        if (isset($m_sub['id_product'])) {
                            $id_product = $m_sub['id_product'];
                        }
                    }
                }
            }

            if ($id_product) {
                $p = new Product($id_product);
                if (!$p->active && $this->redirect_not_active != self::REDIRECT_NONE) {
                    if (Tools::getValue('adtoken', false)) {
                        $gt = Tools::getAdminToken(
                            'AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').
                            (int)Tools::getValue('id_employee')
                        );
                        if (Tools::getValue('adtoken') != $gt) {
                            $id_product = 0;
                        }
                    } else {
                        $id_product = 0;
                    }
                    if ($this->redirect_not_active == self::REDIRECT_PARENT) {
                        $id_category = $p->id_category_default;
                        $cat = new Category($id_category, $id_lang);
                        return $this->owner->redirect($cat->getLink(), ArSeoHelpers::getResponseHeader($this->redirect_not_active_code? $this->redirect_not_active_code : 301));
                    } elseif ($this->redirect_not_active == self::REDIRECT_404) {
                        return $this->owner->redirectToNotFound();
                    }
                }
            }

            if ($id_product) {
                $return->id = $id_product;
                $return->property = 'id_product';
            } else {
                $return->controllerMatched = false;
            }
        }

        if (!$id_product) {
            if ($activeOnly) {
                return $this->preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop, false);
            }
            $id_category = $this->owner->category->getLastIdFromCategoriesRewrite(
                $this->owner->category->getFromParamsCategories($m),
                $context->shop->id_category,
                $id_lang,
                $id_shop
            );

            $id_category = $this->owner->category->getIdFromCategoryRewrite(
                $this->owner->category->getFromParamsCategory($m),
                $id_category,
                $id_lang,
                $id_shop
            );
            
            if ($id_category && $id_category != $context->shop->id_category) {
                $return->controllerProbably = true;

                if ($this->redirect == self::REDIRECT_NONE) {
                    $return->useIfProbably = false;
                }
            }
        }
        return $return;
    }
    
    public function dispatch()
    {
        $context = $this->owner->getContext();
        $id_shop = $context->shop->id;
        $id_lang = $context->language->id;
        if (Tools::getValue('ars_rewrite_product')) {
            $id_product = Tools::getValue('id_product');
            if (!$id_product) {
                if ($this->redirect == self::REDIRECT_PARENT) {
                    $id_category = $this->owner->category->getLastIdFromCategoriesRewrite(
                        Tools::getValue('ars_rewrite_categories'),
                        $context->shop->id_category,
                        $id_lang,
                        $id_shop
                    );

                    $id_category = $this->owner->category->getIdFromCategoryRewrite(
                        Tools::getValue('ars_rewrite_category'),
                        $id_category,
                        $id_lang,
                        $id_shop
                    );

                    if ($id_category != $context->shop->id_category) {
                        $redirect_url = $context->link->getCategoryLink($id_category);
                        $this->owner->redirect($redirect_url, ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                    }

                    $this->owner->redirect($context->link->getPageLink('index'), ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                } else {
                    $this->owner->redirectToNotFound();
                }
            }

            $_GET['id_product'] = $id_product;
            if (ArSeoHelpers::getIsset('ars_rewrite_product')) {
                $_GET['rewrite'] = Tools::getValue('ars_rewrite_product');
                unset($_GET['ars_rewrite_product']);
            }
            if (ArSeoHelpers::getIsset('ars_rewrite_categories')) {
                unset($_GET['ars_rewrite_categories']);
            }
            if (ArSeoHelpers::getIsset('ars_rewrite_category')) {
                unset($_GET['ars_rewrite_category']);
            }
            if (ArSeoHelpers::getIsset('fc')) {
                unset($_GET['fc']);
            }
            if (ArSeoHelpers::getIsset('ars_id_attribute')) {
                $_GET['id_product_attribute'] = Tools::getValue('ars_id_attribute');
                unset($_GET['ars_id_attribute']);
            }
        }
    }
    
    public function getDefaultRoute()
    {
        $route = array();
        if ($this->default_cat) {
            $route[] = '{category:/}';
        } elseif ($this->parent_cat) {
            $route[] = '{categories:/}';
        }
        if ($this->keep_id) {
            $route[] = '{id}-';
        }
        $route[] = '{rewrite}';
        if ($this->enable_attr) {
            $route[] = '{::id_product_attribute}';
        }
        return implode('', $route) . '.html';
    }
    
    public function keywords()
    {
        $keywords = array(
            'id' => $this->keep_id? $this->regexp('[0-9]+', 'ars_id_product') :  $this->regexp('[0-9]+'),
            'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_product'),
            'ean13' => $this->regexp('[0-9\pL]*'),
            'category' => $this->default_cat? $this->regexp('[_a-zA-Z0-9-\pL]*', 'ars_rewrite_category') : $this->regexp(self::REGEX_ALPHA_NUMERIC),
            'categories' => $this->parent_cat? $this->regexp('[/_a-zA-Z0-9-\pL]*', 'ars_rewrite_categories') : $this->regexp('[/_a-zA-Z0-9-\pL]*'),
            'reference' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            'meta_keywords' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            'meta_title' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            'manufacturer' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            'supplier' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            'price' => $this->regexp('[0-9\.,]*'),
            'tags' => $this->regexp('[a-zA-Z0-9-\pL]*'),
        );
        $force_id_product_attribute = false;
        if ((Tools::getValue('action', 'none') == 'productrefresh')
            && Tools::getValue('ajax', false)
            && Tools::getValue('id_product', false)
            && Tools::getValue('qty', false)
            && Tools::isSubmit('group')) {
            $force_id_product_attribute = true;
        }
        if (($this->module->is17() || $this->module->is8x()) && !$force_id_product_attribute && !defined('PS_ADMIN_DIR')) {
            $keywords['id_product_attribute'] = $this->regexp('[0-9]+', 'ars_id_attribute');
            $keywords['id_product_attribute']['required'] = false;
        }
        
        return $keywords;
    }
    
    public function keywordLabels()
    {
        return array(
            'id' => $this->l('Product ID'),
            'rewrite' => $this->l('rewrite'),
            'ean13' => $this->l('EAN 13'),
            'category' => $this->l('Default product category'),
            'categories' => $this->l('Parent product categories'),
            'reference' => $this->l('Product reference'),
            'meta_keywords' => $this->l('Meta keywords'),
            'meta_title' => $this->l('Meta title'),
            'manufacturer' => $this->l('Manufacturer name'),
            'supplier' => $this->l('Supplier name'),
            'price' => $this->l('Product price'),
            'tags' => $this->l('Product tags'),
            'id_product_attribute' => $this->l('Product attribute ID')
        );
    }
    
    public function getRoute()
    {
        $route = array(
            'controller' => 'product',
            'rule' => $this->getDefaultRoute(),
            'keywords' => $this->keywords(),
            'params' => array(
                'fc' => 'controller'
            )
        );
        return $route;
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
    
    public function rules()
    {
        $safe = array(
            'enable',
            'keep_id',
            'default_cat',
            'parent_cat',
            'redirect',
            'redirect_code',
            'redirect_not_active',
            'redirect_not_active_code',
            'schema',
            'keywords',
            'disable_old',
            'remove_anchor_id'
        );
        if ($this->module->is17() || $this->module->is8x()) {
            $safe[] = 'disable_anchor';
            $safe[] = 'disable_default_attr_anchor';
            $safe[] = 'enable_attr';
            $safe[] = 'disable_default_attr';
        }
        return array(
            array(
                $safe, 'safe'
            )
        );
    }
    
    public function redirectSelectOptions()
    {
        return array(
            array(
                'id' => self::REDIRECT_NONE,
                'name' => $this->l('None', 'ArSeoProURLAbstract')
            ),
            array(
                'id' => self::REDIRECT_PARENT,
                'name' => $this->l('Redirect to category', 'ArSeoProURLAbstract')
            ),
            array(
                'id' => self::REDIRECT_404,
                'name' => $this->l('Redirect to page not found', 'ArSeoProURLAbstract')
            )
        );
    }
    
    public function redirectNotActiveSelectOptions()
    {
        return $this->redirectSelectOptions();
    }
    
    public function redirectNotActiveCodeSelectOptions()
    {
        return $this->redirectCodeSelectOptions();
    }
    
    public function attributeDescriptions()
    {
        $domain = $this->getBaseLink();
        $desc = parent::attributeDescriptions();
        return array_merge($desc, array(
            'default_cat' => sprintf($this->l('Example: %sdefault-category/product-rewriten-url.html', 'ArSeoProURLProduct'), $domain),
            'parent_cat' => sprintf($this->l('Example: %sparent-category/children-category/product-rewriten-url.html', 'ArSeoProURLProduct'), $domain),
        ));
    }
    
    public function htmlFields()
    {
        $html = parent::htmlFields();
        return array_merge($html, array(
            //'schema' => $this->renderSchemaField()
        ));
    }
    
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        return array_merge($labels, array(
            'enable' => $this->l('Enable this tab functionality', 'ArSeoProURLProduct'),
            'keep_id' => $this->l('Keep product ID in the URL', 'ArSeoProURLProduct'),
            'default_cat' => $this->l('Include default category to URL', 'ArSeoProURLProduct'),
            'parent_cat' => $this->l('Include parent category to URL', 'ArSeoProURLProduct'),
            'redirect' => $this->l('Redirect type if product not found', 'ArSeoProURLProduct'),
            'redirect_code' => $this->l('Redirect code if product not found', 'ArSeoProURLProduct'),
            'redirect_not_active' => $this->l('Redirect type if product is not active', 'ArSeoProURLProduct'),
            'schema' => $this->l('Current Product URL scheme', 'ArSeoProURLProduct'),
            'disable_anchor' => $this->l('Disable combination anchors for product URL', 'ArSeoProURLProduct'),
            'disable_default_attr_anchor' => $this->l('Disable combination anchors for default attribute only', 'ArSeoProURLProduct'),
            'enable_attr' => $this->l('Include combination ID to product URL', 'ArSeoProURLProduct'),
            'disable_default_attr' => $this->l('Disable default combination ID in product URL', 'ArSeoProURLProduct'),
            'redirect_not_active_code' => $this->l('Redirect code if product not active', 'ArSeoProURLProduct'),
            'remove_anchor_id' => $this->l('Disable IDs in the hashed part of the URL', 'ArSeoProURLProduct')
        ));
    }
    
    public function attributeTypes()
    {
        $types = parent::attributeTypes();
        return array_merge($types, array(
            'default_cat' => 'switch',
            'parent_cat' => 'switch',
            'redirect' => 'select',
            'redirect_code' => 'select',
            'redirect_not_active' => 'select',
            'redirect_not_active_code' => 'select',
            'disable_anchor' => 'switch',
            'disable_default_attr_anchor' => 'switch',
            'enable_attr' => 'switch',
            'disable_default_attr' => 'switch',
            'remove_anchor_id' => 'switch'
        ));
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'keep_id' => 0,
            'default_cat' => 1,
            'redirect' => self::REDIRECT_PARENT,
            'redirect_code' => 301,
            'redirect_not_active' => 0,
            'redirect_not_active_code' => 301,
            'disable_default_attr' => 1,
            'disable_default_attr_anchor' => 1,
            'remove_anchor_id' => 1
        );
    }
    
    public function afterSave()
    {
        if ($rule = Configuration::get('PS_ROUTE_product_rule')) {
            if ($this->hasKeyword($rule, 'category') && $this->parent_cat) {
                $rule = str_replace('category', 'categories', $rule);
            } elseif ($this->hasKeyword($rule, 'categories') && $this->default_cat) {
                $rule = str_replace('categories', 'category', $rule);
            } elseif ($this->hasKeyword($rule, 'category') && !$this->default_cat) {
                $rule = str_replace('{category:/}', '', $rule);
            } elseif ($this->hasKeyword($rule, 'categories') && !$this->parent_cat) {
                $rule = str_replace('{categories:/}', '', $rule);
            } elseif (!$this->hasKeyword($rule, 'category') && $this->default_cat) {
                $rule = '{category:/}' . $rule;
            } elseif (!$this->hasKeyword($rule, 'categories') && $this->parent_cat) {
                $rule = '{categories:/}' . $rule;
            }
            if (!$this->hasKeyword($rule, 'id') && $this->keep_id) {
                $rule = str_replace('{rewrite}', '{id}-{rewrite}', $rule);
            } elseif ($this->hasKeyword($rule, 'id') && !$this->keep_id) {
                $rule = str_replace('{id}-{rewrite}', '{rewrite}', $rule);
            }
            if (!$this->hasKeyword($rule, 'id_product_attribute') && $this->enable_attr) {
                $rule = str_replace('{rewrite}', '{rewrite}{::id_product_attribute}', $rule);
            } elseif ($this->hasKeyword($rule, 'id_product_attribute') && !$this->enable_attr) {
                $rule = str_replace('{::id_product_attribute}', '', $rule);
            }
            ConfigurationCore::updateValue('PS_ROUTE_product_rule', $rule);
        }
        return parent::afterSave();
    }
}
