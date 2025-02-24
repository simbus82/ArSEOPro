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
include_once dirname(__FILE__).'/../../classes/meta/models/ArSeoProMetaTable.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProListHelper.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoMetaController extends AdminArSeoControllerAbstract
{
    public $max_image_size;
    
    protected $mimeTypes = array(
        'jpg' => array(
            'image/jpeg'
        ),
        'jpeg' => array(
            'image/jpeg'
        ),
        'gif' => array(
            'image/gif'
        ),
        'png' => array(
            'image/png'
        )
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->max_image_size = (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE');
    }
    
    protected function isMimeTypeValid($mime, $ext)
    {
        if (isset($this->mimeTypes[$ext])) {
            if (in_array($mime, $this->mimeTypes[$ext])) {
                return true;
            }
        }
        return false;
    }
    
    public function getListId()
    {
        return 'meta-list';
    }
    
    protected function uploadImageFile($id, $storeKey)
    {
        self::$currentIndex = 'index.php?tab=AdminArSeoMeta';
        $fileTypes = array('jpeg', 'gif', 'png', 'jpg');
        $uploader = $id;
        $isImage = true;
        if ($isImage) {
            $image_uploader = new HelperImageUploader($uploader);
        } else {
            $image_uploader = new HelperUploader($uploader);
        }
        $image_uploader->setAcceptTypes($fileTypes);
        if ($isImage) {
            $image_uploader->setMaxSize($this->max_image_size);
        } else {
            $image_uploader->setMaxSize($this->module->fileUploadMaxSize());
        }
        $files = $image_uploader->process();
        $errors = array();
        foreach ($files as &$file) {
            if (isset($file['error']) && $file['error']) {
                $errors[] = $file['error'];
                continue;
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['save_path']);
            finfo_close($finfo);
            
            if ($this->isMimeTypeValid($mime, $ext)) {
                $filename = uniqid() . '.' . $ext;
                $file['filename'] = $filename;
                $file['real_path'] = $this->module->getUploadPath() . $filename;
                copy($file['save_path'], $this->module->getUploadPath() . $filename);
                $file['url'] = $this->module->getUploadsUrl() . $filename;
            } else {
                $file['error'] = $this->module->l('File type does not match its extension');
                $errors[] = $file['error'];
            }
        }
        if ($errors) {
            return array(
                $uploader => $files
            );
        } else {
            if ($storeKey) {
                Configuration::updateValue($storeKey, $file['url']);
            }
            return array(
                $image_uploader->getName() => $files
            );
        }
        
        return false;
    }
    
    public function ajaxProcessUploadFbCustomImage()
    {
        die(ArSeoProTools::jsonEncode($this->uploadImageFile('arseopro_fb_upload_image', null)));
    }
    
    public function ajaxProcessUploadTwCustomImage()
    {
        die(ArSeoProTools::jsonEncode($this->uploadImageFile('arseopro_tw_upload_image', null)));
    }
    
    public function fetchFilters()
    {
        return array(
            array(
                array(
                    'name',
                    'rule_type',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'fb_admins',
                    'fb_app',
                    'fb_title',
                    'fb_description',
                    'fb_type',
                    'fb_custom_image',
                    'tw_type',
                    'tw_account',
                    'tw_title',
                    'tw_description',
                    'tw_ch1',
                    'tw_ch2',
                    'tw_custom_image',
                ), 'pSQL'
            ),
            array(
                array(
                    'id_lang',
                    'id_category',
                    'fb_image',
                    'tw_image',
                ), 'int'
            )
        );
    }
    
    public function getFilterType($attribute)
    {
        foreach ($this->fetchFilters() as $filter) {
            if (in_array($attribute, $filter[0])) {
                return $filter[1];
            }
        }
        return false;
    }
    
    public function fetchData($data)
    {
        $result = array();
        foreach ($data as $param) {
            if (isset($param['name']) && $param['name']) {
                $attribute = trim($param['name']);
                if ($filter = $this->getFilterType($attribute)) {
                    if ($filter == 'pSQL') {
                        $result[$attribute] = trim(pSQL($param['value']));
                    } elseif ($filter == 'int') {
                        $result[$attribute] = (int)$param['value'];
                    }
                }
            }
        }
        return $result;
    }
    
    public function assignData($model, $data)
    {
        foreach ($data as $k => $v) {
            if (property_exists($model, $k)) {
                $model->$k = $v;
            }
        }
    }
    
    public function ajaxProcessSave()
    {
        $data = Tools::getValue('data');
        $id = Tools::getValue('id');
        $errors = array();
        if (!$id) {
            $model = new ArSeoProMetaTable();
            $model->status = 1;
        } else {
            $model = new ArSeoProMetaTable($id);
        }
        
        $id_category = 0;
        $categories = array();
        $metaPages = array();
        foreach ($data as $param) {
            if ((isset($param['name'])) && $param['name'] == 'categoryBox[]') {
                if ((int)$param['value'] > 0) {
                    $categories[] = (int)$param['value'];
                }
            }
            if ((isset($param['name'])) && $param['name'] == 'meta[]') {
                if ((int)$param['value'] > 0) {
                    $metaPages[] = (int)$param['value'];
                }
            }
        }
        $data = $this->fetchData($data);
        $this->assignData($model, $data);
        
        if ($data['id_category'] == 1 && empty($categories) && $model->rule_type != 'metapage') {
            $errors['id_category'] = $this->l('Please select categories');
        }
        if ($model->rule_type == 'metapage' && empty($metaPages)) {
            $errors['id_meta'] = $this->l('Please select meta pages');
        }
        $model->name = $data['name']? $data['name'] : ArSeoProMetaTable::generateRuleName();
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
            $model->clearRelations();
            if (in_array($model->rule_type, array('category', 'product', 'brand'))) {
                if ($categories && ($data['id_category'] == 1)) {
                    foreach ($categories as $id_category) {
                        $model->addRelation($id_category);
                    }
                } else {
                    $model->addRelation(0);
                }
            } else {
                foreach ($metaPages as $id_meta) {
                    $model->addRelation($id_meta);
                }
            }
        }
        
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'errors' => $errors,
            'model' => $model,
            'categories' => $categories
        )));
    }
    
    public function ajaxProcessRemoveBulk()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'DELETE FROM `' . ArSeoProMetaTable::getTableName() . '` WHERE id_rule IN (' . implode(', ', $ids) . ')';
        $sql2 = 'DELETE FROM `' . ArSeoProMetaTable::getTableName(true) . '` WHERE id_rule IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql2) && Db::getInstance()->execute($sql),
        )));
    }
    
    public function ajaxProcessActivate()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'UPDATE `' . ArSeoProMetaTable::getTableName() . '` SET `status` = 1 WHERE id_rule IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql)
        )));
    }
    
    public function ajaxProcessDeactivate()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        $sql = 'UPDATE `' . ArSeoProMetaTable::getTableName() . '` SET `status` = 0 WHERE id_rule IN (' . implode(', ', $ids) . ')';
        die(ArSeoProTools::jsonEncode(array(
            'success' => Db::getInstance()->execute($sql),
        )));
    }
    
    public function displayEditLink($token, $id, $name)
    {
        return $this->module->render('_partials/_button.tpl', array(
            'href' => '#',
            'onclick' => 'arSEO.meta.edit(' . $id . '); return false;',
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
            'onclick' => 'arSEO.meta.remove(' . $id . '); return false;',
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
            'onclick' => 'arSEO.meta.applyRule(' . $id . ', 0, 0, 0); return false;',
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
    
    public function renderTable($params = array())
    {
        $pageSize = isset($params['selected_pagination'])? $params['selected_pagination'] : 20;
        
        $helper = new ArSeoProListHelper();
        $helper->title = $this->l('URL Rewrite Rules for products');
        $helper->actions = array(
            'apply', 'edit', 'delete'
        );
        $helper->list_id = $this->getListId();
        $helper->identifier = 'id_rule';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->module->name;
        $helper->setPagination(array(20, 50, 100));
        
        $totalCount = ArSeoProMetaTable::getCount($params);
        
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
        $helper->token = Tools::getAdminTokenLite('AdminArSeoMeta');
        
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
                'js_action' => 'arSEO.meta.bulk.remove(); return false'
            ),
            'activate' => array(
                'text' => $this->l('Activate selected'),
                'icon' => 'icon-check',
                'confirm' => $this->l('Activate selected items?'),
                'js_action' => 'arSEO.meta.bulk.activate(); return false'
            ),
            'deactivate' => array(
                'text' => $this->l('Deactivate selected'),
                'icon' => 'icon-remove',
                'confirm' => $this->l('Deactivate selected items?'),
                'js_action' => 'arSEO.meta.bulk.deactivate(); return false'
            )
        );
        
        $list = ArSeoProMetaTable::getAll($params);
        $columns = array(
            'id_rule' => array(
                'title' => $this->l('#'),
                'filter_key' => 'id',
                'orderby' => false,
            ),
            'rule_type' => array(
                'title' => $this->l('Rule type'),
                'filter_key' => 'rule_type',
                'orderby' => false,
                'type' => 'select',
                'list' => array(
                    'product' => $this->l('Product'),
                    'category' => $this->l('Category'),
                    'metapage' => $this->l('Meta page')
                )
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
    
    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $model = new ArSeoProMetaTable($id);
        $model->clearRelations();
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->delete()
        )));
    }
    
    public function ajaxProcessClear()
    {
        ArSeoProMetaTable::truncate();
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1
        )));
    }
    
    public function ajaxProcessEnabledconfiguration()
    {
        $id = Tools::getValue('id_rule');
        $model = new ArSeoProMetaTable($id);
        $model->status = $model->status? 0 : 1;
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->save(false),
            'text' => $this->l('Status updated')
        )));
    }
    
    public function ajaxProcessEdit()
    {
        $id = Tools::getValue('id');
        $model = new ArSeoProMetaTable($id);
        $categories = $model->getRelations(true);
        $model->id_category = $categories? 1 : 0;
        $model->categories = $categories;
        if ($model->fb_custom_image) {
            $model->fb_custom_image_url = $this->module->getModuleBaseUrl() . 'uploads/' . $model->fb_custom_image;
        }
        if ($model->tw_custom_image) {
            $model->tw_custom_image_url = $this->module->getModuleBaseUrl() . 'uploads/' . $model->tw_custom_image;
        }
        die(ArSeoProTools::jsonEncode($model));
    }
    
    protected function generateKeywordLists($fields, $keywords)
    {
        $return = array();
        foreach ($fields as $field) {
            $return[] = $this->module->render('_partials/url/_keywords.tpl', array(
                'target' => $field,
                'keywords' => $keywords
            ));
        }
        return $return;
    }


    public function ajaxProcessGetKeywords()
    {
        $type = Tools::getValue('type');
        switch ($type) {
            case 'product':
                $keywords = ArSeoPro::getProductKeywords();
                break;
            case 'metapage':
                $keywords = ArSeoPro::getMetaKeywords();
                break;
            case 'brand':
                $keywords = ArSeoPro::getBrandKeywords();
                break;
            default:
                $keywords = ArSeoPro::getCategoryKeywords();
        }
        die(ArSeoProTools::jsonEncode(array(
            'meta' => $this->generateKeywordLists(array(
                'arseo-meta-rule-form_meta_title',
                'arseo-meta-rule-form_meta_description',
                'arseo-meta-rule-form_meta_keywords',
            ), $keywords),
            'fb' => $this->generateKeywordLists(array(
                'arseo-meta-rule-form_fb_title',
                'arseo-meta-rule-form_fb_description'
            ), $keywords),
            'tw' => $this->generateKeywordLists(array(
                'arseo-meta-rule-form_tw_title',
                'arseo-meta-rule-form_tw_description'
            ), $keywords)
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
            $query->from(ArSeoProMetaTable::TABLE_NAME);
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
        $rule = new ArSeoProMetaTable($id);
        if ($rule->rule_type == 'product') {
            if (!$total) {
                $total = $rule->getRelatedProductsCount();
            }
            $models = $rule->getRelatedProductIds($pageSize, $offset);
            $meta = array();
            foreach ($models as $id_product) {
                $meta[$id_product] = $this->module->generateProductMeta($rule, $id_product);
            }
        } elseif ($rule->rule_type == 'category') {
            if (!$total) {
                $total = $rule->getRelatedCategoriesCount();
            }
            $models = $rule->getRelatedCategories($pageSize, $offset);
            $meta = array();
            foreach ($models as $id_category) {
                $meta[$id_category] = $this->module->generateCategoryMeta($rule, $id_category);
            }
        } elseif ($rule->rule_type == 'metapage') {
            if (!$total) {
                $total = $rule->getRelatedMetaPagesCount();
            }
            $models = $rule->getRelatedMetaPages($pageSize, $offset);
            $meta = array();
            foreach ($models as $id_meta) {
                $meta[$id_meta] = $this->module->generateMetaPageMeta($rule, $id_meta);
            }
        } elseif ($rule->rule_type == 'brand') {
            if (!$total) {
                $total = $rule->getRelatedBrandPagesCount();
            }
            $models = $rule->getRelatedBrandPages($pageSize, $offset);
            $meta = array();
            foreach ($models as $id_meta) {
                $meta[$id_meta] = $this->module->generateBrandMeta($rule, $id_meta);
            }
        }
        $processed = $count + count($models);
        
        if ($processed == $total) {
            $rule->last_applied_at = date('Y-m-d H:i:s');
            $rule->save(false);
        }
        $continue = $processed < $total? 1 : 0;
        $nextRule = 0;
        if ($all) {
            $query = new DbQuery();
            $query->from(ArSeoProMetaTable::TABLE_NAME);
            $query->where('id_rule > ' . (int)$id . ' AND status = 1');
            $query->orderBy('id_rule ASC');
            if ($row = Db::getInstance()->getRow($query)) {
                $nextRule = $row['id_rule'];
            }
        }
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'id' => $id,
            'rule' => $rule,
            'total' => $total,
            'count' => count($models),
            'processed' => $processed,
            'offset' => $offset + count($models),
            'continue' => $continue,
            'percent' => round($processed / $total * 100),
            'meta' => $meta,
            'models' => $models,
            'nextRule' => $nextRule
        )));
    }
    
    public function ajaxProcessCheckOgTags()
    {
        $id_lang = Context::getContext()->language->id;
        $products = Product::getProducts($id_lang, 0, 1, 'id_product', 'asc');
        $link = Context::getContext()->link->getProductLink($products[0], null, null, null, $id_lang);
        $tpl = _PS_THEME_DIR_ . 'templates/catalog/product.tpl';
        $fileExists = file_exists($tpl);
        $fileReadable = is_readable($tpl);
        $fileWritable = is_writable($tpl);
        if (!$fileExists || !$fileReadable) {
            die(ArSeoProTools::jsonEncode(array(
                'link' => $link,
                'tpl' => $tpl,
                'fileExists' => $fileExists,
                'fileReadable' => $fileReadable,
                'fileWritable' => $fileWritable,
                'content' => '',
            )));
        }
        $tplContent = Tools::file_get_contents($tpl);
        $contentArray = explode("\n", $tplContent);
        $lines = array();
        $ogTagsExists = false;
        $ogTypeProduct = false;
        if (preg_match('/og:type/is', $tplContent)) {
            $ogTagsExists = true;
            if (preg_match('/property="og:type"\s+content="product"/is', $tplContent)) {
                $ogTypeProduct = true;
            }
        }
        foreach ($contentArray as $k => $string) {
            if (preg_match('/property="og:.*?"\s+content/is', $string)) {
                $string = trim($string);
                if (!preg_match('/^{\*.*?\*}$/is', $string)) {
                    $lines[$k + 1] = trim($string);
                }
            }
        }
        if (empty($lines)) {
            $ogTagsExists = false;
            $ogTypeProduct = false;
        }
        $content = '';
        $relativePath = str_replace(_PS_ROOT_DIR_, '', $tpl);
        if ($ogTagsExists) {
            $content = $this->module->render('_partials/meta/_og_notice.tpl', array(
                'ogTypeProduct' => $ogTypeProduct,
                'fileExists' => $fileExists,
                'fileReadable' => $fileReadable,
                'fileWritable' => $fileWritable,
                'relativePath' => $relativePath,
                'lines' => $lines,
            ));
        }
        
        die(ArSeoProTools::jsonEncode(array(
            'link' => $link,
            'tpl' => $tpl,
            'contentArray' => $contentArray,
            'fileExists' => $fileExists,
            'fileReadable' => $fileReadable,
            'fileWritable' => $fileWritable,
            'tplContent' => $tplContent,
            'lines' => $lines,
            'content' => $content,
            'ogTagsExists' => (int)$ogTagsExists,
            'ogTypeProduct' => (int)$ogTypeProduct,
            'relativePath' => $relativePath
        )));
    }
}
