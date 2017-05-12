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

    jQuery("form[name='inscription']").on("click",'[type="submit"]',function(event){
        var that = jQuery("form[name='inscription']")[0];
        if(!that.checkValidity())
        {
            //event.preventDefault();
            jQuery(that).find('[required="required"]').each(function()
            {
                if(!this.validity.valid)
                {
                    jQuery("a[href='#"+jQuery(this).parents('.tab-pane:first').attr("id")+"']").tab('show')
                    // break
                    return false;
                }
            });
        }
    });

    jQuery(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {//
        event.preventDefault();
        $(this).clone().removeAttr("title").ekkoLightbox({alwaysShowClose:false});
    });

    jQuery("div[id*='_wysiwyg']").each(function () {
        var realid = jQuery(this).wysiwyg({
            hotKeys: {},
            selectionColor : '#3498db',
        }).data("realid");
        var $target = jQuery("#"+realid);
        $target.hide();
        jQuery(this).html($target.val());
        jQuery(this).on('mouseup keyup mouseout',function () {
            $target.val(jQuery(this).html());
        });
        $target.parents('form').on("submit",function (event) {
            if ($target.val() == ''){
                event.preventDefault();
                alert("La description est obligatoire");
            }
        });
    });

    if (jQuery("#subscribers").is('*')) {
        jQuery("#subscribers").tablesorter({
            dateFormat: 'pt',
            cssAsc: 'sort-asc',
            cssDesc: 'sort-desc',
        });
    }

    jQuery('div[data-role="editor-toolbar"] .color').on('mouseover',function () {
        var $that = jQuery(this);
        var $container = $that.parent();
        $container.find("input[type='text']").trigger("focus").val($that.attr("data-text"));
    });
    jQuery('div[data-role="editor-toolbar"] .color').on('click',function () {
        var $that = jQuery(this);
        var $container = $that.parent().parent();
        $container.find("input[type='text']").val($that.attr("data-text")).trigger("change");
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