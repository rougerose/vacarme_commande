<?php
/*
 * snippets
 * Gestion d'import/export XML de contenu
 *
 * Auteurs :
 * Cedric Morin
 * © 2006 - Distribue sous licence GNU/GPL
 *
 */
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/snippets');

function action_exporter(){
	//global $auteur_session;
	$arg = _request('arg');
	$args = explode(":",$arg);
	$hash = _request('hash');
	//$id_auteur = $auteur_session['id_auteur'];
	$redirect = _request('redirect');
	if ($redirect==NULL) $redirect="";
	//include_spip("inc/securiser_action");
	//if (verifier_action_auteur("snippet_exporte-$arg",$hash,$id_auteur)==TRUE) {
		$table = array_shift($args);
		$id = $args;

		$f = snippets_fond_exporter($table, false);
      spip_log('f '.$f,'export');
		if ($f) {
			include_spip('public/assembler');
			$out = recuperer_fond($f,array('id'=>$id));
			//$out = preg_replace(",\n\n[\s]*(?=\n),","",$out);

			// $filename=str_replace(":","_",$arg);
         $today = date(Ymd);
         $filename = $table.'_export_'.$today;
			if (preg_match(",<titre>(.*)</titre>,Uims",$out,$regs))
				$filename = preg_replace(',[^-_\w]+,', '_', trim(translitteration(textebrut(typo($regs[1])))));
			$extension = "csv";

			Header("Content-Type: text/csv; charset=".$GLOBALS['meta']['charset']);
			Header("Content-Disposition: attachment; filename=$filename.$extension");
			Header("Content-Length: ".strlen($out));
			echo $out;
			exit();
	//	}
	}
	redirige_par_entete(str_replace("&amp;","&",urldecode($redirect)));
}


?>
