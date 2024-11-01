<?php
/*================================================*/
/*=== Récupération de la liste des flux source ===*/
/*================================================*/
function wpfcrf_get_sources() {
	// Tableau des sources
	$allSources = array();

	// Récupération des sources (post_type "wpfcrf_sources")
	$args = array(
		'post_type'   => 'wpfcrf_sources',
		'post_status' => array('publish', 'future'),
		'numberposts' => -1
	);
	$posts = get_posts($args);

	// On parcourt l'ensemble des sources
	foreach($posts as $source) {
		$allSources[] = array(
			"ID"	=> $source->ID,
			"name"	=> $source->post_title,
			"slug"	=> $source->post_name,
			"link"	=> get_post_meta($source->ID, 'wpfcrf_feed_url_source', true)
		);
	}
	return $allSources;
}

// Récupération d'une source par ID
function wpfcrf_get_source_by_ID($ID = '') {
	// Tableau des sources
	$allSources = array();

	// Récupération des sources (post_type "wpfcrf_sources")
	$args = array(
		'include' 		=> intval($ID),
		'post_type'		=> 'wpfcrf_sources',
		'post_status'	=> array('publish', 'future'),
		'numberposts'	=> -1
	);
	$posts = get_posts($args);

	// On parcourt l'ensemble des sources
	foreach($posts as $source) {
		$allSources[] = array(
			"ID"	=> intval($source->ID),
			"name"	=> $source->post_title,
			"slug"	=> $source->post_name,
			"link"	=> get_post_meta(intval($source->ID), 'wpfcrf_feed_url_source', true)
		);
	}
	return $allSources;
}

/*==================================================*/
/*=== Récupération de la liste des items de flux ===*/
/*==================================================*/
function wpfcrf_get_feeds_items() {
	// Tableau des sources
	$allItems = array();

	// Récupération des sources (post_type "wpfcrf_sources")
	$args = array(
		'post_type'   => 'wpfcrf_feeds',
		'post_status' => array('publish', 'future', 'draft', 'private', 'pending', 'trash'),
		'numberposts' => -1
	);
	$posts = get_posts($args);

	// On parcourt l'ensemble des sources
	foreach($posts as $item) {
		$allItems[] = array(
			"ID"		=> intval($item->ID),
			"title"		=> $item->post_title,
			"slug"		=> $item->post_name,
			"date"		=> $item->post_date,
			"date_gmt"	=> $item->post_date_gmt,
			"content"	=> $item->post_content,
			"link"		=> get_post_meta(intval($item->ID), 'wpfcrf_feed_post_url', true),
			"source"	=> array(
				"feed_ID"	=> get_post_meta(intval($item->ID), 'wpfcrf_feed_source_id', true),
				"feed_name"	=> get_post_meta(intval($item->ID), 'wpfcrf_feed_source_name', true),
				"feed_link"	=> get_post_meta(intval($item->ID), 'wpfcrf_feed_source_link', true),
				"feed_slug"	=> get_post_meta(intval($item->ID), 'wpfcrf_feed_source_slug', true)
			)
		);
	}
	return $allItems;
}

/*========================================*/
/*=== Récupère les liens de pagination ===*/
/*========================================*/
function wpfcrf_pagination_links($type, $order = "DESC", $max = "999999") {
	// Si aucune pagination n'est à afficher
	if($type === 'none') {
		return;
	}
	// Sélectionne le type de pagination et l'affiche en conséquence
	if($type === 'numbered') {
		$args = apply_filters(
			'wpfcrf_numbered_pagination_args',
			array(
				'base'		=> str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
				'format'	=> '?paged=%#%',
				'current'	=> max(1, get_query_var('paged')),
				'total'		=> $max,
				'prev_text'	=> __('« Previous', wpfcrf),
				'next_text'	=> __('Next »', wpfcrf)
			)
		);
		$output = '<div class="wpfcrf-nav-numbered">';
		$output.= paginate_links($args);
		$output.= "</div>";
	} else {
		$output = '<div class="wpfcrf-nav-links">';
		if($order == "DESC") {
			$output.= apply_filters('wpfcrf_pagination_next', '<p class="wpfcrf-next wpfcrf-nextprev alignleft">'.get_next_posts_link(__('&laquo; Older Entries', wpfcrf), $max)).'</p>';
			$output.= apply_filters('wpfcrf_pagination_prev', '<p class="wpfcrf-prev wpfcrf-nextprev alignright">'.get_previous_posts_link(__('Newer Entries &raquo;', wpfcrf))).'</p>';
		} else {
			$output.= apply_filters('wpfcrf_pagination_next', '<p class="wpfcrf-prev wpfcrf-nextprev alignleft">'.get_previous_posts_link(__('&laquo; Older Entries', wpfcrf))).'</p>';
			$output.= apply_filters('wpfcrf_pagination_prev', '<p class="wpfcrf-next wpfcrf-nextprev alignright">'.get_next_posts_link(__('Newer Entries &raquo;', wpfcrf), $max)).'</p>';
		}
		$output.= '<div class="clear-wpfcrf"></div>';
		$output.= '</div>';
	}
	return $output;
}
?>