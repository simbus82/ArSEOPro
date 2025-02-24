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

include_once dirname(__FILE__).'/AdminArSeoControllerAbstract.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProUrls.php';
include_once dirname(__FILE__).'/../../classes/url/models/ArSeoProRuleTable.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProListHelper.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProInstaller.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoUrlsController extends AdminArSeoControllerAbstract
{
    public function ajaxProcessResetOldRoutes()
    {
        $reflectionClass = new ReflectionClass('Dispatcher');
        
        $defaultProps = $reflectionClass->getDefaultProperties();
        $defaultRoutes = $defaultProps['default_routes'];
        
        $this->module->getInstaller()->clearDefaultRoutes($defaultRoutes);
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'defaultRoutes' => $defaultRoutes
        )));
    }
    
    public function ajaxProcessResetRoutes()
    {
        $routesToDelete = array(
            'PS_ROUTE_index_rule',
            'PS_ROUTE_category_rule',
            'PS_ROUTE_supplier_rule',
            'PS_ROUTE_manufacturer_rule',
            'PS_ROUTE_cms_rule',
            'PS_ROUTE_cms_category_rule',
            'PS_ROUTE_module',
            'PS_ROUTE_product_rule',
            'PS_ROUTE_layered_rule'
        );
        $deletedRoutes = array();
        foreach ($routesToDelete as $route) {
            if (Configuration::deleteByName($route)) {
                $deletedRoutes[] = $route;
            }
        }
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'deletedRoutes' => $deletedRoutes
        )));
    }
    
    public function ajaxProcessReloadRoutes()
    {
        $urls = new ArSeoProUrls($this->module);
        $urls->loadAllFromConfig();
        $routes = array(
            $urls->product->getRuleId() => $urls->product->getRouteRule(),
            $urls->category->getRuleId() => $urls->category->getRouteRule(),
            $urls->cms->getRuleId() => $urls->cms->getRouteRule(),
            $urls->cmsCategory->getRuleId() => $urls->cmsCategory->getRouteRule(),
            $urls->manufacturer->getRuleId() => $urls->manufacturer->getRouteRule(),
            $urls->supplier->getRuleId() => $urls->supplier->getRouteRule(),
        );
        die(ArSeoProTools::jsonEncode($routes));
    }
    
    public function ajaxProcessSave()
    {
        $data = Tools::getValue('data');
        $id = Tools::getValue('id');
        $errors = array();
        if (!$id) {
            $model = new ArSeoProRuleTable();
            $model->status = 1;
        } else {
            $model = new ArSeoProRuleTable($id);
        }
        
        $name = null;
        $rule = null;
        $id_lang = null;
        $id_category = 0;
        $categories = array();
        foreach ($data as $param) {
            if ((isset($param['name'])) && $param['name'] == 'name') {
                $name = trim($param['value']);
            }
            if ((isset($param['name'])) && $param['name'] == 'rule') {
                $rule = trim($param['value']);
            }
            if ((isset($param['name'])) && $param['name'] == 'id_lang') {
                $id_lang = $param['value'];
            }
            if ((isset($param['name'])) && $param['name'] == 'id_category') {
                $id_category = $param['value'];
            }
            if ((isset($param['name'])) && $param['name'] == 'categoryBox[]') {
                if ((int)$param['value'] > 0) {
                    $categories[] = $param['value'];
                }
            }
        }
        if ($id_category == 1 && empty($categories)) {
            $errors['id_category'] = $this->l('Please select categories');
        }
        $model->name = $name? pSQL($name) : ArSeoProRuleTable::generateRuleName();
        $model->rule = pSQL($rule);
        $model->id_lang = (int)$id_lang;
        $model->status = 1;
        $model->id_shop = (int)Context::getContext()->shop->id;
        
        $modelErrors = $model->validateFields(false, true);
        if ($modelErrors !== true) {
            $errors = array_merge($errors, $modelErrors);
        }
        if ($errors) {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 0,
                'errors' => $errors
            )));
        }
        $model->created_at = date('Y-m-d H:i:s');
        if ($model->save()) {
            $model->clearCategories();
            if ($categories && ($id_category == 1)) {
                foreach ($categories as $id_category) {
                    $model->addCategory((int)$id_category);
                }
            } else {
                $model->addCategory(0);
            }
        }
        
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'errors' => $errors,
            'model' => $model,
            'categories' => $categories
        )));
    }
    
    public function ajaxProcessEdit()
    {
        $id = Tools::getValue('id');
        $model = new ArSeoProRuleTable($id);
        $categories = $model->getCategories(true);
        $model->id_category = $categories? 1 : 0;
        $model->categories = $categories;
        die(ArSeoProTools::jsonEncode($model));
    }

    public function ajaxProcessRemoveBulk()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'DELETE FROM `' . ArSeoProRuleTable::getTableName() . '` WHERE id_rule IN (' . implode(', ', $ids) . ')';
        $sql2 = 'DELETE FROM `' . ArSeoProRuleTable::getTableName(true) . '` WHERE id_rule IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql2) && Db::getInstance()->execute($sql),
        )));
    }
    
    public function ajaxProcessActivate()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'UPDATE `' . ArSeoProRuleTable::getTableName() . '` SET `status` = 1 WHERE id_rule IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql)
        )));
    }
    
    public function ajaxProcessDeactivate()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'UPDATE `' . ArSeoProRuleTable::getTableName() . '` SET `status` = 0 WHERE id_rule IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql),
        )));
    }
    
    public function displayEditLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.url.edit(' . $id . '); return false;',
            'class' => '',
            'target' => '',
            'title' => $this->l('Edit'),
            'icon' => 'icon-pencil'
        ));
    }
    
    public function displayEditItemLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.url.duplication.edit(\'' . $id . '\'); return false;',
            'class' => '',
            'target' => '',
            'title' => $this->l('Edit'),
            'icon' => 'icon-pencil'
        ));
    }
    
    public function displayDeleteLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.url.remove(' . $id . '); return false;',
            'class' => '',
            'target' => '',
            'title' => $this->l('Delete'),
            'icon' => 'icon-trash'
        ));
    }
    
    public function displayApplyLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.url.applyRule(' . $id . ', 0, 0, 0); return false;',
            'class' => 'edit btn btn-default',
            'target' => '',
            'title' => $this->l('Apply'),
            'icon' => 'icon-check'
        ));
    }
    
    public function langTableValue($cellValue, $row)
    {
        if (!$cellValue) {
            return $this->l('All languages');
        }
        if ($lang = Language::getLanguage($cellValue)) {
            return $lang['name'];
        }
        return null;
    }
    
    public function getLanguages()
    {
        $return = array(
            0 => $this->l('All languages')
        );
        $langs = Language::getLanguages();
        foreach ($langs as $lang) {
            $return[$lang['id_lang']] = $lang['name'];
        }
        return $return;
    }
    
    public function renderDuplicates()
    {
        $context = Context::getContext();
        $columns = array();

        if (Shop::isFeatureActive()) {
            $columns['shop'] = array(
                'title' => $this->l('Shop'),
            );
        }

        $columns['type'] = array(
            'title' => $this->l('URL type'),
        );

        $columns['id_object'] = array(
            'title' => $this->l('ID'),
        );

        $columns['name'] = array(
            'title' => $this->l('Name'),
        );

        $columns['lang'] = array(
            'title' => $this->l('Language'),
        );

        $columns['link_rewrite'] = array(
            'title' => $this->l('Friendly URL'),
        );

        $helper = new ArSeoProListHelper();
        $helper->list_id = 'url-duplication-list';
        
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->no_link = true;
        $helper->identifier = 'id_arseopro';
        $helper->actions = array('edititem');
        $helper->show_toolbar = false;
        $helper->imageType = 'jpg';
        $helper->title[] = $this->l('Duplicate URLs');
        $helper->table = 'arseopro';
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminArSeoUrls');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->module->name;

        $duplicates = array();
        
        $urlConfig = $this->module->getUrlConfig();
        $urlConfig->loadAllFromConfig();
        
        if ($urlConfig->product->enable) {
            $duplicates = array_merge($duplicates, $urlConfig->product->getDuplicates());
        }

        if ($urlConfig->category->enable) {
            $duplicates = array_merge($duplicates, $urlConfig->category->getDuplicates());
        }

        if ($urlConfig->manufacturer->enable) {
            $duplicates = array_merge($duplicates, $urlConfig->manufacturer->getDuplicates());
        }
        
        if ($urlConfig->supplier->enable) {
            $duplicates = array_merge($duplicates, $urlConfig->supplier->getDuplicates());
        }

        if ($urlConfig->cms->enable) {
            $duplicates = array_merge($duplicates, $urlConfig->cms->getDuplicates());
        }
        if ($urlConfig->cmsCategory->enable) {
            $duplicates = array_merge($duplicates, $urlConfig->cmsCategory->getDuplicates());
        }
        
        return $helper->generateList($duplicates, $columns);
    }
    
    public function renderTable($params = array())
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 20;
        
        $helper = new ArSeoProListHelper();
        $helper->title = $this->l('URL Rewrite Rules for products');
        $helper->actions = array(
            'apply', 'edit', 'delete'
        );
        $helper->list_id = 'url-list';
        $helper->identifier = 'id_rule';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->module->name;
        $helper->setPagination(array(20, 50, 100));
        
        $totalCount = ArSeoProRuleTable::getCount($params);
        
        if (isset($params['page'])) {
            $totalPages = ceil($totalCount / $pageSize);
            if ($params['page'] > $totalPages) {
                $params['page'] = $totalPages;
            }
        }
        
        $helper->listTotal = $totalCount;
        $helper->currentPage = isset($params['page'])? $params['page'] : 1;
        $helper->module = $this->module;
        $helper->no_link = true;
        $helper->setDefaultPagination($pageSize);
        $helper->filters = isset($params['filter'])? $params['filter'] : array();
        $helper->token = Tools::getAdminTokenLite('AdminArSeoUrls');
        
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
                'js_action' => 'arSEO.url.bulk.remove(); return false'
            ),
            'activate' => array(
                'text' => $this->l('Activate selected'),
                'icon' => 'icon-check',
                'confirm' => $this->l('Activate selected items?'),
                'js_action' => 'arSEO.url.bulk.activate(); return false'
            ),
            'deactivate' => array(
                'text' => $this->l('Deactivate selected'),
                'icon' => 'icon-remove',
                'confirm' => $this->l('Deactivate selected items?'),
                'js_action' => 'arSEO.url.bulk.deactivate(); return false'
            )
        );
        
        $list = ArSeoProRuleTable::getAll($params);
        $columns = array(
            'id_rule' => array(
                'title' => $this->l('#'),
                'filter_key' => 'id',
                'orderby' => false,
            ),
            'id_lang' => array(
                'title' => $this->l('Language'),
                'filter_key' => 'id_lang',
                'orderby' => false,
                'callback' => 'langTableValue',
                'type' => 'select',
                'list' => $this->getLanguages()
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'name',
                'orderby' => false,
            ),
            'rule' => array(
                'title' => $this->l('Rewrite rule'),
                'filter_key' => 'rule',
                'orderby' => false
            )
        );
        if (Shop::isFeatureActive()) {
            $columns['id_shop'] = array(
                'title' => $this->l('Shop'),
                'filter_key' => 'id_shop',
                'callback' => 'shopTableValue',
                'type'  => 'select',
                'orderby' => false,
                'list'  => $this->getShopList(),
            );
        }
        $columns['status'] = array(
            'title' => $this->l('Status'),
            'filter_key' => 'status',
            'type'       => 'bool',
            'active'     => 'enabled',
            'ajax' => true,
            'orderby' => false,
        );
        $columns['created_at'] = array(
            'search' => false,
            'title' => $this->l('Create date'),
            'orderby' => false,
        );
        $columns['last_applied_at'] = array(
            'search' => false,
            'title' => $this->l('Last apply time'),
            'orderby' => false,
        );
        return $helper->generateList($list, $columns);
    }
    
    public function ajaxProcessDuplicatesReload()
    {
        $params = $this->getParams('url-list');
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'content' => $this->renderDuplicates($params)
        )));
    }
    
    public function ajaxProcessReload()
    {
        $params = $this->getParams('url-list');
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'content' => $this->renderTable($params)
        )));
    }
    
    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArSeoProRuleTable($id);
        $model->clearCategories();
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessClear()
    {
        ArSeoProRuleTable::truncate();
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1
        )));
    }
    
    public function ajaxProcessEnabledconfiguration()
    {
        $id = Tools::getValue('id_rule');
        $model = new ArSeoProRuleTable($id);
        $model->status = $model->status? 0 : 1;
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->save(false),
            'text' => $this->l('Status updated')
        )));
    }
    
    public function ajaxProcessApplyRule()
    {
        $id = Tools::getValue('id');
        $all = Tools::getValue('all');
        $offset = (int)Tools::getValue('offset');
        $total = (int)Tools::getValue('total');
        $count = (int)Tools::getValue('count');
        $pageSize = 50;
        if ($all && $id == 0) {
            $query = new DbQuery();
            $query->from(ArSeoProRuleTable::TABLE_NAME);
            $query->where('status = 1');
            $query->orderBy('id_rule ASC');
            if ($row = Db::getInstance()->getRow($query)) {
                $id = $row['id_rule'];
            } else {
                die(ArSeoProTools::jsonEncode(array(
                    'success' => 0,
                    'error' => $this->l('No rules to apply')
                )));
            }
        }
        $rule = new ArSeoProRuleTable($id);
        
        if (!$total) {
            $total = $rule->getRelatedProductsCount();
        }
        $products = $rule->getRelatedProductIds($pageSize, $offset);
        $meta = array();
        foreach ($products as $id_product) {
            $meta[$id_product] = $this->module->generateProductRewrite($rule, $id_product);
        }
        $processed = $count + count($products);
        if ($processed == $total) {
            $rule->last_applied_at = date('Y-m-d H:i:s');
            $rule->save(false);
        }
        $continue = $processed < $total? 1 : 0;
        $nextRule = 0;
        if ($all) {
            $query = new DbQuery();
            $query->from(ArSeoProRuleTable::TABLE_NAME);
            $query->where('id_rule > ' . (int)$id . ' AND status = 1');
            $query->orderBy('id_rule ASC');
            if ($row = Db::getInstance()->getRow($query)) {
                $nextRule = $row['id_rule'];
            }
        }
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'id' => $id,
            'total' => $total,
            'count' => count($products),
            'processed' => $processed,
            'offset' => $offset + count($products),
            'continue' => $continue,
            'rule' => $rule,
            'percent' => round($processed / $total * 100),
            'meta' => $meta,
            'products' => $products,
            'nextRule' => $nextRule
        )));
    }
    
    public function ajaxProcessApplyProductRules()
    {
        $id = Tools::getValue('id');
        $params = array(
            'object' => new Product($id, false, Context::getContext()->language->id)
        );
        $res = $this->module->hookActionObjectProductUpdateAfter($params);
        die(ArSeoProTools::jsonEncode(array(
            'res' => $res
        )));
    }
    
    public function ajaxProcessDuplicateSave()
    {
        $id = Tools::getValue('id');
        $type = Tools::getValue('type');
        $data = Tools::getValue('data');
        $id_shop = 0;
        $link_rewrite = array();
        $name = null;
        foreach ($data as $param) {
            if (isset($param['name'])) {
                if (Tools::strpos($param['name'], 'link_rewrite') !== false) {
                    $lang = str_replace('link_rewrite_', '', $param['name']);
                    $link_rewrite[$lang] = bqSQL($this->module->toLinkRewrite($param['value']));
                }
                if (Tools::strpos($param['name'], 'id_shop') !== false) {
                    $id_shop = (int)$param['value'];
                }
                if ($param['name'] == 'name') {
                    $name = pSQL($param['value']);
                }
            }
        }
        
        switch ($type) {
            case 'product':
                foreach ($link_rewrite as $id_lang => $value) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . "product_lang` SET `link_rewrite` = '" . pSQL($value) . "' WHERE id_lang = " . (int)$id_lang .
                            ' AND id_product = ' . (int)$id;
                    if ($id_shop) {
                        $sql .= ' AND id_shop = ' . (int)$id_shop;
                    }
                    Db::getInstance()->execute($sql);
                }
                break;
            case 'category':
                foreach ($link_rewrite as $id_lang => $value) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . "category_lang` SET `link_rewrite` = '" . pSQL($value) . "' WHERE id_lang = " . (int)$id_lang .
                            ' AND id_category = ' . (int)$id;
                    if ($id_shop) {
                        $sql .= ' AND id_shop = ' . (int)$id_shop;
                    }
                    Db::getInstance()->execute($sql);
                }
                break;
            case 'cms':
                foreach ($link_rewrite as $id_lang => $value) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . "cms_lang` SET `link_rewrite` = '" . pSQL($value) . "' WHERE id_lang = " . (int)$id_lang .
                            ' AND id_cms = ' . (int)$id;
                    if ($id_shop) {
                        $sql .= ' AND id_shop = ' . (int)$id_shop;
                    }
                    Db::getInstance()->execute($sql);
                }
                break;
            case 'cmscategory':
                foreach ($link_rewrite as $id_lang => $value) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . "cms_category_lang` SET `link_rewrite` = '" . pSQL($value) . "' WHERE id_lang = " . (int)$id_lang .
                            ' AND id_cms_category = ' . (int)$id;
                    if ($id_shop) {
                        $sql .= ' AND id_shop = ' . (int)$id_shop;
                    }
                    Db::getInstance()->execute($sql);
                }
                break;
            case 'manufacturer':
                $sql = 'UPDATE `' . _DB_PREFIX_ . "manufacturer` SET `name` = '" . pSQL($name) . "' WHERE id_manufacturer = " . (int)$id;
                    Db::getInstance()->execute($sql);
                break;
            case 'supplier':
                $sql = 'UPDATE `' . _DB_PREFIX_ . "supplier` SET `name` = '" . pSQL($name) . "' WHERE id_supplier = " . (int)$id;
                    Db::getInstance()->execute($sql);
                break;
        }
        
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'name' => $name,
            'link_rewrite' => $link_rewrite
        )));
    }
    
    public function ajaxProcessDuplicateEdit()
    {
        $id = Tools::getValue('id');
        $data = explode('_', $id);
        $type = $data[0];
        $id = $data[1];
        $field = 'link_rewrite';
        switch ($type) {
            case 'product':
                $model = new Product($id);
                break;
            case 'category':
                $model = new Category($id);
                break;
            case 'cms':
                $model = new CMS($id);
                break;
            case 'cmscategory':
                $model = new CMSCategory($id);
                break;
            case 'manufacturer':
                $model = new Manufacturer($id);
                $field = 'name';
                break;
            case 'supplier':
                $model = new Supplier($id);
                $field = 'name';
                break;
        }
        
        die(ArSeoProTools::jsonEncode(array(
            $field => $model->$field,
            'field' => $field,
            'id' => $id,
            'type' => $type,
        )));
    }
    
    public function ajaxProcessDuplicateLinkRewrite()
    {
        $data = Tools::getValue('data');
        $idLang = Tools::getValue('id_lang');
        $link_rewrite = array();
        foreach ($data as $param) {
            if (isset($param['name'])) {
                if (Tools::strpos($param['name'], 'link_rewrite') !== false) {
                    $lang = str_replace('link_rewrite_', '', $param['name']);
                    $link_rewrite[$lang] = bqSQL($this->module->toLinkRewrite($param['value']));
                }
            }
        }
        if (isset($link_rewrite[0]) && $link_rewrite[0] && $idLang == 0) {
            foreach ($link_rewrite as $id_lang => $v) {
                if ($id_lang != $idLang) {
                    $link_rewrite[$id_lang] = $link_rewrite[0];
                }
            }
        }
        die(ArSeoProTools::jsonEncode(array(
            'link_rewrite' => $link_rewrite
        )));
    }
}
