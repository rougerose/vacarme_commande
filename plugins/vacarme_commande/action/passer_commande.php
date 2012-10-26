<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function action_passer_commande_dist($arg=null) {
   if (is_null($arg)){
      //spip_log('test secu',"vacarme_securite");
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}
   // validation de la commande par le client. Fonction reprise de zcommerce_pipelines.php

   // On recupere d'abord toutes les informations dont on va avoir besoin
   // principal visiteur connecte
   $id_auteur = intval($arg);


   // On cree la commande ici
   include_spip('inc/commandes');
   $id_commande = creer_commande_encours();

   // on complète les infos dans la table spip_commandes_details ajoutées à la création de commande
   // par panier2commande_pipelines

   // récupération du panier
   include_spip('inc/paniers');
   $id_panier = paniers_id_panier_encours();
   $items = sql_allfetsel("id_objet,objet,numero","spip_paniers_liens","id_panier=$id_panier");

   // on met à jour la colonne numéro pour les abonnements commandés
   foreach($items as $item) {
      $id_objet = $item['id_objet'];
      $numero = $item['numero'];
      $objet = $item['objet'];
      if ($objet == 'abonnement') {
         sql_updateq("spip_commandes_details",array("numero"=>$numero),"id_commande=$id_commande AND id_objet=$id_objet AND objet='abonnement'");
      }
   }


   // On cherche l'adresse principale du visiteur
   $id_adresse = sql_getfetsel( 'id_adresse',  'spip_adresses_liens',
   array( 'objet = '.sql_quote('auteur'),
   'id_objet = '.intval($id_auteur),
   'type = '.sql_quote('principale') ) );

   $adresse = sql_fetsel('*', 'spip_adresses', 'id_adresse = '.$id_adresse);
   unset($adresse['id_adresse']);

   // On copie cette adresse comme celle de facturation
   $id_adresse_facturation = sql_insertq('spip_adresses', $adresse);
   sql_insertq( 'spip_adresses_liens',
   array( 'id_adresse' => $id_adresse_facturation,
   'objet' => 'commande',
   'id_objet' => $id_commande,
   'type' => 'facturation' ) );

   // On copie cette adresse comme celle de livraison
   $id_adresse_livraison = sql_insertq('spip_adresses', $adresse);
   sql_insertq( 'spip_adresses_liens',
   array( 'id_adresse' => $id_adresse_livraison,
   'objet' => 'commande',
   'id_objet' => $id_commande,
   'type' => 'livraison' ) );

   // envoi aux plugins
	pipeline('post_insertion',
		array(
			'args' => array(
				'table' => 'spip_commandes',
				'id_objet' => $id_commande,
            'action' => 'passer_commande'
			),
			'data' => array('id_auteur' => $id_auteur)
		)
	);

}

?>