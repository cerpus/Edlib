(function($){
    $(".optionBox").change(function(event){
        if( event.target.name == "undefined"){
            return;
        }
        if( event.target.name == "frame"){
            $(this).find(".optionsContainer").each(function(){
                if( !event.target.checked ){
                    $(this).attr("disabled", true);
                    $(this).find(":checkbox").attr("disabled", true);
                } else {
                    $(this).removeAttr("disabled");
                    $(this).find(":checkbox").removeAttr("disabled");
                }
            });
        }
    });

    $(document).ready(function(){
        $("input[name=frame]").trigger("change");
    });
})(jQuery);