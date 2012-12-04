$(document).ready(function() {

// ====================================
// = bouton d'activation de la grille =
// ====================================
   $("#spip-admin").append("<a class='spip-admin-boutons grid_tg' href='#'>Grille</a>");
   $(".grid_tg").click(function(){
   	$("body").toggleClass("grid");
   });

// =================
// = jqueryui tabs =
// =================
   $("#offres_abo,#paiements").tabs();


// ====================
// = jquery accordion =
// ====================
   // Sur compte/commandes et rubrique Questions/Réponses
   $("#commandes, #faq").accordion({
      active: false,
      autoHeight: false,
      navigation: true,
      icons: false
   });

   // Sur rubrique Questions/Réponses :
   // Ouvrir l'item en fonction de l'ancre passée dans l'url
   $("#faq").each(function(){
      if(location.hash) {
         var hash = location.hash;
         if (hash.match(/#(question|reponse)/gi)) {
            var ancre = hash.split(/#(question|reponse)/gi);
            $(this).find("dt[id=question" + ancre[2] + "]").trigger("click");
         }
      }
   });


// ==========
// = Panier =
// ==========
   $("#panier").each(function(){
      var   input = $(this).find("input[type=text]"),
            bouton = $(this).find(".boutons");
      bouton.hide();
      input.change(function(){
         bouton.show('slow');
      });
   });


// ===============================================
// = page sommaire : couverture affichée/masqueé =
// ===============================================
   $("#numero-last").each(function(){
      var li = $(this), img = li.find(".img img"), hauteur = "430px";
      li.animate({height: hauteur},1500);
      img.hover(
         function(){
            li.stop().animate({height: "750px"},1500);
         },
         function(){
            li.stop().animate({height: hauteur},1500);
         }
      );
   });

// =======================================================
// = Présentation produits : accordéon sur les sommaires =
// =======================================================
   initAccordeon();
   onAjaxLoad(initAccordeon);

});

// ====================================
// = jquery accordion sur les numéros =
// ====================================
   var initAccordeon = function() {
      $("#produits.numeros").each(function(){
         var $sommaire = $(this).find(".sommaire");
         $sommaire.accordion({
            active: false,
            autoHeight: false,
            navigation: true,
            icons: false
         });
      });
   }

