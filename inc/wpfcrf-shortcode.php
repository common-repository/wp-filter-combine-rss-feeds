<?php
// Inclusion de la fonction de récupération des flux RSS
include_once('wpfcrf-filtering.php'); // Filtrage lors d'une recherche

/*======================================*/
/*=== Shortcode des flux => [wpfcrf] ===*/
/*======================================*/
function wp_fcrf_shortcode() {
	global $wp_query;
	global $paged;

	// Récupération des options
	$feedPosition = get_site_option('wpfcrf_form_position') ? get_site_option('wpfcrf_form_position') : 'none';
	$feedSeparator = get_site_option('wpfcrf_separator') ? get_site_option('wpfcrf_separator') : ' | ';
	$feedTarget = get_site_option('wpfcrf_target') ? get_site_option('wpfcrf_target') : true;
	$feedDateFormat = get_site_option('wpfcrf_date_format') ? get_site_option('wpfcrf_date_format') : 'F j, Y';
	$feedPagination = get_site_option('wpfcrf_feeds_pagination') ? get_site_option('wpfcrf_feeds_pagination') : 'numbered';
	$feedPostsPerPage = get_site_option('wpfcrf_posts_per_page') ? get_site_option('wpfcrf_posts_per_page') : 10;
	$feedOrder = get_site_option('wpfcrf_feeds_order') ? get_site_option('wpfcrf_feeds_order') : "DESC";
	$feedOrderBy = get_site_option('wpfcrf_feeds_order_by') ? get_site_option('wpfcrf_feeds_order_by') : "publish_date";

	// Recupère le numéro de page (pour la pagination)
    if(get_query_var('page')) {
		$paged = get_query_var('page');
	} elseif(get_query_var('paged')) {
		$paged = get_query_var('paged');
	} else {
		$paged = 1;
	}

	// Query pour récupérer les flux
	$args = array(
		'post_type' => 'wpfcrf_feeds',
		'paged' => $paged,
		'order' => $feedOrder,
		'orderby' => $feedOrderBy,
		'post_status' => 'publish',
		'suppress_filters' => false,
		'ignore_sticky_posts' => true
	);

	// Détermine si on ajoute ou non un nombre de publication par page
	if($feedPostsPerPage != 0 || $feedPagination != "none") {
		$args['posts_per_page'] = $feedPostsPerPage;
	}

	/*=============================================================*/
	/*=== Ajoute les filtres de recherche (moteur de recherche) ===*/
	/*=============================================================*/
	// https://wordpress.stackexchange.com/questions/78649/using-meta-query-meta-query-with-a-search-query-s
	// Si une recherche par flux RSS est effectuée
	if(isset($_GET['wpfcrf_feed_name']) && isset($_GET['wpfcrf_feed_name'])) {
		if(isset($_GET['wpfcrf_feed_name']) && $_GET['wpfcrf_feed_name'] != "wpfcrf_all_feeds") {
			$args['meta_query'] = array(
				array(
					'key' => 'wpfcrf_feed_source_id',
					'value' => intval($_GET['wpfcrf_feed_name'])
				)
			);
		}
	}
	// Si une recherche par mot est effectuée
	if(isset($_GET['wpfcrf_search']) && !empty($_GET['wpfcrf_search'])) {
		// Renvoie vers le filtre posts_where pour la recherche par mot
		add_filter('posts_where', 'wpfcrf_search_by_words', 10, 1);
	}

	// Requête finale
    $feeds = new WP_Query($args);

    // On supprime le filtre post_where
    remove_filter('posts_where', 'wpfcrf_search_by_words', 10, 1);

    // Comptage du nombre de résultats
	$count = $feeds->found_posts;

    // Passe de la requête existante à la nouvelle
	$temp_query = $wp_query;
	$wp_query   = $feeds; // Modifie la requête par la nouvelle

	// Ajoute la cible des liens si nécessaire
	$target = ($feedTarget == true) ? ' target="_blank"' : '';

	// Début de l'affichage des flux
	$display = '<div id="wpfcrf-container" class="wpfcrf-container">';

	// Affichage du formulaire au-dessus des résultats (optionnel)
	if($feedPosition == 'top') {
		$display.= do_shortcode('[wpfcrf-form]');
	}

	// $filteredFeed = wp_fcrf_filtering($feed);

	// Initialisation du résultat à afficher
	if($feeds->have_posts()) {
		// Début de wpfcrf-feeds
		$display.= '<div id="wpfcrf-feeds" class="wpfcrf-feeds">';
		
		// Début de la liste des items
		$display.= '<ul id="wpfcrf-feeds-list" class="wpfcrf-feeds-list">';

        while($feeds->have_posts()) {
        	$feeds->the_post(); // Boucle WP_Query() pour les items de flux
			
        	// Récupération des données relatives aux post_meta
        	$permalink = get_post_meta(get_the_ID(), 'wpfcrf_feed_post_url', true);
        	$source = array(
        		"feed_ID"	=> get_post_meta(get_the_ID(), 'wpfcrf_feed_source_id', true),
				"feed_name"	=> get_post_meta(get_the_ID(), 'wpfcrf_feed_source_name', true),
				"feed_link"	=> get_post_meta(get_the_ID(), 'wpfcrf_feed_source_link', true),
				"feed_slug"	=> get_post_meta(get_the_ID(), 'wpfcrf_feed_source_slug', true)
        	);

			// Gestion des textes
			$sourceText = apply_filters('wpfcrf_source_text_translate', get_site_option('wpfcrf_source_prefix'), 10, 1);
			$dateText = apply_filters('wpfcrf_date_text_translate', get_site_option('wpfcrf_date_prefix'), 10, 1);
			$dateFormat = apply_filters('wpfcrf_date_format_translate', get_site_option('wpfcrf_date_format'), 10, 1);
			
			// Affichage
			$display.= '<li class="wpfcrf-feed-item" data-feed-item="'.get_the_ID().'">';
			$display.= '<span class="wpfcrf-feed-link"><a href="'.$permalink.'"'.$target.'>'.get_the_title().'</a></span>';
			$display.= '<div class="wpfcrf-feed-meta">';
			if(get_site_option('wpfcrf_source_prefix') != '') {
				$display.= '<span class="wpfcrf-source" data-feed-source="'.$source['feed_ID'].'">'.$sourceText.' '.$source['feed_name'].'</span>';
			}
			if(get_site_option('wpfcrf_source_prefix') != '' && get_site_option('wpfcrf_date_prefix') != '') {
				$display.= '<span class="wpfcrf-separator">'.$feedSeparator.'</span>';
			}
			if(get_site_option('wpfcrf_date_prefix') != '') {
				$display.= '<span class="wpfcrf-feed-date">'.$dateText.' '.get_the_date($dateFormat).'</span>';
				// $display.= '<span class="wpfcrf-feed-date">'.$dateText.' '.date($feedDateFormat, strtotime($val['date'])).'</span>';
			}
			$display.= '</div>';
			$display.= '</li>';
		}
		$display.= '</ul>'; // Fin de wpfcrf-feeds-list
	    $display.= '</div>'; // Fin de wpfcrf-feeds

		// Nettoie la requête
		wp_reset_postdata();

		// Gestion de la pagination
		if($count > $feedPostsPerPage) {
			$display.= '<nav id="wpfcrf_pagination">';
			$display.= wpfcrf_pagination_links($feedPagination, $feedOrder, $feeds->max_num_pages);
			$display.= '</nav>';
		}
	} else {
		$display.= '<div class="wpfcrf-feed-no-result">'.__('No RSS feeds match your search. Please try again with another search.', wpfcrf).'</div>';
	}

	// Affichage du formulaire en-dessous des résultats (optionnel)
	if($feedPosition == 'bottom') {
		$display.= do_shortcode('[wpfcrf-form]');
	}

	$display.= '</div>'; // Fin de wpfcrf-container

	// Remet la bonne requête en jeu
	$wp_query = $temp_query; // Reprend l'ancienne requête

	// Affichage final des flux (et/ou du formulaire)
	return $display;
}
add_shortcode('wpfcrf', 'wp_fcrf_shortcode');

/*================================================*/
/*=== Shortcode du formulaire => [wpfcrf-form] ===*/
/*================================================*/
function wp_fcrf_shortcode_form() {
	// Récupération des données du flux
	$feed = wpfcrf_get_sources();

	// Création du formulaire
	$form = '<form id="wpfcrf-form" class="wpfcrf-form" method="GET" action="'.get_the_permalink().'">';

	if(get_site_option('wpfcrf_form_hide_select_name') == false) {
		$form.= '<select class="wpfcrf-select" name="wpfcrf_feed_name">';
		$form.= '<option value="wpfcrf_all_feeds">'.__('All feeds', wpfcrf).'</option>';
		// Liste les noms de flux à ajouter dans le <select>
		$feedName = array();
		foreach($feed as $flux) {
			// Filtre par nom de flux
			if(!empty($flux['ID']) && !in_array($flux['ID'], $feedName)) {
				$form.= '<option value="'.intval($flux['ID']).'" '.((isset($_GET['wpfcrf_feed_name']) && stripslashes($_GET['wpfcrf_feed_name']) == intval($flux['ID'])) ? 'selected="selected"' : '').'>'.$flux['name'].'</option>';
			}

			// Ajoute le nom d'un flux (filtrage)
			$feedName[] = intval($flux['ID']);
		}	
		$form.= '</select>';
	}

	if(get_site_option('wpfcrf_form_hide_input') == false) {
		$search = $_GET['wpfcrf_search'];
		$searchSend = sanitize_text_field(stripslashes($search));

		$wpfcrf_placeholder = apply_filters('wpfcrf_placeholder_text_translate', get_site_option('wpfcrf_form_placeholder'), 10, 1);
		$form.= '<input type="search" class="wpfcrf-input" name="wpfcrf_search" placeholder="'.$wpfcrf_placeholder.'" value="'.((isset($_GET['wpfcrf_search'])) ? $searchSend : "").'"/>';
	}

	if(get_site_option('wpfcrf_form_hide_button') == false && (get_site_option('wpfcrf_form_hide_select_name') == false || get_site_option('wpfcrf_form_hide_input') == false)) {
		$form.= '<input type="submit" class="wpfcrf-submit" value="'.get_site_option('wpfcrf_form_validation_button').'"/>';
	}

	$form.= '</form>';

	// Affichage du formulaire
	return $form;
}
add_shortcode('wpfcrf-form', 'wp_fcrf_shortcode_form');
?>