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

class ArSeoProURLCMSCategory extends ArSeoProURLAbstract
{
    const CONFIG_PREFIX = 'arscmsc_';
    
    public $enable;
    public $keep_id;
    public $parent_cat;
    public $redirect;
    public $redirect_code;
    public $disable_old;
    
    public function getRuleId()
    {
        return 'cms_category_rule';
    }
    
    public function getDuplicates()
    {
        $langs = $this->module->getLanguages();
        $return = array();
        $limit = ' LIMIT 5';

        $sql = 'SELECT ccl.`id_cms_category`, ccl.`link_rewrite`, ccl.`id_lang`'
            . ' FROM `' . _DB_PREFIX_.'cms_category_lang` ccl'
            . ' LEFT JOIN `'._DB_PREFIX_.'cms_category` cc ON ccl.`id_cms_category` = cc.`id_cms_category`'
            . ' WHERE ccl.`id_lang` IN ('.implode(',', $langs).')'
            . ' GROUP BY ccl.`id_lang`, ccl.`link_rewrite`';
        if ($this->parent_cat) {
            $sql .= ', cc.`id_parent`';
        }
        $sql .= ' HAVING count(ccl.`link_rewrite`) > 1' . pSQL($limit);
        $duplicates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($duplicates) {
            foreach ($duplicates as $duplicate) {
                $sql2 = 'SELECT ccl.`id_cms_category`, ccl.`link_rewrite`, ccl.`id_lang`, ccl.`name`'
                    . ' FROM `' . _DB_PREFIX_ . 'cms_category_lang` ccl LEFT JOIN `'._DB_PREFIX_.'cms_category`'
                    . ' cc ON ccl.`id_cms_category` = cc.`id_cms_category`'
                    . ' WHERE ccl.`link_rewrite` = "'.pSQL($duplicate['link_rewrite']).'"'
                    . ' AND ccl.`id_lang` = '.(int)$duplicate['id_lang'];
                if ($this->parent_cat) {
                    $sql2 .= ' AND cc.`id_parent` = '.(int)$duplicate['id_parent'];
                }
                $sql2 .= ' GROUP BY ccl.`id_cms_category`' . pSQL($limit);

                $more = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql2);
                if (count($more) > 1) {
                    foreach ($more as $info) {
                        $row = array();
                        $row['id'] = 'cmscategory_'.$info['id_cms_category'];
                        $row['id_object'] = $info['id_cms_category'];
                        $row['id_type'] = 'cms_category';
                        $row['type'] = 'CMS Category';
                        $row['name'] = $info['name'];
                        $row['link_rewrite'] = $info['link_rewrite'];
                        $row['id_lang'] = $info['id_lang'];
                        $row['lang'] = $this->owner->getIsoLang($info['id_lang']);

                        $return[] = $row;
                    }
                }
            }
        }

        return $return;
    }
    
    public function getQuery($rewrite, $id_lang = null, $id_parent = false)
    {
        $sql = new DbQuery();
        $sql->from('cms_category_lang', 't');
        $sql->join("LEFT JOIN `" . _DB_PREFIX_ . "cms_category` cc ON t.`id_cms_category` = cc.`id_cms_category`");
        $where = array(
            "t.`link_rewrite` = '" . pSQL($rewrite) . "'"
        );
        if ($id_lang) {
            $where[] = "t.`id_lang` = " . (int)$id_lang;
        }
        if ($id_parent !== false) {
            $where[] = "cc.id_parent = " . (int)$id_parent;
        }
        $sql->where(implode(' AND ', $where));
        return $sql;
    }
    
    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop)
    {
        $return = $this->owner->getEmptyPreDispatcherResponse();
        $rewrite =  '';
        
        if (isset($m['ars_rewrite_cms_category'])) {
            $rewrite = $m['ars_rewrite_cms_category'];
        }

        $sql = $this->getQuery($rewrite, $id_lang);
        $result = Db::getInstance()->getRow($sql);

        $id_cms_category = null;
        if (isset($m['ars_id_cms_cat']) && $m['ars_id_cms_cat']) {
            $id_cms_category = $m['ars_id_cms_cat'];
        } else {
            $id_cms_category = null;
        }
        if ($result || $id_cms_category) {
            $return->controllerMatched = true;

            if (!$id_cms_category) {
                $id_parent = $this->getCategoryIdFromCategoriesRewrite(
                    $this->getFromParamsCMSCategories($m),
                    $id_lang,
                    $id_shop
                );
                
                if ($this->parent_cat) {
                    $sql = $this->getQuery($rewrite, $id_lang, $id_parent);
                } else {
                    $sql = $this->getQuery($rewrite, $id_lang);
                }

                $id_cms_category = Db::getInstance()->getValue($sql);

                if (!$id_cms_category) {
                    if ($this->parent_cat) {
                        $id_cms_category = Db::getInstance()->getValue($this->getQuery($rewrite, null, $id_parent));
                    } else {
                        $id_cms_category = Db::getInstance()->getValue($this->getQuery($rewrite, null));
                    }
                }
            }

            if ($id_cms_category) {
                $return->id = $id_cms_category;
                $return->property = 'id_cms_category';
            } else {
                $return->controllerMatched = false;
            }
        }

        if (!$id_cms_category) {
            $id_cms_category = $this->getCategoryIdFromCategoriesRewrite(
                $this->getFromParamsCMSCategories($m),
                $id_lang,
                $id_shop
            );

            if ($id_cms_category > 1) {
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
        
        $id_cms_category = Tools::getValue('id_cms_category');
        
        if (!$id_cms_category) {
            if ($this->redirect == self::REDIRECT_PARENT) {
                $id_parent = $this->getCategoryIdFromCategoriesRewrite(
                    Tools::getValue('ars_rewrite_cms_categories'),
                    $id_lang,
                    $id_shop
                );

                if ($id_parent > 1) {
                    $redirect_url = $context->link->getCMSCategoryLink($id_parent);
                    $this->owner->redirect($redirect_url, ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                }
            }

            $this->owner->redirectToNotFound();
        }

        $_GET['id_cms_category'] = $id_cms_category;
        if (ArSeoHelpers::getIsset('ars_rewrite_cms_category')) {
            unset($_GET['ars_rewrite_cms_category']);
        }
        if (ArSeoHelpers::getIsset('ars_rewrite_cms_categories')) {
            unset($_GET['ars_rewrite_cms_categories']);
        }
        if (ArSeoHelpers::getIsset('fc')) {
            unset($_GET['fc']);
        }
    }
    
    public function getCMSCategoryParentCategories($id_cms_category, $id_lang = null)
    {
        if (is_null($id_lang)) {
            $id_lang = Context::getContext()->language->id;
        }

        $categories = array();
        $id_current = $id_cms_category;
        while (true) {
            $sql = 'SELECT c.*, cl.* FROM `'._DB_PREFIX_.'cms_category` c '
                    . 'LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (c.`id_cms_category` = cl.`id_cms_category` '
                    . 'AND `id_lang` = '.(int)$id_lang.') '
                    . 'WHERE c.`id_cms_category` = '.(int)$id_current.' AND c.`id_parent` != 0';
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

            if ($result) {
                $categories[] = $result;
                if (!$result || $result['id_parent'] == 1) {
                    return $categories;
                }
                $id_current = $result['id_parent'];
            } else {
                return $categories;
            }
        }
    }
    
    public function getCategoryIdFromCategoriesRewrite($rewrite_categories, $id_lang, $id_shop)
    {
        $id_cms_category = 1;
        if ($rewrite_categories) {
            $rewrite_categories_array = explode('/', $rewrite_categories);
            foreach ($rewrite_categories_array as $rewrite_category) {
                $sql = 'SELECT ccl.`id_cms_category` FROM `'._DB_PREFIX_.'cms_category_lang` ccl '
                        . 'LEFT JOIN `'._DB_PREFIX_.'cms_category` cc ON ccl.id_cms_category = cc.id_cms_category '
                        . 'WHERE ccl.`link_rewrite` = "'.pSQL($rewrite_category).'" '
                        . 'AND cc.id_parent = '.(int)($id_cms_category).'';
                $result = Db::getInstance()->getValue($sql . ' AND ccl.`id_lang` = '.(int)($id_lang));

                if ($result) {
                    $id_cms_category = $result;
                }

                if (!$id_cms_category) {
                    $result = Db::getInstance()->getValue($sql);
                    if ($result) {
                        $id_cms_category = $result;
                    }
                }
            }
            $this->owner->doNothing($id_shop);
        }

        return $id_cms_category;
    }
    
    public function getFromParamsCMSCategories($m)
    {
        return isset($m['ars_rewrite_cms_categories'])? $m['ars_rewrite_cms_categories'] : '';
    }
    
    public function getDefaultRoute()
    {
        if (!$this->parent_cat) {
            if ($this->keep_id) {
                return 'content/{id}-{rewrite}/';
            }
            return 'content/{rewrite}/';
        }
        if ($this->keep_id) {
            return 'content/{categories:/}{id}-{rewrite}/';
        }
        return 'content/{categories:/}{rewrite}/';
    }
    
    public function getRoute()
    {
        $route = array(
            'controller' => 'cms',
            'rule' => $this->getDefaultRoute(),
            'keywords' => array(
                'id' => $this->keep_id ? $this->regexp('[0-9]+', 'ars_id_cms_cat') : $this->regexp('[0-9]+'),
                'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_cms_category'),
                'categories' => $this->regexp('[/_a-zA-Z0-9-\pL]*', 'ars_rewrite_cms_categories'),
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
                'name' => $this->l('None', 'ArSeoProURLCMS')
            ),
            array(
                'id' => self::REDIRECT_PARENT,
                'name' => $this->l('Redirect to category', 'ArSeoProURLCMS')
            ),
            array(
                'id' => self::REDIRECT_404,
                'name' => $this->l('Redirect to page not found', 'ArSeoProURLCMS')
            )
        );
    }
    
    public function attributeDescriptions()
    {
        $domain = $this->getBaseLink();
        $desc = parent::attributeDescriptions();
        return array_merge($desc, array(
            'parent_cat' => sprintf($this->l('Example: %scontent/parent-category/children-category/page-rewrite.html', 'ArSeoProURLCMSCategory'), $domain),
            'redirect' => $this->l('Redirect type if category not found', 'ArSeoProURLCMSCategory')
        ));
    }
    
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        return array_merge($labels, array(
            'enable' => $this->l('Enable this tab functionality', 'ArSeoProURLCMSCategory'),
            'keep_id' => $this->l('Keep CMS category ID in the URL', 'ArSeoProURLCMSCategory'),
            'parent_cat' => $this->l('Include parent category to URL', 'ArSeoProURLCMSCategory'),
            'redirect' => $this->l('Redirect type if CMS category not found', 'ArSeoProURLCMSCategory'),
            'schema' => $this->l('Current CMS Category URL scheme', 'ArSeoProURLCMSCategory'),
        ));
    }
    
    public function attributeTypes()
    {
        $types = parent::attributeTypes();
        return array_merge($types, array(
            'parent_cat' => 'switch',
            'redirect' => 'select',
        ));
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'redirect' => self::REDIRECT_PARENT
        );
    }
    
    public function afterSave()
    {
        if ($rule = Configuration::get('PS_ROUTE_cms_category_rule')) {
            if ($this->hasKeyword($rule, 'categories') && !$this->parent_cat) {
                $rule = str_replace('{categories:/}', '', $rule);
            } elseif (!$this->hasKeyword($rule, 'categories') && $this->parent_cat) {
                if ($this->hasKeyword($rule, 'id')) {
                    $rule = str_replace('{id}', '{categories:/}{id}', $rule);
                } else {
                    $rule = str_replace('{rewrite}', '{categories:/}{rewrite}', $rule);
                }
            }
            if (!$this->hasKeyword($rule, 'id') && $this->keep_id) {
                $rule = str_replace('{rewrite}', '{id}-{rewrite}', $rule);
            } elseif ($this->hasKeyword($rule, 'id') && !$this->keep_id) {
                $rule = str_replace('{id}-{rewrite}', '{rewrite}', $rule);
            }
            
            ConfigurationCore::updateValue('PS_ROUTE_cms_category_rule', $rule);
        }
        return parent::afterSave();
    }
}
