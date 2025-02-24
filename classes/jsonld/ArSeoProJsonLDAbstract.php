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

abstract class ArSeoProJsonLDAbstract extends ArSeoModel
{
    public static function getConfigTab()
    {
        return 'jsonld';
    }
    
    public function __construct($module, $configPrefix = null, $owner = null)
    {
        parent::__construct($module, $configPrefix);
        $this->owner = $owner;
        $this->configPrefix = $this->getConfigPrefix();
    }
    
    public function getConfigPrefix()
    {
        return null;
    }
}
