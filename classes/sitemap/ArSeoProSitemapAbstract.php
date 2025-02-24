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

include_once dirname(__FILE__).'/../ArSeoModel.php';

abstract class ArSeoProSitemapAbstract extends ArSeoModel
{
    public function __construct($module, $configPrefix = null)
    {
        parent::__construct($module, $configPrefix);
        $this->configPrefix = $this->getConfigPrefix();
    }
    
    public static function getConfigTab()
    {
        return 'sitemap';
    }
    
    public function attributeLabels()
    {
        return array(
            'freq' => $this->l('Update frequency', 'ArSeoProSitemapAbstract'),
            'priority' => $this->l('Proirity', 'ArSeoProSitemapAbstract'),
            'active_only' => $this->l('Skip non-active', 'ArSeoProSitemapAbstract'),
            'all' => $this->l('Export all data', 'ArSeoProSitemapAbstract')
        );
    }
    
    public function attributeTypes()
    {
        return array(
            'freq' => 'select',
            'priority' => 'select',
            'active_only' => 'switch',
            'all' => 'switch'
        );
    }
    
    public function prioritySelectOptions()
    {
        return array(
            array(
                'id' => '1.0',
                'name' => $this->l('1.0 (high)', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.9',
                'name' => $this->l('0.9', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.8',
                'name' => $this->l('0.8', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.7',
                'name' => $this->l('0.7', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.6',
                'name' => $this->l('0.6', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.5',
                'name' => $this->l('0.5 (standard)', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.4',
                'name' => $this->l('0.4', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.3',
                'name' => $this->l('0.3', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.2',
                'name' => $this->l('0.2', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.1',
                'name' => $this->l('0.1', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => '0.0',
                'name' => $this->l('0.0 (low)', 'ArSeoProSitemapAbstract')
            ),
        );
    }
    
    public function freqSelectOptions()
    {
        return array(
            array(
                'id' => 'always',
                'name' => $this->l('Always', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => 'hourly',
                'name' => $this->l('Hourly', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => 'daily',
                'name' => $this->l('Daily', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => 'weekly',
                'name' => $this->l('Weekly', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => 'monthly',
                'name' => $this->l('Monthly', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => 'yearly',
                'name' => $this->l('Yearly', 'ArSeoProSitemapAbstract')
            ),
            array(
                'id' => 'never',
                'name' => $this->l('Never', 'ArSeoProSitemapAbstract')
            )
        );
    }
    
    abstract public function getConfigPrefix();
}
