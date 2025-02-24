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
include_once dirname(__FILE__).'/ArSeoProTools.php';

class ArSeoProFavicon extends ArSeoModel
{
    public $h;
    public $icon;
    public $icon_preview;
    public $remove_icon;
    public $hr;
    
    public $h1;
    public $ios_master;
    public $ios_icon;
    public $ios_icon_preview;
    public $ios_remove_icon;
    public $hr1;
    
    public $h2;
    public $android_master;
    public $android_icon;
    public $android_icon_preview;
    public $android_remove_icon;
    public $android_name;
    public $android_short_name;
    public $android_theme;
    public $android_start_url;
    public $android_orientation;
    public $hr2;
    
    public $h3;
    public $ms_master;
    public $ms_icon;
    public $ms_icon_preview;
    public $ms_remove_icon;
    public $ms_tile_color;
    public $hr3;
    
    public $h4;
    public $mac_icon;
    public $mac_icon_preview;
    public $mac_remove_icon;
    public $mac_theme;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'icon',
                    'ios_icon',
                    'android_icon',
                    'ms_icon',
                ),
                'validateImage',
                'params' => array(
                    'mime' => array(
                        'image/png'
                    ),
                    'size' => array(
                        'min' => '260x260'
                    ),
                    'dimensions' => array(
                        1
                    )
                ),
                'message' => $this->l('Please check file format is PNG, dimensions of file is min 260*260px and image proportions is 1:1')
            ),
            array(
                array(
                    'mac_icon',
                ),
                'validateImage',
                'params' => array(
                    'mime' => array(
                        'image/svg+xml',
                        'text/plain'
                    ),
                ),
                'message' => $this->l('Please check file format is SVG')
            ),
            array(
                array(
                    'icon_preview',
                    'remove_icon'
                ), $this->icon? 'safe' : 'unsafe'
            ),
            array(
                array(
                    'ios_icon_preview',
                    'ios_remove_icon'
                ), $this->ios_icon? 'safe' : 'unsafe'
            ),
            array(
                array(
                    'android_icon_preview',
                    'android_remove_icon'
                ), $this->android_icon? 'safe' : 'unsafe'
            ),
            array(
                array(
                    'ms_icon_preview',
                    'ms_remove_icon'
                ), $this->ms_icon? 'safe' : 'unsafe'
            ),
            array(
                array(
                    'mac_icon_preview',
                    'mac_remove_icon'
                ), $this->mac_icon? 'safe' : 'unsafe'
            ),
            array(
                array(
                    'ms_tile_color',
                    'mac_theme',
                    'android_theme'
                ), 'color'
            ),
            array(
                array(
                    'hr',
                    'hr1',
                    'hr2',
                    'hr3',
                    'h',
                    'h1',
                    'h2',
                    'h3',
                    'h4',
                    'ios_master',
                    'android_master',
                    'ms_master',
                    'android_name',
                    'android_short_name',
                    'android_start_url',
                    'android_orientation'
                ), 'safe'
            )
        );
    }
    
    public function faviconSizes()
    {
        return array(
            '16x16',
            '32x32',
            '96x96'
        );
    }
    
    public function appleTouchIconSizes()
    {
        return array(
            '57x57',
            '114x114',
            '72x72',
            '144x144',
            '60x60',
            '120x120',
            '76x76',
            '152x152',
            '180x180'
        );
    }
    
    public function msTileSizes()
    {
        return array(
            '128x128',
            '150x150',
            '270x270',
            '558x558'
        );
    }
    
    public function chromeSizes()
    {
        return array(
            '192x192',
            '256x256'
        );
    }
    
    public function getFavicon($info = null)
    {
        if (!$this->icon) {
            return null;
        }
        $filename = pathinfo($this->icon, PATHINFO_FILENAME);
        $ext = pathinfo($this->icon, PATHINFO_EXTENSION);
        $return = array(
            'filename' => $filename,
            'ext' => $ext
        );
        return $info? $return[$info] : $return;
    }
    
    public function getIOSFavicon($info = null)
    {
        if (!$this->ios_master && $this->ios_icon) {
            $filename = pathinfo($this->ios_icon, PATHINFO_FILENAME);
            $ext = pathinfo($this->ios_icon, PATHINFO_EXTENSION);
            $return = array(
                'filename' => $filename,
                'ext' => $ext
            );
            return $info? $return[$info] : $return;
        }
        return $this->getFavicon($info);
    }
    
    public function getAndroidFavicon($info = null)
    {
        if (!$this->android_master && $this->android_icon) {
            $filename = pathinfo($this->android_icon, PATHINFO_FILENAME);
            $ext = pathinfo($this->android_icon, PATHINFO_EXTENSION);
            $return = array(
                'filename' => $filename,
                'ext' => $ext
            );
            return $info? $return[$info] : $return;
        }
        return $this->getFavicon($info);
    }
    
    public function getMsFavicon($info = null)
    {
        if (!$this->ms_master && $this->ms_icon) {
            $filename = pathinfo($this->ms_icon, PATHINFO_FILENAME);
            $ext = pathinfo($this->ms_icon, PATHINFO_EXTENSION);
            $return = array(
                'filename' => $filename,
                'ext' => $ext
            );
            return $info? $return[$info] : $return;
        }
        return $this->getFavicon($info);
    }
    
    protected function removeIconFiles($file, $updateConfigValue = true, $configKey = null)
    {
        if (!$file) {
            return false;
        }
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $srcFile = $this->module->getUploadPath() . $file;
        foreach ($this->faviconSizes() as $size) {
            $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
            if (file_exists($dstFile)) {
                unlink($dstFile);
            }
        }
        foreach ($this->appleTouchIconSizes() as $size) {
            $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
            if (file_exists($dstFile)) {
                unlink($dstFile);
            }
        }
        foreach ($this->msTileSizes() as $size) {
            $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
            if (file_exists($dstFile)) {
                unlink($dstFile);
            }
        }
        foreach ($this->chromeSizes() as $size) {
            $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
            if (file_exists($dstFile)) {
                unlink($dstFile);
            }
        }
        if (file_exists($srcFile)) {
            unlink($srcFile);
        }
        if ($updateConfigValue && $configKey) {
            Configuration::updateValue($configKey, null);
        }
    }


    public function afterSave()
    {
        $this->loadFromConfig();
        if (Tools::getValue('ARSF_REMOVE_ICON_on')) {
            $this->removeIconFiles($this->icon, true, 'ARSF_ICON');
            $this->icon = null;
        }
        if (Tools::getValue('ARSF_IOS_REMOVE_ICON_on')) {
            $this->removeIconFiles($this->ios_icon, true, 'ARSF_IOS_ICON');
            $this->ios_icon = null;
        }
        if (Tools::getValue('ARSF_ANDROID_REMOVE_ICON_on')) {
            $this->removeIconFiles($this->android_icon, true, 'ARSF_ANDROID_ICON');
            $this->android_icon = null;
        }
        if (Tools::getValue('ARSF_MS_REMOVE_ICON_on')) {
            $this->removeIconFiles($this->ms_icon, true, 'ARSF_MS_ICON');
            $this->ms_icon = null;
        }
        if (Tools::getValue('ARSF_MAC_REMOVE_ICON_on')) {
            $this->removeIconFiles($this->mac_icon, true, 'ARSF_MAC_ICON');
            $this->mac_icon = null;
        }
        $oldIcon = $this->icon;
        $this->loadFromConfig();
        if ($oldIcon != $this->icon && !empty($oldIcon)) {
            $this->removeIconFiles($oldIcon, false);
        }
        if ($this->getFavicon()) {
            $filename = $this->getFavicon('filename');
            $ext = $this->getFavicon('ext');
            $srcFile = $this->module->getUploadPath() . $filename . '.' . $ext;
            foreach ($this->faviconSizes() as $size) {
                $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
                $data = explode('x', $size);
                ImageManager::resize($srcFile, $dstFile, $data[0], $data[1], $ext, true);
            }
        }
        if ($this->getIOSFavicon()) {
            $filename = $this->getIOSFavicon('filename');
            $ext = $this->getIOSFavicon('ext');
            $srcFile = $this->module->getUploadPath() . $filename . '.' . $ext;
            foreach ($this->appleTouchIconSizes() as $size) {
                $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
                $data = explode('x', $size);
                ImageManager::resize($srcFile, $dstFile, $data[0], $data[1], $ext, true);
            }
        }
        if ($this->getMsFavicon()) {
            $filename = $this->getMsFavicon('filename');
            $ext = $this->getMsFavicon('ext');
            $srcFile = $this->module->getUploadPath() . $filename . '.' . $ext;
            foreach ($this->msTileSizes() as $size) {
                $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
                $data = explode('x', $size);
                ImageManager::resize($srcFile, $dstFile, $data[0], $data[1], $ext, true);
            }
        }
        if ($this->getAndroidFavicon()) {
            $filename = $this->getAndroidFavicon('filename');
            $ext = $this->getAndroidFavicon('ext');
            $srcFile = $this->module->getUploadPath() . $filename . '.' . $ext;
            foreach ($this->chromeSizes() as $size) {
                $dstFile = $this->module->getUploadPath() . $filename . '_' . $size . '.' . $ext;
                $data = explode('x', $size);
                ImageManager::resize($srcFile, $dstFile, $data[0], $data[1], $ext, true);
            }
        }
        $this->generateIeConfig();
        $this->generateManifest();
        return parent::afterSave();
    }
    
    protected function generateManifest()
    {
        $filename = $this->getAndroidFavicon('filename');
        $ext = $this->getAndroidFavicon('ext');
        $icons = array();
        $id_shop = Context::getContext()->shop->id;
        
        foreach ($this->chromeSizes() as $size) {
            $icons[] = array(
                'src' => $this->module->getUploadsUrl() . $filename . '_' . $size . '.' . $ext,
                'sizes' => $size,
                'type' => 'image/png'
            );
        }
        $data = array(
            'name' => $this->android_name,
            'short_name' => $this->android_short_name,
            'icons' => $icons,
            'start_url' => $this->android_start_url,
            'theme_color' => $this->android_theme,
            'background_color' => $this->android_theme,
            'display' => 'standalone',
            'gcm_sender_id' => '482941778795',
            'gcm_user_visible_only' => true
        );
        if ($this->android_orientation) {
            $data['orientation'] = $this->android_orientation;
        }
        file_put_contents($this->module->getUploadPath() . 'manifest' . $id_shop . '.json', ArSeoProTools::jsonEncode($data));
    }


    protected function generateIeConfig()
    {
        $filename = $this->module->getUploadPath() . 'ieconfig.xml';
        $faviconFilename = $this->getMsFavicon('filename');
        $faviconExt = $this->getMsFavicon('ext');
        file_put_contents($filename, $this->module->render('generators/ieconfig.tpl', array(
            'metaConfig' => $this,
            'msTileSizes' => $this->msTileSizes(),
            'faviconFilename' => $faviconFilename,
            'faviconExt' => $faviconExt,
            'moduleUploadUrl' => $this->module->getUploadsUrl(),
        )));
    }


    public static function getConfigTab()
    {
        return 'favicon';
    }
    
    public function androidOrientationSelectOptions()
    {
        return array(
            array(
                'id' => '',
                'name' => $this->l('None', 'ArSeoProFavicon')
            ),
            array(
                'id' => 'portrait',
                'name' => $this->l('Portrait', 'ArSeoProFavicon')
            ),
            array(
                'id' => 'landscape',
                'name' => $this->l('Landscape', 'ArSeoProFavicon')
            )
        );
    }
    
    public function attributeTypes()
    {
        return array(
            'icon' => 'file',
            'ios_icon' => 'file',
            'android_icon' => 'file',
            'mac_icon' => 'file',
            'ms_icon' => 'file',
            
            'remove_icon' => 'checkbox',
            'ios_remove_icon' => 'checkbox',
            'android_remove_icon' => 'checkbox',
            'ms_remove_icon' => 'checkbox',
            'mac_remove_icon' => 'checkbox',
            
            'ms_tile_color' => 'color',
            'android_theme' => 'color',
            'mac_theme' => 'color',
            
            'icon_preview' => 'html',
            'ios_icon_preview' => 'html',
            'android_icon_preview' => 'html',
            'ms_icon_preview' => 'html',
            'mac_icon_preview' => 'html',
            
            'android_orientation' => 'select',
            'hr' => 'html',
            'hr1' => 'html',
            'hr2' => 'html',
            'hr3' => 'html',
            'h' => 'html',
            'h1' => 'html',
            'h2' => 'html',
            'h3' => 'html',
            'h4' => 'html',
            
            'ios_master' => 'switch',
            'android_master' => 'switch',
            'ms_master' => 'switch'
        );
    }
    
    public function macRemoveIconCheckboxOptions()
    {
        return $this->removeIconCheckboxOptions();
    }
    
    public function iosRemoveIconCheckboxOptions()
    {
        return $this->removeIconCheckboxOptions();
    }
    
    public function androidRemoveIconCheckboxOptions()
    {
        return $this->removeIconCheckboxOptions();
    }
    
    public function msRemoveIconCheckboxOptions()
    {
        return $this->removeIconCheckboxOptions();
    }
    
    public function removeIconCheckboxOptions()
    {
        return array(
            array(
                'id' => 'on',
                'name' => $this->l('Remove current favicon', 'ArSeoProFavicon'),
                'val' => 1
            ),
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'remove_icon' => '',
            'ios_remove_icon' => '',
            'android_remove_icon' => '',
            'ms_remove_icon' => '',
            'mac_remove_icon' => '',
            
            'icon_preview' => $this->l('Current favicon', 'ArSeoProFavicon'),
            'ios_icon_preview' => $this->l('Current favicon', 'ArSeoProFavicon'),
            'android_icon_preview' => $this->l('Current favicon', 'ArSeoProFavicon'),
            'ms_icon_preview' => $this->l('Current favicon', 'ArSeoProFavicon'),
            'mac_icon_preview' => $this->l('Current favicon', 'ArSeoProFavicon'),
            
            'icon' => $this->l('Master favicon image', 'ArSeoProFavicon'),
            'ms_tile_color' => $this->l('Windows Tile Color', 'ArSeoProFavicon'),
            'ios_master' => $this->l('Use master image', 'ArSeoProFavicon'),
            'android_master' => $this->l('Use master image', 'ArSeoProFavicon'),
            'ms_master' => $this->l('Use master image', 'ArSeoProFavicon'),
            'ios_icon' => $this->l('iOS favicon image', 'ArSeoProFavicon'),
            'android_icon' => $this->l('Android favicon image', 'ArSeoProFavicon'),
            'ms_icon' => $this->l('Windows favicon image', 'ArSeoProFavicon'),
            'mac_icon' => $this->l('MacOS Safari favicon image', 'ArSeoProFavicon'),
            'mac_theme' => $this->l('Theme color', 'ArSeoProFavicon'),
            'android_name' => $this->l('App name', 'ArSeoProFavicon'),
            'android_short_name' => $this->l('App short name', 'ArSeoProFavicon'),
            'android_theme' => $this->l('Theme color', 'ArSeoProFavicon'),
            'android_start_url' => $this->l('Start URL', 'ArSeoProFavicon'),
            'android_orientation' => $this->l('Orientation', 'ArSeoProFavicon'),
            'hr' => '',
            'hr1' => '',
            'hr2' => '',
            'hr3' => '',
            'h' => '',
            'h1' => '',
            'h2' => '',
            'h3' => '',
            'h4' => '',
        );
    }
    
    public function htmlFields()
    {
        return array(
            'icon_preview' => $this->module->render('_partials/_icon_preview.tpl', array(
                'icon' => $this->icon,
                'svg' => false,
                'uploadsUrl' => $this->module->getUploadsUrl()
            )),
            'ios_icon_preview' => $this->module->render('_partials/_icon_preview.tpl', array(
                'icon' => $this->ios_icon,
                'svg' => false,
                'uploadsUrl' => $this->module->getUploadsUrl()
            )),
            'android_icon_preview' => $this->module->render('_partials/_icon_preview.tpl', array(
                'icon' => $this->android_icon,
                'svg' => false,
                'uploadsUrl' => $this->module->getUploadsUrl()
            )),
            'ms_icon_preview' => $this->module->render('_partials/_icon_preview.tpl', array(
                'icon' => $this->ms_icon,
                'svg' => false,
                'uploadsUrl' => $this->module->getUploadsUrl()
            )),
            'mac_icon_preview' => $this->module->render('_partials/_icon_preview.tpl', array(
                'icon' => $this->mac_icon,
                'svg' => true,
                'uploadsUrl' => $this->module->getUploadsUrl()
            )),
            'hr' => '<hr/>',
            'hr1' => '<hr/>',
            'hr2' => '<hr/>',
            'hr3' => '<hr/>',
            'h' => '<h3 class="section-head">' . $this->l('General settings') . '</h3>',
            'h1' => '<h3 class="section-head">' . $this->l('iOS Safari settings') . '</h3>',
            'h2' => '<h3 class="section-head">' . $this->l('Android Chrome settings') . '</h3>',
            'h3' => '<h3 class="section-head">' . $this->l('Windows Metro settings') . '</h3>',
            'h4' => '<h3 class="section-head">' . $this->l('MacOS Safari settings') . '</h3>',
        );
    }
    
    public function attributeDefaults()
    {
        return array(
            'ios_master' => 1,
            'android_master' => 1,
            'ms_master' => 1,
            'android_start_url' => Tools::getHttpHost(true) . __PS_BASE_URI__
        );
    }
    
    public function attributeDescriptions()
    {
        return array(
            'icon' => $this->l('This icon will be used for HD favicon, Apple Touch Icon and Windows 8+ tiles. Minimum size is 260x260. Recomended size is 558x558. PNG format only', 'ArSeoProFavicon'),
            'ios_icon' => $this->l('This icon will be used for Apple Touch Icon. Minimum size is 260x260. Recomended size is 558x558. PNG format only', 'ArSeoProFavicon'),
            'android_icon' => $this->l('This icon will be used for Android Chrome. Minimum size is 260x260. Recomended size is 558x558. PNG format only', 'ArSeoProFavicon'),
            'ms_icon' => $this->l('This icon will be used for Windows 8 and 10. Minimum size is 260x260. Recomended size is 558x558. PNG format only', 'ArSeoProFavicon'),
            'mac_icon' => $this->l('This icon will be used for MacOS Safari. SVG format only', 'ArSeoProFavicon'),
        );
    }
    
    public function getFormTitle()
    {
        return $this->l('Favicon', 'ArSeoProFavicon');
    }
}
