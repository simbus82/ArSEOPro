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

include_once dirname(__FILE__).'/ArSeoProSitemapWriter.php';
include_once dirname(__FILE__).'/../ArSeoProSitemap.php';
include_once dirname(__FILE__).'/models/ArSeoProSitemapMetaPages.php';
include_once dirname(__FILE__).'/../ArSeoProTools.php';

/**
 * @property ArSeoPro $module
 */
class ArSeoProSitemapGenerator
{
    public $writer;
    public $module;
    public $config;
    public $id_shop;
    
    protected $hreflangMap = array(
        'gb' => 'en'
    );


    protected $link;
    protected $langs;
    protected $lastmod;
    
    protected $path;
    protected $indexPath;
    
    protected $totalCount;

    protected $data = null;

    protected $count = array(
        'index' => 0,
        'meta' => 0,
        'category' => 0,
        'product' => 0,
        'manufacturer' => 0,
        'cms' => 0,
        'supplier' => 0,
        'image' => 0,
        'smartblog' => 0,
        'smartblog_category' => 0,
        'prestablog' => 0,
        'prestablog_category' => 0,
        'prestablog_author' => 0,
        'simpleblog' => 0,
        'simpleblog_category' => 0,
        'faqs' => 0,
        'faq_category' => 0,
        'total' => 0
    );
    
    protected $realCount = array(
        'index' => 0,
        'meta' => 0,
        'category' => 0,
        'product' => 0,
        'manufacturer' => 0,
        'cms' => 0,
        'supplier' => 0,
        'image' => 0,
        'smartblog' => 0,
        'smartblog_category' => 0,
        'prestablog' => 0,
        'prestablog_category' => 0,
        'prestablog_author' => 0,
        'simpleblog' => 0,
        'simpleblog_category' => 0,
        'faqs' => 0,
        'faq_category' => 0,
        'total' => 0
    );
    
    protected $imageIds = array();

    public function __construct($module, $id_shop)
    {
        $this->module = $module;
        $this->config = new ArSeoProSitemap($this->module);
        $this->config->loadAllFromConfig();
        $this->writer = new ArSeoProSitemapWriter($this);
        $this->id_shop = $id_shop;
        $this->link = new Link('https://', 'https://');
        $this->lastmod = (new DateTime())->format(DateTime::ATOM);
    }
    
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    
    public function setIndexPath($path)
    {
        $this->indexPath = $path;
        return $this;
    }
    
    public function generateAll()
    {
        $data = array_merge(
            $this->getIndexData(),
            $this->getMetaData(),
            $this->getCategoriesData(),
            $this->getManufacturersData(),
            $this->getCmsData(),
            $this->getSuppliersData(),
            $this->getSmartblogData(),
            $this->getSmartblogCategoriesData(),
            $this->getPrestablogData(),
            $this->getPrestablogCategoriesData(),
            $this->getPrestablogAuthorsData(),
            $this->getSimpleblogData(),
            $this->getSimpleblogCategoriesData(),
            $this->getFAQData(),
            $this->getFAQCategoriesData()
        );
        $this->calcTotalCount(count($data));
        $sitemaps = array_chunk($data, $this->config->general->limit, true);
        
        $files = array();
        foreach ($sitemaps as $sitemap) {
            $filename = $this->getFileName(true);
            $this->writer->startSitemap($filename, true, $this->config->general->alternates);
            $this->writer->addXmlNodes($sitemap);
            $this->writer->endSitemap();
            $files[] = $filename;
            $this->writeDataFile($files);
        }
    }
    
    public function calcTotalCount($count)
    {
        $this->totalCount = $count + $this->getProductsCount();
    }
    
    public function getTotalCount()
    {
        return $this->totalCount;
    }
    
    public function readDataFile()
    {
        if (!file_exists($this->module->getSitemapBasePath(true) . $this->path . 'data.json')) {
            $this->data = array();
        } else {
            $json = Tools::file_get_contents($this->module->getSitemapBasePath(true) . $this->path . 'data.json');
            $this->data = ArSeoProTools::jsonDecode($json);
        }
        
        return $this->data;
    }
    
    public function writeDataFile($data)
    {
        $this->data = $data;
        file_put_contents($this->module->getSitemapBasePath(true) . $this->path . 'data.json', ArSeoProTools::jsonEncode($data));
    }
    
    public function generateProducts($page)
    {
        $data = $this->getProductsData($page * $this->config->general->limit);
        if (!empty($data)) {
            $filename = $this->getFileName(true);
            $this->writer->startSitemap($filename, true, $this->config->general->alternates);
            $this->writer->addXmlNodes($data);
            $this->writer->endSitemap();
            $this->data[] = $filename;
            $this->writeDataFile($this->data);
        }
    }
    
    public function generateIndexSitemap()
    {
        $this->writer->startIndexSitemap($this->getIndexFileName(true));
        $this->readDataFile();
        foreach ($this->data as $file) {
            $this->writer->addSitemaps(array(
                array(
                    'loc' => $this->getFileUrl($file),
                    'lastmod' => $this->lastmod
                )
            ));
        }
        $this->writer->endIndexSitemap();
        $this->updateRobots();
    }
    
    public function updateRobots()
    {
        $robotsFile = _PS_ROOT_DIR_.'/robots.txt';
        if (!file_exists($robotsFile)) {
            @file_put_contents($robotsFile, "User-Agent: *\nAllow: /\n");
        }
        if (!file_exists($robotsFile) || !is_writable($robotsFile)) {
            return false;
        }
        
        $robots = Tools::file_get_contents($robotsFile);
        $sitemaps = array();
        
        $d = opendir($this->module->getSitemapBasePath(true) . $this->indexPath);
        while ($f = readdir($d)) {
            $p = preg_replace('{/+}is', '/', $this->module->getSitemapBasePath(true) . $this->indexPath . '/' . $f);
            try {
                if (is_file($p)) {
                    if (pathinfo($f, PATHINFO_EXTENSION) == 'xml') {
                        if (preg_match('{_shop_(\d+)\.xml}is', $f, $matches)) {
                            $sitemaps[$matches[1]] = $this->getIndexFileUrl($f, $matches[1]);
                        }
                    }
                }
            } catch (Exception $ex) {
            }
        }
        $robots = preg_replace('{#~~arseopro.*?arseopro\ssitemaps~~#}is', '', $robots);
        $sitemapConfig = new ArSeoProSitemapGeneral($this->module);
        $sitemapConfig->loadFromConfig();
        $lines = array();
        if (!$sitemapConfig->disable) {
            $robots = preg_replace('{^\n$}im', '', $robots);
            $lines[] = "#~~arseopro sitemaps (this section generates automatically by sitemap submodule)~~#";
            foreach ($sitemaps as $sitemap) {
                $line = sprintf("Sitemap: %s", $sitemap);
                $lines[] = $line;
            }
            $lines[] = "#~~arseopro sitemaps~~#";
        }
        file_put_contents($robotsFile, $robots . "\n" . implode("\n", $lines));
    }
    
    public function clearSitemapDir()
    {
        if (is_dir($this->module->getSitemapBasePath(true) . $this->path)) {
            $d = opendir($this->module->getSitemapBasePath(true) . $this->path);
            while ($f = readdir($d)) {
                if (is_file($this->module->getSitemapBasePath(true) . $this->path . '/' . $f)) {
                    unlink($this->module->getSitemapBasePath(true) . $this->path . '/' . $f);
                }
            }
        }
    }
    
    public function getIndexFileName($abs = false)
    {
        if ($abs) {
            return $this->module->normalizePath($this->module->getSitemapBasePath(true) . $this->indexPath . 'sitemap_shop_' . $this->id_shop . '.xml');
        }
        return $this->module->normalizePath($this->indexPath . 'sitemap_shop_' . $this->id_shop . '.xml');
    }

    public function getFileUrl($file, $id_shop = null)
    {
        return $this->module->normalizePath($this->module->getBaseUrl($id_shop) . '/' . $this->module->getSitemapBasePath(false) . '/' . $this->path . '/' . basename($file));
    }
    
    public function getIndexFileUrl($file, $id_shop = null)
    {
        return $this->module->normalizePath($this->module->getBaseUrl($id_shop) . $this->indexPath . basename($file));
    }

    protected function getFileName($abs = false)
    {
        $data = $this->readDataFile();
        $lastFile = end($data);
        if (empty($lastFile)) {
            $k = 1;
        } else {
            $name = basename($lastFile);
            if (preg_match('/_(\d+)\.xml/is', $name, $mathes)) {
                $lastK = (int)$mathes[1];
                $k = $lastK + 1;
            }
        }
        
        if ($abs) {
            return $this->module->normalizePath($this->module->getSitemapBasePath(true) . $this->path . 'sitemap_shop_' . $this->id_shop . '_' . str_pad($k, 3, '0', STR_PAD_LEFT) . '.xml');
        }
        return $this->module->normalizePath($this->path . 'sitemap_shop_' . $this->id_shop . '_' . str_pad($k, 3, '0', STR_PAD_LEFT) . '.xml');
    }


    protected function getSitemapData($rawData, $type, $idKey, $config)
    {
        $data = array();
        $key = null;
        $ids = array();
        foreach ($rawData as $row) {
            $key = $type . '_' . $row[$idKey] . '::' . $row['id_lang'];
            $loc = null;
            switch ($type) {
                case 'supplier':
                    $loc = $this->link->getSupplierLink($row[$idKey], null, $row['id_lang']);
                    break;
                case 'cms':
                    $loc = $this->link->getCMSLink($row[$idKey], null, null, $row['id_lang']);
                    break;
                case 'manufacturer':
                    $loc = $this->link->getManufacturerLink($row[$idKey], null, $row['id_lang']);
                    break;
                case 'category':
                    $loc = $this->link->getCategoryLink($row[$idKey], null, $row['id_lang']);
                    break;
                case 'meta':
                    $loc = $this->link->getPageLink($row['page'], null, $row['id_lang']);
                    break;
                case 'product':
                    if (isset($row[$idKey]) && $row[$idKey]) {
                        if (isset($row['id_product_attribute'])) {
                            $key = $type . '_' . $row[$idKey] . '-' . (int)$row['id_product_attribute'] . '::' . $row['id_lang'];
                            $loc = $this->link->getProductLink($row[$idKey], null, null, null, $row['id_lang'], null, $row['id_product_attribute']);
                        } else {
                            $loc = $this->link->getProductLink($row[$idKey], null, null, null, $row['id_lang']);
                        }
                    }
                    break;
                case 'smartblog':
                    if (ArSeoProSitemap::isSmartBlog3()) {
                        if (class_exists('SmartBlogLink')) {
                            $smartbloglink = new SmartBlogLink();
                            $loc = $smartbloglink->getSmartBlogPostLink($row[$idKey], null, null, $row['id_lang']);
                        }
                    } elseif (ArSeoProSitemap::isSmartBlog2()) {
                        if (class_exists('smartblog')) {
                            $loc = smartblog::GetSmartBlogLink('smartblog_post', array(
                                'id_post' => $row[$idKey],
                                'slug' => $row['link_rewrite']
                            ), $this->id_shop, $row['id_lang']);
                        }
                    }
                    break;
                case 'smartblog_category':
                    if (ArSeoProSitemap::isSmartBlog3()) {
                        if (class_exists('SmartBlogLink')) {
                            $smartbloglink = new SmartBlogLink();
                            $loc = $smartbloglink->getSmartBlogCategoryLink($row[$idKey], null, null, $row['id_lang']);
                        }
                    } elseif (ArSeoProSitemap::isSmartBlog2()) {
                        if (class_exists('smartblog')) {
                            $loc = smartblog::GetSmartBlogLink('smartblog_category', array(
                                'id_category' => $row[$idKey],
                                'slug' => $row['link_rewrite']
                            ), $this->id_shop, $row['id_lang']);
                        }
                    }
                    break;
                case 'prestablog':
                    if (class_exists('PrestaBlog')) {
                        $loc = PrestaBlog::prestablogUrl(array(
                            'id' => $row[$idKey],
                            'seo' => $row['link_rewrite'],
                            'id_lang' => $row['id_lang']
                        ));
                    }
                    break;
                case 'prestablog_category':
                    if (class_exists('PrestaBlog')) {
                        $loc = PrestaBlog::prestablogUrl(array(
                            'c' => $row[$idKey],
                            'titre' => $row['link_rewrite'],
                            'id_lang' => $row['id_lang']
                        ));
                    }
                    break;
                case 'prestablog_author':
                    if (class_exists('PrestaBlog')) {
                        $loc = PrestaBlog::prestablogUrl(array(
                            'au' => $row[$idKey],
                            'titre' => $row['firstname'],
                            'id_lang' => $row['id_lang']
                        ));
                    }
                    break;
                case 'simpleblog':
                    $loc = $row['url'];
                    break;
                case 'simpleblog_category':
                    $loc = $row['url'];
                    break;
                case 'faqs':
                    $loc = ArSeoProSitemap::getFAQUrl($row['id_lang'], $row['link_rewrite'], $row['category_rewrite']) ;
                    break;
                case 'faqs_category':
                    $loc = ArSeoProSitemap::getFAQCategoryUrl($row['id_lang'], $row['link_rewrite']) ;
                    break;
            }
            if (!empty($loc)) {
                $item = array(
                    '_id' => $row[$idKey],
                    '_id_lang' => $row['id_lang'],
                    '_type' => $type,
                    '_link_rewrite' => isset($row['link_rewrite'])? $row['link_rewrite'] : null,
                    '_title' => isset($row['name'])? $row['name'] : null,
                    'loc' => $loc,
                    'lastmod' => $this->lastmod,
                    'changefreq' => $config->freq,
                    'priority' => $config->priority,
                );
                if ($type == 'product' && $this->config->products->images != 0) {
                    if (isset($row['id_product_attribute'])) {
                        $item['_id_product_attribute'] = (int)$row['id_product_attribute'];
                    }
                    $item['_images'] = $this->getProductImages($item);
                }
                $ids[] = $row[$idKey];
                $data[$key] = $item;
            }
        }
        if ($this->config->general->alternates) {
            $data = $this->addAlternates($data);
        }
        $ids = array_unique($ids);
        $this->realCount[$type] = count($data);
        $this->count[$type] = count($ids);
        if ($type == 'product') {
            $this->imageIds = array_unique($this->imageIds);
            $this->count['image'] = count($this->imageIds);
        }
        return $data;
    }
    
    public function getProductImages($item)
    {
        if ($this->config->products->images == 1) {
            $sql = 'SELECT i.id_image, il.legend FROM `' . _DB_PREFIX_ . 'image` i '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON il.id_image = i.id_image '
                    . 'WHERE i.cover = 1 AND i.id_product=' . (int)$item['_id'] . ' AND il.id_lang = ' . (int)$item['_id_lang'];
            $images = Db::getInstance()->executeS($sql);
        } else {
            if (isset($item['_id_product_attribute'])) {
                $images = Image::getImages($item['_id_lang'], $item['_id'], $item['_id_product_attribute']);
            } else {
                $images = Image::getImages($item['_id_lang'], $item['_id']);
            }
        }
        $data = array();
        if ($images) {
            foreach ($images as $img) {
                $this->imageIds[] = $img['id_image'];
                $url = pSQL($this->link->getImageLink($item['_link_rewrite'], $img['id_image'], $this->config->products->image_type));
                $i = array(
                    'loc' => $url
                );
                if ($this->config->products->image_title && isset($item['_title']) && !empty($item['_title'])) {
                    $i['title'] = $item['_title'];
                }
                if ($this->config->products->image_caption && $img['legend']) {
                    $i['caption'] = $img['legend'];
                }
                $data[] = $i;
                $this->realCount['image'] ++;
            }
        }
        return $data;
    }
    
    public function getProductsData($offset)
    {
        return $this->getSitemapData($this->getProducts($offset), 'product', 'id_product', $this->config->products);
    }


    public function getSuppliersData()
    {
        return $this->getSitemapData($this->getSuppliers(), 'supplier', 'id_supplier', $this->config->suppliers);
    }
    
    public function getFAQData()
    {
        if (!ArSeoProSitemap::isFAQsInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getFAQs(), 'faqs', 'id_gomakoil_faq', $this->config->faqs);
    }
    
    public function getFAQCategoriesData()
    {
        if (!ArSeoProSitemap::isFAQsInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getFAQsCategories(), 'faqs_category', 'id_gomakoil_faq_category', $this->config->faqs);
    }
    
    public function getSimpleblogData()
    {
        if (!ArSeoProSitemap::isSimpleBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getSimpleblogs(), 'simpleblog', 'id_simpleblog_post', $this->config->simpleblog);
    }
    
    public function getSimpleblogCategoriesData()
    {
        if (!ArSeoProSitemap::isSimpleBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getSimpleblogCategories(), 'simpleblog_category', 'id', $this->config->simpleblog);
    }
    
    public function getPrestablogData()
    {
        if (!ArSeoProSitemap::isPrestaBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getPrestablogs(), 'prestablog', 'id_prestablog_news', $this->config->prestablog);
    }
    
    public function getSmartblogData()
    {
        if (!ArSeoProSitemap::isSmartBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getSmartblogs(), 'smartblog', 'id_smart_blog_post', $this->config->smartblog);
    }
    
    public function getSmartblogCategoriesData()
    {
        if (!ArSeoProSitemap::isSmartBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getSmartblogCategories(), 'smartblog_category', 'id_smart_blog_category', $this->config->smartblog);
    }
    
    public function getPrestablogCategoriesData()
    {
        if (!ArSeoProSitemap::isPrestaBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getPrestablogCategories(), 'prestablog_category', 'id_prestablog_categorie', $this->config->prestablog);
    }
    
    public function getPrestablogAuthorsData()
    {
        if (!ArSeoProSitemap::isPrestaBlogInstalled()) {
            return array();
        }
        return $this->getSitemapData($this->getPrestablogAuthors(), 'prestablog_author', 'id_author', $this->config->prestablog);
    }
    
    public function getCmsData()
    {
        return $this->getSitemapData($this->getCms(), 'cms', 'id_cms', $this->config->cms);
    }
    
    public function getManufacturersData()
    {
        return $this->getSitemapData($this->getManufacturers(), 'manufacturer', 'id_manufacturer', $this->config->manufacturers);
    }
    
    public function getCategoriesData()
    {
        return $this->getSitemapData($this->getCategories(), 'category', 'id_category', $this->config->categories);
    }
    
    public function getIndexData()
    {
        $data = array();
        $key = null;
        
        // add index page
        foreach ($this->getLangsIds() as $id_lang) {
            $key = 'index_index::' . $id_lang;
            $loc = $this->link->getPageLink('index', null, $id_lang);
            $item = array(
                '_id' => 'index',
                '_id_lang' => $id_lang,
                '_type' => 'index',
                'loc' => $loc,
                'lastmod' => $this->lastmod,
                'changefreq' => $this->config->meta->freq,
                'priority' => $this->config->meta->priority,
            );
            $data[$key] = $item;
        }
        if ($this->config->general->alternates) {
            $data = $this->addAlternates($data);
        }
        $this->realCount['index'] = count($data);
        $this->count['index'] = 1;
        return $data;
    }
    
    public function getMetaData()
    {
        return $this->getSitemapData($this->getMetaPages(), 'meta', 'id_meta', $this->config->meta);
    }
    
    public function addAlternates($data)
    {
        $alternates = array();
        $langs = $this->getLangs();
        foreach ($data as $k => $item) {
            if (isset($langs[$item['_id_lang']])) {
                if (isset($item['_id_product_attribute'])) {
                    $key = $item['_type'] . '_' . $item['_id'] . '-' . $item['_id_product_attribute'];
                } else {
                    $key = $item['_type'] . '_' . $item['_id'];
                }

                if (isset($alternates[$key])) {
                    $alternates[$key][] = array(
                        'hreflang' => $langs[$item['_id_lang']],
                        'href' => $item['loc'],
                    );
                } else {
                    $alternates[$key] = array(
                        array(
                            'hreflang' => $langs[$item['_id_lang']],
                            'href' => $item['loc'],
                        )
                    );
                }
            }
        }
        foreach ($data as $k => $item) {
            $altKey = Tools::substr($k, 0, strpos($k, ':'));
            if (isset($alternates[$altKey])) {
                $data[$k]['_alternates'] = $alternates[$altKey];
            }
        }
        return $data;
    }
    
    public function getProductsCount()
    {
        $select = array('COUNT(p.id_product) as c');
        if ($this->config->products->all) {
            $sql = 'SELECT ' . implode(', ', $select) . ' FROM `' . _DB_PREFIX_ . 'product_lang` pl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` p ON p.id_product = pl.id_product '
                . 'WHERE pl.id_shop=' . (int)$this->id_shop . ' AND p.id_shop=' . (int)$this->id_shop . ' AND pl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        } else {
            $sql = 'SELECT ' . implode(', ', $select) . ' FROM `' . _DB_PREFIX_ . 'product_lang` pl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` p ON p.id_product = pl.id_product '
                . 'LEFT JOIN `' . ArSeoProSitemapProduct::getTableName() . '` sp ON sp.id_product = p.id_product '
                . 'WHERE pl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') '
                . 'AND sp.id_shop=' . (int)$this->id_shop . ' AND p.id_shop=' . (int)$this->id_shop . ' AND sp.export = 1';
        }
        if ($this->config->products->active_only) {
            $sql .= ' AND p.active = 1';
        }
        $sql .= ' ORDER BY p.id_product';
        $data = Db::getInstance()->getRow($sql);
        
        return $data['c'];
    }
    
    public function getProducts($offset)
    {
        $select = array('p.id_product', 'pl.id_lang');
        if ($this->config->products->images) {
            $select[] = 'pl.link_rewrite';
            if ($this->config->products->image_title) {
                $select[] = 'pl.name';
            }
        }
        if ($this->config->products->attributes) {
            $select[] = 'pa.id_product_attribute';
        }
        if ($this->config->products->all) {
            $sql = 'SELECT ' . implode(', ', $select) . ' FROM `' . _DB_PREFIX_ . 'product_lang` pl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = pl.id_product '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.id_product = pl.id_product ';
            if ($this->config->products->attributes) {
                $sql .= 'LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.id_product = p.id_product ';
            }
            $sql .= 'WHERE pl.id_shop=' . (int)$this->id_shop . ' AND ps.id_shop=' . (int)$this->id_shop . ' AND pl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        } else {
            $sql = 'SELECT ' . implode(', ', $select) . ' FROM `' . _DB_PREFIX_ . 'product_lang` pl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = pl.id_product '
                . 'LEFT JOIN `' . ArSeoProSitemapProduct::getTableName() . '` sp ON sp.id_product = p.id_product ';
            if ($this->config->products->attributes) {
                $sql .= 'LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.id_product = p.id_product ';
            }
            $sql .= 'WHERE pl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') '
                . 'AND sp.id_shop=' . (int)$this->id_shop . ' AND sp.export = 1';
        }
        if ($this->config->products->active_only) {
            $sql .= ' AND p.active = 1';
        }
        $sql .= ' ORDER BY p.id_product';
        $sql .= ' LIMIT ' . (int)$offset . ', ' . (int)$this->config->general->limit;
        
        $data = Db::getInstance()->executeS($sql);
        
        if ($this->config->products->skip_zero) {
            foreach ($data as $k => $row) {
                $id_product_attribute = isset($row['id_product_attribute'])? $row['id_product_attribute'] : null;
                if ($this->isZeroQty($row['id_product'], $id_product_attribute)) {
                    unset($data[$k]);
                }
            }
        }
        return $data;
    }
    
    public function isZeroQty($id_product, $id_product_attribute)
    {
        return Product::getQuantity($id_product, $id_product_attribute) <= 0? true : false;
    }
    
    public function getSuppliers()
    {
        if ($this->config->suppliers->all) {
            $sql = 'SELECT s.id_supplier, sl.id_lang FROM `' . _DB_PREFIX_ . 'supplier_lang` sl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON s.id_supplier = sl.id_supplier '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'supplier_shop` ss ON s.id_supplier = ss.id_supplier '
                . 'WHERE ss.id_shop=' . (int)$this->id_shop . ' AND sl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        } else {
            $sql = 'SELECT s.id_supplier, sl.id_lang FROM `' . _DB_PREFIX_ . 'supplier_lang` sl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON s.id_supplier = sl.id_supplier '
                . 'LEFT JOIN `' . ArSeoProSitemapSupplier::getTableName() . '` ss2 ON ss2.id_supplier = s.id_supplier '
                . 'WHERE sl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') '
                . 'AND ss2.id_shop=' . (int)$this->id_shop . ' AND ss2.export = 1';
        }
        if ($this->config->suppliers->active_only) {
            $sql .= ' AND s.active = 1';
        }
        $sql .= ' ORDER BY s.id_supplier';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getSmartblogCategories()
    {
        $depth = '';
        if (ArSeoProSitemap::isSmartBlog3()) {
            $depth = ' AND sbc.level_depth > 0 ';
        }
        
        $sql = 'SELECT sbc.id_smart_blog_category, sbcl.id_lang, sbcl.link_rewrite FROM `' . _DB_PREFIX_ . 'smart_blog_category` sbc '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'smart_blog_category_lang` sbcl ON sbcl.id_smart_blog_category = sbc.id_smart_blog_category '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'smart_blog_category_shop` sbcs ON sbcs.id_smart_blog_category = sbc.id_smart_blog_category '
            . 'WHERE sbcs.id_shop=' . (int)$this->id_shop . ' AND sbcl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') ' . $depth;
        
        if ($this->config->smartblog->active_only) {
            $sql .= ' AND sbc.active = 1';
        }
        $sql .= ' ORDER BY sbc.id_smart_blog_category';
        
        return Db::getInstance()->executeS($sql);
    }
    
    public function getPrestablogCategories()
    {
        $sql = 'SELECT c.id_prestablog_categorie, cl.id_lang, cl.link_rewrite FROM ' . _DB_PREFIX_ . 'prestablog_categorie c
            LEFT JOIN ' . _DB_PREFIX_ . 'prestablog_categorie_lang cl ON cl.id_prestablog_categorie = c.id_prestablog_categorie
            WHERE c.id_shop = ' . (int)$this->id_shop . ' AND cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        
        if ($this->config->prestablog->active_only) {
            $sql .= ' AND c.actif = 1';
        }
        $sql .= ' ORDER BY c.id_prestablog_categorie';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getPrestablogAuthors()
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'prestablog_author ORDER BY id_author';
        $rows = Db::getInstance()->executeS($sql);
        $res = array();
        foreach ($rows as $row) {
            foreach ($this->getLangsIds() as $id_lang) {
                $row['id_lang'] = $id_lang;
                $res[] = $row;
            }
        }
        return $res;
    }
    
    public function getFAQs()
    {
        $where = array();
        $sql = 'SELECT f.id_gomakoil_faq, fs.id_shop, fl.id_lang, fl.link_rewrite, fcl.link_rewrite AS category_rewrite FROM ' . _DB_PREFIX_ . 'gomakoil_faq f 
            LEFT JOIN ' . _DB_PREFIX_ . 'gomakoil_faq_lang fl ON fl.id_gomakoil_faq = f.id_gomakoil_faq
            LEFT JOIN ' . _DB_PREFIX_ . 'gomakoil_faq_shop fs ON fs.id_gomakoil_faq = f.id_gomakoil_faq
            LEFT JOIN ' . _DB_PREFIX_ . 'gomakoil_faq_category_lang fcl ON fcl.id_gomakoil_faq_category = f.id_gomakoil_faq_category AND fcl.id_lang = fl.id_lang';
        if ($this->config->faqs->active_only) {
            $where[] = 'f.active = 1';
        }
        $where[] = 'fs.id_shop = ' . (int)$this->id_shop;
        $sql .= (' WHERE ' . implode(' AND ', $where));
        return Db::getInstance()->executeS($sql);
    }
    
    public function getFAQsCategories()
    {
        $where = array();
        $sql = 'SELECT fc.id_gomakoil_faq_category, fcs.id_shop, fcl.id_lang, fcl.link_rewrite FROM ' . _DB_PREFIX_ . 'gomakoil_faq_category fc 
            LEFT JOIN ' . _DB_PREFIX_ . 'gomakoil_faq_category_lang fcl ON fcl.id_gomakoil_faq_category = fc.id_gomakoil_faq_category
            LEFT JOIN ' . _DB_PREFIX_ . 'gomakoil_faq_category_shop fcs ON fcl.id_gomakoil_faq_category = fc.id_gomakoil_faq_category';
        if ($this->config->faqs->active_only) {
            $where[] = 'fc.active = 1';
        }
        $where[] = 'fcs.id_shop = ' . (int)$this->id_shop;
        $sql .= (' WHERE ' . implode(' AND ', $where));
        $res = Db::getInstance()->executeS($sql);
        foreach ($this->getLangs() as $id_lang => $iso) {
            $res[] = array(
                'id_gomakoil_faq_category' => 0,
                'id_shop' => $this->id_shop,
                'id_lang' => $id_lang,
                'link_rewrite' => null
            );
        }
        return $res;
    }
    
    public function getSimpleblogs()
    {
        include_once _PS_MODULE_DIR_ . '/ph_simpleblog/classes/BlogPostsFinder.php';
        $finder = new BlogPostsFinder();
        $finder->setLimit(0);
        $res = array();
        if ($this->config->simpleblog->active_only) {
            $finder->setOnlyActive(true);
        }
        foreach ($this->getLangsIds() as $id_lang) {
            $finder->setIdLang($id_lang);
            $posts = $finder->findPosts();
            foreach ($posts as $row) {
                $res[] = $row;
            }
        }
        return $res;
    }
    
    public function getSimpleblogCategories()
    {
        include_once _PS_MODULE_DIR_ . '/ph_simpleblog/models/SimpleBlogCategory.php';
        $res = array();
        foreach ($this->getLangsIds() as $id_lang) {
            $categories = SimpleBlogCategory::getCategories($id_lang, $this->config->simpleblog->active_only);
            $res[] = array(
                'id' => 0,
                'id_lang' => $id_lang,
                'url' => Context::getContext()->link->getModuleLink('ph_simpleblog', 'list', array(), null, $id_lang)
            );
            foreach ($categories as $row) {
                $row['id_lang'] = $id_lang;
                $res[] = $row;
            }
        }
        
        return $res;
    }
    
    public function getPrestablogs()
    {
        $sql = 'SELECT n.id_prestablog_news, nl.id_lang, nl.link_rewrite FROM ' . _DB_PREFIX_ . 'prestablog_news_lang nl 
	LEFT JOIN ' . _DB_PREFIX_ . 'prestablog_news n ON n.id_prestablog_news = nl.id_prestablog_news
	WHERE n.id_shop = ' . (int)$this->id_shop . ' AND nl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        
        if ($this->config->prestablog->active_only) {
            $sql .= ' AND n.actif = 1';
        }
        $sql .= ' ORDER BY n.id_prestablog_news';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getSmartblogs()
    {
        $sql = 'SELECT sbp.id_smart_blog_post, sbpl.id_lang, sbpl.link_rewrite FROM `' . _DB_PREFIX_ . 'smart_blog_post` sbp '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'smart_blog_post_lang` sbpl ON sbpl.id_smart_blog_post = sbp.id_smart_blog_post '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'smart_blog_post_shop` sbps ON sbps.id_smart_blog_post = sbp.id_smart_blog_post '
            . 'WHERE sbps.id_shop=' . (int)$this->id_shop . ' AND sbpl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        
        if ($this->config->smartblog->active_only) {
            $sql .= ' AND sbp.active = 1';
        }
        $sql .= ' ORDER BY sbp.id_smart_blog_post';
        
        return Db::getInstance()->executeS($sql);
    }
    
    public function getCms()
    {
        if (ArSeoProTools::isColumnExists(_DB_PREFIX_ . 'cms_lang', 'id_shop')) {
            if ($this->config->cms->all) {
                $sql = 'SELECT c.id_cms, cl.id_lang FROM `' . _DB_PREFIX_ . 'cms_lang` cl '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms` c ON c.id_cms = cl.id_cms '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms_shop` cs ON cs.id_cms = c.id_cms '
                    . 'WHERE cl.id_shop=' . (int)$this->id_shop . ' AND cs.id_shop=' . (int)$this->id_shop . ' AND cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
            } else {
                $sql = 'SELECT c.id_cms, cl.id_lang FROM `' . _DB_PREFIX_ . 'cms_lang` cl '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms` c ON c.id_cms = cl.id_cms '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms_shop` cs ON cs.id_cms = c.id_cms '
                    . 'LEFT JOIN `' . ArSeoProSitemapCms::getTableName() . '` sc ON sc.id_cms = c.id_cms '
                    . 'WHERE cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') '
                    . 'AND cl.id_shop=' . (int)$this->id_shop . ' AND cs.id_shop=' . (int)$this->id_shop . ' AND sc.export = 1';
            }
        } else {
            if ($this->config->cms->all) {
                $sql = 'SELECT c.id_cms, cl.id_lang FROM `' . _DB_PREFIX_ . 'cms_lang` cl '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms` c ON c.id_cms = cl.id_cms '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms_shop` cs ON cs.id_cms = c.id_cms '
                    . 'WHERE cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')' . ' AND cs.id_shop=' . (int)$this->id_shop;
            } else {
                $sql = 'SELECT c.id_cms, cl.id_lang FROM `' . _DB_PREFIX_ . 'cms_lang` cl '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms` c ON c.id_cms = cl.id_cms '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . 'cms_shop` cs ON cs.id_cms = c.id_cms '
                    . 'LEFT JOIN `' . ArSeoProSitemapCms::getTableName() . '` sc ON sc.id_cms = c.id_cms '
                    . 'WHERE cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') AND cs.id_shop=' . (int)$this->id_shop
                    . 'AND sc.export = 1';
            }
        }
        if ($this->config->cms->active_only) {
            $sql .= ' AND c.active = 1';
        }
        $sql .= ' ORDER BY c.id_cms';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getManufacturers()
    {
        if ($this->config->manufacturers->all) {
            $sql = 'SELECT m.id_manufacturer, ml.id_lang FROM `' . _DB_PREFIX_ . 'manufacturer_lang` ml '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.id_manufacturer = ml.id_manufacturer '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer_shop` ms ON m.id_manufacturer = ms.id_manufacturer '
                . 'WHERE ms.id_shop=' . (int)$this->id_shop . ' AND ml.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        } else {
            $sql = 'SELECT m.id_manufacturer, ml.id_lang FROM `' . _DB_PREFIX_ . 'manufacturer_lang` ml '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.id_manufacturer = ml.id_manufacturer '
                . 'LEFT JOIN `' . ArSeoProSitemapManufacturer::getTableName() . '` sm ON sm.id_manufacturer = m.id_manufacturer '
                . 'WHERE ml.id_lang IN (' . implode(',', $this->getLangsIds()) . ') '
                . 'AND sm.id_shop=' . (int)$this->id_shop . ' AND sm.export = 1';
        }
        if ($this->config->manufacturers->active_only) {
            $sql .= ' AND m.active = 1';
        }
        $sql .= ' ORDER BY m.id_manufacturer';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getCategories()
    {
        if ($this->config->categories->all) {
            $sql = 'SELECT c.id_category, cl.id_lang FROM `' . _DB_PREFIX_ . 'category_lang` cl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON c.id_category = cl.id_category '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category '
                . 'WHERE c.is_root_category = 0 AND c.id_parent != 0 AND cl.id_shop=' . (int)$this->id_shop . ' AND cs.id_shop=' . (int)$this->id_shop . ' AND cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ')';
        } else {
            $sql = 'SELECT c.id_category, cl.id_lang FROM `' . _DB_PREFIX_ . 'category_lang` cl '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON c.id_category = cl.id_category '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category = c.id_category '
                . 'LEFT JOIN `' . ArSeoProSitemapCategory::getTableName() . '` sc ON sc.id_category = c.id_category '
                . 'WHERE c.is_root_category = 0 AND c.id_parent != 0 AND cl.id_shop=' . (int)$this->id_shop . ' AND cs.id_shop=' . (int)$this->id_shop . ' AND cl.id_lang IN (' . implode(',', $this->getLangsIds()) . ') '
                . 'AND cl.id_shop=' . (int)$this->id_shop . ' AND sc.export = 1';
        }
        if ($this->config->categories->active_only) {
            $sql .= ' AND c.active = 1';
        }
        $sql .= ' ORDER BY c.id_category';
        
        return Db::getInstance()->executeS($sql);
    }
    
    public function getMetaPages()
    {
        if ($this->config->meta->all) {
            $sql = 'SELECT m.id_meta, m.page, ml.id_lang FROM `' . _DB_PREFIX_ . 'meta_lang` ml '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'meta` m ON m.id_meta = ml.id_meta '
                . 'WHERE ' . implode(' AND ', ArSeoProSitemapMetaPages::getInitialFilters($this->getLangsIds(), $this->id_shop));
        } else {
            $sql = 'SELECT m.id_meta, m.page, ml.id_lang FROM `' . _DB_PREFIX_ . 'meta_lang` ml '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'meta` m ON m.id_meta = ml.id_meta '
                . 'LEFT JOIN `' . ArSeoProSitemapMetaPages::getTableName() . '` sm ON sm.id_meta = m.id_meta '
                . 'WHERE ' . implode(' AND ', ArSeoProSitemapMetaPages::getInitialFilters($this->getLangsIds(), $this->id_shop)) . ' AND sm.export = 1';
        }
        $sql .= ' ORDER BY m.id_meta';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getLangs()
    {
        if (empty($this->langs)) {
            $langs = array();
            foreach ($this->getLangsIds() as $lang) {
                $l = Language::getIsoById($lang);
                $langs[$lang] = $this->getHrefLang($l);
            }
            $this->langs = $langs;
        }
        return $this->langs;
    }
    
    public function getHrefLang($lang)
    {
        $iso = Tools::strtolower($lang);
        if (isset($this->hreflangMap[$iso])) {
            return $this->hreflangMap[$iso];
        }
        return $lang;
    }
    
    public function getLangsIds()
    {
        $ids = array();
        foreach ($this->config->general->langs as $id) {
            $ids[] = (int)$id;
        }
        return $ids;
    }
    
    public function tst1()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product`';
        return Db::getInstance()->executeS($sql);
    }
    
    public function getRealCount()
    {
        $total = 0;
        foreach ($this->realCount as $k => $count) {
            if ($k != 'image') {
                $total += $count;
            }
        }
        $this->realCount['total'] = $total;
        return $this->realCount;
    }
    
    public function getCount()
    {
        $total = 0;
        foreach ($this->count as $k => $count) {
            if ($k != 'image') {
                $total += $count;
            }
        }
        $this->count['total'] = $total;
        return $this->count;
    }
}
