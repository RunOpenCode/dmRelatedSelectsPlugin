<?php

/**
 * @author TheCelavi
 */
class sfWidgetFormDoctrineRelatedSelect extends sfWidgetFormDoctrineChoice
{
    protected static $i18n;

    public function __construct($options = array(), $attributes = array())
    {
        if (!self::$i18n) {
            self::$i18n = dmContext::getInstance()->getServiceContainer()->getService('i18n');
        }
        parent::__construct($options, $attributes);
    }

    public function configure($options = array(), $attributes = array())
    {
        /**
         * This is the name of the group of related selects. 
         * The group name must be unique within the context.
         */
        $this->addRequiredOption('group');
        /**
         * This is the index of the form widget. 
         * The index is zero (0) based and must be nicely ordered.
         * That means that first parent select have to have index 1, second 2, and so on...
         */
        $this->addRequiredOption('index');
        /**
         * Parent alias which filters the elements of this widgets based on ID
         * of parent. Per example, we have Category->Product. If this is widget
         * for the Product, the parent is alias in defined relation, that is,
         * Category
         */
        $this->addOption('parent', null);
        /**
         * When fetching the objects for populating the select, should
         * `is_active = true` be added in SELECT query?
         */
        $this->addOption('active_only', false);        
        parent::configure($options, $attributes);
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $data = array(
            'group' => $this->getOption('group'),
            'index' => $this->getOption('index'),
            'model' => $this->getOption('model'),
            'active_only' => $this->getOption('active_only')
        );
        if (!is_null($this->getOption('parent'))) {
            $data['parent'] = $this->getOption('parent');
        }
        if ($this->getOption('key_method') != 'getPrimaryKey') {
            $data['key_method'] = $this->getOption('key_method');
        }
        if ($this->getOption('method') != '__toString') {
            $data['label_method'] = $this->getOption('label_method');
        }
        if ($this->getOption('table_method')) {
            $data['table_method'] = $this->getOption('table_method');
        }
                
        if (isset($attributes['class'])){
            $attributes['class'] .= ' sfWidgetFormDoctrineRelatedSelect ' . str_replace('"', "'", json_encode($data));
        } else {
            $attributes['class'] = 'sfWidgetFormDoctrineRelatedSelect ' . str_replace('"', "'", json_encode($data));
        }
        return parent::render($name, $value, $attributes, $errors);        
    }
    
    public function getJavaScripts()
    {
        return array_merge(
            parent::getJavaScripts(),
            array(
                'dmRelatedSelectsPlugin.relatedSelects',
                'dmRelatedSelectsPlugin.launch'
            )
        );
    }
}
