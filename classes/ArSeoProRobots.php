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

include_once dirname(__FILE__).'/sitemap/ArSeoProSitemapGenerator.php';

/**
 * @property ArSeoPro $module
 */
class ArSeoProRobots
{
    public $module;
    
    protected $error;


    public function __construct($module)
    {
        $this->module = $module;
    }
    
    public function getError()
    {
        return $this->error;
    }
    
    public function getContent()
    {
        if (file_exists($this->getRobotsFilename()) && !is_readable($this->getRobotsFilename())) {
            $this->error = 'Error reading file robots.txt. Permission denied.';
            return false;
        }
        if (!file_exists($this->getRobotsFilename())) {
            return null;
        }
        if ($content = Tools::file_get_contents($this->getRobotsFilename())) {
            return $content;
        }
        return null;
    }
    
    public function save($content)
    {
        if (!$this->checkRobotsFile()) {
            return false;
        }
        if (is_array($content)) {
            $content = implode("\n", $content);
        }
        file_put_contents($this->getRobotsFilename(), $content);
        
        $generator = new ArSeoProSitemapGenerator($this->module, Context::getContext()->shop->id);
        $generator->setIndexPath($this->module->getIndexSitemapPath(false));
        $generator->updateRobots();
        return true;
    }
    
    public function checkRobotsFile()
    {
        if (file_exists($this->getRobotsFilename()) && !is_readable($this->getRobotsFilename())) {
            $this->error = 'Error reading file robots.txt. Permission denied.';
            return false;
        }
        if (file_exists($this->getRobotsFilename()) && !is_writable($this->getRobotsFilename())) {
            $this->error = 'Error saving file robots.txt. Permission denied.';
            return false;
        }
        if (!file_exists($this->getRobotsFilename())) {
            if (!is_writable(_PS_ROOT_DIR_)) {
                $this->error = 'Can not create robots.txt. Permission denied.';
                return false;
            }
        }
        return true;
    }
    
    public function loadDefaults($ignoreErrors = false)
    {
        $checkFile = $this->checkRobotsFile();
        if (!$checkFile && $ignoreErrors) {
            return true;
        }
        if (!$checkFile && !$ignoreErrors) {
            return false;
        }
        //$metaController = new AdminMetaController();
        //$metaController->generateRobotsFile();
        return true;
    }
    
    public function getDisallows()
    {
        return array(
            'history',
            'order-slip',
            'addresses',
            'identity',
            'discount'
        );
    }
    
    public function getRobotsFilename()
    {
        return _PS_ROOT_DIR_ . '/robots.txt';
    }
}
