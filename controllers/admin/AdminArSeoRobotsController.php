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

include_once dirname(__FILE__).'/AdminArSeoControllerAbstract.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProRobots.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoRobotsController extends AdminArSeoControllerAbstract
{
    public function ajaxProcessReload()
    {
        $robots = new ArSeoProRobots($this->module);
        $content = $robots->getContent();
        if ($content === false) {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 0,
                'error' => $robots->getError()
            )));
        } else {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 1,
                'content' => $content
            )));
        }
    }
    
    public function ajaxProcessSave()
    {
        $robots = new ArSeoProRobots($this->module);
        $content = Tools::getValue('robots');
        if ($robots->save($content)) {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 1,
                'content' => $robots->getContent()
            )));
        } else {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 0,
                'error' => $robots->getError()
            )));
        }
    }
    
    public function ajaxProcessDefaults()
    {
        $robots = new ArSeoProRobots($this->module);
        if ($robots->loadDefaults()) {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 1,
                'content' => $robots->getContent()
            )));
        } else {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 0,
                'error' => $robots->getError()
            )));
        }
    }
}
