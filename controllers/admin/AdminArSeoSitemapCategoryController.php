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

include_once dirname(__FILE__).'/AdminArSeoSitemapController.php';
include_once dirname(__FILE__).'/../../classes/sitemap/models/ArSeoProSitemapCategory.php';
include_once dirname(__FILE__).'/../../classes/ArSeoProTools.php';

class AdminArSeoSitemapCategoryController extends AdminArSeoSitemapController
{
    public function ajaxProcessSwitch()
    {
        $id = Tools::getValue('id');
        $id_shop = Context::getContext()->shop->id;
        $sql = 'SELECT * FROM `' . ArSeoProSitemapCategory::getTableName() . '` WHERE id_shop=' . (int)$id_shop . ' AND id_category=' . (int)$id;
        if ($row = Db::getInstance()->getRow($sql)) {
            $model = new ArSeoProSitemapCategory($row['id_sitemap']);
        } else {
            $model = new ArSeoProSitemapCategory();
            $model->id_category = (int)$id;
            $model->id_shop = (int)$id_shop;
        }
        $model->updated_at = date('Y-m-d H:i:s');
        $model->export = $model->export? 0 : 1;
        die(ArSeoProTools::jsonEncode(array(
            'success' => $model->save(false),
            'status' => $model->export,
            'text' => $this->l('Status updated')
        )));
    }
    
    public function ajaxProcessCheckAll()
    {
        $ids = $this->filterIdList(Tools::getValue('ids'));
        foreach ($ids as $id) {
            $id_shop = Context::getContext()->shop->id;
            $date = date('Y-m-d H:i:s');

            $sql = 'SELECT * FROM `' . ArSeoProSitemapCategory::getTableName() . '` WHERE id_category=' . (int)$id . ' AND id_shop=' . (int)$id_shop;
            if ($row = Db::getInstance()->getRow($sql)) {
                $model = new ArSeoProSitemapCategory($row['id_sitemap']);
            } else {
                $model = new ArSeoProSitemapCategory();
            }

            $model->id_category = (int)$id;
            $model->id_shop = (int)$id_shop;
            $model->export = 1;
            $model->updated_at = $date;
            $model->save();
        }
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'text' => $this->l('Status updated')
        )));
    }
    
    public function ajaxProcessUncheckAll()
    {
        ArSeoProSitemapCategory::truncate();
        die(ArSeoProTools::jsonEncode(array(
            'success' => 1,
            'text' => $this->l('Status updated')
        )));
    }
}
