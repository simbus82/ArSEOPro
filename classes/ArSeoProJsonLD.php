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

include_once dirname(__FILE__).'/jsonld/ArSeoProJsonLDGeneral.php';
include_once dirname(__FILE__).'/jsonld/ArSeoProJsonLDProduct.php';
include_once dirname(__FILE__).'/jsonld/ArSeoProJsonLDAdvanced.php';

include_once dirname(__FILE__).'/ArSeoHelpers.php';

/**
 * @property ArSeoProJsonLDProduct $product
 * @property ArSeoProJsonLDGeneral $general
 * @property ArSeoProJsonLDAdvanced $advanced
 */
class ArSeoProJsonLD
{
    public $general;
    public $product;
    public $advanced;
    
    protected $context;
    protected $module;


    public function __construct($module)
    {
        $this->module = $module;
        
        $this->general = new ArSeoProJsonLDGeneral($module, null, $this);
        $this->product = new ArSeoProJsonLDProduct($module, null, $this);
        $this->advanced = new ArSeoProJsonLDAdvanced($module, null, $this);
        
        $this->context = Context::getContext();
    }
    
    public function loadAllFromConfig()
    {
        if (!$this->general->isLoaded()) {
            $this->general->loadFromConfig();
        }
        
        if (!$this->product->isLoaded()) {
            $this->product->loadFromConfig();
        }
        if (!$this->advanced->isLoaded()) {
            $this->advanced->loadFromConfig();
        }
    }
    
    public function cleanUpMicrodata($output)
    {
        $html = $output;
        /* $html = preg_replace('{<meta.*?itemprop.*?/?>}is', ' ', $html); */
        $html = preg_replace('/itemprop=".*?"/is', ' ', $html);
        $html = preg_replace('/itemtype=".*?"/is', ' ', $html);
        $html = preg_replace('/itemscope=".*?"/is', ' ', $html);
        $html = preg_replace('/itemscope/is', ' ', $html);
        
        return $this->cleanUpExistingJSONLD($html);
    }
    
    public function cleanUpExistingJSONLD($output)
    {
        $html = $output;
        $html = preg_replace('{<script\s+type="application/ld\+json">.*?</script>}is', ' ', $html);
        return $html;
    }
    
    public function buildBreadcrumbsJson($breadcrumbs)
    {
        $jsonData = array(
            '@context' => 'https://schema.org/',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array()
        );
        
        foreach ($breadcrumbs['links'] as $k => $breadcrumb) {
            $jsonData['itemListElement'][] = array(
                '@type' => 'ListItem',
                'name' => $breadcrumb['title'],
                'position' => $k + 1,
                'item' => array(
                    '@type' => 'Thing',
                    '@id' => $breadcrumb['url']
                )
            );
        }
        
        return $jsonData;
    }
    
    public function buildProductJson($product, $ipa)
    {
        $link = Context::getContext()->link;
        $id_lang = Context::getContext()->language->id;
        
        $qty = Product::getQuantity($product->id, $ipa);
        $url = $link->getProductLink($product, null, null, null, null, null, $ipa);
        
        
        if (($this->product->images == 'all' && $ipa) || ($this->product->images == 'combination' && !$ipa)) {
            $images = array();
            $imgs = $product->getImages($id_lang);
            foreach ($imgs as $img) {
                $images[] = $link->getImageLink($product->link_rewrite, $img['id_image'], $this->product->image_size);
            }
        } elseif ($this->product->images == 'combination' && $ipa) {
            $images = array();
            $sql = 'SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
                FROM `' . _DB_PREFIX_ . 'product_attribute_image` pai
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (il.`id_image` = pai.`id_image`)
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = pai.`id_image`)
                WHERE pai.`id_product_attribute` = ' . (int) $ipa . ' AND il.`id_lang` = ' . (int) $id_lang . ' ORDER by i.`position`';
            $imgs = Db::getInstance()->executeS($sql);
            foreach ($imgs as $img) {
                $images[] = $link->getImageLink($product->link_rewrite, $img['id_image'], $this->product->image_size);
            }
        } else {
            $image = $ipa == $product->cache_default_attribute? Product::getCover($product->id) : Product::getCombinationImageById($ipa, $id_lang);
            $images = $link->getImageLink($product->link_rewrite, $image['id_image'], $this->product->image_size);
        }
        
        $defaultCategory = new Category($product->id_category_default, $id_lang);
        
        $aggregateRating = null;
        if ($reviews = $this->getReviews($product)) {
            $aggregateRating = $this->getAggregateRating($product);
        }
        
        $jsonData = array(
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'url' => $url,
            'image' => $images,
            'description' => $this->product->description == 'description' ? strip_tags($product->description) : strip_tags($product->description_short),
            'sku' =>$product->reference,
            'gtin13' => $product->ean13,
            'brand' => array(
                '@type' => 'Brand',
                'name' => $product->manufacturer_name
            ),
            'category' => array(
                '@type' => 'Thing',
                'name' => $defaultCategory->name,
                'url' => $link->getCategoryLink($defaultCategory, null, $id_lang)
            )
        );
        
        $precision = Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        if (isset(Context::getContext()->currency->precision)) {
            $precision = Context::getContext()->currency->precision;
        }
        
        if ($product->hasAttributes() && $this->product->combinations) {
            $offers = array();
            $prices = array();
            
            $productAttributes = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute` WHERE `id_product` = ' . (int) $product->id
            );
            
            foreach ($productAttributes as $attribute) {
                $url = $link->getProductLink($product, null, null, null, null, null, $attribute['id_product_attribute']);
                $price = $product->getPrice(true, $attribute['id_product_attribute'], $precision);
                if ($price) {
                    $prices[] = $price;
                    $offers[] = array(
                        "@type" => "Offer",
                        "priceCurrency" => Context::getContext()->currency->iso_code,
                        "price" => $price,
                        "priceValidUntil" => date('Y-m-d', strtotime("+" . $this->product->price_valid_until . " Days")),
                        "itemCondition" => $this->getSchemaProductCondition($product),
                        "availability" => $this->getAvailability($qty),
                        'url' => $url,
                        'sku' => $attribute['reference']? $attribute['reference'] : $product->reference,
                        'gtin13' => $attribute['ean13']? $attribute['ean13'] : $product->ean13,
                        "seller" => array(
                            "@type" => "Organization",
                            "name" => $this->general->name,
                            'url' => $this->general->site,
                            'logo' => $this->general->logo
                        )
                    );
                }
            }
            $offers = array(
                '@type' => 'AggregateOffer',
                'offerCount' => count($offers),
                'priceCurrency' => Context::getContext()->currency->iso_code,
                'lowPrice' => min($prices),
                'highPrice' => max($prices),
                'offers' => $offers
            );
        } else {
            $jsonData['gtin13'] = $product->ean13;
            if ($product->isbn) {
                $jsonData['productID'] = 'isbn:' . $product->isbn;
            }
            $jsonData['sku'] = $product->reference;
                    
            $offers = array(
                "@type" => "Offer",
                "priceCurrency" => Context::getContext()->currency->iso_code,
                "price" => $product->getPrice(true, $ipa, $precision),
                "priceValidUntil" => date('Y-m-d', strtotime("+" . $this->product->price_valid_until . " Days")),
                "itemCondition" => $this->getSchemaProductCondition($product),
                "availability" => $this->getAvailability($qty),
                'url' => $url,
                "seller" => array(
                    "@type" => "Organization",
                    "name" => $this->general->name,
                    'url' => $this->general->site,
                    'logo' => $this->general->logo
                )
            );
        }
        
        $jsonData['offers'] = $offers;
        
        if ($reviews) {
            $jsonData['review'] = $reviews;
            $jsonData['aggregateRating'] = $aggregateRating;
        }
        
        return $jsonData;
    }
    
    public function getAvailability($qty)
    {
        if ($this->product->stock == 'as-is' || empty($this->product->stock)) {
            return $qty? "http://schema.org/InStock" : "http://schema.org/OutOfStock";
        }
        return $this->product->stock == 'in-stock'? "http://schema.org/InStock" : "http://schema.org/OutOfStock";
    }
    
    public function buildProductListJson($products)
    {
        if (empty($products)) {
            return array();
        }
        $link = Context::getContext()->link;
        $jsonData = array(
            '@context' => 'http://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => array()
        );
        foreach ($products as $k => $product) {
            $img = null;
            if (!isset($product['link_rewrite'])) {
                $sql = 'SELECT p.id_product, pl.link_rewrite, pl.name FROM `' .
                        _DB_PREFIX_ . 'product` p LEFT JOIN `' .
                        _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product WHERE p.id_product = ' . (int)$product['id_product'] . ' AND pl.id_lang = ' . (int)Context::getContext()->language->id;
                $product = Db::getInstance()->getRow($sql);
            }
            if ($cover = Product::getCover($product['id_product'])) {
                $img = $link->getImageLink($product['link_rewrite'], $cover['id_image'], $this->product->image_size);
            }
            $url = $link->getProductLink($product);
            $jsonData['itemListElement'][] = array(
                '@type' => 'ListItem',
                'mainEntityOfPage' => $url,
                'url' => $url,
                'name' => $product['name'],
                'image' => $img,
                'position' => $k + 1,
            );
        }
        return $jsonData;
    }
    
    public function buildOrgJson()
    {
        return null;
    }
    
    public function buildWebPageJson()
    {
        return null;
    }
    
    public function buildWebSiteJson()
    {
        return null;
    }
    
    public function buildStoreJson()
    {
        $jsonData = array(
            "@context" => "https://schema.org",
            "@type" => "Store",
            "name" => $this->general->name,
            "description" => $this->general->description,
            "openingHours" => $this->general->workinghours,
            "telephone" => $this->general->phone,
            'url' => $this->general->site
        );
        
        return $jsonData;
    }
    
    public function getAggregateRating($product)
    {
        $result = array();
        if (Module::isEnabled('productcomments')) {
            $productComment = Module::getInstanceByName('productcomments');
            if (($this->module->is17() || $this->module->is8x()) && $productComment && $productComment->author == 'PrestaShop' && (version_compare($productComment->version, '4.0.0', '>=') === true)) {
                if (class_exists('PrestaShop\Module\ProductComment\Repository\ProductCommentRepository')) {
                    $productCommentRepository = $this->context->controller->getContainer()->get('product_comment_repository');
                    $averageGrade = $productCommentRepository->getAverageGrade($product->id, (bool) Configuration::get('PRODUCT_COMMENTS_MODERATE'));
                    $commentsNb = $productCommentRepository->getCommentsNumber($product->id, (bool) Configuration::get('PRODUCT_COMMENTS_MODERATE'));
                    $result = array(
                        '@type' => 'AggregateRating',
                        'ratingValue' => $averageGrade,
                        'reviewCount' => $commentsNb
                    );
                }
            }
        }
        return $result;
    }
    
    public function getReviews($product)
    {
        $result = array();
        if (Module::isEnabled('productcomments')) {
            $productComment = Module::getInstanceByName('productcomments');
            if (($this->module->is17() || $this->module->is8x()) && $productComment && $productComment->author == 'PrestaShop' && (version_compare($productComment->version, '4.0.0', '>=') === true)) {
                if (class_exists('PrestaShop\Module\ProductComment\Repository\ProductCommentRepository')) {
                    $productCommentRepository = $this->context->controller->getContainer()->get('product_comment_repository');
                    if ($comments = $productCommentRepository->paginate($product->id, 1, 100, true)) {
                        foreach ($comments as $comment) {
                            $result[] = array(
                                '@type' => 'Review',
                                'reviewRating' => array(
                                    '@type' => 'Rating',
                                    'ratingValue' => $comment['grade'],
                                    'bestRating' => '5',
                                    'worstRating' => '1'
                                ),
                                'author' => array(
                                    '@type' => 'Person',
                                    'name' => "{$comment['firstname']} {$comment['lastname']}"
                                )
                            );
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    public function getSchemaProductCondition($product)
    {
        switch ($product->condition) {
            case 'new':
                return 'http://schema.org/NewCondition';
            case 'used':
                return 'http://schema.org/UsedCondition';
            case 'refurbished':
                return 'http://schema.org/RefurbishedCondition';
        }
    }
}
