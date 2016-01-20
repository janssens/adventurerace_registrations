function convertToSlug(Text)
{
    return Text
        .toLowerCase()
        .replace(/[^\w ]+/g,'')
        .replace(/ +/g,'-')
        ;
}

function redirectPost(location, args)
{
    var form = '';
    $.each( args, function( key, value ) {
        form += '<input type="hidden" name="'+key+'" value="'+value+'">';
    });
    $('<form action="'+location+'" method="POST">'+form+'</form>').appendTo('body').submit();
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

    $("#pay").click(function(event){
        event.preventDefault();
        redirectPost($("#pay").data("payurl"),{
            invoice:$("#invoice_id").val(),
            notify_url:$("#pay").data("notifyurl"),
            custom:$("#nom").val()
        });
    })
});