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

/**
 * @property ArSeoPro $module
 */
class ArSeoProMetaData
{
    protected $module;
    protected $type;
    protected $row;
    protected $object;
    protected $id_lang;

    public $rule;
    
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    
    public $fb_admins;
    public $fb_app;
    public $fb_title;
    public $fb_description;
    public $fb_type;
    public $fb_image;
    public $fb_custom_image;
    
    public $tw_type;
    public $tw_account;
    public $tw_title;
    public $tw_description;
    public $tw_image;
    public $tw_custom_image;
    public $tw_ch1;
    public $tw_ch2;
    
    public $fbImageUrl;
    public $twImageUrl;
    
    public function __construct($module, $row, $object, $id_lang)
    {
        $this->module = $module;
        $this->row = $row;
        $this->type = isset($row['rule_type'])? $row['rule_type'] : null;
        $this->object = $object;
        $this->id_lang = $id_lang;
    }
    
    public function getKeywordsFields()
    {
        return array(
            'fb_title',
            'fb_description',
            'tw_title',
            'tw_description',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'rule'
        );
    }
    
    public function prepareData($attributes = null, $images = true)
    {
        foreach ($this->getKeywordsFields() as $attribute) {
            if ((empty($attributes) || in_array($attribute, $attributes)) && isset($this->row[$attribute])) {
                $value = $this->composeLine($this->row[$attribute]);
                if ($attribute == 'meta_description') {
                    if (Tools::strlen($value) > 157) {
                        $this->$attribute = Tools::substr($value, 0, 157) . '...';
                    } else {
                        $this->$attribute = $value;
                    }
                } else {
                    $this->$attribute = $value;
                }
            }
        }
        if ($images) {
            $this->assignImages();
        }
    }
    
    public static function cleanString($string, $replace = ' ')
    {
        $str = preg_replace('/[\s\n\r\t\x00-\x1F\x7F\xA0^<>={}]+/u', $replace, $string);
        return trim($str);
    }
    
    public function assignImages()
    {
        $this->fb_admins = $this->row['fb_admins'];
        $this->fb_app = $this->row['fb_app'];
        
        $this->fb_image = $this->row['fb_image'];
        $this->tw_image = $this->row['tw_image'];
        
        $this->tw_type = $this->row['tw_type'];
        
        if ($this->tw_image == 1) {
            $this->twImageUrl = $this->getCoverImage();
        }
        
        if ($this->fb_image == 1) {
            $this->fbImageUrl = $this->getCoverImage();
        }
        
        if ($this->tw_image == 2) {
            $this->twImageUrl = $this->getAllImages();
        }
        
        if ($this->fb_image == 2) {
            $this->fbImageUrl = $this->getAllImages();
        }
        
        if ($this->tw_image == 3 && $this->row['tw_custom_image']) {
            $this->tw_custom_image = $this->module->getModuleBaseUrl() . 'uploads/' . $this->row['tw_custom_image'];
        }
        
        if ($this->fb_image == 3 && $this->row['fb_custom_image']) {
            $this->fb_custom_image = $this->module->getModuleBaseUrl() . 'uploads/' . $this->row['fb_custom_image'];
        }
    }
    
    public function getAllImages()
    {
        $images = $this->object->getImages($this->id_lang);
        $imgs = array();
        foreach ($images as $img) {
            $imgs[] = Context::getContext()->link->getImageLink($this->object->link_rewrite, $img['id_image'], $this->getFormattedImageName('large'));
        }
        return $imgs;
    }
    
    public function getCoverImage()
    {
        $image = '';
        if ($this->isCategory()) {
            $image = Context::getContext()->link->getCatImageLink($this->object->link_rewrite, $this->object->id_image, $this->getFormattedImageName('category'));
        }
        if ($this->isProduct()) {
            $cover = Product::getCover($this->object->id);
            if ($cover) {
                $image = Context::getContext()->link->getImageLink($this->object->link_rewrite, $cover['id_image'], $this->getFormattedImageName('large'));
            }
        }
        if ($this->isBrand()) {
            if ($this->module->is17() || $this->module->is8x()) {
                $image = Context::getContext()->link->getManufacturerImageLink($this->object->id);
            } else {
                $imgType = $this->getFormattedImageName('category');
                $img = (!file_exists(_PS_MANU_IMG_DIR_.$this->object->id.'-' . $imgType . '.jpg')) ? Context::getContext()->language->iso_code.'-default' : $this->object->id;
                $image = _THEME_MANU_DIR_ . $img . '-' . $imgType . '.jpg';
            }
        }
        return $image;
    }
    
    public function getFormattedImageName($name)
    {
        if ($this->module->is16()) {
            ImageType::getFormatedName($name);
        } else {
            ImageType::getFormattedName($name);
        }
    }
    
    public function composeLine($string)
    {
        $matches = array();
        preg_match_all('/{.*?}/is', $string, $matches);
        
        if (isset($matches[0])) {
            $replaces = array();
            foreach ($matches[0] as $tag) {
                $name = $this->getTagName($tag);
                $methodName = 'get' . Tools::ucfirst(Tools::toCamelCase($name)) . 'Tag';
                if (method_exists($this, $methodName)) {
                    $value = '';
                    if ($params = $this->getTagParams($tag)) {
                        $prefix = isset($params['title'])? $params['title'] : null;
                        $prefix = isset($params['prefix'])? $params['prefix'] : $prefix;
                        $suffix = isset($params['suffix'])? $params['suffix'] : null;
                        if ($v = $this->$methodName()) {
                            $value = $prefix . $v . $suffix;
                        } elseif (isset($params['default'])) {
                            $value = $params['default'];
                        }
                    } else {
                        $value = $this->$methodName();
                    }
                    $replaces[$tag] = self::cleanString($value);
                }
            }
        }
        if ($replaces) {
            return strtr($string, $replaces);
        }
        return $string;
    }
    
    public function getTagName($tag)
    {
        if (strpos($tag, '|') !== false) {
            $matches = array();
            preg_match('/{(.*?)\|(.*?)}/is', $tag, $matches);
            return $matches[1];
        }
        return str_replace(array('{', '}'), '', $tag);
    }
    
    public function getTagParams($tag)
    {
        $return = array();
        $params = array();
        if (strpos($tag, '|') !== false) {
            $matches = array();
            if (preg_match('/{(.*?)\|(.*?)}/is', $tag, $matches)) {
                $params = $matches[2];
                $params = explode('|', $params);
            }
        }
        if ($params) {
            foreach ($params as $param) {
                $data = explode('=', $param);
                if (isset($data[1]) && !empty($data[1])) {
                    $return[$data[0]] = $data[1];
                }
            }
        }
        return $return;
    }
    
    public function getNameTag()
    {
        return $this->object->name;
    }
    
    public function getDescriptionTag()
    {
        return strip_tags($this->object->description);
    }
    
    public function getDescriptionShortTag()
    {
        if ($this->isProduct()) {
            return strip_tags($this->object->description_short);
        }
    }
    
    public function getReferenceTag()
    {
        if ($this->isProduct()) {
            return strip_tags($this->object->reference);
        }
    }
    
    public function getShortDescriptionTag()
    {
        if ($this->isBrand()) {
            return strip_tags($this->object->short_description);
        } elseif ($this->isProduct()) {
            return strip_tags($this->object->description_short);
        }
    }
    
    public function getFeaturesTag()
    {
        if (!$this->isProduct()) {
            return null;
        }
        $return = array();
        if ($features = $this->getProductFeatures($this->object->id)) {
            foreach ($features as $feature) {
                $model = new Feature($feature['id_feature'], $this->id_lang);
                if (Validate::isLoadedObject($model)) {
                    $value = new FeatureValue($feature['id_feature_value'], $this->id_lang);
                    if (Validate::isLoadedObject($value)) {
                        $return[] = $model->name . ': ' . $value->value;
                    }
                }
            }
        }
        return implode(', ', $return);
    }
    
    public function getProductFeatures($id_product)
    {
        return Db::getInstance()->executeS('
                SELECT fp.id_feature, fp.id_product, fp.id_feature_value, custom
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` fv ON (fp.id_feature_value = fv.id_feature_value)
                WHERE `id_product` = ' . (int)$id_product);
    }
    
    public function getPriceTag()
    {
        $currency = $this->getCurrency();
        if (!$this->isProduct()) {
            return null;
        }
        return Tools::displayPrice(number_format(Product::getPriceStatic($this->object->id, true, null, 2, null, false, false), 2), $currency);
    }
    
    public function getCurrency()
    {
        $currency = Context::getContext()->currency;
        if (empty($currency) || !Validate::isLoadedObject($currency)) {
            $currencies = Currency::getCurrencies();
            $first = reset($currencies);
            $currency = new Currency($first['id_currency']);
        }
        return $currency;
    }

    public function getMpnTag()
    {
        if (!$this->isProduct()) {
            return null;
        }
        return $this->object->mpn;
    }

    public function getEan13Tag()
    {
        if (!$this->isProduct()) {
            return null;
        }
        return $this->object->ean13;
    }
    
    public function getReducePriceTag()
    {
        $currency = $this->getCurrency();
        if (!$this->isProduct()) {
            return null;
        }
        return Tools::displayPrice(number_format(Product::getPriceStatic($this->object->id, true, false, 2, null, false, true), 2), $currency);
    }
    
    public function getPriceWtTag()
    {
        $currency = $this->getCurrency();
        if (!$this->isProduct()) {
            return null;
        }
        return Tools::displayPrice(number_format(Product::getPriceStatic($this->object->id, false, false, 2, null, false, false), 2), $currency);
    }
    
    public function getReducePriceWtTag()
    {
        $currency = $this->getCurrency();
        if (!$this->isProduct()) {
            return null;
        }
        return Tools::displayPrice(number_format(Product::getPriceStatic($this->object->id, false, false, 2, null, false, true), 2), $currency);
    }
    
    public function getReductionPercentTag()
    {
        if (!$this->isProduct()) {
            return null;
        }
        $discounts = SpecificPrice::getByProductId($this->object->id);
        if ($discounts) {
            foreach ($discounts as $reduction) {
                if ($reduction['id_currency'] == 0 && $reduction['reduction_type'] == 'percentage') {
                    return '-' . ($reduction['reduction'] * 100) . '%';
                }
            }
        }
        return null;
    }
    
    public function getDefaultCatNameTag()
    {
        if ($this->isProduct()) {
            $model = new Category($this->object->id_category_default, $this->id_lang);
            if (Validate::isLoadedObject($model)) {
                return $model->name;
            }
        }
    }
    
    public function getCategoryListTag()
    {
        $result = array();
        if ($this->isProduct()) {
            $product = new Product($this->object->id, $this->id_lang);
            if (Validate::isLoadedObject($product)) {
                if ($ids = $product->getCategories()) {
                    foreach ($ids as $id) {
                        $category = new Category($id, $this->id_lang);
                        if (Validate::isLoadedObject($category)) {
                            $result[] = $category->name;
                        }
                    }
                }
            }
        }
        return implode(', ', $result);
    }
    
    public function getManufacturerTag()
    {
        if (!$this->isProduct()) {
            return null;
        }
        if ($this->object->id_manufacturer) {
            $model = new Manufacturer($this->object->id_manufacturer, $this->id_lang);
            if (Validate::isLoadedObject($model)) {
                return strip_tags($model->name);
            }
        }
        return null;
    }
    
    public function getMetaTitleTag()
    {
        if ($this->isMetaPage()) {
            return $this->object->title;
        }
        return $this->object->meta_title;
    }
    
    public function getMetaDescriptionTag()
    {
        if ($this->isMetaPage()) {
            return $this->object->description;
        }
        return $this->object->meta_description;
    }
    
    public function getMetaKeywordsTag()
    {
        if ($this->isMetaPage()) {
            return $this->object->keywords;
        }
        return $this->object->meta_keywords;
    }
    
    public function getParentCategoryTag()
    {
        if (!$this->isCategory()) {
            return null;
        }
        
        $parents = $this->object->getParentsCategories($this->id_lang);
        if (isset($parents[1])) {
            return $parents[1]['name'];
        }
    }
    
    public function getParentCategoriesTag()
    {
        if (!$this->isCategory()) {
            return null;
        }
        $return = array();
        if ($parents = $this->object->getParentsCategories($this->id_lang)) {
            foreach ($parents as $parent) {
                if ($parent['id_category'] != $this->object->id) {
                    $return[] = $parent['name'];
                }
            }
        }
        return !empty($return)? implode(', ', $return) : null;
    }
    
    public function getShopNameTag()
    {
        return Configuration::get('PS_SHOP_NAME');
    }
    
    public function isProduct()
    {
        return $this->object instanceof Product || $this->object instanceof ProductCore;
    }
    
    public function isCategory()
    {
        return $this->object instanceof Category || $this->object instanceof CategoryCore;
    }
    
    public function isMetaPage()
    {
        return $this->object instanceof Meta || $this->object instanceof MetaCore;
    }
    
    public function isBrand()
    {
        return $this->object instanceof Manufacturer || $this->object instanceof ManufacturerCore;
    }
}
