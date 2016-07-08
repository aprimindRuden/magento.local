define([
    "jquery",
    "jquery/ui"
], function($) {
    "use strict";

    $.widget('mage.customJs', {
        _create: function() {
            this.element.on('change', function(e){
               if($("." + this.id).length > 0){
                  $("." + this.id).trigger('change');
               }else{
                   $("#" + this).closest(".label").prop("htmlFor").trigger('change');
               }
            });
        }
 
    });
 
    return $.mage.customJs;
});
