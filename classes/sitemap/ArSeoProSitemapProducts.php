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

include_once dirname(__FILE__).'/ArSeoProSitemapAbstract.php';

class ArSeoProSitemapProducts extends ArSeoProSitemapAbstract
{
    const CONFIG_PREFIX = 'arssp_';
    
    const IMG_NONE = 0;
    const IMG_COVER = 1;
    const IMG_ALL = 2;
    
    public $freq;
    public $priority;
    public $all;
    public $active_only;
    public $skip_zero;
    public $attributes;
    public $images;
    public $image_type;
    public $image_title;
    public $image_caption;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'freq',
                    'priority',
                    'skip_zero',
                    'attributes',
                    'images',
                    'image_type',
                    'image_title',
                    'image_caption',
                    'active_only',
                    'all'
                ), 'safe'
            )
        );
    }
    
    public function imagesSelectOptions()
    {
        return array(
            array(
                'id' => self::IMG_NONE,
                'name' => $this->l('None', 'ArSeoProSitemapProducts')
            ),
            array(
                'id' => self::IMG_COVER,
                'name' => $this->l('Cover image', 'ArSeoProSitemapProducts')
            ),
            array(
                'id' => self::IMG_ALL,
                'name' => $this->l('All images', 'ArSeoProSitemapProducts')
            ),
        );
    }
    
    public function imageTypeSelectOptions()
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
    
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'attributes' => $this->l('Export each combination of product as separate URL', 'ArSeoProSitemapProducts'),
            'images' => $this->l('Include product images', 'ArSeoProSitemapProducts'),
            'image_type' => $this->l('Image size', 'ArSeoProSitemapProducts'),
            'skip_zero' => $this->l('Skip out of stock products', 'ArSeoProSitemapProducts'),
            'image_title' => $this->l('Include image title', 'ArSeoProSitemapProducts'),
            'image_caption' => $this->l('Include image caption', 'ArSeoProSitemapProducts')
        ));
    }
    
    public function attributeDescriptions()
    {
        return array(
            'attributes' => $this->l('Please use this option only if your product URL contains ID of combination', 'ArSeoProSitemapProducts'),
        );
    }
    
    public function attributeTypes()
    {
        return array_merge(parent::attributeTypes(), array(
            'images' => 'select',
            'skip_zero' => 'switch',
            'image_type' => 'select',
            'image_title' => 'switch',
            'image_caption' => 'switch',
            'attributes' => 'switch'
        ));
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
    
    public function attributeDefaults()
    {
        $type = null;
        $types = ImageType::getImagesTypes('products');
        if (isset($types[0])) {
            $type = $types[0]['name'];
        }
        return array(
            'images' => '0',
            'skip_zero' => '0',
            'image_type' => $type,
            'freq' => 'always',
            'priority' => '1.0',
            'images',
            'image_type',
            'image_title',
            'image_caption',
            'active_only' => 1,
            'all' => 1
        );
    }
}
