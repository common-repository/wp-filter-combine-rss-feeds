<?php
// Cron_schedule
add_filter('cron_schedules', 'wpfcrf_cron_intervals');
function wpfcrf_cron_intervals($schedules) {
	$frequencies = array(
		'10min' => array(
			'interval' => 10 * MINUTE_IN_SECONDS,
			'display' => __('Once every minutes', wpfcrf)
		),
		'30min' => array(
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display' => __('Once every thirty minutes', wpfcrf)
		),
		'1h' => array(
			'interval' => 1 * HOUR_IN_SECONDS,
			'display' => __('Once an hour', wpfcrf)
		),
		'2h' => array(
			'interval' => 2 * HOUR_IN_SECONDS,
			'display' => __('Once every two hours', wpfcrf)
		),
		'6h' => array(
			'interval' => 6 * HOUR_IN_SECONDS,
			'display' => __('Once every six hours', wpfcrf)
		),
		'12h' => array(
			'interval' => 12 * HOUR_IN_SECONDS,
			'display' => __('Once a half day', wpfcrf)
		),
		'1d' => array(
			'interval' => 1 * DAY_IN_SECONDS,
			'display' => __('Once a day', wpfcrf)
		),
		'4d' => array(
			'interval' => 4 * DAY_IN_SECONDS,
			'display' => __('Once every 4 days', wpfcrf)
		),
		'1w' => array(
			'interval' => 7 * DAY_IN_SECONDS,
			'display' => __('Once a week', wpfcrf)
		),
		'1m' => array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display' => __('Once a month', wpfcrf)
		),
	);
	return array_merge($schedules, $frequencies);
}

// Activation de la tâche CRON
// Voir aussi : https://codex.wordpress.org/Function_Reference/wp_schedule_single_event
function wpfcrf_active_cron() {
	if(!wp_next_scheduled('wpfcfc_cron_job')) {
		$interval = get_site_option('wpfcrf_frequency_cron');
		wp_schedule_event(time(), $interval, 'wpfcfc_cron_job');
	}
	add_action('wpfcfc_cron_job', 'wpfcrf_update_cron_date');
}

function wpfcrf_reactive_cron($interval) {
	// Désactivation de la tâche CRON
	wpfcrf_deactivate_cron();
	delete_site_option('wpfcrf_last_cron');

	// Relance la tâche CRON
	if(!wp_next_scheduled('wpfcfc_cron_job')) {
		wp_schedule_event(time(), $interval, 'wpfcfc_cron_job');
	}
	add_action('wpfcfc_cron_job', 'wpfcrf_update_cron_date');
}

// Tâche CRON
function wpfcrf_update_cron_date() {
	// Remet à jour la date d'update
	$timestamp = wp_next_scheduled('wpfcfc_cron_job');
	update_site_option('wpfcrf_last_cron', $timestamp);

	// Active la tâche à effectuer
	wpfcrf_enable_cron_task();
}
function wpfcrf_enable_cron_task() {
	// Lance la fonction d'enregistrement des options
    include_once('wpfcrf-import-feeds-items.php');

    // Mise à jour des flux
    $fluxList = wpfcrf_get_sources();
    wpfcrf_insert_all_feeds_items($fluxList);
}

// Désactivation de la tâche CRON
function wpfcrf_deactivate_cron() {
	wp_clear_scheduled_hook('wpfcfc_cron_job'); // Au cas où...
	$timestamp = wp_next_scheduled('wpfcfc_cron_job');
	wp_unschedule_event($timestamp, 'wpfcfc_cron_job');
}
?>