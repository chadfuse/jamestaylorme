<?php
/*
Plugin Name: Genesis Custom Footer
Plugin URI: https://www.nutsandboltsmedia.com/genesis-custom-footer/
Description: Allows you to change the Genesis footer credits from the Genesis Theme Settings page.
Version: 1.0.0
Author: Nuts and Bolts Media, LLC
Author URI: https://www.nutsandboltsmedia.com/

This plugin is released under the GPLv2 license. The images packaged with this plugin are the property of
their respective owners, and do not, necessarily, inherit the GPLv2 license.
*/

// Check to make sure a Genesis child theme is active
register_activation_hook(__FILE__, 'nabm_require_genesis');
function nabm_require_genesis() {
		
	$theme_info = get_theme_data(TEMPLATEPATH.'/style.css');
	
    if( basename(TEMPLATEPATH) != 'genesis' ) {
	    deactivate_plugins(plugin_basename(__FILE__)); // Deactivate if Genesis isn't present
        wp_die('Sorry, you can\'t use this plugin unless a <a href="http://my.studiopress.com/themes/" target="_blank" rel="nofollow">Genesis</a> theme is active. <a href="/wp-admin/plugins.php">Go Back</a>');
	}

}

// Add settings link on plugin page
add_filter("plugin_action_links_$plugin", 'nabm_custom_footer_settings_link' );
function nabm_custom_footer_settings_link($links) { 

	$plugin = plugin_basename(__FILE__); 
	$settings_link = '<a href="admin.php?page=genesis">Settings</a>';

  array_unshift($links, $settings_link); 
  return $links; 
}

// Register defaults
add_filter( 'genesis_theme_settings_defaults', 'nabm_custom_footer_defaults' );
function nabm_custom_footer_defaults( $defaults ) {
 
	$defaults['nabm_footer_creds'] = 'Copyright [footer_copyright] [footer_childtheme_link] &amp;middot; [footer_genesis_link] [footer_studiopress_link] &amp;middot; [footer_wordpress_link] &amp;middot; [footer_loginout]';
 
	return $defaults;
}

// Sanitization
add_action( 'genesis_settings_sanitizer_init', 'nabm_sanitization_filters' );
function nabm_sanitization_filters() {
	genesis_add_option_filter( 'safe_html', GENESIS_SETTINGS_FIELD, array( 'nabm_footer_creds' ) );
}

// Register metabox
add_action('genesis_theme_settings_metaboxes', 'nabm_footer_settings_box');
function nabm_footer_settings_box( $_genesis_theme_settings_pagehook ) {
	add_meta_box( 'nabm-genesis-settings', __( 'Genesis Custom Footer', 'nabm' ), 'nabm_footer_box', $_genesis_theme_settings_pagehook, 'main', 'high' );
}

// Create metabox
function nabm_footer_box() {
	?>
	<p><?php _e("Enter your custom credits text, including HTML if desired.", 'nabm_footer'); ?></p>
	<label>Custom Footer Text:</label>
	<textarea id="nabm_footer_creds" class="large-text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[nabm_footer_creds]" cols="78" rows="8" /><?php echo htmlspecialchars( genesis_get_option('nabm_footer_creds') ); ?></textarea>
    <p><?php _e('<strong>Default Text:</strong><br /><br /> <code>Copyright [footer_copyright] [footer_childtheme_link] &amp;middot; [footer_genesis_link] [footer_studiopress_link] &amp;middot; [footer_wordpress_link] &amp;middot; [footer_loginout]</code>', 'nabm_footer'); ?></p>
	<?php
}

// Remove other filters if they exist
add_action('after_setup_theme', 'nabm_remove_footer_filters' );
function nabm_remove_footer_filters() {
    remove_all_filters( 'genesis_footer_creds_text' );
}

// Customize the footer credits text
add_filter('genesis_footer_output', 'nabm_footer_creds_text', 10, 3);
function nabm_footer_creds_text($creds) {
	$custom_creds = genesis_get_option('nabm_footer_creds');
	if ($custom_creds) return $custom_creds;
	else return $creds;
}
