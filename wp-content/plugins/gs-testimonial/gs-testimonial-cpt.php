<?php 

/**
* Registers a new post type
* @uses $wp_post_types Inserts new post type object into the list
*
* @param string  Post type key, must not exceed 20 characters
* @param array|string  See optional args description above.
* @return object|WP_Error the registered post type object, or an error object
*/
function GS_Testimonial_Slider() {

	$labels = array(
		'name'               => _x( 'Testimonials', 'golamsamdani' ),
		'singular_name'      => _x( 'Testimonial', 'golamsamdani' ),
		'menu_name'          => _x( 'Testimonials', 'admin menu', 'golamsamdani' ),
		'name_admin_bar'     => _x( 'Testimonial', 'add new on admin bar', 'golamsamdani' ),
		'add_new'            => _x( 'Add New', 'testimonial', 'golamsamdani' ),
		'add_new_item'       => __( 'Add New Testimonial', 'golamsamdani' ),
		'new_item'           => __( 'New Testimonial', 'golamsamdani' ),
		'edit_item'          => __( 'Edit Testimonial', 'golamsamdani' ),
		'view_item'          => __( 'View Testimonial', 'golamsamdani' ),
		'all_items'          => __( 'All Testimonials', 'golamsamdani' ),
		'search_items'       => __( 'Search Testimonials', 'golamsamdani' ),
		'parent_item_colon'  => __( 'Parent Testimonials:', 'golamsamdani' ),
		'not_found'          => __( 'No testimonials found.', 'golamsamdani' ),
		'not_found_in_trash' => __( 'No testimonials found in Trash.', 'golamsamdani' ),
	);

	$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'gs_testimonials' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-editor-quote',
			'supports'           => array( 'title', 'editor','thumbnail')
		);

		register_post_type( 'gs_testimonial', $args );
}

add_action( 'init', 'GS_Testimonial_Slider' );

// Register Theme Features (feature image for Logo)
if ( ! function_exists('gs_testimonial_theme_support') ) {

	function gs_testimonial_theme_support()  {
		// Add theme support for Featured Images
		add_theme_support( 'post-thumbnails', array( 'gs_testimonial' ) );
		// Add Shortcode support in text widget
		add_filter('widget_text', 'do_shortcode'); 
	}

	// Hook into the 'after_setup_theme' action
	add_action( 'after_setup_theme', 'gs_testimonial_theme_support' );
}