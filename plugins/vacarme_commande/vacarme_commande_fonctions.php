<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

   function filtre_capitale($texte){
      return $texte = ucfirst($texte);
   }

   // Une fonction qui retourne les différents statuts possibles pour une commande ou le nom d'un statut précis
   function filtre_lister_statuts($statut=false){
      $statuts =  array(
      'encours' => _T('vacarme_commande:statut_encours'),
      'erreur' => _T('vacarme_commande:statut_erreur'),
      'attente' => _T('vacarme_commande:statut_attente'),
      'partiel' => _T('vacarme_commande:statut_partiel'),
      'paye' => _T('vacarme_commande:statut_paye'),
      'envoye' => _T('vacarme_commande:statut_envoye'),
      'retour' => _T('vacarme_commande:statut_retour'),
      'retour_partiel' => _T('vacarme_commande:statut_retour_partiel'),
      'offert' => _T('zabonnement:statut_offert')
      );

      if ($statut and $nom = $statuts[$statut]) return $nom;
      else return $statuts;
   }

   function filtre_lister_paiements($p){
      $paiements = array(
         'paypal'   => _T('vacarme_commande:paiement_paypal'),
         'virement' => _T('vacarme_commande:paiement_virement'),
         'cheque'   => _T('vacarme_commande:paiement_cheque')
      );
      return $paiements[$p];
   }

   // on garde deux chiffres après la virgule. filtre appliqué pour le formulaire paypal. Ce qui permet de retrouver le prix envoyé par la fonction |prix_formater (qui n'est pas un arrondi).
   function filtre_decimale($texte) {
      $texte = sprintf("%.2f",$texte);
      return $texte;
   }

   function filtre_tva_applicable($tva=true,$type_client,$pays) {
   	$tva_applicable = charger_fonction('tva_applicable', 'inc');
   	if ($tva_applicable) {
   	   $tva = $tva_applicable($type_client,$pays);
   	}
      return $tva;
   }

   // calculer le prix ht. Oui, il y a une api prix, sauf que qu'elle part du principe (logique) qu'une colonne prix_ht dans une base sort du... HT. Or, trimballer du prix HT amène au bout de la chaîne chez Paypal des différences de + ou - 1 centimes. Donc, on bricole : les prix sont TTC dans la base et on calcule par ce filtre le prix HT.
   function prixht($prix,$round='') {
      // on part du principe que le taux de TVA est uniforme pour abonnements ET produits.
		include_spip('inc/config');
		$taxe = (lire_config('produits/taxe', 0)) + 1;
      $prix = ($prix/$taxe);
      if ($round) {round($prix,$round);}
      #$prix = ($prix/$taxe);
      return $prix;
   }

?>
