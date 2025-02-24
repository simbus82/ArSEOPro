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

class ArSeoProURLSupplier extends ArSeoProURLAbstract
{
    const CONFIG_PREFIX = 'arss_';
    
    public $enable;
    public $keep_id;
    public $disable_html;
    public $redirect;
    public $redirect_code;
    public $disable_old;
    
    public function getRuleId()
    {
        return 'supplier_rule';
    }
    
    public function getDuplicates()
    {
        $return = array();
        $limit = ' LIMIT 5';

        $sql = 'SELECT s.`id_supplier`, ss.`id_shop`, s.`name` FROM `'._DB_PREFIX_.'supplier` s '
                . 'LEFT JOIN `'._DB_PREFIX_.'supplier_shop` ss ON s.`id_supplier` = ss.`id_supplier` '
                . 'GROUP BY ss.`id_shop`, s.`name` '
                . 'HAVING count(s.`name`) > 1 ORDER BY ss.`id_shop` ASC' . pSQL($limit);
        $duplicates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($duplicates) {
            foreach ($duplicates as $duplicate) {
                $sql2 = 'SELECT s.`id_supplier`, ss.`id_shop`, s.`name` FROM `'._DB_PREFIX_.'supplier` s '
                        . 'LEFT JOIN `'._DB_PREFIX_.'supplier_shop` ss ON s.`id_supplier` = ss.`id_supplier` '
                        . 'WHERE ss.`id_shop` = '.(int)($duplicate['id_shop']).'  '
                        . 'AND s.`name` = '.(int)($duplicate['name']).' '
                        . 'GROUP BY s.`id_supplier`' . pSQL($limit);

                $more = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql2);
                foreach ($more as $info) {
                    $row = array();
                    $row['id'] = 'supplier_'.$info['id_supplier'];
                    $row['id_object'] = $info['id_supplier'];
                    $row['id_type'] = 'supplier';
                    $row['type'] = 'Supplier';
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
        
        if (isset($m['ars_rewrite_supplier'])) {
            $rewrite = $m['ars_rewrite_supplier'];
        }
        
        if (isset($m['ars_id_supplier']) && $m['ars_id_supplier']) {
            $id_supplier = $m['ars_id_supplier'];
        } else {
            $id_supplier = null;
        }

        if (!$id_supplier) {
            $id_supplier = Db::getInstance()->getValue(
                'SELECT s.`id_supplier` FROM `'._DB_PREFIX_.'supplier` s '
                    . 'LEFT JOIN `'._DB_PREFIX_.'supplier_shop` ss ON s.id_supplier = ss.id_supplier '
                    . 'WHERE ss.`id_shop` = '.(int)$id_shop.' '
                    . 'AND REPLACE(LOWER(s.`name`), " ", "-") = "'.pSQL($rewrite).'"'
            );
        }

        if (!$id_supplier) {
            $suppliers = Db::getInstance()->executeS(
                'SELECT s.`id_supplier`, s.`name` FROM `'._DB_PREFIX_.'supplier` s '
                    . 'LEFT JOIN `'._DB_PREFIX_.'supplier_shop` ss ON s.id_supplier = ss.id_supplier '
                    . 'WHERE ss.`id_shop` = '.(int)$id_shop
            );

            if ($suppliers) {
                foreach ($suppliers as $supplier) {
                    if ($rewrite == Tools::str2url($supplier['name'])) {
                        $id_supplier = $supplier['id_supplier'];
                        break;
                    }
                }
            }
        }

        if ($id_supplier) {
            $return->controllerMatched = true;
            $return->id = $id_supplier;
            $return->property = 'id_supplier';
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
        
        if (Tools::getValue('ars_rewrite_supplier')) {
            $id_supplier = Tools::getValue('id_supplier');
            if (!$id_supplier) {
                if ($this->redirect == self::REDIRECT_PARENT) {
                    $this->owner->redirect($context->link->getPageLink('supplier'), ArSeoHelpers::getResponseHeader($this->redirect_code? $this->redirect_code : 301));
                }

                $this->owner->redirectToNotFound();
            }

            $_GET['id_supplier'] = $id_supplier;
            if (ArSeoHelpers::getIsset('ars_rewrite_supplier')) {
                unset($_GET['ars_rewrite_supplier']);
            }
            if (ArSeoHelpers::getIsset('fc')) {
                unset($_GET['fc']);
            }
        }
    }
    
    public function getDefaultRoute()
    {
        if ($this->keep_id) {
            return 'supplier/{id}-{rewrite}' . ($this->disable_html ? '' : '.html');
        }
        return 'supplier/{rewrite}' . ($this->disable_html ? '' : '.html');
    }
    
    public function getRoute()
    {
        return array(
            'controller' => 'supplier',
            'rule' => $this->getDefaultRoute(),
            'keywords' => array(
                'id' => $this->keep_id ? $this->regexp('[0-9]+', 'ars_id_supplier') : $this->regexp('[0-9]+'),
                'rewrite' => $this->regexp('[_a-zA-Z0-9\pL\pS-]*', 'ars_rewrite_supplier'),
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
                'name' => $this->l('None', 'ArSeoProURLSupplier')
            ),
            array(
                'id' => self::REDIRECT_PARENT,
                'name' => $this->l('Redirect to supplier list', 'ArSeoProURLSupplier')
            ),
            array(
                'id' => self::REDIRECT_404,
                'name' => $this->l('Redirect to page not found', 'ArSeoProURLSupplier')
            )
        );
    }
    
    public function attributeDescriptions()
    {
        $desc = parent::attributeDescriptions();
        return array_merge($desc, array(
            'redirect' => $this->l('Redirect type if category not found', 'ArSeoProURLSupplier')
        ));
    }
    
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        return array_merge($labels, array(
            'enable' => $this->l('Enable this tab functionality', 'ArSeoProURLSupplier'),
            'keep_id' => $this->l('Keep supplier ID in the URL', 'ArSeoProURLSupplier'),
            'redirect' => $this->l('Redirect type if supplier not found', 'ArSeoProURLSupplier'),
            'schema' => $this->l('Current Supplier URL scheme', 'ArSeoProURLSupplier'),
        ));
    }
    
    public function attributeTypes()
    {
        $types = parent::attributeTypes();
        return array_merge($types, array(
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
        if ($rule = Configuration::get('PS_ROUTE_supplier_rule')) {
            if (!$this->hasKeyword($rule, 'id') && $this->keep_id) {
                $rule = str_replace('{rewrite}', '{id}-{rewrite}', $rule);
            } elseif ($this->hasKeyword($rule, 'id') && !$this->keep_id) {
                $rule = str_replace('{id}-{rewrite}', '{rewrite}', $rule);
            }
            
            ConfigurationCore::updateValue('PS_ROUTE_supplier_rule', $rule);
        }
        return parent::afterSave();
    }
}
