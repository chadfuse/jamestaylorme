<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Layout
 * @author  StudioPress
 * @license GPL-2.0+
 * @link    http://my.studiopress.com/themes/genesis/
 */

add_action( 'genesis_setup', 'genesis_create_initial_layouts' );
/**
 * Register Genesis default layouts.
 *
 * Genesis comes with six layouts registered by default. These are:
 *
 *  - content-sidebar (default)
 *  - sidebar-content
 *  - content-sidebar-sidebar
 *  - sidebar-sidebar-content
 *  - sidebar-content-sidebar
 *  - full-width-content
 *
 * @since 1.4.0
 */
function genesis_create_initial_layouts() {

	// Common path to default layout images.
	$url = GENESIS_ADMIN_IMAGES_URL . '/layouts/';

	$layouts = apply_filters( 'genesis_initial_layouts', array(
		'content-sidebar' => array(
			'label'   => __( 'Content, Primary Sidebar', 'genesis' ),
			'img'     => $url . 'cs.gif',
			'default' => is_rtl() ? false : true,
		),
		'sidebar-content' => array(
			'label'   => __( 'Primary Sidebar, Content', 'genesis' ),
			'img'     => $url . 'sc.gif',
			'default' => is_rtl() ? true : false,
		),
		'content-sidebar-sidebar' => array(
			'label' => __( 'Content, Primary Sidebar, Secondary Sidebar', 'genesis' ),
			'img'   => $url . 'css.gif',
		),
		'sidebar-sidebar-content' => array(
			'label' => __( 'Secondary Sidebar, Primary Sidebar, Content', 'genesis' ),
			'img'   => $url . 'ssc.gif',
		),
		'sidebar-content-sidebar' => array(
			'label' => __( 'Secondary Sidebar, Content, Primary Sidebar', 'genesis' ),
			'img'   => $url . 'scs.gif',
		),
		'full-width-content' => array(
			'label' => __( 'Full Width Content', 'genesis' ),
			'img'   => $url . 'c.gif',
		),
	), $url );

	foreach ( (array) $layouts as $layout_id => $layout_args ) {
		genesis_register_layout( $layout_id, $layout_args );
	}

}

/**
 * Register new layouts in Genesis.
 *
 * Modifies the global `$_genesis_layouts` variable.
 *
 * The support `$args` keys are:
 *
 *  - label (Internationalized name of the layout),
 *  - img   (URL path to layout image),
 *  - type  (Layout type).
 *
 * Although the 'default' key is also supported, the correct way to change the default is via the
 * `genesis_set_default_layout()` function to ensure only one layout is set as the default at one time.
 *
 * @since 1.4.0
 *
 * @see genesis_set_default_layout() Set a default layout.
 *
 * @global array $_genesis_layouts Holds all layouts data.
 *
 * @param string $id   ID of layout.
 * @param array  $args Layout data.
 * @return bool|array Return `false` if ID is missing or is already set. Return merged `$args` otherwise.
 */
function genesis_register_layout( $id = '', $args = array() ) {

	global $_genesis_layouts;

	if ( ! is_array( $_genesis_layouts ) )
		$_genesis_layouts = array();

	// Don't allow empty $id, or double registrations.
	if ( ! $id || isset( $_genesis_layouts[$id] ) )
		return false;

	$defaults = array(
		'label' => __( 'No Label Selected', 'genesis' ),
		'img'   => GENESIS_ADMIN_IMAGES_URL . '/layouts/none.gif',
		'type'  => 'site',
	);

	$args = wp_parse_args( $args, $defaults );

	$_genesis_layouts[$id] = $args;

	return $args;

}

/**
 * Set a default layout.
 *
 * Allow a user to identify a layout as being the default layout on a new install, as well as serve as the fallback layout.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layouts data.
 *
 * @param string $id ID of layout to set as default.
 * @return bool|string Return `false` if ID is empty or layout is not registered. Return ID otherwise.
 */
function genesis_set_default_layout( $id = '' ) {

	global $_genesis_layouts;

	if ( ! is_array( $_genesis_layouts ) )
		$_genesis_layouts = array();

	// Don't allow empty $id, or unregistered layouts.
	if ( ! $id || ! isset( $_genesis_layouts[$id] ) )
		return false;

	// Remove default flag for all other layouts.
	foreach ( (array) $_genesis_layouts as $key => $value ) {
		if ( isset( $_genesis_layouts[$key]['default'] ) )
			unset( $_genesis_layouts[$key]['default'] );
	}

	$_genesis_layouts[$id]['default'] = true;

	return $id;

}

/**
 * Unregister a layout in Genesis.
 *
 * Modifies the global $_genesis_layouts variable.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $id ID of the layout to unregister.
 * @return bool `false` if ID is empty, or layout is not registered, `true` if unregister is successful.
 */
function genesis_unregister_layout( $id = '' ) {

	global $_genesis_layouts;

	if ( ! $id || ! isset( $_genesis_layouts[$id] ) )
		return false;

	unset( $_genesis_layouts[$id] );

	return true;

}

/**
 * Return all registered Genesis layouts.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $type Layout type to return. Leave empty to return all types.
 * @return array Registered layouts.
 */
function genesis_get_layouts( $type = '' ) {

	global $_genesis_layouts;

	// If no layouts exists, return empty array.
	if ( ! is_array( $_genesis_layouts ) ) {
		$_genesis_layouts = array();
		return $_genesis_layouts;
	}

	// Return all layouts, if no type specified.
	if ( '' === $type )
		return $_genesis_layouts;

	$layouts = array();

	// Cycle through looking for layouts of $type.
	foreach ( (array) $_genesis_layouts as $id => $data ) {
		if ( $data['type'] === $type )
			$layouts[$id] = $data;
	}

	return $layouts;

}

/**
 * Return registered layouts in a format the WordPress Customizer accepts.
 *
 * @since 2.0.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $type Layout type to return. Leave empty to return all types.
 * @return array Registered layouts.
 */
function genesis_get_layouts_for_customizer( $type = '' ) {

	$layouts = genesis_get_layouts( $type );

	if ( empty( $layouts ) )
		return $layouts;

	// Simplified layout array.
	foreach ( (array) $layouts as $id => $data )
		$customizer_layouts[$id] = $data['label'];

	return $customizer_layouts;

}

/**
 * Return the data from a single layout, specified by the $id passed to it.
 *
 * @since 1.4.0
 *
 * @param string $id ID of the layout to return data for.
 * @return null|array `null` if ID is not set, or layout is not registered. Array of layout data
 *                    otherwise, with 'label' and 'image' (and possibly 'default') sub-keys.
 */
function genesis_get_layout( $id ) {

	$layouts = genesis_get_layouts();

	if ( ! $id || ! isset( $layouts[$id] ) )
		return;

	return $layouts[$id];

}

/**
 * Return the layout that is set to default.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @return string Return ID of the layout, or `nolayout`.
 */
function genesis_get_default_layout() {

	global $_genesis_layouts;

	$default = 'nolayout';

	foreach ( (array) $_genesis_layouts as $key => $value ) {
		if ( isset( $value['default'] ) && $value['default'] ) {
			$default = $key;
			break;
		}
	}

	return $default;

}

/**
 * Determine if the site has more than 1 registered layouts.
 *
 * @since 2.3.0
 *
 * @return bool `true` if more than one layout, `false` otherwise.
 */
function genesis_has_multiple_layouts() {

	$layouts = genesis_get_layouts();

	if ( count( $layouts ) < 2 ) {
		return false;
	}

	return true;

}

/**
 * Return the site layout for different contexts.
 *
 * Checks both the custom field and the theme option to find the user-selected site layout, and returns it.
 *
 * Applies `genesis_site_layout` filter early to allow shortcutting of function.
 *
 * @since 0.2.2
 *
 * @global WP_Query $wp_query Query object.
 *
 * @param bool $use_cache Conditional to use cache or get fresh.
 * @return string Key of site layout or filtered value of `genesis_site_layout`.
 */
function genesis_site_layout( $use_cache = true ) {

	// Allow child theme to short-circuit this function.
	$pre = apply_filters( 'genesis_site_layout', null );
	if ( null !== $pre )
		return $pre;

	// If we're supposed to use the cache, setup cache. Use if value exists.
	if ( $use_cache ) {

		// Setup cache.
		static $layout_cache = '';

		// If cache is populated, return value.
		if ( '' !== $layout_cache )
			return esc_attr( $layout_cache );

	}

	global $wp_query;

	// If viewing a singular page or post, or the posts page, but not the front page.
	if ( is_singular() || ( is_home() && ! genesis_is_root_page() ) ) {
		$post_id      = is_home() ? get_option( 'page_for_posts' ) : null;
		$custom_field = genesis_get_custom_field( '_genesis_layout', $post_id );
		$site_layout  = $custom_field ? $custom_field : genesis_get_option( 'site_layout' );
	}

	// If viewing a taxonomy archive.
	elseif ( is_category() || is_tag() || is_tax() ) {

		$term        = $wp_query->get_queried_object();
		$term_layout = $term ? get_term_meta( $term->term_id, 'layout', true) : '';
		$site_layout = $term_layout ? $term_layout : genesis_get_option( 'site_layout' );

	}

	// If viewing a supported post type.
	elseif ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
		$site_layout = genesis_get_cpt_option( 'layout' ) ? genesis_get_cpt_option( 'layout' ) : genesis_get_option( 'site_layout' );
	}

	// If viewing an author archive.
	elseif ( is_author() ) {
		$site_layout = get_the_author_meta( 'layout', (int) get_query_var( 'author' ) ) ? get_the_author_meta( 'layout', (int) get_query_var( 'author' ) ) : genesis_get_option( 'site_layout' );
	}

	// Else pull the theme option.
	else {
		$site_layout = genesis_get_option( 'site_layout' );
	}

	// Use default layout as a fallback, if necessary.
	if ( ! genesis_get_layout( $site_layout ) )
		$site_layout = genesis_get_default_layout();

	// Push layout into cache, if caching turned on.
	if ( $use_cache )
		$layout_cache = $site_layout;

	// Return site layout.
	return esc_attr( $site_layout );

}

/**
 * Output the form elements necessary to select a layout.
 *
 * You must manually wrap this in an HTML element with the class of `genesis-layout-selector` in order for the CSS and
 * JavaScript to apply properly.
 *
 * Supported `$args` keys are:
 *  - name     (default is ''),
 *  - selected (default is ''),
 *  - echo     (default is true).
 *
 * The Genesis admin script is enqueued to ensure the layout selector behaviour (amending label class to add border on
 * selected layout) works.
 *
 * @since 1.7.0
 *
 * @param array $args Optional. Function arguments. Default is empty array.
 * @return string HTML markup of labels, images and radio inputs for layout selector.
 */
function genesis_layout_selector( $args = array() ) {

	// Enqueue the JavaScript.
	genesis_load_admin_js();

	// Merge defaults with user args.
	$args = wp_parse_args(
		$args,
		array(
			'name'     => '',
			'selected' => '',
			'type'     => '',
			'echo'     => true,
		)
	);

	$output = '';

	foreach ( genesis_get_layouts( $args['type'] ) as $id => $data ) {
		$class = $id == $args['selected'] ? ' selected' : '';

		$output .= sprintf(
			'<label class="box%2$s" for="%5$s"><span class="screen-reader-text">%1$s </span><img src="%3$s" alt="%1$s" /><input type="radio" name="%4$s" id="%5$s" value="%5$s" %6$s class="screen-reader-text" /></label>',
			esc_attr( $data['label'] ),
			esc_attr( $class ),
			esc_url( $data['img'] ),
			esc_attr( $args['name'] ),
			esc_attr( $id ),
			checked( $id, $args['selected'], false )
		);
	}

	// Echo or return output.
	if ( $args['echo'] )
		echo $output;
	else
		return $output;

}

/**
 * Potentially echo or return a structural wrap div.
 *
 * A check is made to see if the `$context` is in the `genesis-structural-wraps` theme support data. If so, then the
 * `$output` may be echoed or returned.
 *
 * @since 1.6.0
 *
 * @param string $context The location ID.
 * @param string $output  Optional. The markup to include. Can also be 'open'
 *                        (default) or 'closed' to use pre-determined markup for consistency.
 * @param bool   $echo    Optional. Whether to echo or return. Default is true (echo).
 * @return null|string Wrap HTML, or `null` if `genesis-structural-wraps` support is falsy.
 */
function genesis_structural_wrap( $context = '', $output = 'open', $echo = true ) {

	$wraps = get_theme_support( 'genesis-structural-wraps' );

	// If theme doesn't support structural wraps, bail.
	if ( ! $wraps )
		return;

	// Map of old $contexts to new $contexts.
	$map = array(
		'nav'    => 'menu-primary',
		'subnav' => 'menu-secondary',
		'inner'  => 'site-inner',
	);

	// Make the swap, if necessary.
	if ( $swap = array_search( $context, $map ) ) {
		if ( in_array( $swap, $wraps[0] ) )
			$wraps[0] = str_replace( $swap, $map[ $swap ], $wraps[0] );
	}

	if ( ! in_array( $context, (array) $wraps[0] ) )
		return '';

	// Save original output param.
	$original_output = $output;

	switch ( $output ) {
		case 'open':
			$output = sprintf( '<div %s>', genesis_attr( 'structural-wrap' ) );
			break;
		case 'close':
			$output = '</div>';
			break;
	}

	$output = apply_filters( "genesis_structural_wrap-{$context}", $output, $original_output );

	if ( $echo )
		echo $output;
	else
		return $output;

}

/**
 * Return layout key 'content-sidebar'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'content-sidebar'`.
 */
function __genesis_return_content_sidebar() {

	return 'content-sidebar';

}

/**
 * Return layout key 'sidebar-content'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'sidebar-content'`.
 */
function __genesis_return_sidebar_content() {

	return 'sidebar-content';

}

/**
 * Return layout key 'content-sidebar-sidebar'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'content-sidebar-sidebar'`.
 */
function __genesis_return_content_sidebar_sidebar() {

	return 'content-sidebar-sidebar';

}

/**
 * Return layout key 'sidebar-sidebar-content'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'sidebar-sidebar-content'`.
 */
function __genesis_return_sidebar_sidebar_content() {

	return 'sidebar-sidebar-content';

}

/**
 * Return layout key 'sidebar-content-sidebar'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'sidebar-content-sidebar'`.
 */
function __genesis_return_sidebar_content_sidebar() {

	return 'sidebar-content-sidebar';

}

/**
 * Return layout key 'full-width-content'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'full-width-content'`.
 */
function __genesis_return_full_width_content() {

	return 'full-width-content';

}