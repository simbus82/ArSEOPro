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

include_once dirname(__FILE__).'/../../classes/ArSeoProListHelper.php';
include_once dirname(__FILE__).'/AdminArSeoControllerAbstract.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoSitemapController extends AdminArSeoControllerAbstract
{
    protected function renderCheckbox($link, $cellValue, $row)
    {
        return $this->module->render('_partials/_checkbox.tpl', array(
            'href' => $link,
            'onclick' => 'arSEO.sitemap.toggleItem(this); return false;',
            'class' => ($row['export']? 'list-action-enable action-enabled' : 'list-action-enable action-disabled'),
            'export' => $row['export'],
            'title' => ($row['export']? $this->l('Enabled') : $this->l('Disabled')),
        ));
    }
    
    public function ajaxProcessActivate()
    {
        $this->toggle();
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1
        )));
    }
    
    public function ajaxProcessDeactivate()
    {
        $this->toggle(0);
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1
        )));
    }
}
