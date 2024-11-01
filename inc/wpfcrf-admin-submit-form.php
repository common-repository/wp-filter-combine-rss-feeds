<?php
/*========================================================*/
/*=== Insertion du bouton d'ajout/suppression des flux ===*/
/*========================================================*/
add_action('admin_head-edit.php', 'wpfcrf_add_custom_button');
function wpfcrf_add_custom_button() {
    global $current_screen;
    if('wpfcrf_feeds' != $current_screen->post_type) {
        return;
    }
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Ajout du bouton d'importation
                var htmlSubmit = '<form class="wpfcrf-submit" method="post" action="" style="padding:.5em 0">';
                    htmlSubmit+= '<input type="submit" class="page-title-action" name="wpfcrf-submit-import-feeds-items" value="<?php _e('Import feeds items', wpfcrf); ?>"/>';
                    htmlSubmit+= "</form>";

                var html = '<form class="wpfcrf-delete" method="post" action="">';
                html+= '<input type="submit" class="delete button-small page-title-action" name="wpfcrf-delete-all-feeds-items" value="<?php _e('Delete all feeds items', wpfcrf); ?>"/>';
                html+= "</form>";

                $($(".wrap .wp-heading-inline")[0]).append(htmlSubmit);
                $($(".wrap .wp-heading-inline")[0]).append(html);
            });
        </script>
    <?php
}

add_action('admin_head-edit.php', 'wpfcrf_add_custom_button_source');
function wpfcrf_add_custom_button_source() {
    global $current_screen;
    if('wpfcrf_sources' != $current_screen->post_type) {
        return;
    }
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Ajout du bouton d'importation
                var htmlSubmit = '<form class="wpfcrf-submit" method="post" action="">';
                    htmlSubmit+= '<input type="submit" class="page-title-action" name="wpfcrf-submit-import-feeds-items" value="<?php _e('Import feeds items', wpfcrf); ?>"/>';
                    htmlSubmit+= "</form>";

                // Ajout du bouton de suppression total
                var htmlDelete = '<form class="wpfcrf-delete" method="post" action="">';
                    htmlDelete+= '<input type="submit" class="page-title-action" name="wpfcrf-delete-all-sources" value="<?php _e('Delete all feeds sources', wpfcrf); ?>"/>';
                    htmlDelete+= '<input type="submit" class="page-title-action" name="wpfcrf-delete-all-sources-with-items" value="<?php _e('Delete all feeds sources and related items', wpfcrf); ?>"/>';
                    htmlDelete+= "</form>";
                
                // Positionnement des éléments dans le DOM
                $($(".wrap .wp-heading-inline")[0]).append(htmlSubmit);
                $($(".wrap .wp-heading-inline")[0]).append(htmlDelete);
                $('.wpfcrf-submit').prepend($("a.page-title-action"));
            });
        </script>
    <?php
}

/*======================================================*/
/*=== Gestion des imports et suppressions (edit.php) ===*/
/*======================================================*/
add_action('load-edit.php', 'post_listing_page');
function post_listing_page() {
    // Suppression de toutes les sources (si soumission)
    if(isset($_POST['wpfcrf-delete-all-sources'])) {
        // Lance la fonction d'enregistrement des options
        wpfcrf_delete_all_sources();
        add_action('admin_notices', 'wpfcrf_feeds_sources_delete_admin_notice_success');
        add_action('network_admin_notices', 'wpfcrf_feeds_sources_delete_admin_notice_success');
    }
    // Suppression de tous les items des flux (si soumission)
    if(isset($_POST['wpfcrf-delete-all-feeds-items'])) {
        // Lance la fonction d'enregistrement des options
        wpfcrf_delete_all_feeds_items();
        add_action('admin_notices', 'wpfcrf_feeds_items_delete_admin_notice_success');
        add_action('network_admin_notices', 'wpfcrf_feeds_items_delete_admin_notice_success');
    }

    // Ajout des items des flux (si soumission)
    if(isset($_POST['wpfcrf-submit-import-feeds-items'])) {
        // Récupération des données
        $fluxList = wpfcrf_get_sources();

        // Lance la fonction d'importation des items de flux
        wpfcrf_insert_all_feeds_items($fluxList);

        // Notice d'importation
        add_action('admin_notices', 'wpfcrf_feeds_items_importation_admin_notice_success');
        add_action('network_admin_notices', 'wpfcrf_feeds_items_importation_admin_notice_success');
    }

    // Ajout des items des flux (si soumission)
    if(isset($_POST['wpfcrf-delete-all-sources-with-items'])) {
        // Lance la fonction de suppression totale
        wpfcrf_delete_all_sources_with_items();

        // Notice d'importation
        add_action('admin_notices', 'wpfcrf_all_delete_admin_notice_success');
        add_action('network_admin_notices', 'wpfcrf_all_delete_admin_notice_success');
    }
}

/*=====================*/
/*=== Admin notices ===*/
/*=====================*/
function wpfcrf_feeds_sources_delete_admin_notice_success() {
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>'.__('All sources have been successfully removed.', wpfcrf).'</p>';
    echo '</div>';
}
function wpfcrf_feeds_items_delete_admin_notice_success() {
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>'.__('All feeds items have been successfully removed.', wpfcrf).'</p>';
    echo '</div>';
}
function wpfcrf_all_delete_admin_notice_success($count = 1) {
    echo '<div class="notice notice-success is-dismissible">';
    if($count === 1) {
        echo '<p>'.__('Feed source and related items have been successfully removed.', wpfcrf).'</p>';
    } else {
        echo '<p>'.__('Feeds sources and related items have been successfully removed.', wpfcrf).'</p>';
    }
    echo '</div>';
}
function wpfcrf_feeds_items_importation_admin_notice_success() {
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>'.__('Import successfully completed.', wpfcrf).'</p>';
    echo '</div>';
}

// Admin notice avec paramètres (on doit passer par une classe ici)
class wpfcrf_feeds_importation_admin_notice_error {
    private $_errors;

    function __construct($errors = array()) {
        $this->_errors = $errors;
        // On ajoute l'action admin_notices pour renvoyer vers l'objet
        add_action('admin_notices', array($this, 'errorMessages'));
        add_action('network_admin_notices', array($this, 'errorMessages'));
    }

    function errorMessages() {
        if(is_array($this->_errors) && !empty($this->_errors)) {
            echo '<div class="notice notice-error is-dismissible">';
            foreach($this->_errors as $error) {
                echo "<p>";
                printf(__('Unable to completely read this RSS feed: %s', wpfcrf), $error['feed_link']);
                echo "<br/>";
                printf(__('Name: %s | ID: %d', wpfcrf), $error['feed_name'], $error['feed_ID']);
                echo "</p>";
            }
            echo '</div>';
        }
    }
}
?>