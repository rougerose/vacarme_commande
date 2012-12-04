$(function(){
   var initBtnVacarmeCommande = function() {
      // mise en forme des options de tri
      $('.btn').button({icons:{secondary: "ui-icon-triangle-1-s"}});

      // passer le numéro de référence dans l'url des liens définis dans la fonction
      $("#select_numero").change(changerVal);
      //changerVal();
   }
   initBtnVacarmeCommande();
   onAjaxLoad(initBtnVacarmeCommande);
});


function changerVal(){
   var val = $("#select_numero").val(),
   $idurl = $(".validite"); //console.log($idurl);

   // code repris de http://www.samaxes.com/2011/09/change-url-parameters-with-jquery/
   var queryParameters = {},
   queryString = $idurl.attr("href").substring(3),
   re = /([^&=]+)=([^&]*)/g, m;
   while (m = re.exec(queryString)) {
      queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
   }
   queryParameters['numero'] = val;
   var url = jQuery.param(queryParameters);
   $idurl.attr("href",'./?'+url);
}
