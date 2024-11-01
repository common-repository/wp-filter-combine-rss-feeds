<?php
/*
Plugin Name: WP Filter & Combine RSS Feeds
Plugin URI: https://blog.internet-formation.fr
Description: WP Filter & Combine RSS Feeds is a plugin designed to manage or multiple RSS feeds with an integrated search engine. The extension is multi-site compatible and offers many filters and customization settings (WP Filter & Combine RSS Feeds est un plugin destiné à gérer ou plusieurs flux RSS avec un moteur de recherche intégré. L'extension est compatible multisite et offre de nombreux filtres et paramètres de personnalisation).
Author: Mathieu Chartier
Version: 0.4
Author URI: http://www.mathieu-chartier.com
Text Domain: wpfcrf
Domain Path: /lang
License: GPLv3
*/

// Instanciation des variables globales
global $wp_filter_combine_rss_feeds_version;

// Version du plugin
$wp_filter_combine_rss_feeds_version = "0.4";

/*===========================*/
/*=== Gestion des langues ===*/
/*===========================*/
function wp_filter_combine_rss_feeds_lang() {
	define('wpfcrf', 'wpfcrf');
	$path = dirname(plugin_basename(__FILE__)).'/lang/';
	load_plugin_textdomain(wpfcrf, NULL, $path);
}
add_action('plugins_loaded', 'wp_filter_combine_rss_feeds_lang');

// Filtres pour les chaînes traduites via des get_site_option
add_filter('wpfcrf_placeholder_text_translate', 'wpfcrf_placeholder_text_translate');
function wpfcrf_placeholder_text_translate($placeholder_text) {
	return __($placeholder_text, wpfcrf);
}
add_filter('wpfcrf_date_text_translate', 'wpfcrf_date_text_translate');
function wpfcrf_date_text_translate($date_text) {
	return __($date_text, wpfcrf);
}
add_filter('wpfcrf_date_format_translate', 'wpfcrf_date_format_translate');
function wpfcrf_date_format_translate($date_format) {
	return __($date_format, wpfcrf);
}
add_filter('wpfcrf_source_text_translate', 'wpfcrf_source_text_translate');
function wpfcrf_source_text_translate($source_text) {
	return __($source_text, wpfcrf);
}

/*======================================*/
/*=== Inclusion des fonctions utiles ===*/
/*======================================*/
// Récupération des getters
include_once('inc/wpfcrf-getters.php');

// Fonctions de suppression des données
include_once('inc/wpfcrf-delete.php');

// Fonction d'importation
include_once('inc/wpfcrf-import-feeds-items.php');

// Ajout des metaboxes (metabox pour chaque custom post type)
include_once('inc/wpfcrf-metaboxes.php');

// Ajout des boutons d'importation et suppression des données + validation des formulaires
include_once('inc/wpfcrf-admin-submit-form.php');

// Inclusion du custom post type et du menu  d'administration
include_once('inc/wpfcrf-custom-post-types.php');

// Inclusion de la gestion des menus, bulk actions...
include_once('inc/wpfcrf-manage-menus-and-actions.php');

// Inclusion des tâches CRON
include_once('inc/wpfcrf-cron.php');
if(get_site_option('wpfcrf_frequency_cron') != "none") {
	wpfcrf_active_cron(); // Lance la tâche CRON
} else {
	wpfcrf_deactivate_cron(); // Désactivation de la tâche CRON
}

// Inclusion des shortcodes associés
include_once('inc/wpfcrf-shortcode.php');

// Inclusion des pages d'options
include_once('wp-filter-combine-rss-feeds-options.php');
// include_once('wp-filter-combine-rss-feeds-custom-options.php');
// include_once('wp-filter-combine-rss-feeds-documentation.php');

/*============================================*/
/*=== Fonctions d'activation/désactivation ===*/
/*============================================*/
register_activation_hook(__FILE__, 'wp_fcrf_install');
register_deactivation_hook(__FILE__, 'wp_fcrf_uninstall');

function wp_fcrf_install() {	
	global $wpdb, $wp_filter_combine_rss_feeds_version;
	
	// Pour le multisite
	if(function_exists('is_multisite') && is_multisite()) {
        $original_blog_id = $wpdb->blogid;
        // Obtient les autres ID du multisite
        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach($blogids as $blog_id) {
            switch_to_blog($blog_id);
            wp_fcrf_install_datas(); // Installation des données (multisite)
        }
        switch_to_blog($original_blog_id);  
    } else { // Si ce n'est pas du multisite...
		wp_fcrf_install_datas(); // Installation des données (sans multisite)
	}

	// Prise en compte de la version en cours
	add_site_option("wpfcrf_version", $wp_filter_combine_rss_feeds_version);
}

// Quand ça désactive l'extension, la table est supprimée...
function wp_fcrf_uninstall() {
	global $wpdb, $wp_filter_combine_rss_feeds_version;

	// Pour le multisite (désinstallation pour chaque site)
	if(function_exists('is_multisite') && is_multisite()) {
        $original_blog_id = $wpdb->blogid;
        // Obtient les autres ID du multisite
        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach($blogids as $blog_id) {
            switch_to_blog($blog_id);
            wp_fcrf_uninstall_datas(); // Suppression des données (multisite)
        }
        switch_to_blog($original_blog_id);  
    } else { // Sinon...
		wp_fcrf_uninstall_datas(); // Suppression des données (sans multisite)
	}

	// Supprime la version du plugin
	delete_site_option("wpfcrf_version");
}

/*==========================*/
/*=== Multisites (WP MU) ===*/
/*==========================*/
// Quand un nouveau site (en multisites) est ajouté
add_action('wpmu_new_blog', 'wp_fcrf_new_site', 10, 6);        
function wp_fcrf_new_site($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    global $wpdb, $tableName;
    $original_blog_id = $wpdb->blogid;
    switch_to_blog($blog_id);
    wp_fcrf_install_datas(); // Rajoute les données pour le nouveau site
    switch_to_blog($original_blog_id);
}

/*===================================*/
/*=== Données (install/uninstall) ===*/
/*===================================*/
// Liste des données par défaut
function wp_fcrf_install_datas() {
	// Valeurs par défaut
	add_site_option('wpfcrf_form_position', 'none');
	add_site_option('wpfcrf_form_placeholder', __('Search', wpfcrf));
	add_site_option('wpfcrf_form_validation_button', __('OK', wpfcrf));
	add_site_option('wpfcrf_feeds_pagination', 'numbered');
	add_site_option('wpfcrf_posts_per_page', 10);
	add_site_option('wpfcrf_feeds_order', 'DESC');
	add_site_option('wpfcrf_feeds_order_by', 'publish_date');
	add_site_option('wpfcrf_form_hide_select_name', false);
	add_site_option('wpfcrf_form_hide_input', false);
	add_site_option('wpfcrf_form_hide_button', false);
	add_site_option('wpfcrf_date_format', __('F j, Y', wpfcrf));
	add_site_option('wpfcrf_source_prefix', __('Source:', wpfcrf));
	add_site_option('wpfcrf_date_prefix', __('Published on', wpfcrf));
	add_site_option('wpfcrf_separator', '&nbsp;|&nbsp;');
	add_site_option('wpfcrf_target', true);
	add_site_option('wpfcrf_combine', true);
	add_site_option('wpfcrf_last_cron', time());
	add_site_option('wpfcrf_frequency_cron', '1w');
	add_site_option('wpfcrf_import_method', 'xmlparser');
	add_site_option('wpfcrf_template', 'dark-1');

	// Prise en compte de la version en cours
	add_site_option("wpfcrf_version", $wp_filter_combine_rss_feeds_version);
}
function wp_fcrf_uninstall_datas() {
	// Suppression des options
	delete_site_option('wpfcrf_form_position');
	delete_site_option('wpfcrf_form_placeholder');
	delete_site_option('wpfcrf_form_validation_button');
	delete_site_option('wpfcrf_feeds_pagination');
	delete_site_option('wpfcrf_posts_per_page');
	delete_site_option('wpfcrf_feeds_order');
	delete_site_option('wpfcrf_feeds_order_by');
	delete_site_option('wpfcrf_form_hide_select_name');
	delete_site_option('wpfcrf_form_hide_input');
	delete_site_option('wpfcrf_form_hide_button');
	delete_site_option('wpfcrf_date_format');
	delete_site_option('wpfcrf_source_prefix');
	delete_site_option('wpfcrf_date_prefix');
	delete_site_option('wpfcrf_separator');
	delete_site_option('wpfcrf_target');
	delete_site_option('wpfcrf_combine');
	delete_site_option('wpfcrf_last_cron');
	delete_site_option('wpfcrf_frequency_cron');
	delete_site_option('wpfcrf_import_method');
	delete_site_option('wpfcrf_template');

	delete_site_option('wpfcrf_custom_feeds'); // À SUPPRIMER !!!!!

	// Désactivation de la tâche CRON
	wpfcrf_deactivate_cron();

	// Supprime la version du plugin
	delete_site_option("wpfcrf_version");

	// Suppression des sources et posts existants
	wpfcrf_delete_all_sources();
	wpfcrf_delete_all_feeds_items();
}

/*==============================*/
/*=== Mises à jour du plugin ===*/
/*==============================*/
function wp_filter_combine_rss_feeds_upgrade() {
    global $wpdb, $wp_filter_combine_rss_feeds_version;

    if(get_site_option('wpfcrf_version') != $wp_filter_combine_rss_feeds_version) { 
		// Pour le multisite
		if(function_exists('is_multisite') && is_multisite()) {
	        $original_blog_id = $wpdb->blogid;
	        // Obtient les autres ID du multisite
	        $blogids = $wpdb->get_col("SELECT blog_id FROM ".$wpdb->blogs);
	        foreach($blogids as $blog_id) {
	            switch_to_blog($blog_id);
	            wp_fcrf_update_datas();
	        }
	        switch_to_blog($original_blog_id);
	    } else {
			wp_fcrf_update_datas();
		}

		// Mise à jour de la version
		update_site_option("wpfcrf_version", $wp_filter_combine_rss_feeds_version);
    }
}
add_action('plugins_loaded', 'wp_filter_combine_rss_feeds_upgrade');

function wp_fcrf_update_datas() {
	// Options à rajouter lors de la mise à jour
	update_site_option('wpfcrf_import_method', 'xmlparser');

	// Mise à jour de la version
	update_site_option("wpfcrf_version", $wp_filter_combine_rss_feeds_version);
}

/*===============================*/
/*=== Style CSS Back et front ===*/
/*===============================*/
// Admin (back office)
function wp_filter_combine_rss_feeds_admin_css() {
	$handle = 'wp-filter-combine-rss-feeds-admin';
	$style	= plugins_url('inc/wpfcrf-admin.css', __FILE__);
	wp_enqueue_style($handle, $style, 15);
}
add_action('admin_print_styles', 'wp_filter_combine_rss_feeds_admin_css');

// Site (front office)
if(get_site_option('wpfcrf_template') && get_site_option('wpfcrf_template') != "none") {
	function wp_filter_combine_rss_feeds_css() {
		$templateName = get_site_option('wpfcrf_template');
		$style = plugins_url('inc/templates/wpfcrf-'.$templateName.'.css', __FILE__);
		wp_enqueue_style('wpfcrf-template-style', $style, 15);
	}
	add_action('wp_enqueue_scripts', 'wp_filter_combine_rss_feeds_css');
}

/*==========================*/
/*=== Scripts Javascript ===*/
/*==========================*/
// Admin (back office)
function wp_filter_combine_rss_feeds_admin_js() {
    wp_enqueue_script('wpfcrf-admin-script', plugins_url('js/wp-filter-combine-rss-feeds-admin.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'wp_filter_combine_rss_feeds_admin_js');

// front office
function wp_filter_combine_rss_feeds_js() {
	wp_enqueue_script('wpfcrf-script', plugins_url().'/wp-filter-combine-rss-feeds/js/wp-filter-combine-rss-feeds.js', array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', 'wp_filter_combine_rss_feeds_js');

/*=========================================*/
/*=== Sanitize pour register_settings() ===*/
/*=========================================*/
function wp_fcrf_sanitize_fields($datas) {
	// Tableau vide par défaut
	$new_datas = array();

	// On vérifie si les données ne sont pas vides dans le tableau
	foreach($datas as $key => $tab) {
		foreach($tab as $subkey => $subtab) {
			if(!empty($datas[$key][$subkey])) {
				$new_datas[$key][$subkey] = stripslashes($subtab);
				// $new_datas[$key][$subkey] = sanitize_text_field($subtab);
			}
		}
	}

	// On retourne le tableau de données nettoyé
	if(!empty($new_datas)) {
		return $new_datas;
	}
}
?>