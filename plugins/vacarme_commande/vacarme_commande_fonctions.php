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
      include_spip('inc/vacarme_commande');
      if (function_exists('tva_applicable'))
         $tva = tva_applicable($type_client,$pays);
      return $tva;
   }

?>
