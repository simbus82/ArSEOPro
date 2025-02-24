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
include_once dirname(__FILE__).'/../../classes/redirects/models/ArSeoProRedirectTable.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProListHelper.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoRedirectsController extends AdminArSeoControllerAbstract
{
    public function getListId()
    {
        return 'redirect-list';
    }
    
    public function processImportcsv()
    {
        $file = $_FILES['file'];
        
        $dest = $this->module->getPath(true) . 'csv/import-' . date('Y-m-d_h-i-s') . '.csv';
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $data = file($dest);
            foreach ($data as $line) {
                $row = explode(';', $line);
                $model = new ArSeoProRedirectTable();
                $model->from = pSQL($row[0]);
                $model->to = pSQL($row[1]);
                $model->type = (int)$row[2];
                $model->id_shop = (int)$row[3];
                $model->status = isset($row[4])? (int)$row[4] : 1;
                $model->created_at = date('Y-m-d H:i:s');
                $model->save(false);
            }
        }
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name . '&activeTab=redirects');
    }


    public function ajaxProcessRemoveBulk()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . ArSeoProRedirectTable::TABLE_NAME . '` WHERE id_redirect IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql),
        )));
    }
    
    public function ajaxProcessActivate()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'UPDATE `' . _DB_PREFIX_ . ArSeoProRedirectTable::TABLE_NAME . '` SET `status` = 1 WHERE id_redirect IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql)
        )));
    }
    
    public function ajaxProcessDeactivate()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'UPDATE `' . _DB_PREFIX_ . ArSeoProRedirectTable::TABLE_NAME . '` SET `status` = 0 WHERE id_redirect IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql),
        )));
    }
    
    public function displayEditLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.redirect.edit(' . $id . '); return false;',
            'class' => 'edit btn btn-default',
            'target' => '',
            'title' => $this->l('Edit'),
            'icon' => 'icon-pencil'
        ));
    }
    
    public function displayDeleteLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.redirect.remove(' . $id . '); return false;',
            'class' => '',
            'target' => '',
            'title' => $this->l('Delete'),
            'icon' => 'icon-trash'
        ));
    }
    
    public function displayTestLink($token, $id, $name)
    {
        $redirect = new ArSeoProRedirectTable($id);
        return $this->module->render('_partials/_button.tpl', array(
            'href' => $redirect->from,
            'onclick' => '',
            'class' => '',
            'target' => '_blank',
            'title' => $this->l('Test'),
            'icon' => 'icon-link'
        ));
    }
    
    public static function redirectTypeLabels()
    {
        return array(
            301 => '301',
            302 => '302',
            303 => '303'
        );
    }

    public static function getRedirectTypeLabel($type)
    {
        $types = self::redirectTypeLabels();
        return $types[$type];
    }
    
    public function redirectTypeTableValue($cellValue, $row)
    {
        return self::getRedirectTypeLabel($row['type']);
    }
    
    public function renderTable($params = array())
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 100;
        
        $helper = new ArSeoProListHelper();
        $helper->title = $this->l('List of redirects');
        $helper->actions = array(
            'edit', 'test', 'delete'
        );
        $helper->list_id = $this->getListId();
        $helper->identifier = 'id_redirect';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->module->name;
        $helper->setPagination(array(20, 50, 100));
        
        $totalCount = ArSeoProRedirectTable::getCount($params);
        
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
        $helper->token = Tools::getAdminTokenLite('AdminArSeoRedirects');
        
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
                'js_action' => 'arSEO.redirect.bulk.remove()'
            ),
            'activate' => array(
                'text' => $this->l('Activate selected'),
                'icon' => 'icon-check',
                'confirm' => $this->l('Activate selected items?'),
                'js_action' => 'arSEO.redirect.bulk.activate()'
            ),
            'deactivate' => array(
                'text' => $this->l('Deactivate selected'),
                'icon' => 'icon-remove',
                'confirm' => $this->l('Deactivate selected items?'),
                'js_action' => 'arSEO.redirect.bulk.deactivate()'
            )
        );
        
        $list = ArSeoProRedirectTable::getAll($params);
        $columns = array(
            'id_redirect' => array(
                'title' => $this->l('#'),
                'filter_key' => 'id',
                'orderby' => false,
            ),
            'from' => array(
                'title' => $this->l('Redirect from URL'),
                'filter_key' => 'from',
                'orderby' => false,
            ),
            'to' => array(
                'title' => $this->l('Redirect to URL'),
                'filter_key' => 'to',
                'orderby' => false,
            ),
            'type' => array(
                'title' => $this->l('Redirect type'),
                'filter_key' => 'type',
                'callback' => 'redirectTypeTableValue',
                'type'  => 'select',
                'orderby' => false,
                'list'  => array(
                    '301' => '301',
                    '302' => '302',
                    '303' => '303',
                )
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
        $columns['last_used_at'] = array(
            'search' => false,
            'title' => $this->l('Last use date'),
            'orderby' => false,
        );
        $columns['use_times'] = array(
            'search' => false,
            'title' => $this->l('Use times'),
            'orderby' => false,
        );
        return $helper->generateList($list, $columns);
    }
    
    public function ajaxProcessSave()
    {
        $data = Tools::getValue('data');
        $id = Tools::getValue('id');
        $errors = array();
        if (!$id) {
            $model = new ArSeoProRedirectTable();
            $model->status = 1;
        } else {
            $model = new ArSeoProRedirectTable($id);
        }
        
        $from = null;
        $to = null;
        $type = null;
        $id_shop = 0;
        foreach ($data as $param) {
            if ($param['name'] == 'from') {
                $from = trim($param['value']);
            }
            if ($param['name'] == 'to') {
                $to = trim($param['value']);
            }
            if ($param['name'] == 'type') {
                $type = $param['value'];
            }
            if ($param['name'] == 'id_shop') {
                $id_shop = $param['value'];
            }
        }
        $model->from = pSQL($from);
        $model->to = pSQL($to);
        $model->type = (int)$type;
        $model->id_shop = (int)$id_shop;
        
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
        $model->save();
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'errors' => $errors,
            'model' => $model
        )));
    }
    
    public function ajaxProcessExport()
    {
        $pageSize = 100;
        $page = (int)Tools::getValue('page');
        $count = (int)Tools::getValue('count');
        
        $sql = 'SELECT COUNT(1) as c FROM `' . _DB_PREFIX_ . 'arseopro_redirect`';
        
        $res = Db::getInstance()->getRow($sql);
        $totalCount = (int)$res['c'];
        
        if ($totalCount == 0) {
            die(ArSeoProTools::jsonEncode(array(
                'percent' => 0,
                'count' => 0,
                'continue' => 0,
                'page' => $page,
                'success' => 0,
                'error' => $this->l('Your redirect list is empty. Nothing to export.')
            )));
        }
        
        $totalPages = ceil($totalCount / $pageSize);
        
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'arseopro_redirect` LIMIT ' . (int)($page * $pageSize) . ', ' . (int)$pageSize;
        
        $result = Db::getInstance()->executeS($sql);
        $filename = $this->module->getPath(true) . 'csv/export.csv';
        $f = null;
        if (!is_dir($this->module->getPath(true) . 'csv')) {
            if (!@mkdir($this->module->getPath(true) . 'csv', 0777, true)) {
                die(ArSeoProTools::jsonEncode(array(
                    'percent' => 0,
                    'count' => 0,
                    'continue' => 0,
                    'page' => $page,
                    'success' => 0,
                    'error' => sprintf($this->l('Cant create directory %s. Please create this directory manualy.'), $this->module->getPath(true) . 'csv/')
                )));
            }
        } elseif (!is_writable($this->module->getPath(true) . 'csv')) {
            die(ArSeoProTools::jsonEncode(array(
                'percent' => 0,
                'count' => 0,
                'continue' => 0,
                'page' => $page,
                'success' => 0,
                'error' => sprintf($this->l('Directory %s is not writable. Please change permission for this directory.'), $this->module->getPath(true) . 'csv/')
            )));
        }
        
        if ($page == 0) {
            if (file_exists($filename)) {
                unlink($filename);
            }
            $f = fopen($filename, 'w');
        } elseif ($page > 0) {
            $f = fopen($filename, 'a');
        }
        
        foreach ($result as $k => $row) {
            $line = array();
            $line[] = $row['from'];
            $line[] = $row['to'];
            $line[] = $row['type'];
            $line[] = $row['id_shop'];
            $line[] = $row['status'];
            fwrite($f, implode(';', $line) . PHP_EOL);
        }
        
        fclose($f);
        
        die(ArSeoProTools::jsonEncode(array(
            'percent' => ceil(($page + 1) / $totalPages * 100),
            'count' => count($result),
            'totalCount' => $totalCount,
            'processed' => $count + count($result),
            'continue' => (($page + 1) == $totalPages)? 0 : 1,
            'success' => 1,
            'time' => date('Y-m-d H:i:s', Configuration::get('ARSEO_NFL_TIME')),
            'page' => $page + 1
        )));
    }
    
    public function ajaxProcessPageNotFound()
    {
        $pageSize = 100;
        $page = (int)Tools::getValue('page');
        $count = (int)Tools::getValue('count');
        $to = Tools::getValue('to');
        $type = Tools::getValue('type');
        
        $sql = 'SELECT COUNT(DISTINCT(t.request_uri), t.id_shop) as c
            FROM `' . _DB_PREFIX_ . 'pagenotfound` as t 
            LEFT JOIN `' . _DB_PREFIX_ . 'arseopro_redirect` as r ON t.request_uri = r.`from` 
            WHERE r.`from` IS NULL';
        
        $res = Db::getInstance()->getRow($sql);
        $totalCount = (int)$res['c'];
        
        if ($totalCount == 0) {
            die(ArSeoProTools::jsonEncode(array(
                'percent' => 0,
                'count' => 0,
                'continue' => 0,
                'page' => $page,
                'success' => 0,
                'error' => $this->l('Page-not-found table is empty. Nothing to export.')
            )));
        }
        
        $totalPages = ceil($totalCount / $pageSize);
        
        $sql = 'SELECT DISTINCT(t.request_uri), t.id_shop
            FROM `' . _DB_PREFIX_ . 'pagenotfound` as t 
            LEFT JOIN `' . _DB_PREFIX_ . 'arseopro_redirect` as r ON t.request_uri = r.`from` 
            WHERE r.`from` IS NULL LIMIT ' . (int)($page * $pageSize) . ', ' . (int)$pageSize;
        
        $result = Db::getInstance()->executeS($sql);
        $filename = $this->module->getPath(true) . 'csv/not-found-list.csv';
        $f = null;
        if (!is_dir($this->module->getPath(true) . 'csv')) {
            if (!@mkdir($this->module->getPath(true) . 'csv', 0777, true)) {
                die(ArSeoProTools::jsonEncode(array(
                    'percent' => 0,
                    'count' => 0,
                    'continue' => 0,
                    'page' => $page,
                    'success' => 0,
                    'error' => sprintf($this->l('Cant create directory %s. Please create this directory manualy.'), $this->module->getPath(true) . 'csv/')
                )));
            }
        } elseif (!is_writable($this->module->getPath(true) . 'csv')) {
            die(ArSeoProTools::jsonEncode(array(
                'percent' => 0,
                'count' => 0,
                'continue' => 0,
                'page' => $page,
                'success' => 0,
                'error' => sprintf($this->l('Directory %s is not writable. Please change permission for this directory.'), $this->module->getPath(true) . 'csv/')
            )));
        }
        
        if ($page == 0) {
            if (file_exists($filename)) {
                unlink($filename);
            }
            Configuration::updateValue('ARSEO_NFL_TIME', time());
            $f = fopen($filename, 'w');
        } elseif ($page > 0) {
            $f = fopen($filename, 'a');
        }
        
        foreach ($result as $k => $row) {
            $line = array();
            $line[] = $row['request_uri'];
            $line[] = $to;
            $line[] = $type;
            $line[] = $row['id_shop'];
            fwrite($f, implode(';', $line) . PHP_EOL);
        }
        
        fclose($f);
        
        die(ArSeoProTools::jsonEncode(array(
            'percent' => ceil(($page + 1) / $totalPages * 100),
            'count' => count($result),
            'totalCount' => $totalCount,
            'processed' => $count + count($result),
            'continue' => (($page + 1) == $totalPages)? 0 : 1,
            'success' => 1,
            'time' => date('Y-m-d H:i:s', Configuration::get('ARSEO_NFL_TIME')),
            'page' => $page + 1
        )));
    }
    
    public function ajaxProcessEdit()
    {
        $id = Tools::getValue('id');
        $model = new ArSeoProRedirectTable($id);
        die(ArSeoProTools::jsonEncode($model));
    }
    
    public function ajaxProcessClear()
    {
        ArSeoProRedirectTable::truncate();
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1
        )));
    }
    
    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArSeoProRedirectTable($id);
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessEnabledconfiguration()
    {
        $id = Tools::getValue('id_redirect');
        $model = new ArSeoProRedirectTable($id);
        $model->status = $model->status? 0 : 1;
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->save(false),
            'text' => $this->l('Status updated')
        )));
    }
}
