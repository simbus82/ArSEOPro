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

class ArSeoProSitemapGeneral extends ArSeoProSitemapAbstract
{
    const CONFIG_PREFIX = 'arssg_';
    
    public $disable;
    public $limit;
    public $langs;
    public $alternates;
    public $ping_google;
    public $ping_bing;
    public $ping_yandex;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'disable',
                    'limit',
                    'langs',
                    'alternates',
                    'ping_google',
                    'ping_bing',
                    'ping_yandex'
                ), 'safe'
            ),
            array(
                array(
                    'limit'
                ), 'isInt'
            )
        );
    }
    
    public function afterSave()
    {
        $generator = new ArSeoProSitemapGenerator($this->module, Context::getContext()->shop->id);
        $generator->setIndexPath($this->module->getIndexSitemapPath(false));
        $generator->updateRobots();
        return parent::afterSave();
    }
    
    public function langsSelectOptions()
    {
        $langs = Language::getLanguages(true);
        $res = array();
        foreach ($langs as $lang) {
            $res[] = array(
                'id' => $lang['id_lang'],
                'name' => $lang['name']
            );
        }
        
        return $res;
    }
    
    public function attributeTypes()
    {
        return array(
            'disable' => 'switch',
            'limit' => 'text',
            'langs' => 'select',
            'alternates' => 'switch',
            'ping_google' => 'switch',
            'ping_bing' => 'switch',
            'ping_yandex' => 'switch'
        );
    }
    
    public function multipleSelects()
    {
        return array(
            'langs' => true
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'disable' => $this->l('Disable sitemap functionality', 'ArSeoProSitemapGeneral'),
            'limit' => $this->l('Max items count in each sitemap', 'ArSeoProSitemapGeneral'),
            'langs' => $this->l('Include languages', 'ArSeoProSitemapGeneral'),
            'alternates' => $this->l('Alternates', 'ArSeoProSitemapGeneral'),
            'ping_google' => $this->l('Ping Google', 'ArSeoProSitemapGeneral'),
            'ping_bing' => $this->l('Ping Bing', 'ArSeoProSitemapGeneral'),
            'ping_yandex' => $this->l('Ping Yandex', 'ArSeoProSitemapGeneral'),
        );
    }
    
    public function attributeDescriptions()
    {
        return array(
            'alternates' => $this->l('Please refer https://goo.gl/fc6JDj for more details', 'ArSeoProSitemapGeneral'),
            'ping_google' => $this->l('Notify Google Search Console when sitemap is updated', 'ArSeoProSitemapGeneral'),
            'ping_bing' => $this->l('Notify Bing Webmaster Tools when sitemap is updated', 'ArSeoProSitemapGeneral'),
            'ping_yandex' => $this->l('Notify Yandex when sitemap is updated', 'ArSeoProSitemapGeneral'),
        );
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
    
    public function attributeDefaults()
    {
        return array(
            'disable' => 0,
            'limit' => '3000',
            'langs' => $this->getLangIds(),
            'alternates' => 1,
            'ping_google' => 1,
            'ping_bing' => 1,
            'ping_yandex' => 0
        );
    }
    
    public function getLangIds()
    {
        return Language::getLanguages(true, false, true);
    }
}
