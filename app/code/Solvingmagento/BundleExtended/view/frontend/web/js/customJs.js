define([
    "jquery",
    "jquery/ui"
], function($) {
    "use strict";

    $.widget('mage.customJs', {
        _create: function() {
            this.element.on('change', function(e){
               if($(this).closest(".field.option").is(':has("#hidden")')){
                  $(this).closest(".field.option").find("#hidden").trigger('change');
               }else{
                   $(this).closest(".field.choice").find(".checkbox").trigger('change');
               }
            });
        }
 
    });
 
    return $.mage.customJs;
});
