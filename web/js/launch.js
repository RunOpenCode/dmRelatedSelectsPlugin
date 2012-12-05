(function($){
    
    function initDmRelatedSelectsPlugin($context) {
        var $selects = $context.find('select.sfWidgetFormDoctrineRelatedSelect');
        if ($selects.length == 0) return; // There is no selects to init
        
        // Parse groups to init
        var groups = [];
        $.each($selects, function(){
            var data = $(this).metadata();
            if (!groups[data.group]) {
                groups[data.group] = [];
            };
            groups[data.group][data.index] = $(this).prop('name');
        });
        
        // Init related selects groups
        for (var group in groups){
            
            // It is required to have default values selected...
            var defaultValues = [];
            for (var i=groups[group].length - 1 ; i>-1; i--) {
                defaultValues.push($context.find('select[name="' + groups[group][i] + '"]').val());
            };
            
            var parseDefaultSelected = function() {
                var $select = $(this);
                if (defaultValues == null) return; 
                var val = defaultValues.pop();
                if (val) {
                    $select.val(val);
                    $select.change();
                } else {
                    defaultValues = null;
                };
            };
            // In charge to filter all in related selects...
            $context.relatedSelects({
                onChangeLoad: dm_configuration.relative_url_root + dm_configuration.script_name + 'dm-utils/related-selects',
                selects: groups[group],
                onPopulate: parseDefaultSelected
            });  
            
            if (defaultValues[defaultValues.length-1] != '') {
                defaultValues.pop();
                $context.find('select[name="' + groups[group][0] + '"]').change();
            } else {
                defaultValues = null;
            };
            
        };
    };
        
    // Admin backend
    if ($('#dm_admin_content').length >0) {
        initDmRelatedSelectsPlugin($('#dm_admin_content'));
    };
    
    // Widget
    $('#dm_page div.dm_widget').bind('dmWidgetLaunch', function() {
        initDmRelatedSelectsPlugin($(this));
    });

    // Admin frontend
    $('div.dm.dm_widget_edit_dialog_wrap').live('dmAjaxResponse', function() {
        initDmRelatedSelectsPlugin($(this));
    });
    
})(jQuery);