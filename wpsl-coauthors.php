<?php
/**
 * @package   WPSL_Coauthors
 * @author    Tobias Lounsbury <TobiasLounsbury@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/TobiasLounsbury/wpsl-coauthors
 * @copyright 2019 Tobias Lounsbury
 *
 * @wordpress-plugin
 * Plugin Name:       Store Locator: Co-Authors
 * Plugin URI:        https://github.com/TobiasLounsbury/wpsl-coauthors
 * Description:       Adds permission hooks to allow co-authors provided via Co-Authors+ to edit metadata for individual store locations
 * Version:           1.0.0
 * Author:            Tobias Lounsbury
 * Author URI:        http://TobiasLounsbury.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/TobiasLounsbury/wpsl-coauthors
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

//Define the current version number
define( 'WPSL_COAUTHORS_VERSION', '1.0.0' );


/**
 * Creates a new role for Store location co-authors
 */
function wpsl_coauthors_plugin_activate() {

  global $wp_roles;

  if ( class_exists( 'WP_Roles' ) ) {
    if ( !isset( $wp_roles ) ) {
      $wp_roles = new WP_Roles();
    }
  }

  if ( is_object( $wp_roles ) ) {
    add_role( 'wpsl_store_coauthor', 'Store Locator: Co-Author', array(
        'read'                   => true,
        'publish_stores'          => true,
        'edit_store'             => true,
        'read_store'             => true
    ) );
  }

}

/**
 * Remove the role created on activation
 */
function wpsl_coauthors_plugin_deactivate() {
  remove_role( 'wpsl_store_coauthor' );
}

/**
 * Alters the permission scheme to allow co-authors to edit stores
 *
 * @param $caps
 * @param $cap
 * @param $user_id
 * @param $args
 * @return array
 */
function wpsl_coauthors_map_meta_cap($caps, $cap, $user_id, $args) {
  $postId = (isset($args[0]) ? $args[0] : null);
  if($cap === "edit_post" && $postId && get_post_type($postId) == "wpsl_stores") {
    if(function_exists("is_coauthor_for_post") && is_coauthor_for_post( $user_id, $postId )) {
      return ["edit_store"];
    }
  }

  return $caps;
}

/**
 * Adds a new Store Locations widget and removes the activity widget
 */
function wpsl_coauthors_dashboard_widgets() {
  global $wp_meta_boxes;

  $user = wp_get_current_user();

  if (in_array("wpsl_store_coauthor", $user->roles)) {
    //Add the Store list widget
    wp_add_dashboard_widget('wpsl_coauthors_my_stores_widget', 'Store Locations', 'wpsl_coauthors_my_stores_widget');

    //Remove the activity widget
    if(array_key_exists("dashboard_activity",  $wp_meta_boxes['dashboard']['normal']['core'])) {
      unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
    }
  }
}

/**
 * Creates the new Store Locations Widget with links to the
 * store locations that the user is co-author of.
 */
function wpsl_coauthors_my_stores_widget() {
  global $current_user;
  get_currentuserinfo();

  $args = [
      'author' => $current_user->ID,
      'post_type' => "wpsl_stores",
      'suppress_filters' => false
  ];
  $stores = get_posts($args);

  foreach ($stores as $store) {
    echo edit_post_link("Edit Details", "<p>".$store->post_title." - ", "</p>", $store);
  }
}

//Register the needed hooks/filters
register_activation_hook( __FILE__, 'wpsl_coauthors_plugin_activate' );
register_deactivation_hook( __FILE__, 'wpsl_coauthors_plugin_deactivate' );
add_filter( 'map_meta_cap', 'wpsl_coauthors_map_meta_cap', 10, 4);
add_action('wp_dashboard_setup', 'wpsl_coauthors_dashboard_widgets');