<?php
/**
 * Plugin Name: Cruise Model [Post Type]
 * Plugin URI: https://www.bonseo.es/
 * Description: Modelo de Cruceros
 * Author: jjlmoya
 * Author URI: https://www.bonseo.es/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * @package BS
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once plugin_dir_path(__FILE__) . '/Cruise.php';
function bs_cruise_get_post_type()
{
	return Cruise::getInstance('Crucero', 'Cruceros', "crucero",
		array(
			"distance" => array(
				"name" => "Distancia",
				"value" => "distance",
				"input" => "number"
			),
			"days" => array(
				"name" => "Nº Días",
				"value" => "days",
				"input" => "number"
			),
			"price" => array(
				"name" => "Precio",
				"value" => "price",
				"input" => "number"
			),
			"affiliateLink" => array(
				"name" => "Enlace de Afiliados",
				"value" => "affiliateLink",
				"input" => "text"
			),
			"affiliateCTA" => array(
				"name" => "CTA de Afiliados",
				"value" => "affiliateCTA",
				"input" => "text"
			),
			"company" => array(
				"name" => "Compañía",
				"value" => "company",
				"input" => "text"
			)
		)
	);
}

function bs_cruise_register_post_type()
{
	$model = bs_cruise_get_post_type();
	$labels = array(
		"name" => __($model->plural, "custom-post-type-ui"),
		"singular_name" => __($model->singular, "custom-post-type-ui"),
	);

	$args = array(
		"label" => __($model->plural, "custom-post-type-ui"),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		'menu_icon' => $model->icon,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array("slug" => $model->path, "with_front" => true),
		"query_var" => true,
		"supports" =>
			array("title",
				"editor",
				"thumbnail",
				"custom-fields",
				"excerpt"),
	);

	register_post_type($model->db, $args);
}

function bs_cruise_create_custom_params()
{
	$model = bs_cruise_get_post_type();
	foreach ($model->customFields as $customField) {
		add_action('add_meta_boxes', $model->nameSpace . '_' . $customField["value"] . '_register');
	}
}

function bs_cruise_register($customType)
{
	$model = bs_cruise_get_post_type();
	$customField = $model->customFields;
	$customField = $customField[$customType];
	add_meta_box(
		$model->db . '_' . $customField['value'],
		$customField['name'],
		$model->nameSpace . '_' . $customField['value'] . '_callback',
		$model->db,
		'side',
		'high'
	);

}

function bs_cruise_callback($fieldType)
{
	$model = bs_cruise_get_post_type();
	$customField = $model->customFields;
	$customField = $customField[$fieldType];
	$dbEntry = $model->db . '_' . $customField['value'];
	global $post;
	wp_nonce_field(basename(__FILE__), $dbEntry);
	$value = get_post_meta($post->ID, $dbEntry, true);
	echo '<input type="' . $customField['input'] . '" name="' . $dbEntry . '" value="' . esc_textarea($value) . '" class="widefat">';
}

function bs_cruise_on_save($post_id)
{

	$model = bs_cruise_get_post_type();

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (isset($_POST['post_type']) && $_POST['post_type'] == $model->db) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	foreach ($model->customFields as $customField) {
		$customFieldEntry = $model->db . '_' . $customField['value'];
		if (!isset($_POST[$customFieldEntry])) {
			return;
		}
		$myValue = sanitize_text_field($_POST[$customFieldEntry]);
		update_post_meta($post_id, $customFieldEntry, $myValue);
	}
}

add_action('init', 'bs_cruise_register_post_type');
add_action('save_post', 'bs_cruise_on_save');
bs_cruise_create_custom_params();

function bs_cruise_distance_register()
{
	bs_cruise_register('distance');
}

function bs_cruise_distance_callback()
{
	bs_cruise_callback('distance');
}

function bs_cruise_days_register()
{
	bs_cruise_register('days');
}

function bs_cruise_days_callback()
{
	bs_cruise_callback('days');
}

function bs_cruise_price_register()
{
	bs_cruise_register('price');
}

function bs_cruise_price_callback()
{
	bs_cruise_callback('price');
}

function bs_cruise_affiliateLink_register()
{
	bs_cruise_register('affiliateLink');
}

function bs_cruise_affiliateLink_callback()
{
	bs_cruise_callback('affiliateLink');
}

function bs_cruise_affiliateCTA_register()
{
	bs_cruise_register('affiliateCTA');
}

function bs_cruise_affiliateCTA_callback()
{
	bs_cruise_callback('affiliateCTA');
}

function bs_cruise_company_register()
{
	bs_cruise_register('company');
}

function bs_cruise_company_callback()
{
	bs_cruise_callback('company');
}
