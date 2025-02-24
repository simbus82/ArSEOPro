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

class ArSeoProSitemapCmsConfig extends ArSeoProSitemapAbstract
{
    const CONFIG_PREFIX = 'arsscms_';
    
    public $freq;
    public $priority;
    public $all;
    public $active_only;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'freq',
                    'priority',
                    'active_only',
                    'all'
                ), 'safe'
            )
        );
    }
    
    public function attributeDefaults()
    {
        return array(
            'freq' => 'always',
            'priority' => '1.0',
            'active_only' => 1,
            'all' => 1
        );
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
}
