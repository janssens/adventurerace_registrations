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
        $(this).clone().removeAttr("title").ekkoLightbox({alwaysShowClose:false});
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
                // var conf = {
                //     remote:'/bundles/plopcominscriptions/640px.jpg',
                //     onShown:function () {
                //         jQuery('.ekko-lightbox-container').html(jQuery('<iframe src="'+link+'"></iframe>').css({"width":"100%","min-height":"500px"}));
                //     }
                // };
                jQuery("<a>").attr('href',link).text("(lire)").appendTo($label);
                // $label.on('click',"a",function () {
                //     e.preventDefault();
                //     jQuery(this).ekkoLightbox(conf);
                // });
                // jQuery(this).ekkoLightbox(conf);
                var win = window.open(link, '_blank');
                if (win) {
                    //Browser has allowed it to be opened
                    win.focus();
                } else {
                    //Browser has blocked it
                    alert('Merci de lire le document avant de continuer');
                }
            });
        });
    }
});