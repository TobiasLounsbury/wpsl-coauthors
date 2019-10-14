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
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'edit_store'             => true,
        'read_store'             => true
    ) );
  }

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

//Register the needed hooks/filters
register_activation_hook( __FILE__, 'wpsl_coauthors_plugin_activate' );
add_filter( 'map_meta_cap', 'wpsl_coauthors_map_meta_cap', 10, 4);