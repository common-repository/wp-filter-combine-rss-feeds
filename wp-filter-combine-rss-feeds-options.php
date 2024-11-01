<?php
// Fonction d'enregistrement des options
function wpfcrf_register_mysettings() {
	register_setting('wp-fcrf-settings-group', 'wpfcrf_form_position');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_form_placeholder');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_form_validation_button');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_feeds_pagination');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_posts_per_page');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_feeds_order');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_feeds_order_by');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_form_hide_select_name');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_form_hide_input');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_form_hide_button');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_date_format');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_source_prefix');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_date_prefix');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_separator');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_target');
	// register_setting('wp-fcrf-settings-group', 'wpfcrf_combine');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_frequency_cron');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_import_method');
	register_setting('wp-fcrf-settings-group', 'wpfcrf_template');

	// Si la tâche CRON change de périodicité
	if(isset($_POST['wpfcrf_frequency_cron']) && $_POST['wpfcrf_frequency_cron'] != get_site_option('wpfcrf_frequency_cron')) {
		if($_POST['wpfcrf_frequency_cron'] != 'none') {
			// Relance la tâche CRON
			$interval = $_POST['wpfcrf_frequency_cron'];
			update_site_option('wpfcrf_frequency_cron', $interval);
			wpfcrf_reactive_cron("1min");
		} else {
			// Désactive la tâche CRON
			wpfcrf_deactivate_cron();
		}
	}
}

// Fonction pour le formulaire d'option (page "Settings")
function wp_fcrf_callback() {
?>
<div class="wrap" id="wp-fcrf-container">
<h2><img src="<?php echo plugins_url('/img/slogan.png', __FILE__); ?>" alt="WP Filter & Combine RSS Feeds" height="42"></h2>

<div class="wpfcrf-intro">
	<p><?php
		_e("Use the shortcodes <strong>[wpfcrf]</strong> to display the combined RSS feeds (one or more feeds) and <strong>[wpfcrf-form]</strong> to display the search form within feeds (optional filtering).", wpfcrf);
	?></p>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
		<input type="button" id="btn-wpfcrf" data-text="[wpfcrf]" class="button-secondary" value="<?php _e('Copy [wpfcrf]', wpfcrf); ?>" />
		<span class="wpfcrf-clipboard button-secondary"><?php _e('Copied!', wpfcrf); ?></span>
		<input type="button" id="btn-wpfcrf-form" data-text="[wpfcrf-form]" class="button-secondary" value="<?php _e('Copy [wpfcrf-form]', wpfcrf); ?>" />
		<span class="wpfcrf-form-clipboard button-secondary"><?php _e('Copied!', wpfcrf); ?></span>
    </p>
</div>

<form method="post" action="">
    <?php settings_fields('wp-fcrf-settings-group'); ?>

    <div class="wp-fcrf-form-table">
        <div class="col-left">
			<h3><?php _e('Content settings', wpfcrf); ?></h3>
			<div class="wp-fcrf-readme">
				<h4><?php _e('Search settings', wpfcrf); ?></h4>
				<p><?php
					_e("Set the search form (optional) used to filter the results.", wpfcrf);
				?></p>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Show or not the search form?', wpfcrf); ?><br/>
					<em><?php _e('Choose the position of the search form (above feeds, below feeds, or hidden). It is possible to display the search form where you want it with the shortcode [wpfcrf-form].', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-form-position" class="subblock-form">
					<select name="wpfcrf_form_position">
						<option value="none"<?php if(get_site_option('wpfcrf_form_position') == "none") { echo ' selected="selected"'; } ?>><?php _e('Hide the form', wpfcrf); ?></option>
						<option value="top"<?php if(get_site_option('wpfcrf_form_position') == "top") { echo ' selected="selected"'; } ?>><?php _e('Above RSS feeds', wpfcrf); ?></option>
						<option value="bottom"<?php if(get_site_option('wpfcrf_form_position') == "bottom") { echo ' selected="selected"'; } ?>><?php _e('Below RSS feeds', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Placeholder for the search field', wpfcrf); ?><br/>
					<em><?php _e('Choose the replacement text that will be entered in the search field ("Search ..." by default).', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-form-placeholder" class="subblock-form">
					<input type="text" name="wpfcrf_form_placeholder" value="<?php if(get_site_option('wpfcrf_form_placeholder')) { echo get_site_option('wpfcrf_form_placeholder'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Text for the validation button', wpfcrf); ?><br/>
					<em><?php _e('Set the text for the validation button ("OK" by default).', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-form-validation-button" class="subblock-form">
					<input type="text" name="wpfcrf_form_validation_button" value="<?php if(get_site_option('wpfcrf_form_validation_button')) { echo get_site_option('wpfcrf_form_validation_button'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Pagination type', wpfcrf); ?><br/>
					<em><?php _e('Select the pagination type.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf_feeds_pagination" class="subblock-form">
					<select name="wpfcrf_feeds_pagination">
						<option value="numbered"<?php if(get_site_option('wpfcrf_feeds_pagination') == "numbered") { echo ' selected="selected"'; } ?>><?php _e('Numbered pagination', wpfcrf); ?></option>
						<option value="prevnext"<?php if(get_site_option('wpfcrf_feeds_pagination') == "prevnext") { echo ' selected="selected"'; } ?>><?php _e('Previous/Next links', wpfcrf); ?></option>
						<option value="none"<?php if(get_site_option('wpfcrf_feeds_pagination') == "none") { echo ' selected="selected"'; } ?>><?php _e('None', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('How many posts per page?', wpfcrf); ?><br/>
					<em><?php _e('Set the number of posts to display per page (0 to cancel pagination and display all results on one page).', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf_posts_per_page" class="subblock-form">
					<input type="number" min="0" name="wpfcrf_posts_per_page" value="<?php if(get_site_option('wpfcrf_posts_per_page')) { echo get_site_option('wpfcrf_posts_per_page'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Order of posting RSS feeds', wpfcrf); ?><br/>
					<em><?php _e('Select ascending or descending order according to your desires.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf_feeds_order" class="subblock-form">
					<select name="wpfcrf_feeds_order">
						<option value="DESC"<?php if(get_site_option('wpfcrf_feeds_order') == "DESC") { echo ' selected="selected"'; } ?>><?php _e('Descending (DESC)', wpfcrf); ?></option>
						<option value="ASC"<?php if(get_site_option('wpfcrf_feeds_order') == "ASC") { echo ' selected="selected"'; } ?>><?php _e('Ascending (ASC)', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Ranking type', wpfcrf); ?><br/>
					<em><?php _e('Select the sorting type for the publication display ("By publish date" recommended).', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf_feeds_order_by" class="subblock-form">
					<select name="wpfcrf_feeds_order_by">
						<option value="publish_date"<?php if(get_site_option('wpfcrf_feeds_order_by') == "publish_date") { echo ' selected="selected"'; } ?>><?php _e('By publish date', wpfcrf); ?></option>
						<option value="post_title"<?php if(get_site_option('wpfcrf_feeds_order_by') == "post_title") { echo ' selected="selected"'; } ?>><?php _e('By title', wpfcrf); ?></option>
						<option value="ID"<?php if(get_site_option('wpfcrf_feeds_order_by') == "ID") { echo ' selected="selected"'; } ?>><?php _e('By ID', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Hide search by name?', wpfcrf); ?><br/>
					<em><?php _e('If you select "yes", the search field by feed name will be disabled.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-form-hide-select-name" class="subblock-form">
					<select name="wpfcrf_form_hide_select_name">
						<option value="1"<?php if(get_site_option('wpfcrf_form_hide_select_name') == 1) { echo ' selected="selected"'; } ?>><?php _e('Yes', wpfcrf); ?></option>
						<option value="0"<?php if(get_site_option('wpfcrf_form_hide_select_name') == 0) { echo ' selected="selected"'; } ?>><?php _e('No', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Hide search input?', wpfcrf); ?><br/>
					<em><?php _e('If you select "yes", the search input field will be hidden.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-form-hide-input" class="subblock-form">
					<select name="wpfcrf_form_hide_input">
						<option value="1"<?php if(get_site_option('wpfcrf_form_hide_input') == 1) { echo ' selected="selected"'; } ?>><?php _e('Yes', wpfcrf); ?></option>
						<option value="0"<?php if(get_site_option('wpfcrf_form_hide_input') == 0) { echo ' selected="selected"'; } ?>><?php _e('No', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Hide validation button?', wpfcrf); ?><br/>
					<em><?php _e('If you select "yes", you must validate the search by pressing the "Enter" key.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-form-hide-button" class="subblock-form">
					<select name="wpfcrf_form_hide_button">
						<option value="1"<?php if(get_site_option('wpfcrf_form_hide_button') == 1) { echo ' selected="selected"'; } ?>><?php _e('Yes', wpfcrf); ?></option>
						<option value="0"<?php if(get_site_option('wpfcrf_form_hide_button') == 0) { echo ' selected="selected"'; } ?>><?php _e('No', wpfcrf); ?></option>
					</select>
				</div>
			</div>
        </div>
	
		<div class="col-middle">
			<h3><?php _e('Custom settings', wpfcrf); ?></h3>
			<div class="wp-fcrf-readme">
				<h4><?php _e('General settings to manage the display of RSS feeds.', wpfcrf); ?></h4>
				<p><?php
					_e("Change the following options to adjust the display of feeds.", wpfcrf);
				?></p>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Date format', wpfcrf); ?><br/>
					<em><?php _e('Choose the correct date format for RSS feeds. Use the <a href="https://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">official PHP documentation</a> to find the ideal format ("j F Y" in France for example).', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-date-format" class="subblock-form">
					<input type="text" name="wpfcrf_date_format" value="<?php if(get_site_option('wpfcrf_date_format')) { echo get_site_option('wpfcrf_date_format'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Source prefix (metadata)', wpfcrf); ?><br/>
					<em><?php _e('By default, the name of RSS feeds is preceded by "Source:".', wpfcrf); ?><br/><?php _e('Leave blank to hide the block.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-source-prefix" class="subblock-form">
					<input type="text" name="wpfcrf_source_prefix" value="<?php if(get_site_option('wpfcrf_source_prefix')) { echo get_site_option('wpfcrf_source_prefix'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Date prefix (metadata)', wpfcrf); ?><br/>
					<em><?php _e('By default, the published date of posts is preceded by "Published by".', wpfcrf); ?><br/><?php _e('Leave blank to hide the block.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-date-prefix" class="subblock-form">
					<input type="text" name="wpfcrf_date_prefix" value="<?php if(get_site_option('wpfcrf_date_prefix')) { echo get_site_option('wpfcrf_date_prefix'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Separator', wpfcrf); ?><br/>
					<em><?php _e('Choose the separator that will be placed between the feed name and the date (if both information is displayed).', wpfcrf); ?><br/><?php _e('Leave blank to hide the separator.', wpfcrf); ?> <?php _e('Automatically hidden if one of the prefixes above is hidden.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-separator" class="subblock-form">
					<input type="text" name="wpfcrf_separator" value="<?php if(get_site_option('wpfcrf_separator')) { echo get_site_option('wpfcrf_separator'); } ?>"/>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Open feeds links in a new tab?', wpfcrf); ?><br/>
					<em><?php _e('If you select "yes", the links will automatically have a target="_blank" to open the page in a new tab.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-target" class="subblock-form">
					<select name="wpfcrf_target">
						<option value="1"<?php if(get_site_option('wpfcrf_target') == 1) { echo ' selected="selected"'; } ?>><?php _e('Yes', wpfcrf); ?></option>
						<option value="0"<?php if(get_site_option('wpfcrf_target') == 0) { echo ' selected="selected"'; } ?>><?php _e('No', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Frequency of automatic update of RSS feeds', wpfcrf); ?><br/>
					<em><?php _e('Determine how often the feed items need to be updated.', wpfcrf); ?><br/>
						<?php
						if(get_site_option('wpfcrf_frequency_cron') != "none") {
							$wpfcrf_tz = get_option('timezone_string'); // Détermine la timezone pour PHP
							date_default_timezone_set($wpfcrf_tz); // Détermine la zone GMT (pour le décalage horaire)
							echo __('Next update:', wpfcrf).' '.date_i18n(__('Y-m-d H:i:s', wpfcrf), get_site_option('wpfcrf_last_cron'));
						}
						?>
					</em>
				</h5>
				<div id="wpfcrf_frequency_cron" class="subblock-form">
					<select name="wpfcrf_frequency_cron">
						<option value="none"<?php if(get_site_option('wpfcrf_frequency_cron') == "none") { echo ' selected="selected"'; } ?>><?php _e('No automatic update', wpfcrf); ?></option>
						<option value="10min"<?php if(get_site_option('wpfcrf_frequency_cron') == "10min") { echo ' selected="selected"'; } ?>><?php _e('Once every ten minutes', wpfcrf); ?></option>
						<option value="30min"<?php if(get_site_option('wpfcrf_frequency_cron') == "30min") { echo ' selected="selected"'; } ?>><?php _e('Once every thirty minutes', wpfcrf); ?></option>
						<option value="1h"<?php if(get_site_option('wpfcrf_frequency_cron') == "1h") { echo ' selected="selected"'; } ?>><?php _e('Once an hour', wpfcrf); ?></option>
						<option value="2h"<?php if(get_site_option('wpfcrf_frequency_cron') == "2h") { echo ' selected="selected"'; } ?>><?php _e('Once every two hours', wpfcrf); ?></option>
						<option value="6h"<?php if(get_site_option('wpfcrf_frequency_cron') == "6h") { echo ' selected="selected"'; } ?>><?php _e('Once every six hours', wpfcrf); ?></option>
						<option value="12h"<?php if(get_site_option('wpfcrf_frequency_cron') == "12h") { echo ' selected="selected"'; } ?>><?php _e('Once a half day', wpfcrf); ?></option>
						<option value="1d"<?php if(get_site_option('wpfcrf_frequency_cron') == "1d") { echo ' selected="selected"'; } ?>><?php _e('Once a day', wpfcrf); ?></option>
						<option value="4d"<?php if(get_site_option('wpfcrf_frequency_cron') == "4d") { echo ' selected="selected"'; } ?>><?php _e('Once every four days', wpfcrf); ?></option>
						<option value="1w"<?php if(get_site_option('wpfcrf_frequency_cron') == "1w") { echo ' selected="selected"'; } ?>><?php _e('Once a week', wpfcrf); ?></option>
						<option value="1m"<?php if(get_site_option('wpfcrf_frequency_cron') == "1m") { echo ' selected="selected"'; } ?>><?php _e('Once a month', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('Importation method in PHP (for developers)', wpfcrf); ?><br/>
					<em><?php _e('Select your preferred (and compatible) method for importing RSS feeds into PHP. If one method does not work, test the other to solve the problem.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf_import_method" class="subblock-form">
					<select name="wpfcrf_import_method">
						<option value="xmlreader"<?php if(get_site_option('wpfcrf_import_method') == "xmlreader") { echo ' selected="selected"'; } ?>><?php _e('XMLReader (recommended)', wpfcrf); ?></option>
						<option value="simplexml"<?php if(get_site_option('wpfcrf_import_method') == "simplexml") { echo ' selected="selected"'; } ?>><?php _e('SimpleXML', wpfcrf); ?></option>
					</select>
				</div>
			</div>
        </div>
		
        <div class="col-right">
			<h3><?php _e('Style settings', wpfcrf); ?></h3>
			<div class="wp-fcrf-readme">
				<h4><?php _e('Some elements to stylize the form and feeds', wpfcrf); ?></h4>
				<p><?php
					_e("Choose a template or create your own CSS style with the selectors shown below.", wpfcrf);
				?></p>
			</div>		
			<div class="bloc-form">
				<h5>
					<?php _e('Template', wpfcrf); ?><br/>
					<em><?php _e('Choose your favorite template. You can select "none" if you want to create your own CSS style.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-template" class="subblock-form">
					<select name="wpfcrf_template">
						<option value="none"<?php if(get_site_option('wpfcrf_template') == 'none') { echo ' selected="selected"'; } ?>><?php _e('None', wpfcrf); ?></option>
						<option value="dark-1"<?php if(get_site_option('wpfcrf_template') == 'dark-1') { echo ' selected="selected"'; } ?>><?php _e('Dark blocks', wpfcrf); ?></option>
						<option value="dark-2"<?php if(get_site_option('wpfcrf_template') == 'dark-2') { echo ' selected="selected"'; } ?>><?php _e('Dark text', wpfcrf); ?></option>
						<option value="white-1"<?php if(get_site_option('wpfcrf_template') == 'white-1') { echo ' selected="selected"'; } ?>><?php _e('White blocks', wpfcrf); ?></option>
						<option value="white-2"<?php if(get_site_option('wpfcrf_template') == 'white-2') { echo ' selected="selected"'; } ?>><?php _e('White text', wpfcrf); ?></option>
						<option value="blue-1"<?php if(get_site_option('wpfcrf_template') == 'blue-1') { echo ' selected="selected"'; } ?>><?php _e('Blue blocks', wpfcrf); ?></option>
						<option value="blue-2"<?php if(get_site_option('wpfcrf_template') == 'blue-2') { echo ' selected="selected"'; } ?>><?php _e('Blue text', wpfcrf); ?></option>
					</select>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('CSS selectors for RSS feed list', wpfcrf); ?><br/>
					<em><?php _e('See the following CSS selectors if you want to customize the display of RSS feeds.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-selectors-feeds" class="subblock-form">
					<strong>#wpfcrf-container, .wpfcrf-container</strong> => <span><?php _e('ID or class for the full container', wpfcrf); ?></span><br/>
					<strong>#wpfcrf-feeds, .wpfcrf-feeds</strong> => <span><?php _e('ID or class for the list of RSS feeds', wpfcrf); ?></span><br/>
					<strong>#wpfcrf-feeds-list, .wpfcrf-feeds-list</strong> => <span><?php _e('ID or class for the ul block', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-feed-item</strong> => <span><?php _e('class for each list item', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-feed-link</strong> => <span><?php _e('Container class (span) that includes the link of a feed (a)', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-feed-meta</strong> => <span><?php _e('class of the metadata block', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-source</strong> => <span><?php _e('source class (name of the feed)', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-separator</strong> => <span><?php _e('separator class', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-feed-date</strong> => <span><?php _e('date class', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-feed-no-result</strong> => <span><?php _e('class if no result is found during a search', wpfcrf); ?></span>
				</div>
			</div>
			<div class="bloc-form">
				<h5>
					<?php _e('CSS selectors for search form', wpfcrf); ?><br/>
					<em><?php _e('See the following CSS selectors if you want to customize the display of the search form.', wpfcrf); ?></em>
				</h5>
				<div id="wpfcrf-selectors-form" class="subblock-form">
					<strong>#wpfcrf-form, .wpfcrf-form</strong> => <span><?php _e('ID or class of the form', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-select</strong> => <span><?php _e('select class', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-input</strong> => <span><?php _e('search input class', wpfcrf); ?></span><br/>
					<strong>.wpfcrf-submit</strong> => <span><?php _e('submit button class', wpfcrf); ?></span>
				</div>
			</div>
        </div>
		
		<div class="clear-wpfcrf"></div>
    </div>
    
    <p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php } ?>