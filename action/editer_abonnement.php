<?php

/**
 * Plugin abonnement pour Spip 2.0
 * Licence GPL (c) 2011
 *
 * Surcharge de zabonnement/action/editer_abonnement.php
 * ajout de la colonne reference
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


function action_editer_abonnement_dist()
{
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_abonnement = intval($securiser_action());

	if (!$id_abonnement) {
		$id_abonnement = sql_insertq("spip_abonnements");
	}

	// modifier le contenu via l'API
	include_spip('inc/modifier');

	$c = $opt = array();
	foreach (array(
		'titre', 'reference', 'duree', 'periode','exact','nb_rub','ids_zone', 'prix',  'descriptif','cadeau'
	) as $champ)
		$c[$champ] = _request($champ);

	revision_abonnement($id_abonnement, $c);
	if ($redirect = _request('redirect')) {
		include_spip('inc/headers');
		redirige_par_entete(parametre_url(urldecode($redirect),
			'id_abonnement', $id_abonnement, '&'));
	} else
		return array($id_abonnement,'');
}


function revision_abonnement($id_abonnement, $c=false) {

	modifier_contenu('abonnement', $id_abonnement,
		array(
			'nonvide' => array('titre' => _T('info_sans_titre'))
		),
		$c);
}

?>
