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

include_once dirname(__FILE__).'/ArSeoModel.php';

class ArSeoProRedirects extends ArSeoModel
{
    public $enable;
    public $log;
    public $auto_create;
    public $debug;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'enable',
                    'log',
                    'auto_create',
                    'debug'
                ), 'safe'
            )
        );
    }
    
    public function attributeTypes()
    {
        return array(
            'enable' => 'switch',
            'log' => 'switch',
            'auto_create' => 'switch',
            'debug' => 'switch'
        );
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'log' => 1,
            'auto_create' => 0,
            'debug' => 0
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'enable' => $this->l('Enable', 'ArSeoProRedirects'),
            'log' => $this->l('Proactive', 'ArSeoProRedirects'),
            'auto_create' => $this->l('Auto-create', 'ArSeoProRedirects'),
            'debug' => $this->l('Debug mode', 'ArSeoProRedirects')
        );
    }
    
    public function attributeDescriptions()
    {
        return array(
            'log' => $this->l('Create a new record in the redirect table if some page redirected to another', 'ArSeoProRedirects'),
            'auto_create' => $this->l('Automatically create new record in the redirect table if product or category URL is changed', 'ArSeoProRedirects')
        );
    }
    
    public static function getConfigTab()
    {
        return 'redirects';
    }
    
    public function getFormTitle()
    {
        return $this->l('Redirects', 'ArSeoProRedirects');
    }
}
