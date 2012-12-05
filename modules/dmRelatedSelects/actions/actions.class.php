<?php

/**
 * @author TheCelavi
 */
class dmRelatedSelectsActions extends dmBaseActions
{
    public function executeIndex(dmWebRequest $request)
    {
        // Get params
        $params = $request->getParameterHolder()->getAll();
        // Remove unwanted params
        if (isset($params['dm_cpi'])) {
            unset($params['dm_cpi']);
        }
        if (isset($params['dm_xhr'])) {
            unset($params['dm_xhr']);
        }
        if (isset($params['module'])) {
            unset($params['module']);
        }
        if (isset($params['action'])) {
            unset($params['action']);
        }
        
        // Ok, now here goes the magic...
        // We know that fields can be given in form
        // But we do not know if the form fields are given as form[field] or just
        // field without array.
        // 
        // So we have to detect it
        $sortedFields = array();
        
        foreach($params as $key=>$val) {
            if (isset($val['metadata'])) { // these are just form fields
                $sortedFields[$val['metadata']['index']] = $val; // TODO Check this, maybe has a bug...?
            } else { // these are the fields serialized from form
                foreach ($val as $k=>$v) {
                    $sortedFields[$v['metadata']['index']] = $v;                    
                }
            }
        }
        
        // We only need two fields
        // The one that we are fetching for (highest index)
        $seekField = $sortedFields[count($sortedFields)-1];
        // and its parent
        $parentField = $sortedFields[count($sortedFields)-2];
        
        // These are some settings....
        $culture = dmContext::getInstance()->getUser()->getCulture();
        $key_method = isset($seekField['metadata']['key_method']) ? $seekField['metadata']['key_method'] : 'getPrimaryKey';
        $label_method = isset($seekField['metadata']['label_method']) ? $seekField['metadata']['label_method'] : '__toString';
        
        $table = dmDb::table($seekField['metadata']['model']);
        $results = null;
        
        if (isset($seekField['metadata']['table_method'])) {
            // There is no need for anything to do, just call method and pass the parent id value
            $results = $this->parseObjectsToArray($table->{$seekField['metadata']['table_method']}($parentField['val']), $key_method, $label_method);
        } else {
            // Now, we have a fields and it is required to create a query that will
            // fetch objects that we are looking for...
            $query = Doctrine_Query::create();
            $query->from(sprintf('%s SA', $seekField['metadata']['model']));
            // Is i18n?
            if ($table->hasI18n()) {
                $query->innerJoin('SA.Translation SATranslation');
                $query->andWhere('SATranslation.lang = ?', $culture);                
            }
            // Use only where is_active = true?
            if ($seekField['metadata']['active_only'] && $table->hasColumn('is_active')) {
                if ($table->isI18nColumn('is_active')) {
                    $query->andWhere('SATranslation.is_active = ?', true);
                } else {
                    $query->andWhere('SA.is_active = ?', true);
                }
            }
            // Join parent
            $query->innerJoin(sprintf('SA.%s Parent', $seekField['metadata']['parent']));
            // Filter by parent
            $query->andWhere('Parent.id = ?', $parentField['val']);
            // Execute and fetch results
            $results = $this->parseObjectsToArray($query->execute(), $key_method, $label_method);
        }        
        return $this->renderJson($results);
    }
    
    protected function parseObjectsToArray($objects, $key_method, $label_method) {
        $result = array();
        foreach ($objects as $obj) {
            $result[$obj->{$key_method}()] = $obj->{$label_method}();
        }  
        return $result;
    }
    
}
