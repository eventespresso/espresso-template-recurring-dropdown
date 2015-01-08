<?php
/*
  Plugin Name: Event Espresso Template - Recurring Events Table w/ Dropdowns
  Plugin URI: http://www.eventespresso.com
  Description: This template creates a list of events, displayed in a table, with dropdowns selections for recurring events. It can display events by category and/or maximum number of days. [EVENT_CUSTOM_VIEW template_name="recurring-dropdown"]
  Version: 1.0
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2013 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA

*/


//Shortcode Example: [EVENT_CUSTOM_VIEW template_name="recurring-dropdown" max_days="30" category_identifier="concerts"].
//Requirements: CSS skills to customize styles, some renaming of the table columns, Espresso WP User Add-on (optional)
//The end of the action name (example: "action_hook_espresso_custom_template_") should match the name of the template. In this example, the last part the action name is "default",
add_action('action_hook_espresso_custom_template_recurring-dropdown','espresso_recurring_dropdown', 10, 1);
if (!function_exists('espresso_recurring_dropdown')) {
	function espresso_recurring_dropdown(){
		global $events, $org_options, $events_in_session, $ee_attributes;

		$button_text = __('Select a Date', 'event_espresso');
		//Check if Multi Event Registration is installed
		$multi_reg = false;
		if (function_exists('event_espresso_multi_reg_init')) {
			$multi_reg = true;
		}
		//Check if show_mer_icons has been set on the shortcode
		if(isset($ee_attributes['show_mer_icons'])) {
			$show_mer_icons = $ee_attributes['show_mer_icons'];
		} else {
			$show_mer_icons = false;
		}
		//Validate $show_mer_icons to be used as a boolean
		$show_mer_icons = filter_var($show_mer_icons, FILTER_VALIDATE_BOOLEAN);
		
		/* group recurring events */
		wp_register_script( 'jquery_dropdown', WP_PLUGIN_URL. "/".plugin_basename(dirname(__FILE__)) .'/js/jquery.dropdown.js', array('jquery'), '0.1', TRUE );
		wp_enqueue_script( 'jquery_dropdown' );
		wp_register_style( 'espresso_recurring_dropdown_stylesheet', WP_PLUGIN_URL. "/".plugin_basename(dirname(__FILE__)) .'/css/jquery.dropdown.css');
		wp_enqueue_style( 'espresso_recurring_dropdown_stylesheet' );
		
		/* group recurring events */
		$events_type_index = -1;
		$events_of_same_type = array();
		$recurrence_ids_array = array();

		//Check for custom templates
		if(function_exists('espresso_custom_template_locate')) {
			$custom_template_path = espresso_custom_template_locate("recurring-dropdown");
		} else {
			$custom_template_path = '';
		}

		if( !empty($custom_template_path) ) {
			//If custom template found include here
			include( $custom_template_path );
		} else {
			//Otherwise use the default template
			include( 'template.php' );
		}
	}
}

/**
 * hook into PUE updates
 */
//Update notifications
add_action('action_hook_espresso_template_recurring_dropdown_update_api', 'espresso_template_recurring_dropdown_load_pue_update');
function espresso_template_recurring_dropdown_load_pue_update() {
	global $org_options, $espresso_check_for_updates;
	if ( $espresso_check_for_updates == false )
		return;

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file
		require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
		$api_key = $org_options['site_license_key'];
		$host_server_url = 'http://eventespresso.com';
		$plugin_slug = array(
			'premium' => array('p'=> 'recurring-events-drop-down'),
			'prerelease' => array('b'=> 'recurring-events-drop-down-pr')
			);
		$options = array(
			'apikey' => $api_key,
			'lang_domain' => 'event_espresso',
			'checkPeriod' => '24',
			'option_key' => 'site_license_key',
			'options_page_slug' => 'event_espresso',
			'plugin_basename' => plugin_basename(__FILE__),
			'use_wp_update' => FALSE
		);
		$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
	}
}