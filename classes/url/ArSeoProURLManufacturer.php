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

include_once dirname(__FILE__).'/ArSeoProURLAbstract.php';

class ArSeoProURLManufacturer extends ArSeoProURLAbstract
{
    const CONFIG_PREFIX = 'arsm_';
    
    public $enable;
    public $keep_id;
    public $disable_html;
    public $redirect;
    public $redirect_code;
    public $disable_old;
    
    public function getRuleId()
    {
        return 'manufacturer_rule';
    }
    
    public function getDuplicates()
    {
        $return = array();
        $limit = ' LIMIT 5';

        $sql = 'SELECT m.`id_manufacturer`, ms.`id_shop`, m.`name` FROM `'._DB_PREFIX_.'manufacturer` m '
                . 'LEFT JOIN `'._DB_PREFIX_.'manufacturer_shop` ms ON m.`id_manufacturer` = ms.`id_manufacturer` '
                . 'GROUP BY ms.`id_shop`, m.`name` '
                . 'HAVING count(m.`name`) > 1 ORDER BY ms.`id_shop` ASC' . pSQL($limit);
        $duplicates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($duplicates) {
            foreach ($duplicates as $duplicate) {
                $sql2 = 'SELECT m.`id_manufacturer`, ms.`id_shop`, m.`name` FROM `'._DB_PREFIX_.'manufacturer` m '
                        . 'LEFT JOIN `'._DB_PREFIX_.'manufacturer_shop` ms ON m.`id_manufacturer` = ms.`id_manufacturer` '
                        . 'WHERE ms.`id_shop` = '.(int)($duplicate['id_shop']).' '
                        . 'AND m.`name` = "'.pSQL($duplicate['name']).'" '
                        . 'GROUP BY m.`id_manufacturer`' . pSQL($limit);

                $more = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql2);
                foreach ($more as $info) {
                    $row = array();
                    $row['id'] = 'manufacturer_'.$info['id_manufacturer'];
                    $row['id_object'] = $info['id_manufacturer'];
                    $row['id_type'] = 'manufacturer';
                    $row['type'] = 'Manufacturer';
                    $row['name'] = $info['name'];
                    $row['shop'] = '';
                    if ($shop = Shop::getShop($info['id_shop'])) {
                        $row['shop'] = $shop['name'];
                    }

                    $return[] = $row;
                }
            }
        }

        return $return;
    }
    
    public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop)
    {
        $return = $this->owner->getEmptyPreDispatcherResponse();
        $rewrite = '';
        
        if (isset($m['ars_rewrite_manufacturer'])) {
            $rewrite = $m['ars_rewrite_manufacturer'];
        }

        if (isset($m['ars_id_manufacturer']) && $m['ars_id_manufacturer']) {
            $id_manufacturer = $m['ars_id_manufacturer'];
        } else {
            $id_manufacturer = null;
        }
        
        if (!$id_manufacturer) {
            $id_manufacturer = Db::getInstance()->getValue(
                'SELECT m.`id_manufacturer` FROM `'._DB_PREFIX_.'manufacturer` m '
                    . 'LEFT JOIN `'._DB_PREFIX_.'manufacturer_shop` ms ON m.id_manufacturer = ms.id_manufacturer '
                    . 'WHERE ms.`id_shop` = '.(int)($id_shop).' AND REPLACE(LOWER(m.`name`), " ", "-") = "'.pSQL($rewrite).'"'
            );
        }

        if (!$id_manufacturer) {
            $manufacturers = Db::getInstance()->executeS(
                'SELECT m.`id_manufacturer`, m.`name` FROM `'._DB_PREFIX_.'manufacturer` m '
                    . 'LEFT JOIN `'._DB_PREFIX_.'manufacturer_shop` ms ON m.id_manufacturer = ms.id_manufacturer '
                    . 'WHERE ms.`id_shop` = '.(int)($id_shop)
            );

            if ($manufacturers) {
                foreach ($manufacturers as $manufacturer) {
                    if ($rewrite == Tools::str2url($manufacturer['name'])) {
                        $id_manufacturer = $manufacturer['id_manufacturer'];
                        break;
                    }
                }
            }
        }

        if ($id_manufacturer) {
            $return->controllerMatched = true;
            $return->id = $id_manufacturer;
            $return->property = 'id_manufacturer';
        } else {
            if (!ArSeoHelpers::startWith(trim($route['rule']), '{')) {
                $return->controllerProbably = true;

                if ($this->redirect == self::REDIRECT_NONE) {
                    $return->useIfProbably = false;
                }
            }
        }
        
        return $return;
    }
    
    public function dispatch()
    {
        $context = $this->owner->getContext();
        if (Tools::getValue('ars_rewrite_manufacturer')) {
            $id_manufacturer = Tools::getValue('id_manufacturer');
            if (!$id_manufacturer) {
                if ($this->redirect == ArSeoProURLManufacturer::REDIRECT_PARENT) {
                    $this->owner->redirect($context->link->getPageLink('manufacturer'), ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                }

                $this->owner->redirectToNotFound();
            }

            $_GET['id_manufacturer'] = $id_manufacturer;
            if (ArSeoHelpers::getIsset('ars_rewrite_manufacturer')) {
                unset($_GET['ars_rewrite_manufacturer']);
            }
            if (ArSeoHelpers::getIsset('fc')) {
                unset($_GET['fc']);
            }
        }
    }
    
    public function getDefaultRoute()
    {
        if ($this->keep_id) {
            return 'manufacturer/{id}-{rewrite}' . ($this->disable_html ? '' : '.html');
        }
        return 'manufacturer/{rewrite}' . ($this->disable_html ? '' : '.html');
    }
    
    public function getRoute()
    {
        return array(
            'controller' => 'manufacturer',
            'rule' => $this->getDefaultRoute(),
            'keywords' => array(
                'id' => $this->keep_id ? $this->regexp('[0-9]+', 'ars_id_manufacturer') : $this->regexp('[0-9]+'),
                'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_manufacturer'),
                'meta_keywords' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
                'meta_title' => $this->regexp(self::REGEX_ALPHA_NUMERIC),
            ),
            'params' => array(
                'fc' => 'controller'
            )
        );
    }
    
    public function getConfigPrefix()
    {
        return self::CONFIG_PREFIX;
    }
    
    public function rules()
    {
        return array(
            array(
                array(
                    'enable',
                    'keep_id',
                    'redirect',
                    'redirect_code',
                    'schema',
                    'keywords',
                    'disable_old',
                    'disable_html'
                ), 'safe'
            )
        );
    }
    
    public function redirectSelectOptions()
    {
        return array(
            array(
                'id' => self::REDIRECT_NONE,
                'name' => $this->l('None', 'ArSeoProURLManufacturer')
            ),
            array(
                'id' => self::REDIRECT_PARENT,
                'name' => $this->l('Redirect to manufacturer list', 'ArSeoProURLManufacturer')
            ),
            array(
                'id' => self::REDIRECT_404,
                'name' => $this->l('Redirect to page not found', 'ArSeoProURLManufacturer')
            )
        );
    }
    
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), array(
            'redirect' => $this->l('Redirect type if category not found', 'ArSeoProURLManufacturer')
        ));
    }
    
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'enable' => $this->l('Enable this tab functionality', 'ArSeoProURLManufacturer'),
            'keep_id' => $this->l('Keep manufacturer ID in the URL', 'ArSeoProURLManufacturer'),
            'redirect' => $this->l('Redirect type if manufacturer not found', 'ArSeoProURLManufacturer'),
            'schema' => $this->l('Current Manufacturer URL scheme', 'ArSeoProURLManufacturer'),
        ));
    }
    
    public function attributeTypes()
    {
        return array_merge(parent::attributeTypes(), array(
            'redirect' => 'select'
        ));
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'redirect' => self::REDIRECT_PARENT
        );
    }
    
    public function afterSave()
    {
        if ($rule = Configuration::get('PS_ROUTE_manufacturer_rule')) {
            if (!$this->hasKeyword($rule, 'id') && $this->keep_id) {
                $rule = str_replace('{rewrite}', '{id}-{rewrite}', $rule);
            } elseif ($this->hasKeyword($rule, 'id') && !$this->keep_id) {
                $rule = str_replace('{id}-{rewrite}', '{rewrite}', $rule);
            }
            
            ConfigurationCore::updateValue('PS_ROUTE_manufacturer_rule', $rule);
        }
        return parent::afterSave();
    }
}
