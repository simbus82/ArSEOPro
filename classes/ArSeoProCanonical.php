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

class ArSeoProCanonical extends ArSeoModel
{
    public $enable;
    public $product;
    public $category;
    public $cms;
    public $other;
    
    public function rules()
    {
        return array(
            array(
                array(
                    'enable',
                    'product',
                    'category',
                    'cms',
                    'other'
                ), 'safe'
            )
        );
    }
    
    public function attributeTypes()
    {
        return array(
            'enable' => 'switch',
            'product' => 'switch',
            'category' => 'switch',
            'cms' => 'switch',
            'other' => 'switch'
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'enable' => $this->l('Enable canonical functionality', 'ArSeoProCanonical'),
            'product' => $this->l('Enable for products', 'ArSeoProCanonical'),
            'category' => $this->l('Enable for categories', 'ArSeoProCanonical'),
            'cms' => $this->l('Enable for CMS pages', 'ArSeoProCanonical'),
            'other' => $this->l('Enable for other pages', 'ArSeoProCanonical')
        );
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable' => 1,
            'product' => 1,
            'category' => 1,
            'cms' => 1,
            'other' => 1
        );
    }
    
    public function attributeDescriptions()
    {
        parent::attributeLabels();
    }
    
    public static function getConfigTab()
    {
        return 'canonical';
    }
    
    public function getFormTitle()
    {
        return $this->l('Canonical', 'ArSeoProCanonical');
    }
    
    private function getCanonicalURL($controller, $id)
    {
        if ($controller == 'category') {
            return Context::getContext()->link->getCategoryLink($id);
        } elseif ($controller == 'product') {
            return Context::getContext()->link->getProductLink($id);
        } elseif ($controller == 'cms') {
            if ($id == 0 || $id == null) {
                return Context::getContext()->link->getCMSCategoryLink(Tools::getValue('id_cms_category'));
            } else {
                return Context::getContext()->link->getCMSLink($id);
            }
        }
    }
    
    public function getPageName()
    {
        if (Context::getContext()->controller instanceof ProductController || Context::getContext()->controller instanceof ProductControllerCore) {
            return 'product';
        }
        if (Context::getContext()->controller instanceof CategoryController || Context::getContext()->controller instanceof CategoryControllerCore) {
            return 'category';
        }
        if (Context::getContext()->controller instanceof CmsController || Context::getContext()->controller instanceof CmsControllerCore) {
            return 'cms';
        }
    }
    
    public function getCanonicalData($params)
    {
        $pageName = $this->getPageName();
        $canonicalURL = '';
        $canonicalURLPrev = '';
        $canonicalURLNext = '';
        $pagePrev = '';
        $pageNext = '';
        $primero = 'NO';
        $ultimo = 'NO';
        $disabled = 'NO';
        $page_a = '';

        if ($pageName == 'product') {
            $id_category = '';
            if ($this->product) {
                $id_product = (int)Context::getContext()->controller->getProduct()->id;
                $productCanonical = ArSeoProCanonicalProduct::getCanonicalURLByIdLang($id_product, Context::getContext()->language->id);
                if (empty($productCanonical) || $productCanonical['url'] == '') {
                    $canonicalURL = $this->getCanonicalURL($pageName, $id_product);
                } else {
                    $canonicalURL = ($productCanonical['active'] == 1) ? $productCanonical['url'] : $this->getCanonicalURL($pageName, $id_product);
                }
            }
        } elseif ($pageName == 'category') {
            if (Tools::getIsset('page')) {
                $page_a = Tools::getValue('page');
            } else {
                $primero = 'SI';
                $page_a = '';
            }
            if (isset($_COOKIE['disabled'])) {
                if (Context::getContext()->cookie->disabled == 'SI') {
                    $ultimo = 'SI';
                }
            }

            preg_match_all('!\d+!', $page_a, $page_actual);
            if (isset($page_actual[0][0])) {
                $page_actual = $page_actual[0][0];
                $pagePrev = (int)($page_actual) - 1;
                $pageNext = (int)($page_actual) + 1;
            } else {
                $page_actual = 1;
                $pagePrev = '';
                $pageNext = 1;
            }

            $active = 1;
            $id_category = (int) Tools::getValue('id_category');
            $categoryCanonical = ArSeoProCanonicalCategory::getCanonicalURLByIdLang($id_category, Context::getContext()->language->id);

            if (empty($categoryCanonical) || $categoryCanonical['url'] == '') {
                $canonicalURL = $this->getCanonicalURL($pageName, $id_category);
                if ($pagePrev != 0) {
                    $canonicalURLPrev = $canonicalURL.'?page='.$pagePrev;
                } else {
                    $canonicalURLPrev = '';
                }
                $canonicalURLNext = $canonicalURL.'?page='.$pageNext;
            } else {
                $canonicalURL = $categoryCanonical['active'] == 1 ? $categoryCanonical['url'] : $this->getCanonicalURL($pageName, $id_category);

                if ($pagePrev != 0) {
                    $canonicalURLPrev = $canonicalURL.'?page='.$pagePrev;
                } else {
                    $canonicalURLPrev = $canonicalURL;
                }
                $canonicalURLNext = $canonicalURL.'?page='.$pageNext;
            }
        } elseif ($pageName == 'cms') {
            $id_category = '';
            if ($this->cms) {
                $active = 1;
                $id_cms = (int) Tools::getValue('id_cms');
                $canonicalURL = $this->getCanonicalURL($pageName, $id_cms);
            }
        } else {
            $id_category = '';
            if ($this->other) {
                $active = 1;
                $request_uri = Context::getContext()->smarty->getTemplateVars('request_uri');

                if (!empty($request_uri)) {
                    $pos = strpos($request_uri, '?');
                    if ($pos != '') {
                        $request_uri = Tools::substr($request_uri, 0, $pos);
                    }
                    $canonicalURL = Tools::getShopProtocol() . Tools::getShopDomain() . $request_uri;
                } else {
                    $canonicalURL = '';
                    $active = 0;
                }
            }
        }

        $type_parse = parse_url($canonicalURL);
        if (!isset($type_parse['scheme'])) {
            $ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
            $canonicalURL = $ssl? ('https://' . $canonicalURL) : ('http://' . $canonicalURL);
        }

        if (Tools::getIsset('page')) {
            if ($page_a != '' && $page_a != 1) {
                $canonicalURL = $canonicalURL.'?page='.$page_a;
            }

            if ($page_a == 1 || $page_a == null || $page_a == '') {
                $canonicalURLNext++;
            }
        }


        $category = new Category(Tools::getValue('id_category'));

        $pagesWithProducts = (count($category->getProductsWs())) / Configuration::get('PS_PRODUCTS_PER_PAGE');
        $howmanypages = Tools::ps_round($pagesWithProducts, 0, PS_ROUND_UP);

        $data = array(
            'ps_version' => Tools::substr(_PS_VERSION_, 0, 3),
            'canonicalURL' => $canonicalURL,
            'canonicalURLPrev' => $canonicalURLPrev,
            'canonicalURLNext' => $canonicalURLNext,
            'pagePrev' => $pagePrev,
            'pageNext' => $pageNext,
            'categoryActive' => $this->category,
            'productActive' => $this->product,
            'cmsActive' => $this->cms,
            // 'id_category' => $id_category,
            'primero' => $primero,
            'ultimo' => $ultimo,
            'otherActive' => $this->other,
            'disabled' => $disabled
        );


        if ($pageName == 'product') {
            if ($this->product) {
                if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
                    if ($product = Context::getContext()->smarty->getTemplateVars('product')) {
                        $product->offsetSet('canonicalURL', $canonicalURL, true);
                        $product->offsetSet('canonical_url', $canonicalURL, true);
                        $data['product'] = $product;
                    }
                } else {
                    // < 1.7.5
                    if ($product = Context::getContext()->smarty->getTemplateVars('product')) {
                        $product['canonicalURL'] = $canonicalURL;
                        $product['canonical_url'] = $canonicalURL;
                        $data['product'] = $product;
                    }
                }
                return null;
            }
        } elseif ($pageName == 'category') {
            $explodedCanonicalUrl = explode("?", $canonicalURL);
            if (count($explodedCanonicalUrl) > 0) {
                $canonicalURL = $explodedCanonicalUrl[0];
            }

            if ($this->category) {
                if ($page = Context::getContext()->smarty->getTemplateVars('page')) {
                    $page['canonical'] = null;
                    $page['canonical_seo'] = $canonicalURL;
                    $page['next'] = $canonicalURLNext;
                    $page['prev'] = $canonicalURLPrev;
                    $page['primero'] = $primero;
                    $page['ultimo'] = $ultimo;
                    $page['disabled'] = $disabled;
                    $page['id_category'] = $id_category;
                    $page['pagePrev'] = $pagePrev;
                    $page['pageNext'] = $pageNext;

                    $data = array_merge($data, array(
                        'page' => $page,
                        'page_name' => $pageName,
                        'ps_version' => Tools::substr(_PS_VERSION_, 0, 3),
                        'canonicalURL' => $canonicalURL,
                        'pagePrev' => $pagePrev,
                        'pageNext' => $pageNext,
                        'categoryActive' => $this->category,
                        'productActive' => $this->product,
                        'cmsActive' => $this->cms,
                        'id_category' => $id_category,
                        'primero' => $primero,
                        'ultimo' => $ultimo,
                        'otherActive' => $this->other,
                        'disabled' => $disabled,
                        'max' => $howmanypages,
                        'actual' => $page_actual
                    ));
                    return $this->module->render('canonical.tpl', $data);
                }
            } else {
                if ($page = Context::getContext()->smarty->getTemplateVars('page')) {
                    $page['canonical'] = null;
                    $page['canonical_seo'] = $canonicalURL;
                    $page['next'] = $canonicalURLNext;
                    $page['prev'] = $canonicalURLPrev;
                    $page['primero'] = $primero;
                    $page['ultimo'] = $ultimo;
                    $page['disabled'] = $disabled;
                    $page['id_category'] = $id_category;
                    $page['pagePrev'] = $pagePrev;
                    $page['pageNext'] = $pageNext;

                    $data = array_merge($data, array(
                        'page' => $page,
                        'page_name' => $pageName,
                        'ps_version' => Tools::substr(_PS_VERSION_, 0, 3),
                        'canonicalURL' => $canonicalURL,
                        'pagePrev' => $pagePrev,
                        'pageNext' => $pageNext,
                        'categoryActive' => $this->category,
                        'productActive' => $this->product,
                        'cmsActive' => $this->cms,
                        'id_category' => $id_category,
                        'primero' => $primero,
                        'ultimo' => $ultimo,
                        'otherActive' => $this->other,
                        'disabled' => $disabled,
                        'max' => $howmanypages,
                        'actual' => $page_actual
                    ));
                    return $this->module->render('canonical.tpl', $data);
                }
            }
        } elseif ($pageName == 'cms') {
            if ($this->cms) {
                if ($page = Context::getContext()->smarty->getTemplateVars('page')) {
                    $page['canonical'] = $canonicalURL;

                    Context::getContext()->smarty->assign(array(
                        'page' => $page,
                    ));
                }
            }
        } else {
            if ($this->other) {
                if ($page = Context::getContext()->smarty->getTemplateVars('page')) {
                    $page['canonical'] = $canonicalURL;
                    Context::getContext()->smarty->assign(array(
                        'page' => $page,
                    ));
                }
            }
        }
    }
}
