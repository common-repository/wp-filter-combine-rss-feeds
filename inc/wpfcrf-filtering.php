<?php
// Désactive la redirection automatique en cas de singular
add_filter('redirect_canonical', 'wpfcrf_disable_redirect_canonical');
function wpfcrf_disable_redirect_canonical($redirect_url) {
    if(is_paged() && is_singular()) {
    	return false;
    }
}

// Filtre de recherche par mot
function wpfcrf_search_by_words($where) {
	global $wpdb;

	$words = array();

	// Récupération des mots tapés
	$wpfcrf_query = sanitize_text_field(stripslashes($_GET['wpfcrf_search']));
	$wpfcrf_query = mb_strtolower($wpfcrf_query, get_bloginfo('charset'));
	$words[] = explode(" ", $wpfcrf_query);

	// Conversion en entités HTML
	$specialsChars = array('"', "'", "<", ">", "«", "»", "“", "”", "„", "‘", "’", "‚", "´");
	$correspEntity = array('&quot;', '&apos;', '&lsaquo;', '&rsaquo;', '&laquo;', '&raquo;', '&rdquo;','&rdquo;', '&bdquo;', '&lsquo;', '&rsquo;', '&sbquo;', '&acute;');
	$wpfcrf_query_2 = str_ireplace($specialsChars, $correspEntity, $wpfcrf_query);
	$words[] = explode(" ", $wpfcrf_query_2);

	// Conversion en ASCII (au cas où...)
	$correspsASCII = array('&#34;', '&#39;', '&#8249', '&#8250', '&#171;', '&#187', '&#8220;', '&#8221', '&#8222', '&#8216;', '&#8217;', '&#8218;', '&#180;');
	$wpfcrf_query_3 = str_ireplace($specialsChars, $correspsASCII, $wpfcrf_query);
	$words[] = explode(" ", $wpfcrf_query_3);

	// Correspondance d'entités
	$specialsChars = array("l'", "d'", "t'", "c'", "j'", "m'", "n'", "s'", "t'");
	$correspsChars = array("l’", "d’", "t’", "c’", "j’", "m’", "n’", "s’", "t’");
	$wpfcrf_query_4 = str_ireplace($specialsChars, $correspsChars, $wpfcrf_query);
	$words[] = explode(" ", $wpfcrf_query_4);

	// Correspondance d'entités
	$specialsChars = array("l'", "d'", "t'", "c'", "j'", "m'", "n'", "s'", "t'");
	$correspsChars = array("l´", "d´", "t´", "c´", "j´", "m´", "n´", "s´", "t´");
	$wpfcrf_query_5 = str_ireplace($specialsChars, $correspsChars, $wpfcrf_query);
	$words[] = explode(" ", $wpfcrf_query_5);

	$relationBase = " AND ";
	$relationGroupe = " OR ";

	// Boucle sur chaque mot de la requête pour chercher dans les titres et contenus de flux
	// $where.= '(';
	$loop = 0;
	$where.= $relationBase.'(';
	foreach($words as $tabWord) {
		foreach($tabWord as $word) {
			// On encadre chaque mot pour une recherche exacte => Méthode REGEXP
			$word = "'[[:<:]]".esc_sql($word)."[[:>:]]'";
			$type = "REGEXP";

			// On encadre chaque mot pour une recherche approchante => Méthode LIKE
			// $word = "'%".$wpdb->esc_like($word)."%'";
			// $type = "LIKE";

			// On concatène la requête de recherche
			$where.= ($loop != 0) ? $relationGroupe.'(' : '(';
			$where.= $wpdb->posts.'.post_title '.$type.' '.$word;
			$where.= $relationGroupe;
			$where.= $wpdb->posts.'.post_content '.$type.' '.$word;
			$where.= ')';
		}
		$loop++;
	}
	$where.= ")";

	// Retourne la clause WHERE "revisitée"
	return $where;
}
?>