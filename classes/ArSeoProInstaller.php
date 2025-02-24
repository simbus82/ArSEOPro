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

include_once dirname(__FILE__).'/redirects/models/ArSeoProRedirectTable.php';
include_once dirname(__FILE__).'/url/models/ArSeoProRuleTable.php';
include_once dirname(__FILE__).'/sitemap/models/ArSeoProSitemapProduct.php';
include_once dirname(__FILE__).'/sitemap/models/ArSeoProSitemapSupplier.php';
include_once dirname(__FILE__).'/sitemap/models/ArSeoProSitemapManufacturer.php';
include_once dirname(__FILE__).'/sitemap/models/ArSeoProSitemapCms.php';
include_once dirname(__FILE__).'/sitemap/models/ArSeoProSitemapCategory.php';
include_once dirname(__FILE__).'/sitemap/models/ArSeoProSitemapMetaPages.php';
include_once dirname(__FILE__).'/meta/models/ArSeoProMetaTable.php';
include_once dirname(__FILE__).'/canonical/ArSeoProCanonicalProduct.php';
include_once dirname(__FILE__).'/canonical/ArSeoProCanonicalCategory.php';
include_once dirname(__FILE__).'/ArSeoProRobots.php';
include_once dirname(__FILE__).'/ArSeoProTools.php';


/**
 * @property ArSeoPro $module
 */
class ArSeoProInstaller
{
    protected $module = null;
    
    protected $tabs = array(
        'AdminArSeoUrls',
        'AdminArSeoMeta',
        'AdminArSeoRedirects',
        'AdminArSeoRobots',
        'AdminArSeoSitemap',
        'AdminArSeoSitemapProduct',
        'AdminArSeoSitemapSupplier',
        'AdminArSeoSitemapManufacturer',
        'AdminArSeoSitemapCms',
        'AdminArSeoSitemapMeta',
        'AdminArSeoSitemapCategory',
        'AdminArSeo'
    );
    
    protected $hooks = array(
        'actionAdminMetaAfterWriteRobotsFile',
        'displayAdminNavBarBeforeEnd',
        'displayHeader',
        'actionDispatcher',
        'moduleRoutes',
        
        'actionObjectProductAddAfter',
        'actionObjectProductUpdateAfter',
        'actionObjectProductUpdateBefore',
        
        'displayBeforeBodyClosingTag',
        'displayFooter',
        'actionProductSearchAfter',
        'actionProductListModifier',
        
        'actionProductUpdate',
        'actionCategoryUpdate',
        'displayAdminProductsSeoStepBottom',
        'displayBackOfficeCategory',
    );
    
    protected $dbTables = array(
        'ArSeoProRedirectTable',
        'ArSeoProRuleTable',
        'ArSeoProSitemapProduct',
        'ArSeoProSitemapSupplier',
        'ArSeoProSitemapManufacturer',
        'ArSeoProSitemapCms',
        'ArSeoProSitemapMetaPages',
        'ArSeoProSitemapCategory',
        'ArSeoProMetaTable',
        'ArSeoProCanonicalProduct',
        'ArSeoProCanonicalCategory',
    );

    public function __construct($module)
    {
        $this->setModule($module);
    }
    
    public function setModule($module)
    {
        $this->module = $module;
    }
    
    public function getModule()
    {
        return $this->module;
    }
    
    public function install()
    {
        Configuration::updateValue('ARSEO_INSTALL_TS', time());
        Configuration::updateValue('ARSEO_SITEMAP_TOKEN', md5(uniqid()));
        $robots = new ArSeoProRobots($this->module);
        $this->module->getLogger()->getInstance()->log('Installation process started. PS version is ' . _PS_VERSION_);
        $res = $this->installHook() &&
                $this->installTabs() &&
                $this->installDB() &&
                $this->installDefaults() &&
                $this->installOverrides() &&
                $robots->loadDefaults(true) &&
                $this->clearDefaultRoutes() &&
                $this->module->clearGlobalCache();
        $this->module->getLogger()->getInstance()->log('Installation process complete' . PHP_EOL);
        return $res;
    }
    
    public function uninstall()
    {
        $this->module->getLogger()->log('Deinstallation process started. PS version is ' . _PS_VERSION_);
        $res = $this->uninstallDB() && $this->uninstallDefaults() && $this->unistallTabs() && $this->restoreDefaultRoutes();
        $this->module->getLogger()->getInstance()->log('Deinstallation process complete' . PHP_EOL);
        return $res;
    }
    
    public function unistallTabs()
    {
        foreach ($this->tabs as $tabName) {
            $id_tab = Tab::getIdFromClassName($tabName);
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }
    
    public function uninstallDB()
    {
        $this->module->getLogger()->log('DB DEINSTALLATION');
        Configuration::deleteByName('ARSEO_NFL_TIME');
        Configuration::deleteByName('ARSP_SCHEMA');
        Configuration::deleteByName('ARSEO_INSTALL_TS');
        Configuration::deleteByName('ARSEO_SITEMAP_TOKEN');
        Configuration::deleteByName('ARSEO_SITEMAP_GEN');
        $res = true;
        
        foreach ($this->dbTables as $dbClassName) {
            $res = $res && $dbClassName::uninstallTable();
            $this->module->getLogger()->log('Table ' . $dbClassName . ' deinstallation result ' . (int)$res);
        }
        $this->module->getLogger()->log();
        return $res;
    }
    
    public function installTabs()
    {
        $this->module->getLogger()->log('TABS INSTALLATION');
        foreach ($this->tabs as $tabName) {
            if (!Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'tab` WHERE module = "arseopro" AND class_name = "' . $tabName . '"')) {
                $tab = new Tab();
                $tab->active = 1;
                $tab->class_name = $tabName;
                $tab->name = array();
                foreach (Language::getLanguages(true) as $lang) {
                    if ($tabName == 'AdminArSeo') {
                        $tab->name[$lang['id_lang']] = 'All in one SEO Pro';
                    } else {
                        $tab->name[$lang['id_lang']] = $tabName;
                    }
                }
                if ($tabName == 'AdminArSeo') {
                    if ($this->module->is17() || $this->module->is8x()) {
                        $parentId = Tab::getIdFromClassName('CONFIGURE');
                        $tab->id_parent = $parentId;
                        if (property_exists($tab, 'icon')) {
                            $tab->icon = 'link';
                        }
                    } else {
                        $tab->id_parent = 0;
                    }
                } else {
                    $tab->id_parent = -1;
                }
                $tab->module = $this->module->name;
                $res = $tab->add();
                $this->module->getLogger()->log('Tab ' . $tabName . ' installation result ' . (int)$res);
            }
        }
        $this->module->getLogger()->log();
        return true;
    }
    
    public function installHook()
    {
        $this->module->getLogger()->log('HOOKS INSTALLATION');
        $res = true;
        foreach ($this->hooks as $hook) {
            $res = $res && $this->module->registerHook($hook);
            $this->module->getLogger()->log('Hook ' . $hook . ' installation result ' . (int)$res);
        }
        $this->module->getLogger()->log();
        return $res;
    }
    
    public function installDB()
    {
        $this->module->getLogger()->log('DB INSTALLATION');
        $res = true;
        
        foreach ($this->dbTables as $dbClassName) {
            $res = $res && $dbClassName::installTable();
            $this->module->getLogger()->log('Table ' . $dbClassName . ' installation result ' . (int)$res);
        }
        $this->module->getLogger()->log();
        return $res;
    }
    
    public function uninstallDefaults()
    {
        $this->module->getLogger()->log('UNINSTALL MODEL SETTINGS');
        foreach ($this->module->getForms() as $model) {
            $model->clearConfig();
            $this->module->getLogger()->log('Models settings ' . get_class($model) . ' deleted');
        }
        $this->module->getLogger()->log();
        return true;
    }
    
    public function installDefaults()
    {
        $this->module->getLogger()->log('DEFAULT SETTINGS INSTALLATION');
        foreach ($this->module->getForms() as $model) {
            $model->loadDefaults();
            $res = $model->saveToConfig(false);
            $this->module->getLogger()->log('Model ' . get_class($model) . ' defaults saved: ' . (int)$res);
        }
        $this->module->getLogger()->log();
        return true;
    }
    
    public function clearDefaultRoutes($defaultRoutes = null)
    {
        $this->module->getLogger()->log('SAVE OLD ROUTES');
        if ($defaultRoutes === null) {
            $dispatcher = Dispatcher::getInstance();
            $defaultRoutes = $dispatcher->default_routes;
        }
        $prefix = 'PS_ROUTE_';
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        foreach (array_keys($defaultRoutes) as $rule) {
            if (strpos($rule, 'old_') === false) {
                $currentRule = Configuration::get($prefix . $rule);
                if ($currentRule) {
                    $defaultRoutes[$rule]['rule'] = $currentRule;
                    $defaultRoutes[$rule]['fromDB'] = 1;
                    $this->module->getLogger()->log('Old route fethed from DB ' . $prefix . $rule . ':' . $currentRule);
                } else {
                    $defaultRoutes[$rule]['fromDB'] = 0;
                    $this->module->getLogger()->log('Old route (default) ' . $prefix . $rule . ':' . $defaultRoutes[$rule]['rule']);
                }
                Configuration::deleteByName($prefix . $rule);
            }
        }
        Configuration::updateValue('ARSEO_OLD_ROUTES', ArSeoProTools::jsonEncode($defaultRoutes));
        $this->module->getLogger()->log();
        return true;
    }
    
    public function restoreDefaultRoutes()
    {
        $this->module->getLogger()->log('RESTORING DEFAULT ROUTES');
        if ($value = Configuration::get('ARSEO_OLD_ROUTES')) {
            $defaultRoutes = ArSeoProTools::jsonDecode($value);
            $prefix = 'PS_ROUTE_';
            foreach ($defaultRoutes as $k => $rule) {
                if ($rule->rule) {
                    if ((isset($rule->fromDB) && $rule->fromDB) || !isset($rule->fromDB)) {
                        Configuration::updateValue($prefix . $k, $rule->rule);
                        $this->module->getLogger()->log('Old route previously stored in DB ' . $k . ' ' . $rule->rule . ' restored');
                    } else {
                        $this->module->getLogger()->log('Old route is default. ' . $k . ' ' . $rule->rule . ' Does not restored to the database.');
                    }
                }
            }
        }
        $this->module->getLogger()->log();
        return Configuration::deleteByName('ARSEO_OLD_ROUTES');
    }
    
    public function installOverrides()
    {
        return true;
    }
    
    public function prepareOverrides()
    {
        $this->module->getLogger()->log('PREPARING OVERRIDES FOR VERSION ' . _PS_VERSION_);
        $override_path = realpath(dirname(__FILE__).'/../override/') . '/';
        
        if ($this->module->is16()) {
            $override_version_path = realpath(dirname(__FILE__).'/../override_versions/1.6.x/') . '/';
            $this->module->getLogger()->log('Overrides folder is ' . $override_version_path);
            $files_to_copy = Tools::scandir($override_version_path, 'php', '', true);
            if ($files_to_copy) {
                foreach ($files_to_copy as $file) {
                    Tools::copy($override_version_path.$file, $override_path.$file);
                    $this->module->getLogger()->log('Copy ' . $override_version_path . $file . ' to ' . $override_path.$file);
                }
            }
        }

        if ($this->module->is17() || $this->module->is8x()) {
            if ($this->module->is178x()) {
                $override_version_path = realpath(dirname(__FILE__).'/../override_versions/1.7.8.x/') . '/';
            } elseif ($this->module->is8x()) {
                $override_version_path = realpath(dirname(__FILE__).'/../override_versions/8.x.x/') . '/';
            } else {
                $override_version_path = realpath(dirname(__FILE__).'/../override_versions/1.7.x/') . '/';
            }
            $this->module->getLogger()->log('Overrides folder is ' . $override_version_path);
            
            $files_to_copy = Tools::scandir($override_version_path, 'php', '', true);
            
            if ($files_to_copy) {
                foreach ($files_to_copy as $file) {
                    $info = pathinfo($file);
                    if (!is_dir($override_path.$info['dirname'])) {
                        mkdir($override_path.$info['dirname'], 0777, true);
                    }
                    
                    Tools::copy($override_version_path.$file, $override_path.$file);
                    $this->module->getLogger()->log('Copy ' . $override_version_path . $file . ' to ' . $override_path.$file);
                }
            }
        }
        $this->module->getLogger()->log();
        return true;
    }
}
