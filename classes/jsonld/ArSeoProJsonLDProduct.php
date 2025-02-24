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

include_once dirname(__FILE__).'/ArSeoProJsonLDAbstract.php';

class ArSeoProJsonLDProduct extends ArSeoProJsonLDAbstract
{
    const CONFIG_PREFIX = 'arseojsonp_';
    
    public $enable;
    public $enable_list;
    public $description;
    public $images;
    public $image_size;
    public $price_valid_until;
    public $combinations;
    public $features;
    public $stock;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'enable',
                    'enable_list',
                    'description',
                    'images',
                    'image_size',
                    'price_valid_until',
                    'combinations',
                    'stock'
                    //'features',
                ), 'safe'
            )
        );
    }
    
    public function imageSizeSelectOptions()
    {
        $types = ImageType::getImagesTypes('products');
        $result = array();
        foreach ($types as $type) {
            $result[] = array(
                'id' => $type['name'],
                'name' => $type['name'] . ' (' . $type['width'] . 'x' . $type['height'] . ')'
            );
        }
        return $result;
    }
    
    public function stockSelectOptions()
    {
        return array(
            array(
                'id' => 'as-is',
                'name' => $this->l('As is')
            ),
            array(
                'id' => 'in-stock',
                'name' => $this->l('Always in-stock')
            ),
            array(
                'id' => 'out-of-stock',
                'name' => $this->l('Always out-of-stock')
            )
        );
    }
    
    public function imagesSelectOptions()
    {
        return array(
            array(
                'id' => 'all',
                'name' => $this->l('All images')
            ),
            array(
                'id' => 'combination',
                'name' => $this->l('Current combination images')
            ),
            array(
                'id' => 'cover',
                'name' => $this->l('Cover image only')
            )
        );
    }
    
    public function descriptionSelectOptions()
    {
        return array(
            array(
                'id' => 'description',
                'name' => $this->l('Description')
            ),
            array(
                'id' => 'description_short',
                'name' => $this->l('Short description')
            )
        );
    }
    
    public function attributeTypes()
    {
        return array(
            'enable' => 'switch',
            'enable_list' => 'switch',
            'description' => 'select',
            'images' => 'select',
            'image_size' => 'select',
            'price_valid_until' => 'text',
            'combinations' => 'switch',
            'features' => 'switch',
            'stock' => 'select'
        );
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'enable_list' => 1,
            'description' => 'description',
            'images' => 'cover',
            'image_size' => $this->getDefaultImageSize(),
            'price_valid_until' => '30',
            'combinations' => 1,
            'features' => 1,
            'stock' => 'as-is'
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'enable' => $this->l('Enable product JSON-LD microdata', 'ArSeoProJsonLDProduct'),
            'enable_list' => $this->l('Enable JSON-LD microdata in listings', 'ArSeoProJsonLDProduct'),
            'description' => $this->l('Use this field as description', 'ArSeoProJsonLDProduct'),
            'images' => $this->l('Product images', 'ArSeoProJsonLDProduct'),
            'image_size' => $this->l('Image size', 'ArSeoProJsonLDProduct'),
            'price_valid_until' => $this->l('Days for "price valid until" field', 'ArSeoProJsonLDProduct'),
            'combinations' => $this->l('Include product combinations', 'ArSeoProJsonLDProduct'),
            'stock' => $this->l('Availability', 'ArSeoProJsonLDProduct'),
        );
    }
    
    public function getDefaultImageSize()
    {
        if ($this->module->is17() || $this->module->is8x()) {
            return ImageType::getFormattedName('large');
        }
        return ImageType::getFormatedName('large');
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
}
