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
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoController extends AdminArSeoControllerAbstract
{
    public function initContent()
    {
        $url = Context::getContext()->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name;
        Tools::redirectAdmin($url);
    }
    
    public function ajaxProcessSaveOldRoutes()
    {
        $content = Tools::getValue('content');
        $id_shop = Tools::getValue('id_shop');
        $content = str_replace('\\', '\\\\', $content);
        $res = ArSeoProTools::jsonDecode($content);
        if ($res == null) {
            die(ArSeoProTools::jsonEncode(array(
                'success' => 0,
                'error' => 'JSON is not valid'
            )));
        }
        
        ConfigurationCore::updateValue('ARSEO_OLD_ROUTES', ArSeoProTools::jsonEncode($res), false, null, (int)$id_shop);
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'res' => $res
        )));
    }
    
    public function ajaxProcessLoadOldRoutes()
    {
        $id_shop = Tools::getValue('id_shop');
        $json = ConfigurationCore::get('ARSEO_OLD_ROUTES', null, null, (int)$id_shop);
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'content' => $json
        )));
    }
    
    public function ajaxProcessReinstallOverrides()
    {
        $return = true;
        $this->module->getLogger()->log('Re-install overrides');

        $res = $this->module->uninstallOverrides();
        $this->module->getLogger()->log('Uninstall overrides: ' . (int)$res);
        $return = $return && $res;

        $res = $this->module->getInstaller()->prepareOverrides();
        $this->module->getLogger()->log('Preparing overrides: ' . (int)$res);
        $return = $return && $res;

        $res = $this->module->installOverrides();
        $this->module->getLogger()->log('Install overrides: ' . (int)$res);
        $return = $return && $res;

        $this->module->getLogger()->log('Re-install overrides complete' . PHP_EOL);
        
        die(ArSeoProTools::jsonEncode(array(
            'success' => (int)$return
        )));
    }
    
    public function ajaxProcessGetOverridesVersion()
    {
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'versions' => $this->module->getOverridesVersion(),
            'moduleVersion' => $this->module->version
        )));
    }
}
