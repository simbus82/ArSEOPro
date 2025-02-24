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

class Link extends LinkCore
{
    public static $cacheDisableDefaultAttributeAnchor;
    public static $cacheDisableAnchor;
    public static $cacheDisableDefaultAttribute;
    public static $cacheDisableAnchorIds;
    
    public function arSeoProOverrideVersion()
    {
        return '1.9.5';
    }
    
    protected function getLangLink($id_lang = null, Context $context = null, $id_shop = null)
    {
        if (!Module::isEnabled('arseopro')) {
            return parent::getLangLink($id_lang, $context, $id_shop);
        }
        
        if (Configuration::get('ARS_REMOVE_DEF_LANG', null, null, $id_shop) &&
            Language::isMultiLanguageActivated()) {
            if (!$id_lang) {
                $id_lang = $context->language->id;
            }

            if ($id_lang == Configuration::get('PS_LANG_DEFAULT', null, null, $id_shop)) {
                return '';
            }
        }

        return parent::getLangLink($id_lang, $context, $id_shop);
    }

    public function getCategoryLink(
        $category,
        $alias = null,
        $id_lang = null,
        $selected_filters = null,
        $id_shop = null,
        $relative_protocol = false
    ) {
        if (!Module::isEnabled('arseopro')) {
            return parent::getCategoryLink($category, $alias, $id_lang, $selected_filters, $id_shop, $relative_protocol);
        }
        
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

        if (!is_object($category)) {
            if (is_array($category) && isset($category['id_category'])) {
                $category = new Category($category['id_category'], $id_lang);
            } elseif ((int)$category) {
                $category = new Category((int)$category, $id_lang);
            } else {
                return null;
                throw new PrestaShopException('Invalid category vars');
            }
        }

        $params = array();
        $params['id'] = $category->id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));

        $dispatcher = Dispatcher::getInstance();
        if ($dispatcher->hasKeyword('category_rule', $id_lang, 'categories', $id_shop)) {
            $cats = array();
            foreach ($category->getParentsCategories($id_lang) as $cat) {
                if (!in_array($cat['id_category'], Link::$category_disable_rewrite) && !empty($cat['link_rewrite'])) {
                    $cats[] = $cat['link_rewrite'];
                }
            }
            $cats = array_reverse($cats);
            array_pop($cats);
            $params['categories'] = implode('/', $cats);
        }

        $selected_filters = is_null($selected_filters) ? '' : $selected_filters;
        if (empty($selected_filters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selected_filters;
        }

        return $url.Dispatcher::getInstance()->createUrl($rule, $id_lang, $params, $this->allow, '', $id_shop);
    }

    public function getCMSCategoryLink(
        $cms_category,
        $alias = null,
        $id_lang = null,
        $id_shop = null,
        $relative_protocol = false
    ) {
        if (!Module::isEnabled('arseopro')) {
            return parent::getCMSCategoryLink($cms_category, $alias, $id_lang, $id_shop, $relative_protocol);
        }
        
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

        $dispatcher = Dispatcher::getInstance();
        if (!is_object($cms_category)) {
            $cms_category = new CMSCategory((int)$cms_category, $id_lang);
        }

        // Set available keywords
        $params = array();
        $params['id'] = $cms_category->id;

        $params['rewrite'] = $cms_category->link_rewrite;
        if (is_array($params['rewrite']) && isset($params['rewrite'][(int)$id_lang])) {
            $params['rewrite'] = $params['rewrite'][(int)$id_lang];
        }
        if ($alias) {
            $params['rewrite'] = $alias;
        }

        $params['meta_keywords'] = $cms_category->meta_keywords;
        if (is_array($params['meta_keywords']) && isset($params['meta_keywords'][(int)$id_lang])) {
            $params['meta_keywords'] = Tools::str2url($params['meta_keywords'][(int)$id_lang]);
        }

        $params['meta_title'] = $cms_category->meta_title;
        if (is_array($params['meta_title']) && isset($params['meta_title'][(int)$id_lang])) {
            $params['meta_title'] = Tools::str2url($params['meta_title'][(int)$id_lang]);
        }

        if ($dispatcher->hasKeyword('cms_category_rule', $id_lang, 'categories', $id_shop)) {
            $cats = array();
            if (Module::isEnabled('arseopro')) {
                $module = Module::getInstanceByName('arseopro');
                $categories = $module->getUrlConfig()->cmsCategory->getCMSCategoryParentCategories($cms_category->id, $id_lang);
                if ($categories) {
                    foreach ($categories as $cat) {
                        if (!empty($cat['link_rewrite'])) {
                            $cats[] = $cat['link_rewrite'];
                        }
                    }
                    $cats = array_reverse($cats);
                    array_pop($cats);
                }
            }
            $params['categories'] = implode('/', $cats);
        }

        return $url.$dispatcher->createUrl('cms_category_rule', $id_lang, $params, $this->allow, '', $id_shop);
    }

    public function getCMSLink(
        $cms,
        $alias = null,
        $ssl = null,
        $id_lang = null,
        $id_shop = null,
        $relative_protocol = false
    ) {
        if (!Module::isEnabled('arseopro')) {
            return parent::getCMSLink($cms, $alias, $ssl, $id_lang, $id_shop, $relative_protocol);
        }
        
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($id_shop, $ssl, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

        $dispatcher = Dispatcher::getInstance();
        if (!is_object($cms)) {
            $cms = new CMS((int)$cms, $id_lang);
        }

        // Set available keywords
        $params = array();
        $params['id'] = $cms->id;

        $params['rewrite'] = $cms->link_rewrite;
        if (is_array($params['rewrite']) && isset($params['rewrite'][(int)$id_lang])) {
            $params['rewrite'] = $params['rewrite'][(int)$id_lang];
        }
        if ($alias) {
            $params['rewrite'] = $alias;
        }

        $params['meta_keywords'] = $cms->meta_keywords;
        if (is_array($params['meta_keywords']) && isset($params['meta_keywords'][(int)$id_lang])) {
            $params['meta_keywords'] = Tools::str2url($params['meta_keywords'][(int)$id_lang]);
        }

        $params['meta_title'] = $cms->meta_title;
        if (is_array($params['meta_title']) && isset($params['meta_title'][(int)$id_lang])) {
            $params['meta_title'] = Tools::str2url($params['meta_title'][(int)$id_lang]);
        }

        if ($dispatcher->hasKeyword('cms_rule', $id_lang, 'categories', $id_shop)) {
            $cats = array();
            $cms_category = new CMSCategory($cms->id_cms_category, $id_lang);
            if (Validate::isLoadedObject($cms_category)) {
                if (Module::isEnabled('arseopro')) {
                    $module = Module::getInstanceByName('arseopro');
                    $categories = $module->getUrlConfig()->cmsCategory->getCMSCategoryParentCategories($cms_category->id, $id_lang);
                    if ($categories) {
                        foreach ($categories as $cat) {
                            $cats[] = $cat['link_rewrite'];
                        }
                        $cats = array_reverse($cats);
                    }
                }
            }
            $params['categories'] = implode('/', $cats);
        }

        return $url.$dispatcher->createUrl('cms_rule', $id_lang, $params, $this->allow, '', $id_shop);
    }
    
    public function getProductLink(
        $product,
        $alias = null,
        $category = null,
        $ean13 = null,
        $idLang = null,
        $idShop = null,
        $ipa = 0,
        $force_routes = false,
        $relativeProtocol = false,
        $addAnchor = false,
        $extraParams = array()
    ) {
        if (!Module::isEnabled('arseopro')) {
            return parent::getProductLink($product, $alias, $category, $ean13, $idLang, $idShop, $ipa, $force_routes, $relativeProtocol, $addAnchor, $extraParams);
        }
        $dispatcher = Dispatcher::getInstance();

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($idShop, null, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);

        // Set available keywords
        $params = array();

        if (!is_object($product)) {
            if (is_array($product) && isset($product['id_product'])) {
                $params['id'] = $product['id_product'];
            } elseif ((int) $product) {
                $params['id'] = $product;
            } else {
                throw new PrestaShopException('Invalid product vars');
            }
        } else {
            $params['id'] = $product->id;
        }
        if (!is_object($product)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
        }
        if (self::isDefaultAttributeDisabled()) {
            if ($ipa == $product->cache_default_attribute) {
                $params['id_product_attribute'] = 0;
            } else {
                $params['id_product_attribute'] = $ipa;
            }
        } else {
            $params['id_product_attribute'] = $ipa;
        }
        if ($params['id_product_attribute'] == 0 && (!$dispatcher->hasKeyword('product_rule', $idLang, 'id_product_attribute', $idShop))) {
            unset($params['id_product_attribute']);
        }
        if (!$alias) {
            $product = $this->getProductObject($product, $idLang, $idShop);
        }
        $params['rewrite'] = (!$alias) ? $product->getFieldByLang('link_rewrite') : $alias;
        if (!$ean13) {
            $product = $this->getProductObject($product, $idLang, $idShop);
        }
        $params['ean13'] = (!$ean13) ? $product->ean13 : $ean13;
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'meta_keywords', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['meta_keywords'] = Tools::str2url($product->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'meta_title', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['meta_title'] = Tools::str2url($product->getFieldByLang('meta_title'));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'manufacturer', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['manufacturer'] = Tools::str2url($product->isFullyLoaded ? $product->manufacturer_name : Manufacturer::getNameById($product->id_manufacturer));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'supplier', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['supplier'] = Tools::str2url($product->isFullyLoaded ? $product->supplier_name : Supplier::getNameById($product->id_supplier));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'price', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['price'] = $product->isFullyLoaded ? $product->price : Product::getPriceStatic($product->id, false, null, 6, null, false, true, 1, false, null, null, null, $product->specificPrice);
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'tags', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['tags'] = Tools::str2url($product->getTags($idLang));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'category', $idShop)) {
            if (!$category) {
                $product = $this->getProductObject($product, $idLang, $idShop);
            }
            $params['category'] = (!$category) ? $product->category : $category;
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'reference', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['reference'] = Tools::str2url($product->reference);
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'categories', $idShop)) {
            $product = $this->getProductObject($product, $idLang, $idShop);
            $params['category'] = (!$category) ? $product->category : $category;
            $cats = array();
            foreach ($product->getParentCategories($idLang) as $cat) {
                if (!in_array($cat['id_category'], Link::$category_disable_rewrite) && !empty($cat['link_rewrite'])) {
                    //remove root and home category from the URL
                    $cats[] = $cat['link_rewrite'];
                }
            }
            $params['categories'] = implode('/', $cats);
        }
        if ($ipa) {
            $product = $this->getProductObject($product, $idLang, $idShop);
        }
        if (($product->cache_default_attribute == $ipa && self::isDefaultAttributeAnchorDisabled()) || self::isAnchorDisabled()) {
            $anchor = '';
        } else {
            if (self::isAnchorIdsDisabled()) {
                $anchor = $ipa ? $product->getAnchor((int) $ipa, false) : '';
            } else {
                $anchor = $ipa ? $product->getAnchor((int) $ipa, (bool) $addAnchor) : '';
            }
        }

        return $url . $dispatcher->createUrl('product_rule', $idLang, array_merge($params, $extraParams), $force_routes, $anchor, $idShop);
    }
    
    public static function isAnchorDisabled()
    {
        if (self::$cacheDisableAnchor === null) {
            self::$cacheDisableAnchor = Configuration::get('ARSP_DISABLE_ANCHOR');
        }
        return self::$cacheDisableAnchor;
    }
    
    public static function isDefaultAttributeAnchorDisabled()
    {
        if (self::$cacheDisableDefaultAttributeAnchor === null) {
            self::$cacheDisableDefaultAttributeAnchor = Configuration::get('ARSP_DISABLE_DEFAULT_ATTR_ANCHOR');
        }
        return self::$cacheDisableDefaultAttributeAnchor;
    }
    
    public static function isDefaultAttributeDisabled()
    {
        if (self::$cacheDisableDefaultAttribute === null) {
            self::$cacheDisableDefaultAttribute = Configuration::get('ARSP_DISABLE_DEFAULT_ATTR');
        }
        return self::$cacheDisableDefaultAttribute;
    }
    
    public static function isAnchorIdsDisabled()
    {
        if (self::$cacheDisableAnchorIds === null) {
            self::$cacheDisableAnchorIds = Configuration::get('ARSP_REMOVE_ANCHOR_ID');
        }
        return self::$cacheDisableAnchorIds;
    }
}
