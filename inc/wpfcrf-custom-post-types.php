<?php
// Ajout du post-type personnalisé (portfolio) et des menus correspondants
function wp_fcrf_custom_post_type() {
    // Sources
    $labels = array(
        'name'                => 'RSS Feeds F&C',
        'add_new'             => __('Add a feed source', wpfcrf),
        'add_new_item'        => __('Add a feed source', wpfcrf),
        'new_item'            => __('New feed', wpfcrf),
        'singular_name'       => __('Feeds sources', wpfcrf),
        'all_items'           => __('Feeds sources', wpfcrf),
        'view_item'           => __('View feed source', wpfcrf),
        'edit_item'           => __('Edit a feed source', wpfcrf),
        'update_item'         => __('Update', wpfcrf),
        'search_items'        => __('Search for a source', wpfcrf),
        'not_found'           => __('No result', wpfcrf),
        'not_found_in_trash'  => __('No result in the trash', wpfcrf)
    );
    $args = array(
        'exclude_from_search'   => true,
        'publicly_querable'     => false,
        'show_in_nav_menus'     => false,
        'show_in_admin_bar'     => false,
        'public'                => true,
        'show_ui'               => true,
        'query_var'             => 'wpfcrf_feeds_sources',
        'menu_position'         => 200,
        'show_in_admin_bar'     => false,
        'rewrite'               => array(
            'slug'       => 'feeds_sources',
            'with_front' => false,
        ),
        'has_archive'           => true,
        // 'capability_type'       => 'wpfcrf_feed',
        'map_meta_cap'          => true,
        'supports'              => array('title'),
        'labels'                => $labels,
        'menu_icon'             => plugins_url('../img/icon-16x16.png',__FILE__),
        'register_meta_box_cb'  => 'wpfcrf_feeds_sources_metaboxes', // Ajoute une metabox personnalisée
    );
    register_post_type('wpfcrf_sources', $args);

    // Items
    $labels = array(
        'name'                => __('Feeds items', wpfcrf),
        'singular_name'       => __('Feeds items', wpfcrf),
        'all_items'           => __('Feeds items', wpfcrf),
        'view_item'           => __('View feed item', wpfcrf),
        'edit_item'           => __('Edit a feed item', wpfcrf),
        'update_item'         => __('Update', wpfcrf),
        'search_items'        => __('Search for an item', wpfcrf),
        'not_found'           => __('No result', wpfcrf),
        'not_found_in_trash'  => __('No result in the trash', wpfcrf)
    );
    $args = array(
        'exclude_from_search'   => true,
        'publicly_querable'     => false,
        'show_in_nav_menus'     => false,
        'show_in_admin_bar'     => false,
        'public'                => true,
        'show_ui'               => true,
        'query_var'             => 'wpfcrf_feeds',
        'menu_position'         => 200,
        'show_in_menu'          => true,
        'show_in_admin_bar'     => false,
        'show_in_menu'          => 'edit.php?post_type=wpfcrf_sources',
        'rewrite'               => array(
            'slug'      => 'feeds_items',
            'with_front' => false,
        ),
        'has_archive'           => true,
        // 'capability_type'       => 'wpfcrf_feed',
        'capabilities' => array(
            'create_posts' => false, // Supprime le bouton "Ajouter"
        ),
        'map_meta_cap'          => true,
        'supports'              => array('title', 'editor'),
        'labels'                => $labels,
        'menu_icon'             => plugins_url('../img/icon-16x16.png',__FILE__),
        'register_meta_box_cb'  => 'wpfcrf_feeds_items_metaboxes', // Ajoute une metabox personnalisée
    );
    register_post_type('wpfcrf_feeds', $args);
}
add_action('init', 'wp_fcrf_custom_post_type', 0);
?>