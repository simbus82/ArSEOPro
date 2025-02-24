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

/**
 * @property ArSeoPro $module
 */
abstract class ArSeoModel
{
    protected $errors = array();
    protected $module;
    protected $owner;
    protected $configPrefix = null;
    protected $defaultAttributeType = 'text';
    
    protected $isLoaded = false;


    public function __construct($module, $configPrefix = null, $owner = null)
    {
        $this->module = $module;
        $this->owner = $owner;
        $this->configPrefix = $configPrefix;
    }
    
    protected function l($string, $specific = false)
    {
        return $this->module->l($string, $specific);
    }
    
    public function beforeSave()
    {
        return true;
    }
    
    public function isLoaded()
    {
        return $this->isLoaded;
    }
    
    public function attributeLabels()
    {
        return array();
    }
    
    public function attributeHints()
    {
        return array();
    }
    
    public function attributeDescriptions()
    {
        return array();
    }
    
    public function getMultiLangAttribute($attribute)
    {
        $labels = $this->multiLangFields();
        if (isset($labels[$attribute])) {
            return $labels[$attribute];
        }
        return false;
    }
    
    public function isFile($attribute)
    {
        return $this->getAttributeType($attribute) == 'file';
    }
    
    public function multiLangFields()
    {
        return array();
    }
    
    public function rules()
    {
        return array();
    }
    
    public function filters()
    {
        return array();
    }
    
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        if (isset($labels[$attribute])) {
            return $labels[$attribute];
        }
        return $attribute;
    }
    
    public function getAttributeHint($attribute)
    {
        $hints = $this->attributeHints();
        if (isset($hints[$attribute])) {
            return $hints[$attribute];
        }
        return null;
    }
    
    public function getAttributeDescription($attribute)
    {
        $descriptions = $this->attributeDescriptions();
        if (isset($descriptions[$attribute])) {
            return $descriptions[$attribute];
        }
        return null;
    }
    
    public function isAttributeSafe($attribute)
    {
        $rules = $this->rules();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0]) && $rule[1] != 'unsafe') {
                return true;
            }
        }
        return false;
    }
    
    public function isAttributeRequired($attribute)
    {
        $rules = $this->rules();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0]) && $rule[1] == 'validateRequired') {
                return true;
            }
        }
        return false;
    }
    
    public function getAttributes()
    {
        $attributes = array();
        foreach ($this as $attribute => $value) {
            if ($this->isAttributeSafe($attribute)) {
                $attributes[$attribute] = $value;
            }
        }
        return $attributes;
    }
    
    public function saveToConfig($runValidation = true, $attributes = array())
    {
        if ($this->beforeSave() && (($runValidation && $this->validate()) || !$runValidation)) {
            $languages = Language::getLanguages(true);
            foreach ($this->getAttributes() as $attr => $value) {
                if (($attributes && in_array($attr, $attributes)) || empty($attributes)) {
                    $a = $this->getConfigAttribueName($attr, false);
                    if ($this->getMultipleSelect($attr)) {
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                    }
                    if ($this->getMultiLangAttribute($attr)) {
                        $values = array();
                        foreach ($languages as $lang) {
                            $values[$lang['id_lang']] = $value[$lang['id_lang']];
                        }
                        Configuration::updateValue($a, $values);
                    } elseif ($this->isFile($attr) && isset($_FILES[$a])) {
                        $this->saveFile($attr, $_FILES[$a]);
                    } else {
                        if ($mirror = $this->getMirroredAttribute($attr)) {
                            Configuration::updateValue($mirror, $value);
                        } else {
                            Configuration::updateValue($a, $value);
                        }
                    }
                }
            }
            $this->afterSave();
            return true;
        }
        return false;
    }
    
    public function afterSave()
    {
        return true;
    }
    
    public function validateImage($value, $params)
    {
        $a = $this->getConfigAttribueName($params['attribute'], false);
        if (!isset($_FILES) || !isset($_FILES[$a])) {
            return true;
        }
        $file = $_FILES[$a];
        if ((isset($file['tmp_name']) && $file['tmp_name']) && file_exists($file['tmp_name']) && $this->isAllowedMimeType($file, $params) && $this->isAllowedFileSize($file, $params) && $this->isAllowedDimensions($file, $params)) {
            return true;
        }
        if ((!isset($file['tmp_name']) || empty($file['tmp_name']))) {
            return true;
        }
        return false;
    }
    
    public function allowedMimeTypes()
    {
        return array();
    }
    
    public function isAllowedMimeType($file, $params)
    {
        $mimetypes = isset($params['mime'])? $params['mime'] : array();
        if (empty($mimetypes)) {
            return true;
        }
        $mimetype = mime_content_type($file['tmp_name']);
        if (in_array($mimetype, $mimetypes)) {
            return true;
        }
        return false;
    }
    
    public function isAllowedDimensions($file, $params)
    {
        $dimensions = isset($params['dimensions'])? $params['dimensions'] : array();
        if (empty($dimensions)) {
            return true;
        }
        list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);
        $dimension = round($width / $height, 2);
        if (in_array($dimension, $dimensions)) {
            return true;
        }
        return false;
    }
    
    public function isAllowedFileSize($file, $params)
    {
        $allowedSizes = isset($params['size'])? $params['size'] : array();
        if (empty($allowedSizes)) {
            return true;
        }
        list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);
        $size = $width . ':' . $height;
        
        if (in_array($size, $allowedSizes)) {
            return true;
        }
        if (in_array('*:*', $allowedSizes)) {
            return true;
        }
        
        if (isset($allowedSizes['min'])) {
            $min = explode('x', $allowedSizes['min']);
            $minWidth = $min[0];
            $minHeight = $min[1];
            if ($width >= $minWidth && $height >= $minHeight) {
                return true;
            }
        }
        return false;
    }
    
    public function saveFile($attribute, $file)
    {
        $a = $this->getConfigAttribueName($attribute, false);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('favicon_') . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $this->module->getUploadPath() . '/' . $filename)) {
            Configuration::updateValue($a, $filename);
        }
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function addError($attribute, $error)
    {
        if (isset($this->errors[$attribute])) {
            $this->errors[$attribute][] = $error;
        } else {
            $this->errors[$attribute] = array($error);
        }
    }
    
    public function filter()
    {
        foreach ($this->getAttributes() as $attr => $value) {
            if ($filters = $this->getAttributeFilters($attr)) {
                foreach ($filters as $filter) {
                    $method = $filter['filter'];
                    if ($this->getMultiLangAttribute($attr) && is_array($value)) {
                        foreach ($value as $k => $v) {
                            if (method_exists($this, $method)) {
                                $this->$attr[$k] = $this->$method($v, $filter['params']);
                            }
                        }
                    } else {
                        if (method_exists($this, $method)) {
                            $this->$attr = $this->$method($value, $filter['params']);
                        }
                    }
                }
            }
        }
    }
    
    public function validate($addErrors = true)
    {
        if ($addErrors) {
            $this->errors = array();
        }
        $this->filter();
        $valid = true;
        foreach ($this->getAttributes() as $attr => $value) {
            if ($validators = $this->getAttributeValidators($attr)) {
                foreach ($validators as $validator) {
                    $method = $validator['validator'];
                    $params = isset($validator['params'])? $validator['params'] : array();
                    $params['attribute'] = $attr;
                    if ((isset($validator['on']) && $validator['on']) || (!isset($validator['on']) || $validator['on'] === null)) {
                        if (method_exists('Validate', $method)) {
                            if ($this->getMultiLangAttribute($attr) && is_array($value)) {
                                foreach ($value as $v) {
                                    if (Validate::$method($v)) {
                                        $valid = $valid && Validate::$method($v);
                                    } else {
                                        if ($addErrors) {
                                            if (isset($validator['message'])) {
                                                $this->addError($attr, $this->getMessage($validator['message'], $attr, $value));
                                            } else {
                                                $this->addError($attr, sprintf($this->l('Incorrect "%s" value', 'ArSeoModel'), $this->getAttributeLabel($attr)));
                                            }
                                        }
                                        $valid = false;
                                    }
                                }
                            } else {
                                if (Validate::$method($value)) {
                                    $valid = $valid && Validate::$method($value);
                                } else {
                                    if ($addErrors) {
                                        if (isset($validator['message'])) {
                                            $this->addError($attr, $this->getMessage($validator['message'], $attr, $value));
                                        } else {
                                            $this->addError($attr, sprintf($this->l('Incorrect "%s" value', 'ArSeoModel'), $this->getAttributeLabel($attr)));
                                        }
                                    }
                                    $valid = false;
                                }
                            }
                        } elseif (method_exists($this, $method)) {
                            if ($this->$method($value, $params)) {
                                $valid = $valid && $this->$method($value, $params);
                            } else {
                                if ($addErrors) {
                                    if (isset($validator['message'])) {
                                        $this->addError($attr, $this->getMessage($validator['message'], $attr, $value));
                                    } else {
                                        $this->addError($attr, sprintf($this->l('Incorrect "%s" value', 'ArSeoModel'), $this->getAttributeLabel($attr)));
                                    }
                                }
                                $valid = false;
                            }
                        }
                    } else {
                        $valid = $valid && true;
                    }
                }
            }
        }
        return $valid;
    }
    
    protected function filterStripTags($value, $params = array())
    {
        if (isset($params['allowedTags']) && $params['allowedTags']) {
            return strip_tags($value, $params['allowedTags']);
        }
        return strip_tags($value);
    }
    
    public function interval($value, $params)
    {
        $data = explode('-', $value);
        foreach ($data as $v) {
            if (!Validate::isInt($v)) {
                return false;
            }
        }
        if (count($data) > 2) {
            return false;
        }
        foreach ($data as $v) {
            if (isset($params['min'])) {
                if ($v < $params['min']) {
                    return false;
                }
            }
            if (isset($params['max'])) {
                if ($v > $params['max']) {
                    return false;
                }
            }
        }
        return true;
    }


    protected function integer($value, $params = array())
    {
        if (Validate::isInt($value)) {
            if (isset($params['min']) && $value >= $params['min']) {
                if (isset($params['max']) && $value <= $params['max']) {
                    return true;
                } elseif (!isset($params['max'])) {
                    return true;
                }
            }
        }
        return false;
    }


    protected function getMessage($message, $attribute, $value)
    {
        return strtr($message, array(
            '{attribute}' => $attribute,
            '{label}' => $this->getAttributeLabel($attribute),
            '{value}' => $value
        ));
    }


    public function getAttributeValidators($attribute)
    {
        $rules = $this->rules();
        $validators = array();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0]) && $rule[1] != 'unsafe') {
                $validator = array(
                    'validator' => $rule[1],
                    'params' => isset($rule['params'])? $rule['params'] : array(),
                    'message' => isset($rule['message'])? $rule['message'] : null,
                );
                if (isset($rule['on'])) {
                    $validator['on'] = $rule['on'];
                }
                $validators[] = $validator;
            }
        }
        return $validators;
    }
    
    public function getAttributeFilters($attribute)
    {
        $rules = $this->filters();
        $filters = array();
        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1]) && in_array($attribute, $rule[0])) {
                $filter = array(
                    'filter' => $rule[1],
                    'params' => isset($rule['params'])? $rule['params'] : array()
                );
                if (isset($rule['on'])) {
                    $filter['on'] = $rule['on'];
                }
                $filters[] = $filter;
            }
        }
        return $filters;
    }
    
    public function loadFromConfig()
    {
        $attributes = array();
        $multiLangs = array();
        foreach ($this->getAttributeNames() as $attr) {
            if (!$this->getMultiLangAttribute($attr)) {
                $attributes[] = $this->getConfigAttribueName($attr, false);
            } else {
                $multiLangs[] = $this->getConfigAttribueName($attr, false);
            }
        }
        if ($attributes) {
            $config = Configuration::getMultiple($attributes);
            if ($config) {
                foreach ($config as $k => $v) {
                    $a = $this->getModelAttributeName($k);
                    if ($this->isAttributeSafe($a)) {
                        if ($this->getMultipleSelect($a)) {
                            $this->$a = explode(',', $v);
                        } else {
                            $this->$a = $v;
                        }
                    }
                }
            }
        }
        if ($multiLangs) {
            $languages = Language::getLanguages(true);
            foreach ($multiLangs as $attr) {
                $values = array();
                foreach ($languages as $lang) {
                    $values[$lang['id_lang']] = Configuration::get($attr, $lang['id_lang']);
                }
                
                $a = $this->getModelAttributeName($attr);
                if ($this->isAttributeSafe($a)) {
                    $this->$a = $values;
                }
            }
        }
        $this->isLoaded = true;
    }
    
    public function populate()
    {
        $languages = Language::getLanguages(true);
        $attributes = array();
        foreach ($this->getAttributes() as $attribute => $value) {
            $name = $this->getConfigAttribueName($attribute, false);
            if ($this->getMultiLangAttribute($attribute)) {
                foreach ($languages as $lang) {
                    $n = $name . '_' . $lang['id_lang'];
                    if (Tools::isSubmit(get_class($this))) {
                        $attributes[$attribute][$lang['id_lang']] = Tools::getValue($n);
                        //$this->$attribute[$lang['id_lang']] = Tools::getValue($n);
                    } else {
                        $attributes[$attribute] = $value;
                        //$this->$attribute = $value;
                    }
                }
            } else {
                if (!$this->isFile($attribute)) {
                    $attributes[$attribute] = Tools::getValue($name, $value);
                }
                //$this->$attribute = Tools::getValue($name, $value);
            }
        }
        foreach ($attributes as $attr => $value) {
            $this->$attr = $value;
        }
    }
    
    public function isAttributeHasErrors($attribute)
    {
        if (isset($this->errors[$attribute])) {
            return true;
        }
        return false;
    }
    
    public function getModelAttributeName($attribute)
    {
        $mirrors = array_flip($this->mirroredAttributes());
        if (isset($mirrors[$attribute])) {
            return $mirrors[$attribute];
        }
        $attr = Tools::strtolower($attribute);
        if ($this->configPrefix) {
            return str_replace($this->configPrefix, '', $attr);
        }
        return $attr;
    }
    
    public function getConfigAttribueName($attribute, $multi = true)
    {
        if ($this->getMirroredAttribute($attribute)) {
            return $this->getMirroredAttribute($attribute);
        }
        if ($this->getMultipleSelect($attribute) && $multi && $this->getAttributeType($attribute) != 'html') {
            $multi = '[]';
        } else {
            $multi = '';
        }
        $attribute = $this->configPrefix . $attribute . $multi;
        return Tools::strtoupper($attribute);
    }
    
    public function validateRequired($value)
    {
        return !empty($value);
    }
    
    public function getFormHelperConfig()
    {
        $config = array();
        foreach ($this->getAttributeNames() as $attr) {
            $name = $this->getConfigAttribueName($attr);
            $config[$name] = array(
                'type' => $this->getAttributeType($attr),
                'label' => $this->getAttributeLabel($attr),
                'multiple' => $this->getMultipleSelect($attr),
                'id' => $name,
                'name' => $name,
                'prefix' => $this->getFieldPrefix($attr),
                'suffix' => $this->getFieldSuffix($attr),
                'lang' => $this->getMultiLangAttribute($attr),
                'placeholder' => $this->getAttributePlaceholder($attr),
                'form_group_class' => $this->getFormGroupClass($attr),
                'hint' => $this->getAttributeHint($attr),
                'desc' => $this->getAttributeDescription($attr),
                'required' => $this->isAttributeRequired($attr)
            );
            if ($this->getAttributeType($attr) == 'switch') {
                $config[$name]['values'] = array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled', 'ArSeoModel'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled', 'ArSeoModel'),
                    )
                );
            }
            if ($this->getAttributeType($attr) == 'checkbox') {
                $config[$name]['values'] = array(
                    'query' => $this->getCheckboxOptions($attr),
                    'id' => 'id',
                    'name' => 'name'
                );
            }
            if ($this->getAttributeType($attr) == 'html') {
                $config[$name]['html_content'] = $this->getHtmlField($attr);
                $config[$name]['name'] = $this->getHtmlField($attr);
            }
            if ($this->getAttributeType($attr) == 'select') {
                if ($this->getGroupedSelect($attr)) {
                    $config[$name]['options'] = array(
                        'optiongroup' => array(
                            'query' =>  $this->getSelectOptions($attr),
                            'label' => 'name'
                        ),
                        'options' => array(
                            'query' => 'items',
                            'id' => 'id',
                            'name' => 'name'
                        )
                    );
                } else {
                    $config[$name]['options'] = array(
                        'query' => $this->getSelectOptions($attr),
                        'id' => 'id',
                        'name' => 'name',
                    );
                }
            }
        }
        return $config;
    }
    
    public function getFieldPrefix($attribute)
    {
        $prefix = $this->fieldPrefix();
        if (isset($prefix[$attribute])) {
            return $prefix[$attribute];
        }
        return null;
    }
    
    public function fieldPrefix()
    {
        return array();
    }
    
    public function getFieldSuffix($attribute)
    {
        $suffix = $this->fieldSuffix();
        if (isset($suffix[$attribute])) {
            return $suffix[$attribute];
        }
        return null;
    }
    
    public function fieldSuffix()
    {
        return array();
    }
    
    public function getHtmlField($attribute)
    {
        $pls = $this->htmlFields();
        if (isset($pls[$attribute])) {
            return $pls[$attribute];
        }
        return null;
    }
    
    public function htmlFields()
    {
        return array();
    }
    
    public function groupedSelects()
    {
        return array();
    }
    
    public function getGroupedSelect($attribute)
    {
        $pls = $this->groupedSelects();
        if (isset($pls[$attribute])) {
            return $pls[$attribute];
        }
        return null;
    }
    
    public function mirroredAttributes()
    {
        return array();
    }
    
    public function getMirroredAttribute($attribute)
    {
        $attrs = $this->mirroredAttributes();
        return isset($attrs[$attribute])? $attrs[$attribute] : false;
    }
    
    public function multipleSelects()
    {
        return array();
    }
    
    public function getMultipleSelect($attribute)
    {
        $pls = $this->multipleSelects();
        if (isset($pls[$attribute])) {
            return $pls[$attribute];
        }
        return null;
    }
    
    public function attributePlaceholders()
    {
        return array();
    }
    
    public function getAttributePlaceholder($attribute)
    {
        $pls = $this->attributePlaceholders();
        if (isset($pls[$attribute])) {
            return $pls[$attribute];
        }
        return null;
    }

    public function getCheckboxOptions($attribute)
    {
        $method = Tools::toCamelCase("{$attribute}CheckboxOptions");
        if (method_exists(get_called_class(), $method)) {
            return $this->$method();
        }
    }

    public function getSelectOptions($attribute)
    {
        $method = Tools::toCamelCase("{$attribute}SelectOptions");
        if (method_exists(get_called_class(), $method)) {
            return $this->$method();
        }
    }
    
    public function getFormTitle()
    {
        return null;
    }
    
    public function getFormIcon()
    {
        return 'icon-cog';
    }
    
    public function attributeTypes()
    {
        return array();
    }
    
    public function getAttributeType($attribute)
    {
        $types = $this->attributeTypes();
        if (isset($types[$attribute])) {
            return $types[$attribute];
        }
        return $this->defaultAttributeType;
    }
    
    public function getFormGroupClass($attr)
    {
        $addClass = 'field_' . Tools::strtolower($attr);
        if ($this->getAddCssClass($attr)) {
            $addClass .= (' ' . $this->getAddCssClass($attr));
        }
        return $this->isAttributeHasErrors($attr)? ('has-error ' . $addClass) : $addClass;
    }
    
    public function getAddCssClass($attribute)
    {
        $classes = $this->attributeCssClasses();
        if (isset($classes[$attribute])) {
            return $classes[$attribute];
        }
    }
    
    public function attributeCssClasses()
    {
        return array();
    }
    
    public function attributeDefaults()
    {
        return array();
    }
    
    public function getAttributeDefault($attribute)
    {
        $defaults = $this->attributeDefaults();
        $value = isset($defaults[$attribute])? $defaults[$attribute] : null;
        if ($this->getMultiLangAttribute($attribute)) {
            $languages = Language::getLanguages(true);
            $values = array();
            foreach ($languages as $lang) {
                $values[$lang['id_lang']] = $value;
            }
            return $values;
        }
        
        return $value;
    }
    
    public function loadAttributeDefault($attribute)
    {
        if (!empty($attribute)) {
            $this->$attribute = $this->getAttributeDefault($attribute);
        }
    }
    
    public function loadDefaults()
    {
        foreach ($this->getAttributeNames() as $attribute) {
            $this->loadAttributeDefault($attribute);
        }
    }
    
    public function clearConfig()
    {
        foreach ($this->getAttributeNames() as $attribute) {
            $a = $this->getConfigAttribueName($attribute, false);
            Configuration::deleteByName($a);
        }
    }
    
    public function getAttributeNames()
    {
        return array_keys($this->getAttributes());
    }
}
