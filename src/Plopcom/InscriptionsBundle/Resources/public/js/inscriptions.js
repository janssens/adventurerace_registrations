function convertToSlug(Text)
{
    return Text
        .toLowerCase()
        .replace(/[^\w ]+/g,'')
        .replace(/ +/g,'-')
        ;
}

jQuery(function(){
    jQuery("form[name='race']").on("keyup","#race_title",function(){
       jQuery("#race_slug").val(convertToSlug(jQuery(this).val()));
    }).find("#race_title").trigger("keyup");

    jQuery("form[name='event']").on("keyup","#event_title",function(){
       jQuery("#event_slug").val(convertToSlug(jQuery(this).val()));
    }).find("#event_title").trigger("keyup");

    jQuery("form[name='inscription']").on("click","a.toggle",function(event){
        event.preventDefault();
        jQuery(this).parent().parent().find('>div').toggle();
    });

});