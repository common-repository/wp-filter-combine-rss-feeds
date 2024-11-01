<?php
/*=============================*/
/*=== Suppression complètes ===*/
/*=============================*/
// Fonction de suppression de toutes les sources
function wpfcrf_delete_all_sources() {
	$argsSources = array(
		'post_type'     => 'wpfcrf_sources',
		'post_status'	=> array('publish', 'future', 'draft', 'private', 'pending', 'trash'),
		'numberposts'	=> -1
	);
	$postsSources = get_posts($argsSources);
	foreach($postsSources as $post) {
		wp_delete_post($post->ID, true);
	}
}

// Fonction de suppression de tous les items de flux
function wpfcrf_delete_all_feeds_items() {
	$argsFeedsItems = array(
		'post_type'     => 'wpfcrf_feeds',
		'post_status'	=> array('publish', 'future', 'draft', 'private', 'pending', 'trash'),
		'numberposts'	=> -1,
	);
	$postsItems = get_posts($argsFeedsItems);

	foreach($postsItems as $post) {
		wp_delete_post($post->ID, true);
	}
}

// Fonction de suppression de toutes les sources et des flux associés
function wpfcrf_delete_all_sources_with_items() {
	// Suppression des sources (parents)
	wpfcrf_delete_all_sources();

	// Suppression des items relatifs à ces sources (enfants)
	wpfcrf_delete_all_feeds_items();
}

/*==============================*/
/*=== Suppression partielles ===*/
/*==============================*/
// Fonction de suppression de tous les items de flux
add_action('before_delete_post', 'wpfcrf_delete_all_related_items');
function wpfcrf_delete_all_related_items($post_id) {
	global $post_type;
	if($post_type != 'wpfcrf_sources') return;

	$argsFeedsItems = array(
		'post_type'     => 'wpfcrf_feeds',
		'post_status'	=> array('publish', 'future', 'draft', 'private', 'pending', 'trash'),
		'numberposts'	=> -1,
		'meta_query' => array(
			array(
				'key' => 'wpfcrf_related_post_type',
				'value' => 'wpfcrf_sources',
			),
			array(
				'key' => 'wpfcrf_feed_source_id',
				'value' => intval($post_id)
			)
		)
	);
	$postsItems = get_posts($argsFeedsItems);

	foreach($postsItems as $post) {
		wp_delete_post(intval($post->ID), true);
	}
}
?>