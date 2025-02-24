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

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

include_once dirname(__FILE__).'/../ArSeoModel.php';

/**
 * @property ArSeoProUrls $owner
 */
abstract class ArSeoProURLAbstract extends ArSeoModel
{
    const REDIRECT_NONE = 0;
    const REDIRECT_PARENT = 1;
    const REDIRECT_404 = 2;
    
    const REGEX_ALPHA_NUMERIC = '[_a-zA-Z0-9-\pL]*';
    
    public $schema;
    
    public function __construct($module, $configPrefix = null, $owner = null)
    {
        parent::__construct($module, $configPrefix);
        $this->owner = $owner;
        $this->configPrefix = $this->getConfigPrefix();
    }
    
    abstract public function getRuleId();
    abstract public function getRoute();
    abstract public function getDefaultRoute();
    abstract public function dispatch();
    abstract public function preDispatch($uri, $route_id, $route, $m, $id_lang, $id_shop);
    
    public function keywords()
    {
        return array();
    }
    
    public function keywordLabels()
    {
        return array();
    }
    
    public function getKeywordLabel($keyword)
    {
        $labels = $this->keywordLabels();
        return isset($labels[$keyword])? $labels[$keyword] : null;
    }
    
    public function getKeywords()
    {
        $result = array();
        $keywords = $this->keywords();
        foreach (array_keys($keywords) as $k) {
            $result[$k] = $this->getKeywordLabel($k);
        }
        return $result;
    }
    
    public function regexp($regexp, $param = null, $required = true)
    {
        if ($param) {
            return array('regexp' => $regexp, 'param' => $param);
        }
        return array('regexp' => $regexp);
    }
    
    public static function getConfigTab()
    {
        return 'url';
    }
    
    public function beforeSave()
    {
        if ($this->schema == $this->getDefaultRoute()) {
            $this->schema = null;
        }
        return parent::beforeSave();
    }
    
    public function getRouteRule()
    {
        $dispatcher = Dispatcher::getInstance();
        $defaultRoutes = $dispatcher->default_routes;
        $rule = Configuration::get('PS_ROUTE_' . $this->getRuleId());

        if (!$rule) {
            $rule = $defaultRoutes[$this->getRuleId()]['rule'];
        }
        return $rule;
    }
    
    public function renderSchemaField()
    {
        return $this->module->render('_partials/url/_schema.tpl', array(
            'schema' => $this->getRouteRule(),
            'ruleId' => $this->getRuleId(),
            'link' => Context::getContext()->link->getAdminLink('AdminMeta'),
            'is16' => $this->module->is16(),
            'is17' => $this->module->is17(),
            'is174' => $this->module->is174(),
            'is8x' => $this->module->is8x()
        ));
    }
    
    public function redirectCodeSelectOptions()
    {
        return array(
            array(
                'id' => '301',
                'name' => $this->l('301 Moved Permanently', 'ArSeoProURLProduct')
            ),
            array(
                'id' => '302',
                'name' => $this->l('302 Moved Temporarily', 'ArSeoProURLProduct')
            )
        );
    }
    
    public function getConfigPrefix()
    {
        return null;
    }
    
    public function attributeTypes()
    {
        return array(
            'enable' => 'switch',
            'keep_id' => 'switch',
            'redirect_code' => 'select',
            'keywords' => 'html',
            'schema' => 'html',
            'disable_old' => 'switch',
            'disable_html' => 'switch'
        );
    }
    
    public function htmlFields()
    {
        return array(
            'keywords' => $this->module->render('_partials/url/_keywords.tpl', array(
                'keywords' => $this->getKeywords(),
                'target' => 'PS_ROUTE_' . $this->getRuleId()
            )),
            'schema' => $this->renderSchemaField()
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'enable' => $this->l('Enable', 'ArSeoProURLAbstract'),
            'disable_old' => $this->l('Disable old routes', 'ArSeoProURLAbstract'),
            'disable_html' => $this->l('Disable .html suffix', 'ArSeoProURLAbstract'),
            'redirect_code' => $this->l('Redirect code', 'ArSeoProURLAbstract'),
            'keywords' => ''
        );
    }
    
    public function hasKeyword($rule, $keyword)
    {
        return preg_match('#\{([^{}]*:)?' . preg_quote($keyword, '#') . '(:[^{}]*)?\}#', $rule);
    }
    
    public function getBaseLink($idShop = null, $ssl = null, $relativeProtocol = false)
    {
        if (null === $ssl) {
            $ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        }
        $ssl_enable = Configuration::get('PS_SSL_ENABLED');

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $idShop !== null) {
            $shop = new Shop($idShop);
        } else {
            $shop = Context::getContext()->shop;
        }

        if ($relativeProtocol) {
            $base = '//' . ($ssl && $ssl_enable ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl && $ssl_enable) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        }

        return $base . $shop->getBaseURI();
    }
}
