<?php
/*======================================*/
/*=== Suppression de corbeille, etc. ===*/
/*======================================*/
// Désactive la corbeille pour les custom post type
add_filter('post_row_actions', 'wpfcrf_remove_row_actions', 10, 2);
add_filter('page_row_actions', 'wpfcrf_remove_row_actions', 10, 2);
function wpfcrf_remove_row_actions($actions, $object) {
    if(get_post_type() === 'wpfcrf_sources') {
        unset($actions['view']); // Supprime "Afficher"
        unset($actions['trash']); // Supprime la corbeille

        $paged = isset($_GET['paged']) ? '&paged='.$_GET['paged'] : '';
        $customUrl = admin_url(get_current_screen()->parent_file.'&action=import_feeds_from_source&source_ID='.$object->ID).$paged;
        $actions['import_feeds_from_source'] = '<a href="'.wp_nonce_url($customUrl, 'wpfcrf-source-'.$object->ID, 'wpfcrf_wpnonce').'">'.__('Import items from this source', wpfcrf).'</a>'; // Ajoute un lien d'importation
        $actions['delete_feed'] = '<a href="'.get_delete_post_link($object->ID, '', true).'">'.__('Delete feed and related items', wpfcrf).'</a>'; // Ajoute un lien de suppression totale
    }
    return $actions;
}

add_action('load-edit.php', 'set_add_feeds_post_link');
function set_add_feeds_post_link() {
    global $typenow;
    if('wpfcrf_sources' != $typenow || empty($_GET['source_ID'])) {
        return;
    }
    
    // Récupération de l'ID
    $ID = $_REQUEST['source_ID'];
    
    // Vérifie la page
    check_admin_referer('wpfcrf-source-'.$ID, 'wpfcrf_wpnonce');

    if(isset($_GET['action']) && $_GET['action'] == 'import_feeds_from_source') {
        // Récupération des données
        $fluxList = wpfcrf_get_source_by_ID($ID);

        // Lance la fonction d'importation des items de flux
        wpfcrf_insert_all_feeds_items($fluxList);
    }

    // Génère une redirection
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
    $paged = isset($_GET['paged']) ? '&paged='.$_GET['paged'] : '';
    wp_redirect(admin_url("edit.php?post_type=$post_type&import_feeds_from_source=1".$paged));
    exit();
}

// Désactive la corbeille dans le select des bulk_actions
add_filter('bulk_actions-edit-wpfcrf_sources', 'wpfcrf_register_bulk_actions');
function wpfcrf_register_bulk_actions($bulk_actions) {
    unset($bulk_actions['trash']);
    $bulk_actions['import_feeds_from_source'] = __('Import items from this source', wpfcrf);
    $bulk_actions['delete_feed'] = __('Delete feed and related items', wpfcrf);
    return $bulk_actions;
}

// Ajoute une action à la nouvelle bulk_action "delete_feed"
add_filter('handle_bulk_actions-edit-wpfcrf_sources', 'wpfcrf_bulk_action_handler', 10, 3);
function wpfcrf_bulk_action_handler($redirect_to, $doaction, $post_ids) {
	if($doaction !== 'delete_feed') {
		return $redirect_to;
	}
	// Supprime tous les items relatifs à chaque source sélectionnée
	foreach($post_ids as $post_id) {
		wpfcrf_delete_all_related_items($post_id); // Supprime les items relatifs
		wp_delete_post($post_id, true); // Supprime les sources
	}
	// Redirige vers la page des sources
	$redirect_to = add_query_arg('wpfcrf_deleted_sources', count($post_ids), $redirect_to);
	return $redirect_to;
}

// Ajoute une action à la nouvelle bulk_action "add_feeds_items_form_source"
add_filter('handle_bulk_actions-edit-wpfcrf_sources', 'wpfcrf_add_feeds_items_bulk_action_handler', 10, 3);
function wpfcrf_add_feeds_items_bulk_action_handler($redirect_to, $doaction, $post_ids) {
    if($doaction !== 'import_feeds_from_source') {
        return $redirect_to;
    }
    // Supprime tous les items relatifs à chaque source sélectionnée
    foreach($post_ids as $post_id) {
        // Récupération des données
        $fluxList = wpfcrf_get_source_by_ID($post_id);

        // Lance la fonction d'importation des items de flux
        wpfcrf_insert_all_feeds_items($fluxList);
    }
    // Redirige vers la page des sources
    $redirect_to = add_query_arg('wpfcrf_imported_feeds_items', count($post_ids), $redirect_to);
    return $redirect_to;
}

// Ajoute l'admin notice si besoin
add_action('admin_notices', 'wpfcrf_bulk_action_admin_notice');
add_action('network_admin_notices', 'wpfcrf_bulk_action_admin_notice');
function wpfcrf_bulk_action_admin_notice() {
	if(!empty($_REQUEST['wpfcrf_deleted_sources'])) {
		$count = intval($_REQUEST['wpfcrf_deleted_sources']);
		wpfcrf_all_delete_admin_notice_success($count);
	}
    if(!empty($_REQUEST['wpfcrf_imported_feeds_items']) || isset($_REQUEST['import_feeds_from_source'])) {
        wpfcrf_feeds_items_importation_admin_notice_success();
    }
}

/*=============================*/
/*=== Menu/Sous-menus Admin ===*/
/*=============================*/
function wp_filter_combine_rss_feeds_admin() {
    $menu_title     = 'F&C RSS Feeds';                      // Titre du sous-menu
    $capability     = 'manage_options';                     // Rôle d'administration qui a accès au sous-menu
    $menu_slug      = 'edit.php?post_type=wpfcrf_sources';  // Alias (slug) de la page

    add_submenu_page($menu_slug, __('Settings', wpfcrf), __('Settings', wpfcrf), $capability, 'wpfcrf-settings', 'wp_fcrf_callback');
    // add_submenu_page($menu_slug, __('Readme','wpfcrf'), __('Readme','wpfcrf'), $capability, 'wp-fcrf-readme', 'wp_fcrf_readme_callback');
    
    // Supprime l'ajout d'item dans les sous-menus (Ne fonctionne pas...)
    remove_menu_page('post-new.php?post_type=wpfcrf_sources');

    // Lance la fonction d'enregistrement des settings
    add_action('admin_init', 'wpfcrf_register_mysettings');
}
add_action('admin_menu', 'wp_filter_combine_rss_feeds_admin');

/*===============================*/
/*=== Métadonnées et filtrage ===*/
/*===============================*/
add_filter('manage_wpfcrf_feeds_posts_columns', 'wpfcrf_set_column_source' );
add_filter('manage_edit-wpfcrf_feeds_sortable_columns', 'wpfcrf_sort_column_source');
add_action('pre_get_posts', 'wpfcrf_sort_column_source_orderby');
add_action('manage_wpfcrf_feeds_posts_custom_column' , 'wpfcrf_add_column_source', 10, 2);

function wpfcrf_set_column_source($columns) {
    $columns['wpfcrf_link'] = __('Link', wpfcrf);
    $columns['wpfcrf_sources'] = __('RSS Feeds', wpfcrf);
    return $columns;
}
function wpfcrf_add_column_source($column, $post_id) {
    switch($column) {
        case 'wpfcrf_sources' :
            $source_id = get_post_meta($post_id, 'wpfcrf_feed_source_id', true);
            $source_name = get_post_meta($post_id, 'wpfcrf_feed_source_name', true);
            echo '<a href="'.get_edit_post_link($source_id).'">'.$source_name.'</a>';
            break;
        case 'wpfcrf_link' :
            $url = get_post_meta($post_id, 'wpfcrf_feed_post_url', true);
            echo '<a href="'.$url.'">'.$url.'</a>';
            break;
    }
}
function wpfcrf_sort_column_source($columns) {
    $columns['wpfcrf_link'] = 'wpfcrf_source_link';
    $columns['wpfcrf_sources'] = 'wpfcrf_source_name';
    return $columns;
}
function wpfcrf_sort_column_source_orderby($query) {
    if(!is_admin()) return;
 
    $orderby = $query->get('orderby');
    if('wpfcrf_source_name' == $orderby) {
        $query->set('meta_key','wpfcrf_feed_source_name');
        $query->set('orderby','meta_value');
    }
    if('wpfcrf_source_link' == $orderby) {
        $query->set('meta_key','wpfcrf_feed_source_link');
        $query->set('orderby','meta_value');
    }
}

// Ajout du filtre par nom de flux
add_action('restrict_manage_posts', 'wpfcrf_filter');
function wpfcrf_filter() {
    global $typenow;
    if($typenow == 'wpfcrf_feeds') {
        // Récupération des données du flux
        $feed = wpfcrf_get_sources();
        $args = array(
            'post_type'   => 'wpfcrf_sources',
            'post_status' => array('publish', 'future', 'draft', 'private', 'pending', 'trash'),
            'numberposts' => -1
        );
        $feeds = get_posts($args);


        $form = '<select name="wpfcrf_source_filter">';
        $form.= '<option value="all">'.__('All feeds', wpfcrf).'</option>';
        foreach($feeds as $flux) {
            $form.= '<option value="'.$flux->post_name.'" '.((isset($_GET['wpfcrf_source_filter']) && stripslashes($_GET['wpfcrf_source_filter']) == $flux->ID) ? 'selected="selected"' : '').'>'.$flux->post_title.'</option>';
        }
        $form.= '</select>';

        echo $form;
    }
}
add_filter('parse_query', 'wpfcrf_filter_owner');
function wpfcrf_filter_owner($query) {
    global $typenow, $pagenow;
    if($pagenow == 'edit.php' && $typenow == 'wpfcrf_feeds' && $_GET['wpfcrf_source_filter'] && $query->is_main_query()) {
        $query->query_vars['meta_key'] = 'wpfcrf_feed_source_slug';
        $query->query_vars['meta_value'] = (string) $_GET['wpfcrf_source_filter'];
    }
}
?>