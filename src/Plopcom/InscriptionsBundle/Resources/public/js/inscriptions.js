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

    jQuery(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {//
        event.preventDefault();
        $(this).ekkoLightbox();
    });


    if (jQuery('.radio_read').length){
        jQuery('.radio_read').each(function () {
            var $label = jQuery(this).siblings("label").first();
            var str = $label.text();
            const regex = /\[\[(.*)\]\]/g;
            let m;
            var link = '';
            while ((m = regex.exec(str)) !== null) {
                // This is necessary to avoid infinite loops with zero-width matches
                if (m.index === regex.lastIndex) {
                    regex.lastIndex++;
                }
                // The result can be accessed through the `m`-variable.
                m.forEach((match, groupIndex) => {
                    link = match;
                });
            }
            var newLabel = str.substr(0,(str.length-link.length-4))
            $label.html(newLabel);
            jQuery(this).one("click",function (event) {
                event.preventDefault();
                jQuery("<a>").attr('href',link).attr('data-toggle',"lightbox").text("(lire)").appendTo($label).on('click',function () {
                    e.preventDefault();
                    jQuery(this).ekkoLightbox({type:'url'});
                })
                    .ekkoLightbox({type:'url'});
            });
        });
    }
});