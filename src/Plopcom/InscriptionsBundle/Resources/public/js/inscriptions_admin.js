
jQuery(function(){
    jQuery( "#inscriptionstable" ).sortable({
        axis: "y", // Le sortable ne s'applique que sur l'axe vertical
        containment: "#inscriptionstable", // Le drag ne peut sortir de l'élément qui contient la liste
        handle: ".grab", // Le drag ne peut se faire que sur l'élément .item (le texte)
        distance: 10, // Le drag ne commence qu'à partir de 10px de distance de l'élément
        // Évènement appelé lorsque l'élément est relâché
        stop: function(event, ui){
            // Pour chaque item de liste
            $("#inscriptionstable").find("tr").each(function(){
                // On actualise sa position
                index = parseInt($(this).index()+1);
                // On la met à jour dans la page
                $(this).find(".order").text(index);
            });
        },
        update: function( event, ui )
        {
            // On prépare la variable contenant les paramètres
            var order = $(this).sortable("serialize");
            //console.log(order);
            // $(this).sortable("serialize") sera le paramètre "element", un tableau contenant les différents "id"
            // action sera le paramètre qui permet éventuellement par la suite de gérer d'autres scripts de mise à jour

            // Ensuite on appelle notre page updateListe.php en lui passant en paramètre la variable order
            $.post($("#inscriptionstable").data("updateorderurl"), order, function(theResponse)
            {
                // On affiche dans l'élément portant la classe "reponse" le résultat du script de mise à jour
                //$(".response").html(theResponse).fadeIn("fast");
                //setTimeout(function()
                //{
                //    $(".response").fadeOut("slow");
                //}, 2000);
                ui.item.addClass("success");
                setTimeout(function() {ui.item.removeClass("success");}, 2000);

            });
        }
    });
    jQuery( "#inscriptionstable" ).disableSelection();
});