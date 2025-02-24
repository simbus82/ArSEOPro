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

class ArSeoProURLCMS extends ArSeoProURLAbstract
{
    const CONFIG_PREFIX = 'arscms_';
    
    public $enable;
    public $keep_id;
    public $parent_cat;
    public $redirect;
    public $redirect_code;
    public $disable_old;
    
    public function getRuleId()
    {
        return 'cms_rule';
    }
    
    public function getDuplicates()
    {
        $langs = $this->module->getLanguages();
        $return = array();
        $limit = ' LIMIT 5';

        $sql = 'SELECT cl.`id_cms`, cl.`link_rewrite`, cs.`id_shop`, cl.`id_lang` FROM `'._DB_PREFIX_.'cms_lang` cl ' .
            'LEFT JOIN `'._DB_PREFIX_.'cms` c ON cl.`id_cms` = c.`id_cms` ' .
            'LEFT JOIN `'._DB_PREFIX_.'cms_shop` cs ON cl.`id_cms` = cs.`id_cms` ' .
            'WHERE cl.`id_lang` IN ('.implode(',', $langs).') ' .
            'GROUP BY cs.`id_shop`, cl.`id_lang`, cl.`link_rewrite`';
        if ($this->parent_cat) {
            $sql .= ', c.`id_cms_category`';
        }
        $sql .= ' HAVING count(cl.`link_rewrite`) > 1 ORDER BY cs.`id_shop` ASC' . pSQL($limit);
        
        $duplicates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($duplicates) {
            foreach ($duplicates as $duplicate) {
                $sql2 = 'SELECT cl.`id_cms`, cl.`link_rewrite`, cs.`id_shop`, cl.`id_lang`, cl.`meta_title` FROM `' . _DB_PREFIX_.'cms_lang` cl '
                        . 'LEFT JOIN `'._DB_PREFIX_.'cms` c ON cl.`id_cms` = c.`id_cms` '
                        . 'LEFT JOIN `'._DB_PREFIX_.'cms_shop` cs ON cl.`id_cms` = cs.`id_cms` '
                        . 'WHERE cs.`id_shop` = '.(int)($duplicate['id_shop']).' '
                        . 'AND cl.`link_rewrite` = "'.pSQL($duplicate['link_rewrite']).'" '
                        . 'AND cl.`id_lang` = '.(int)($duplicate['id_lang']);
                if ($this->parent_cat) {
                    $sql2 .= ' AND c.`id_cms_category` = '.(int)($duplicate['id_cms_category']);
                }
                $sql2 .= ' GROUP BY cl.`id_cms`'.pSQL($limit);
                
                $more = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql2);
                if (count($more) > 1) {
                    foreach ($more as $info) {
                        $row = array();
                        $row['id'] = 'cms_'.$info['id_cms'];
                        $row['id_object'] = $info['id_cms'];
                        $row['id_type'] = 'cms';
                        $row['type'] = 'CMS';
                        $row['name'] = $info['meta_title'];
                        $row['link_rewrite'] = $info['link_rewrite'];
                        $row['id_lang'] = $info['id_lang'];
                        $row['lang'] = $this->owner->getIsoLang($info['id_lang']);
                        $row['shop'] = '';
                        $shop = Shop::getShop($info['id_shop']);
                        if ($shop) {
                            $row['shop'] = $shop['name'];
                        }

                        $return[] = $row;
                    }
                }
            }
        }

        return $return;
    }
    
    protected function getQuery($rewrite, $id_lang = null, $id_shop = null, $id_cms_category = false)
    {
        $sql = new DbQuery();
        $sql->from('cms_lang', 't');
        $sql->join('LEFT JOIN `'._DB_PREFIX_.'cms` c ON t.`id_cms` = c.`id_cms`');
        if ($id_shop) {
            $sql->join('LEFT JOIN `'._DB_PREFIX_.'cms_shop` cs ON c.`id_cms` = cs.`id_cms`');
        }
        $where = array("t.`link_rewrite` = '" . pSQL($rewrite) . "'");
        if ($id_lang) {
            $where[] = "t.`id_lang` = " . (int)$id_lang;
        }
        if ($id_shop) {
            $where[] = "t.`id_shop` = " . (int)$id_shop;
            $where[] = "cs.`id_shop` = " . (int)$id_shop;
        }
        if ($id_cms_category !== false) {
            $where[] = "c.id_cms_category = " . (int)$id_cms_category;
        }
        $where[] = 'c.active = 1';
        $sql->where(implode(' AND ', $where));
        return $sql;
    }


    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop)
    {
        $return = $this->owner->getEmptyPreDispatcherResponse();
        $rewrite = '';
        
        if (isset($m['ars_rewrite_cms'])) {
            $rewrite = $m['ars_rewrite_cms'];
        }
        
        if ($this->module->is17() || $this->module->is8x()) {
            $sql = $this->getQuery($rewrite, $id_lang, $id_shop);
        } else {
            $sql = $this->getQuery($rewrite, $id_lang);
        }
        
        $result = Db::getInstance()->getRow($sql);

        if (isset($m['ars_id_cms']) && $m['ars_id_cms']) {
            $id = $m['ars_id_cms'];
        } else {
            $id = null;
        }
        if ($result || $id) {
            if ($result) {
                $id = $result['id_cms'];
            }
            $return->controllerMatched = true;

            if (!$id) {
                $id_cms_category = $this->owner->cmsCategory->getCategoryIdFromCategoriesRewrite(
                    $this->owner->cmsCategory->getFromParamsCMSCategories($m),
                    $id_lang,
                    $id_shop
                );
                
                if ($this->parent_cat) {
                    $id = Db::getInstance()->getValue($this->getQuery($rewrite, $id_lang, null, $id_cms_category));
                } else {
                    $sql = $this->getQuery($rewrite);
                }

                if (!$id) {
                    $id = Db::getInstance()->getValue($sql);
                }
            }

            if ($id) {
                $return->id = $id;
                $return->property = 'id_cms';
            } else {
                $return->controllerMatched = false;
            }
        }

        if (!$id) {
            $id_cms_category = $this->owner->cmsCategory->getCategoryIdFromCategoriesRewrite(
                $this->owner->cmsCategory->getFromParamsCMSCategories($m),
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
        
        $id_cms = Tools::getValue('id_cms');

        if (!$id_cms) {
            if ($this->redirect == self::REDIRECT_PARENT) {
                $id_parent = $this->owner->cmsCategory->getCategoryIdFromCategoriesRewrite(
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

        $_GET['id_cms'] = $id_cms;
        if (ArSeoHelpers::getIsset('ars_rewrite_cms')) {
            unset($_GET['ars_rewrite_cms']);
        }
        if (ArSeoHelpers::getIsset('ars_rewrite_cms_categories')) {
            unset($_GET['ars_rewrite_cms_categories']);
        }
        if (ArSeoHelpers::getIsset('fc')) {
            unset($_GET['fc']);
        }
    }
    
    public function getDefaultRoute()
    {
        if (!$this->parent_cat) {
            if ($this->keep_id) {
                return 'content/{id}-{rewrite}.html';
            }
            return 'content/{rewrite}.html';
        }
        if ($this->keep_id) {
            return 'content/{categories:/}{id}-{rewrite}.html';
        }
        return 'content/{categories:/}{rewrite}.html';
    }
    
    public function getRoute()
    {
        $route = array(
            'controller' => 'cms',
            'rule' => $this->getDefaultRoute(),
            'keywords' => array(
                'id' => $this->keep_id ? $this->regexp('[0-9]+', 'ars_id_cms') : $this->regexp('[0-9]+'),
                'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_cms'),
                'categories' => $this->regexp('[/_a-zA-Z0-9-\pL]*', 'ars_rewrite_cms_categories'),
                'meta_keywords' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
                'meta_title' =>$this->regexp(self::REGEX_ALPHA_NUMERIC),
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
            'parent_cat' => sprintf($this->l('Example: %scontent/parent-category/children-category/page-rewrite.html', 'ArSeoProURLCMS'), $domain),
            'redirect' => $this->l('Redirect type if CMS page not found', 'ArSeoProURLCMS')
        ));
    }
    
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        return array_merge($labels, array(
            'enable' => $this->l('Enable this tab functionality', 'ArSeoProURLCMS'),
            'keep_id' => $this->l('Keep CMS page ID in the URL', 'ArSeoProURLCMS'),
            'parent_cat' => $this->l('Include parent category to URL', 'ArSeoProURLCMS'),
            'redirect' => $this->l('Redirect type if CMS Page not found', 'ArSeoProURLCMS'),
            'schema' => $this->l('Current CMS Page URL scheme', 'ArSeoProURLCMS'),
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
        if ($rule = Configuration::get('PS_ROUTE_cms_rule')) {
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
            
            ConfigurationCore::updateValue('PS_ROUTE_cms_rule', $rule);
        }
        return parent::afterSave();
    }
}
