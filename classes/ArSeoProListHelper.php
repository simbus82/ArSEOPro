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

class ArSeoProListHelper extends HelperList
{
    public $shopLinkType;
    
    public $currentPage = 0;
    
    public $filters = array();
    
    public $checkedIds = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->override_folder = 'arseopro/';
        $this->no_link = true;
    }
    
    public function setDefaultPagination($value)
    {
        $this->_default_pagination = $value;
    }
    
    public function getDefaultPagination()
    {
        return $this->_default_pagination;
    }
    
    public function setPagination($value)
    {
        $this->_pagination = $value;
    }
    
    public function getPagination()
    {
        return $this->_pagination;
    }
    
    /**
     * Display list header (filtering, pagination and column names)
     */
    public function displayListHeader()
    {
        if (!isset($this->list_id)) {
            $this->list_id = $this->table;
        }

        $id_cat = (int)Tools::getValue('id_'.($this->is_cms ? 'cms_' : '').'category');

        $token = $this->token;

        /* Determine total page number */
        $pagination = $this->_default_pagination;
        if (in_array((int)Tools::getValue($this->list_id.'_pagination'), $this->_pagination)) {
            $pagination = (int)Tools::getValue($this->list_id.'_pagination');
        } elseif (isset($this->context->cookie->{$this->list_id.'_pagination'}) && $this->context->cookie->{$this->list_id.'_pagination'}) {
            $pagination = $this->context->cookie->{$this->list_id.'_pagination'};
        }

        $total_pages = max(1, ceil($this->listTotal / $pagination));

        $identifier = Tools::getIsset($this->identifier) ? '&'.$this->identifier.'='.(int)Tools::getValue($this->identifier) : '';
        $order = '';
        if (Tools::getIsset($this->table.'Orderby')) {
            $order = '&'.$this->table.'Orderby='.urlencode($this->orderBy).'&'.$this->table.'Orderway='.urlencode(Tools::strtolower($this->orderWay));
        }

        $action = $this->currentIndex.$identifier.'&token='.$token.'#'.$this->list_id;

        /* Determine current page number */
        $page = (int)Tools::getValue('submitFilter'.$this->list_id);

        if (!$page) {
            $page = $this->currentPage;
        }
        
        if (!$page) {
            $page = 1;
        }

        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $this->page = (int)$page;

        /* Choose number of results per page */
        $selected_pagination = Tools::getValue(
            $this->list_id.'_pagination',
            isset($this->context->cookie->{$this->list_id.'_pagination'}) ? $this->context->cookie->{$this->list_id.'_pagination'} : $this->_default_pagination
        );

        if (!isset($this->table_id) && $this->position_identifier && (int)Tools::getValue($this->position_identifier, 1)) {
            $this->table_id = Tools::substr($this->identifier, 3, Tools::strlen($this->identifier));
        }

        if ($this->position_identifier && ($this->orderBy == 'position' && $this->orderWay != 'DESC')) {
            $table_dnd = true;
        }

        $prefix = isset($this->controller_name) ? str_replace(array('admin', 'controller'), '', Tools::strtolower($this->controller_name)) : '';
        $ajax = false;
        foreach ($this->fields_list as $key => $params) {
            if (!isset($params['type'])) {
                $params['type'] = 'text';
            }

            $value_key = $prefix.$this->list_id.'Filter_'.(array_key_exists('filter_key', $params) ? $params['filter_key'] : $key);
            if ($key == 'active' && strpos($key, '!') !== false) {
                $keys = explode('!', $params['filter_key']);
                $value_key = $keys[1];
            }
            $value = Context::getContext()->cookie->{$value_key};
            if (!$value && Tools::getIsset($value_key)) {
                $value = Tools::getValue($value_key);
            }
            if (isset($params['filter_key']) && !empty($params['filter_key']) && isset($this->filters[$params['filter_key']])) {
                $value = $this->filters[$params['filter_key']];
            }
            switch ($params['type']) {
                case 'bool':
                    if (isset($params['ajax']) && $params['ajax']) {
                        $ajax = true;
                    }
                    break;

                case 'date':
                case 'datetime':
                    if (is_string($value)) {
                        $value = Tools::unSerialize($value);
                    }
                    if (!Validate::isCleanHtml($value[0]) || !Validate::isCleanHtml($value[1])) {
                        $value = '';
                    }
                    $name = $this->list_id.'Filter_'.(isset($params['filter_key']) ? $params['filter_key'] : $key);
                    $name_id = str_replace('!', '__', $name);

                    $params['id_date'] = $name_id;
                    $params['name_date'] = $name;

                    $this->context->controller->addJqueryUI('ui.datepicker');
                    break;

                case 'select':
                    foreach ($params['list'] as $option_value => $option_display) {
                        if (isset(Context::getContext()->cookie->{$prefix.$this->list_id.'Filter_'.$params['filter_key']})
                            && Context::getContext()->cookie->{$prefix.$this->list_id.'Filter_'.$params['filter_key']} == $option_value
                            && Context::getContext()->cookie->{$prefix.$this->list_id.'Filter_'.$params['filter_key']} != '') {
                            $this->fields_list[$key]['select'][$option_value]['selected'] = 'selected';
                        }
                    }
                    break;

                case 'text':
                    if (!Validate::isCleanHtml($value)) {
                        $value = '';
                    }
            }

            $params['value'] = $value;
            $this->fields_list[$key] = $params;
        }

        $has_value = false;
        $has_search_field = false;

        foreach ($this->fields_list as $key => $field) {
            if (isset($field['value']) && $field['value'] !== false && $field['value'] !== '') {
                if (is_array($field['value']) && trim(implode('', $field['value'])) == '') {
                    continue;
                }

                $has_value = true;
                break;
            }
            if (!(isset($field['search']) && $field['search'] === false)) {
                $has_search_field = true;
            }
        }

        Context::getContext()->smarty->assign(array(
            'page' => $page,
            'simple_header' => $this->simple_header,
            'total_pages' => $total_pages,
            'selected_pagination' => $selected_pagination,
            'pagination' => $this->_pagination,
            'list_total' => $this->listTotal,
            'sql' => isset($this->sql) && $this->sql ? str_replace('\n', ' ', str_replace('\r', '', $this->sql)) : false,
            'token' => $this->token,
            'table' => $this->table,
            'bulk_actions' => $this->bulk_actions,
            'show_toolbar' => $this->show_toolbar,
            'toolbar_scroll' => $this->toolbar_scroll,
            'toolbar_btn' => $this->toolbar_btn,
            'has_bulk_actions' => $this->hasBulkActions($has_value),
            'filters_has_value' => (bool)$has_value
        ));
        
        $isMultiShopActive = Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
        
        $this->header_tpl->assign(array_merge(array(
            'ajax' => $ajax,
            'title' => array_key_exists('title', $this->tpl_vars) ? $this->tpl_vars['title'] : $this->title,
            'show_filters' => ((count($this->_list) > 1 && $has_search_field) || $has_value),
            'currentIndex' => $this->currentIndex,
            'action' => $action,
            'is_order_position' => $this->position_identifier && $this->orderBy == 'position',
            'order_way' => $this->orderWay,
            'order_by' => $this->orderBy,
            'fields_display' => $this->fields_list,
            'delete' => in_array('delete', $this->actions),
            'identifier' => $this->identifier,
            'id_cat' => $id_cat,
            'shop_link_type' => $this->shopLinkType,
            'multishop_active' => $isMultiShopActive,
            'has_actions' => !empty($this->actions),
            'table_id' => isset($this->table_id) ? $this->table_id : null,
            'table_dnd' => isset($table_dnd) ? $table_dnd : null,
            'name' => isset($name) ? $name : null,
            'name_id' => isset($name_id) ? $name_id : null,
            'row_hover' => $this->row_hover,
            'list_id' => isset($this->list_id) ? $this->list_id : $this->table
        ), $this->tpl_vars));

        return $this->header_tpl->fetch();
    }
}
