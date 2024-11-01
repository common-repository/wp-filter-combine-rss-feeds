<?php
/*==============================*/
/*=== Suppression de Metabox ===*/
/*==============================*/
add_action('add_meta_boxes', 'wpfcrf_remove_sources_metabox', 100);
function wpfcrf_remove_sources_metabox() {
	// global $wp_meta_boxes;
	$post_type = 'wpfcrf_sources';
    if($post_type !== get_current_screen()->id) return;
    
    wpfcrf_remove_metabox_list($post_type);
}
add_action('add_meta_boxes', 'wpfcrf_remove_feeds_metabox', 100);
function wpfcrf_remove_feeds_metabox() {
	// global $wp_meta_boxes;
	$post_type = 'wpfcrf_feeds';
    if($post_type !== get_current_screen()->id) return;
    
    wpfcrf_remove_metabox_list($post_type);
}

function wpfcrf_remove_metabox_list($post_type = "") {
    remove_meta_box('wpseo_meta', $post_type, 'normal');                 // WP SEO Yoast
    remove_meta_box('ta-reviews-post-meta-box', $post_type, 'normal');   // Author hReview
    remove_meta_box('wpdf_editor_section', $post_type, 'advanced');      // ImageInject
    remove_meta_box('A2A_SHARE_SAVE_meta', $post_type, 'advanced');      // AddToAny Share
    remove_meta_box('wpseo_meta', $post_type,'normal');
    remove_meta_box('postpsp', $post_type,'normal');
    remove_meta_box('su_postmeta', $post_type,'normal');
    remove_meta_box('woothemes-settings', $post_type,'normal');
    remove_meta_box('wpcf-post-relationship', $post_type,'normal');
    remove_meta_box('wpar_plugin_meta_box ', $post_type,'normal');
    remove_meta_box('sharing_meta', $post_type,'advanced');
    remove_meta_box('content-permissions-meta-box', $post_type,'advanced');
    remove_meta_box('theme-layouts-post-meta-box', $post_type,'side');
    remove_meta_box('post-stylesheets', $post_type,'side');
    remove_meta_box('hybrid-core-post-template', $post_type,'side');
    remove_meta_box('wpcf-marketing', $post_type,'side');
    remove_meta_box('trackbacksdiv22', $post_type,'advanced');
    remove_meta_box('aiosp', $post_type,'advanced');
    remove_action('post_submitbox_start', 'fpp_post_submitbox_start_action');
}

/*==========================================*/
/*=== Ajout des Metabox pour chaque flux ===*/
/*==========================================*/
function wpfcrf_feeds_items_metaboxes() {
	// Ajoute la metabox des URL de flux
	add_meta_box('wpfcrf_feed_item', __('Feed item information', wpfcrf), 'wpfcrf_feed_item_callback', 'wpfcrf_feeds', 'normal', 'high');
}

// Ajout du formulaire correspondant à la metabox personnalisée pour le titre de la vignette (callback)
function wpfcrf_feed_item_callback() {
	global $post;

	// Noncename nécessaire pour savoir d'où proviennent les données
	wp_nonce_field('wpfcrf_feed_item_metabox', 'wpfcrf_feed_item_metabox_nonce');
	
	// Récupère les données si le champ a déjà été rempli...
	$wpfcrf_feed_post_url = get_post_meta($post->ID, 'wpfcrf_feed_post_url', true);
	$wpfcrf_feed_source_name = get_post_meta($post->ID, 'wpfcrf_feed_source_name', true);
	$wpfcrf_feed_source_id = get_post_meta($post->ID, 'wpfcrf_feed_source_id', true);
	
	// Affiche le champ de formulaire supplémentaire
	echo '<p><em>'.__('Post URL', wpfcrf)."</em></p>\n";
	echo '<input type="text" name="wpfcrf_feed_post_url" value="'.esc_attr($wpfcrf_feed_post_url).'" class="widefat" />';
	echo '<p><em>'.__('Related source (ID)', wpfcrf)."</em></p>\n";
	echo '<p>'.$wpfcrf_feed_source_name.' ('.$wpfcrf_feed_source_id.")</p>\n";
}

// Gestion de l'enregistrement de la metabox personnalisée
function wpfcrf_feed_item_save_metabox() {
	global $post;

	// Vérifie que le noncename est valide !
	if(!wp_verify_nonce($_POST['wpfcrf_feed_item_metabox_nonce'], 'wpfcrf_feed_item_metabox')) {
		return $post->ID;
	}

	// Vérifie les permissions des utilisateurs
	if(!current_user_can('edit_post', $post->ID)) {
		return $post->ID;
	}

	// Récupère l'information du champ personnalisé
	$slug = 'wpfcrf_feed_post_url';
	$url_feed = sanitize_text_field($_POST[$slug]);

	// Adapte les données en fonction de l'existence du champ personnalisé
	if(get_post_meta($post->ID, $slug, false)) {
		update_post_meta($post->ID, $slug, $url_feed);
	} else {
		add_post_meta($post->ID, $slug, $url_feed);
	}
	
	// Supprime le champ personnalisé (donnée uniquement) s'il est vide
	if(!$url_feed) {
		delete_post_meta($post->ID, $slug);
	}
}
add_action('save_post', 'wpfcrf_feed_item_save_metabox');

/*==========================================*/
/*=== Ajout des Metabox pour les sources ===*/
/*==========================================*/
function wpfcrf_feeds_sources_metaboxes() {
	// Ajoute la metabox des URL de flux
	add_meta_box('wpfcrf_feed_url_source', __('Feed URL', wpfcrf), 'wpfcrf_feed_url_source_callback', 'wpfcrf_sources', 'normal', 'high');
}

// Ajout du formulaire correspondant à la metabox personnalisée pour le titre de la vignette (callback)
function wpfcrf_feed_url_source_callback() {
	global $post;

	// Noncename nécessaire pour savoir d'où proviennent les données
	wp_nonce_field('wpfcrf_feed_url_source_metabox', 'wpfcrf_feed_url_source_metabox_nonce');
	
	// Récupère les données si le champ a déjà été rempli...
	$wpfcrf_source_url = get_post_meta($post->ID, 'wpfcrf_feed_url_source', true);
	
	// Affiche le champ de formulaire supplémentaire
	echo '<p><em>'.__('Add the URL feed', wpfcrf)."</em></p>\n";
	echo '<input type="text" name="wpfcrf_feed_url_source" value="'.esc_attr($wpfcrf_source_url).'" class="widefat" />';
}

// Gestion de l'enregistrement de la metabox personnalisée
function wpfcrf_feed_url_source_save_metabox() {
	global $post;

	// Vérifie que le noncename est valide !
	if(!wp_verify_nonce($_POST['wpfcrf_feed_url_source_metabox_nonce'], 'wpfcrf_feed_url_source_metabox')) {
		return $post->ID;
	}

	// Vérifie les permissions des utilisateurs
	if(!current_user_can('edit_post', $post->ID)) {
		return $post->ID;
	}

	// Récupère l'information du champ personnalisé
	$slug = 'wpfcrf_feed_url_source';
	$url_feed = sanitize_text_field($_POST[$slug]);

	// Adapte les données en fonction de l'existence du champ personnalisé
	if(get_post_meta($post->ID, $slug, false)) {
		update_post_meta($post->ID, $slug, $url_feed);
	} else {
		add_post_meta($post->ID, $slug, $url_feed);
	}
	
	// Supprime le champ personnalisé (donnée uniquement) s'il est vide
	if(!$url_feed) {
		delete_post_meta($post->ID, $slug);
	}
}
add_action('save_post', 'wpfcrf_feed_url_source_save_metabox');
?>