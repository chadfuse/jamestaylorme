<?php
/**
 * Smart Podcast Player
 * 
 * @package   SPP_Core
 * @author    jonathan@redplanet.io
 * @link      http://www.smartpodcastplayer.com
 * @copyright 2015 SPI Labs, LLC
 */

/**
 * @package SPP_Core
  * @author Jonathan Wondrusch <jonathan@redplanet.io?
 */
class SPP_Core {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '2.1.1';

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    0.8.0
	 *
	 * @var      string
	 */
	const PLUGIN_SLUG = 'askpat-player';

	/**
	 * Instance of this class.
	 *
	 * @since    0.8.0
	 *
	 * @var      object
	 */
	protected static $instance = null;


	/**
	 * Default (Green) Color for SPP/STP
	 *
	 * @since   1.0.2
	 *
	 * @var     string
	 */
	const SPP_DEFAULT_PLAYER_COLOR = '#60b86c';

	/**
	 * Soundcloud API URL 
	 *
	 * @since   1.0.3
	 *
	 * @var     string
	 */
	const SPP_SOUNDCLOUD_API_URL = 'https://api.soundcloud.com';

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Register public-facing JavaScript.
		add_action( 'wp_enqueue_scripts', array( 'SPP_Core', 'register_js' ) );

		add_shortcode( 'smart_track_player', array( $this, 'shortcode_smart_track_player' ) );
		add_shortcode( 'smart_track_player_latest', array( $this, 'shortcode_smart_track_player_latest' ) );
		add_shortcode( 'smart_podcast_player', array( $this, 'shortcode_smart_podcast_player' ) );
		add_shortcode( 'smart_podcast_player_assets', array( $this, 'enqueue_assets' ) );
		
		add_action( 'wp_ajax_nopriv_get_spplayer_tracks', array( 'SPP_Ajax_Feed', 'ajax_get_tracks' ) );
		add_action( 'wp_ajax_get_spplayer_tracks', array( 'SPP_Ajax_Feed', 'ajax_get_tracks' ) );

		add_action( 'wp_ajax_nopriv_fetch_track_data', array( 'SPP_Ajax_Tracks', 'fetch_track_data' ) );
		add_action( 'wp_ajax_fetch_track_data', array( 'SPP_Ajax_Tracks', 'fetch_track_data' ) );

		add_action( 'wp_ajax_nopriv_get_soundcloud_track', array( 'SPP_Ajax_Tracks', 'ajax_get_soundcloud_track' ) );
		add_action( 'wp_ajax_get_soundcloud_track', array( 'SPP_Ajax_Tracks', 'ajax_get_soundcloud_track' ) );

		add_action( 'template_redirect', array( $this, 'force_download' ), 1 );

		add_action( 'template_redirect', array( $this, 'cache_bust' ), 1 );

		// Use shortcodes in text widgets.
		add_filter('widget_text', 'do_shortcode');

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    0.8.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return self::PLUGIN_SLUG;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.8.0
	 */
	public function load_plugin_textdomain() {

		$domain = self::PLUGIN_SLUG;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	public static function register_js() {
		// Only one file for all of the Javascript, as it all auto loaded into main.min.js
		wp_register_script(
				self::PLUGIN_SLUG . '-plugin-script',
				SPP_ASSETS_URL . 'js/main.min.js',
				array( 'jquery', 'underscore' ),
				self::VERSION,
				true );

		// If we're on an Optimize Press pagebuilder, we actually enqueue the script at this point
		global $post;
		if( is_object( $post ) && get_post_meta( $post->ID, '_optimizepress_pagebuilder', true ) == 'Y' ) {
			self::enqueue_assets();
		}
	}
	
	public static function enqueue_assets( $html_assets = false ) {
	
		// This should only be done once per page, no matter how many shortcodes there are
		static $already_done = false;
		if( $already_done )
			return;
		$already_done = true;
		
		// Get the Soundcloud key if set, or just use our own
		$soundcloud_options = get_option( 'spp_player_soundcloud' );
		if( isset( $soundcloud_options['consumer_key'] ) && !empty( $soundcloud_options['consumer_key'] ) ) {
			$soundcloud_key = $soundcloud_options['consumer_key'];
		} else {
			$soundcloud_key = 'b38b3f6ee1cdb01e911c4d393c1f2f6e';
		}

		// Put the JS object with our general settings onto the page
		$importantStr = self::get_css_important_str() === ' !important' ? 'important' : '';
		wp_localize_script( self::PLUGIN_SLUG . '-plugin-script', 'AP_Player', array(
			'homeUrl' => home_url(),
			'baseUrl' => SPP_ASSETS_URL . 'js/',
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'soundcloudConsumerKey' => $soundcloud_key,
			'version' => self::VERSION,
			'importantStr' => $importantStr,
			'licensed' => self::is_paid_version()
		));
	
		// Enqueue our CSS file.  If the important option is set, use the override file
		if ( "important" == $importantStr ) {
			$css_file = "css/style-override.css";
		} else {
			$css_file = "css/style.css";
		}
		wp_enqueue_style( self::PLUGIN_SLUG . '-plugin-styles',
				SPP_ASSETS_URL . $css_file,
				array(), self::VERSION );
		
		// Enqueue the Javascript file, in the general case.
		//   - if this is Thrive Content Builder, do nothing. (HS 3831)
		//   - if user wants HTML assets, do that instead.
		if( ! self::is_thrive_content_builder() ) {
			if( $html_assets == 'true' ) {
				add_action( 'wp_footer', array( 'SPP_Core', 'add_assets_to_html' ) );
			} else {
				wp_enqueue_script( self::PLUGIN_SLUG . '-plugin-script' );
			}
		}
		
		// Enqueue our fonts
		wp_enqueue_style( self::PLUGIN_SLUG . '-plugin-fonts',
				'https://fonts.googleapis.com/css?family=Roboto:300,400italic,600italic,700italic,400,600,700',
				array(), self::VERSION);
	}
	
	public static function is_thrive_content_builder() {
		$tve = filter_input( INPUT_GET, 'tve' );
		if ( $tve == 'true' ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if( is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Output the shortcode for social customization or default it
	 * 
	 * @param  array  $atts Shortcode arguments array
	 * @return string $html Shortcode HTML
	 */
	public function shortcode_social_customize ( $atts = array(), $full_player = true) {

		$search_array = array(
				'social_twitter'=>'social_twitter','social_facebook'=>'social_facebook','social_gplus'=>'social_gplus',
				'social_linkedin'=>'social_linkedin','social_pinterest'=>'social_pinterest',
				'social_stumble'=>'social_stumble','social_email'=>'social_email');

		$html = '';

		$customized = false;

		if( isset( $atts['social'] ) && $atts['social'] == 'false' ) {
			$html .= ' data-social="' . $atts['social'] . '" ';
			return $html;
		}

		foreach ( $search_array as $value ) {
			if ( is_array ($atts) && array_key_exists( $value, $atts ) ) 
				$customized = true;
	
			if ( $customized )
				break;	
		}	 

		if ( !$customized ) {
			$atts['social']='true';
			$atts['social_twitter']='true';
			$atts['social_facebook']='true';
			$atts['social_gplus']='true';

			if ( $full_player )
				$atts['social_email']='true';
		}


		$html .= ' data-social="true" ';

		foreach ( $search_array as $key => $value ) {
			if( isset( $atts[$value] ) ) {
				$html .= ' data-' . $key . '="' . $atts[$value] . '" ';
			}
		}

		return $html;

	}
	
	public static function get_spp_attribute_defaults() {
		return array(
			'ajax_delay'            => 0,
			'background'            => 'default',
			'color'                 => self::SPP_DEFAULT_PLAYER_COLOR,
			'download'              => 'true',
			'episode_limit'         => '0',
			'featured_episode'      => '',
			'hashtag'               => '',
			'hide_listens'          => 'false',
			'hover_timestamp'       => 'true',
			'html_assets'           => 'false',
			'image'                 => 'not set',
			'numbering'             => '',
			'permalink'             => '',
			'poweredby'             => 'false',
			'show_episode_numbers'  => 'true',
			'show_name'             => '',
			'social'                => 'true',
			'social_twitter'        => 'false',
			'social_facebook'       => 'false',
			'social_linkedin'       => 'false',
			'social_gplus'          => 'false',
			'social_pinterest'      => 'false',
			'social_email'          => 'false',
			'sort'                  => 'newest',
			'speedcontrol'          => 'true',
			'style'                 => 'light',
			'subscribe_itunes'      => '',
			'subscribe_buzzsprout'  => '',
			'subscribe_googleplay'  => '',
			'subscribe_iheartradio' => '',
			'subscribe_pocketcasts' => '',
			'subscribe_soundcloud'  => '',
			'subscribe_stitcher'    => '',
			'subscribe_rss'         => 'true',
			'tweet_text'            => '',
			'twitter_username'      => '',
			'uid'                   => '',
			'url'                   => '',
			'view'                  => 'responsive',
		);
	}
	
	public static function get_spp_attribute_settings( $atts ) {
	
		$options = get_option( 'spp_player_defaults' );
		$advanced = get_option( 'spp_player_advanced' );
		$out = array();
		foreach( $atts as $key => $value ) {
			if( isset( $options[$key] ) )
				$out[$key] = $options[$key];
			else if ( isset( $advanced[$key] ) )
				$out[$key] = $advanced[$key];
		}
		
		// bg_color got changed to color, but we've kept the setting name
		if( isset( $options['bg_color'] ) )
			$out['color'] = $options['bg_color'];
		
		// background is stored as spp_background
		if( isset( $options['spp_background'] ) )
			$out['background'] = $options['spp_background'];
		
		return $out;
	}
	
	public static function spp_social_customize( $shortcode_atts ) {
		// This function maintains backwards compatibility with users' old
		// shortcodes.  If no "social_" attributes have been set, we set
		// Twitter, Facebook, Google+, and Email.  Otherwise, we use
		// the users' settings.
		foreach( $shortcode_atts as $key => $value ) {
			if( substr( $key, 0, 7 ) === 'social_' )
				// Found a setting - no changes.
				return $shortcode_atts;
		}
		// No social_s were set, so set these four.
		$shortcode_atts[ 'social_twitter' ] = 'true';
		$shortcode_atts[ 'social_facebook' ] = 'true';
		$shortcode_atts[ 'social_gplus' ] = 'true';
		$shortcode_atts[ 'social_email' ] = 'true';
		return $shortcode_atts;
	}

	/**
	 * Output the shortcode for the podcast player
	 * 
	 * @param  array  $atts Shortcode arguments array
	 * @return string $html Shortcode HTML
	 */
	public function shortcode_smart_podcast_player( $shortcode_atts ) {
		
		// For the empty shortcode [smart_podcast_player], use an empty list of atts
		if( ! is_array( $shortcode_atts ) )
			$shortcode_atts = array();
		
		// Set the social options if they weren't set
		$shortcode_atts = self::spp_social_customize( $shortcode_atts );
		
		// Get the attribute values in order:
		//   1) Static defaults 2) Settings page 3) Shortcode
		// Later definitions override earlier ones
		$default_atts = self::get_spp_attribute_defaults();
		$settings_page = self::get_spp_attribute_settings( $default_atts );
		$processed_atts = array_merge( $default_atts, $settings_page, $shortcode_atts );
		extract( $processed_atts );

		// Check URL to see if it is an html link or a url
		if( strpos( $url, ' href="' ) !== false ) {
			preg_match( '/href="(.+)"/', $url, $match);
			$url = parse_url( $match[1] );
		}
		
		// If the user put in the name of a known color, replace it with the hex code
		if( is_string( $color ) ) {
			$known_colors = self::get_free_colors();
			$color_lower = strtolower( $color );
			if( array_key_exists( $color_lower, $known_colors ) )
				$color = $known_colors[ $color_lower ];
		}
		
		// If 'social' was set to false, all social sharing is also false
		if( $social === 'false' ) {
			foreach( $default_atts as $attr => $val )
				if( substr( $attr, 0, 7 ) === 'social_' )
					$$attr = 'false';
		}
		
		// If this is showing as an unlicensed copy, force certain options
		if( !self::is_paid_version() ) {
			$color = self::SPP_DEFAULT_PLAYER_COLOR;
			$download = false;
			$social = false;
			$speedcontrol = false;
			$poweredby = true;
			$sort = 'newest';
		}
		
		// Set the player's unique ID
		$uid = uniqid();
		
		// If the highlight color is too close to the background color,
		// make the play/pause button contrast by setting it to black or white
		$play_pause_color = $color;
		if( $style === 'light' && SPP_Utils_Color::get_brightness($color) < 0.1 )
			$play_pause_color = '#FFFFFF';
		else if( $style === 'dark' && SPP_Utils_Color::get_brightness($color) > 0.9 )
			$play_pause_color = '#000000';
		
		// Add dynamic CSS based on the chosen color options
		$this->color_arrays[] = array(
				'$color' => $color,
				'$background_color' => $color,
				'$play_pause_color' => $play_pause_color,
		);
		add_action( 'wp_footer', array( &$this, 'add_dynamic_css' ) );
		
		// Put the JS and CSS on the page
		self::enqueue_assets( $html_assets );
		
		// If the tracks are cached, put the data for ten of them onto the page
		if( $cache = SPP_Ajax_Feed::get_cached_tracks( $url, $episode_limit ) ) {
			if( is_array( $cache ) && isset( $cache["tracks"] ) ) {
				if( $sort === 'oldest' )
					$cache["tracks"] = array_slice( $cache["tracks"], -10, 10 );
				else
					$cache["tracks"] = array_slice( $cache["tracks"], 0, 10 );
				wp_localize_script( self::PLUGIN_SLUG . '-plugin-script',
						'SmartPodcastPlayer_Tracks_' . $uid,
						$cache );
			}
		}

		// Create a div where the SPP will go
		$html = '<div class="smart-podcast-player-container ';
		
		// The class also includes the parts necessary for CSS: color, style, and view (mobile or responsive)
		$html .= ' smart-podcast-player-' . str_replace( '#', '', $color ) . '  spp-color-' . str_replace( '#', '', $color ) . ' ';
		if( $style != 'light' )
			$html .= 'smart-podcast-player-' . $style . ' ';
		if( $view === 'mobile' )
			$html .= 'smart-podcast-player-mobile-view ';
		$html .= '" ';

		// Create data attributes for all of the shortcode options
		// Each attribute gets included if it's not the default
		foreach( $default_atts as $attr => $default ) {
			if( $$attr !== $default ) {
				if( $attr === 'image' ) {
					// Rename 'image' to 'show_image' for HTML portability
					$html .= 'data-show_image="' . $$attr . '" ';
				} else {
					$html .= 'data-' . $attr . '="' . $$attr . '" ';
					// Note the double '$$' above: PHP "variable variables"
				}
			}
		}
		// 'paid' has special handling
		if( self::is_paid_version() )
			$html .= 'data-paid="true" ';

		$html .= '></div>';

		// Return the HTML div we've built
		if( self::is_thrive_content_builder() ) {
			// On Thrive Content Builder, there's no JS, so we put some visible stuff in
			return $html . '<p>Smart Podcast Player</p><p>Feed URL: ' . $url . '</p>';
		}
		return $html;	

	}

	/**
	 * Output the shortcode for the track player
	 * @param  array  $atts Shortcode arguments, needs to be extracted
	 * @return string $html Shortcode HTML
	 */
	public function shortcode_smart_track_player( $atts = array() ) {

		return $this->get_track_mp3_html( $atts, false );

	}
	

	/**
	 * Output the shortcode for the latest episode track player
	 * @param  array  $atts Shortcode arguments, needs to be extracted
	 * @return string $html Shortcode HTML
	 */
	public function shortcode_smart_track_player_latest( $atts = array() ) {

		// Check if we've already gotten the feed stored in a transient
		$transient_name = SPP_Transients::spp_transient_name( array(
				'purpose' => 'tracks from feed url',
				'url' => $atts['url'],
				'episode_limit' => 1 ) );
		$data = SPP_Transients::spp_get_transient( $transient_name );
		
		// If we have the transient data
		if( $data != null
				&& isset( $data['tracks'] )
				&& isset( $data['tracks'][0]->stream_url )
				&& isset( $data['tracks'][0]->title )
				&& isset( $data['tracks'][0]->show_name ) ) {
				
			// Set the URL and the track title/artist based on what's in the feed
			$atts['url'] = $data['tracks'][0]->stream_url;
			if( !isset( $atts['title'] ) ) {
				$atts['title'] = $data['tracks'][0]->title;
			}
			if( !isset( $atts['artist'] ) ) {
				$options = get_option( 'spp_player_defaults' );
				if( isset( $options['artist_name'] ) ) {
					$atts['artist'] = $options['artist_name'];
				} else {
					$atts['artist'] = $data['tracks'][0]->show_name;
				}
			}
			// Run it as a normal STP
			return $this->get_track_mp3_html( $atts, false);
		} else {
			// Run as an STP with a "latest" flag
			return $this->get_track_mp3_html( $atts, true );
		}

	}
	

	/**
	 * Output HTML for a single 
	 * @param  string 	$audio_url 	Link to an MP3
	 * @param  array 	$atts      	Array of shortcode attributes
	 * @return string 	$html 		HTML output for shortcode
	 */
	public function get_track_mp3_html( $atts, $is_latest_player ) {

		// Include the MP3 class to handle MP3 data
		require_once( SPP_PLUGIN_BASE . 'classes/mp3.php' );

		$options = get_option( 'spp_player_defaults' );
		$advanced = get_option( 'spp_player_advanced' );
		if( $options == false )
			$options = array();
		if( $advanced == false )
			$advanced = array();

		$seed = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$uniq_id = array();

		for ($i=0; $i < 8; $i++) { 
			$index = rand( 0, 61 );
			$uniq_id[] = $seed[$index];
		}

		$uid = implode( '', $uniq_id );

		extract( shortcode_atts( array(
			'url' => '',
			'view' => 'responsive',
			'style' => ( isset( $options['style'] ) ? $options['style'] : 'light' ),
			'show_numbering' => '',
			'title' => '',
			'image' => ( isset( $options['stp_image'] ) ? $options['stp_image'] : '' ),
			'download' => ( isset( $options['download'] ) ? $options['download'] : 'true' ),
			'social' => 'true',
			'social_twitter' => 'true',
			'social_facebook' => 'true',
			'social_gplus' => 'true',
			'social_linkedin' => 'false',
			'social_stumble' => 'false',
			'social_pinterest' => 'false',
			'social_email' => 'false',
			'speedcontrol' => 'true',
			'color' => ( isset( $options['bg_color'] ) ? $options['bg_color'] : self::SPP_DEFAULT_PLAYER_COLOR ),
			'loaded_color' => 'not set',
			'played_color' => 'not set',
			'background' => ( 'not set' ),
			'sticky' => '',
			'episode_timer' => 'down',
			'hover_timestamp' => 'true',
			'html_assets' => ( isset( $advanced['html_assets'] ) ? $advanced['html_assets'] : 'false' ),
			'artist' => ( isset( $options['artist_name'] ) ? $options['artist_name'] : '' ),
			'tweet_text' => '',
			'hashtag' => '',
			'twitter_username' => '',
			'permalink' => '',
		), $atts ) );
		
		self::enqueue_assets( $html_assets );
		
		// If the user typed the name of a known color, replace it with the hex code
		$free_colors = self::get_free_colors();
		$free_colors = array_change_key_case( $free_colors, CASE_LOWER );
		if( array_key_exists( $color, $free_colors ) ) 
			$color = $free_colors[ $color ];
		if( array_key_exists( $background, $free_colors ) )
			$background = $free_colors[ $background ];

		if( !self::is_paid_version() ) {
			$atts['color'] = self::SPP_DEFAULT_PLAYER_COLOR;
			$atts['download'] = false;
			$atts['social'] = false;
			$atts['speedcontrol'] = false;
			$atts['loaded_color'] = 'not set';
			$atts['played_color'] = 'not set';
		}
		
		if( $is_latest_player == false ) {
			// Check URL to see if it is an html link or a url
			// Users were very often including an HTML link (<a href=""></a>) 
			// instead of just a raw URL
			if( strpos( $url, 'href=' ) !== false ) {

				$xml = simplexml_load_string( $url );
				$list = $xml->xpath("//@href");

				$preparedUrls = array();
				foreach($list as $item) {
					$i = $item;
					$item = parse_url($item);
					$preparedUrls[] = $item['scheme'] . '://' .  $item['host'] . $item['path'];
				}

				$url = $preparedUrls[0];

			}

			$url = $url ? $url : '';
			
			// Verify the URL is for an MP3 or M4A file
			$is_audio = false;
			if( strpos( $url, 'soundcloud.com' ) !== false ) {
				$test = rtrim( $url, '/' );
				$count = substr_count( $test, '/' );
				if( $count > 3 && strpos( $url, '/sets/' ) === false ) {
					$is_audio = true;
				}		
			} else {
				if( strpos( $url, '.mp3' ) !== false || strpos( $url, '.m4a' ) !== false ) {
					$is_audio = true;
				}
			}
			// If it's not an MP3 or M4A, we give nothing out so as to not crash the page.
			if( !$is_audio )
				return;
		}
		
		if( $loaded_color === 'not set' ) {
			require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
			$brightness = SPP_Utils_Color::get_brightness( $color );
			$dimmed = SPP_Utils_Color::tint_hex( $color, 0.9 );
			if( $brightness < 0.2 ) {
				$loaded_color = SPP_Utils_Color::add_hex( $dimmed, '1a1a1a' );
			} else {
				$loaded_color = $dimmed;
			}
		}
		if( $played_color === 'not set' ) {
			require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
			$brightness = SPP_Utils_Color::get_brightness( $color );
			$dimmed = SPP_Utils_Color::tint_hex( $loaded_color, 0.9 );
			if( $brightness < 0.2 ) {
				$played_color = SPP_Utils_Color::add_hex( $dimmed, '1a1a1a' );
			} else {
				$played_color = $dimmed;
			}
		}

		$class = 'smart-track-player-container ';

		$transient = 'spp_cachem_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', md5($url) ), -32 );
		$no_cache = isset( $_GET['spp_no_cache'] ) && $_GET['spp_no_cache'] == 'true' ? 'true' : 'false';
		
		$data = SPP_Transients::spp_get_transient( $transient );
		
		if ( false === $data || $no_cache == 'true' ) {
			$data = array();
		}
		
		if( $background === 'default' || ( $background === 'not set'
				&& (!isset( $options['stp_background'] ) || $options['stp_background'] === 'default' ) ) ) {
			// Default background.  No data attribute required
			$background_color = $style === 'dark' ? '#2A2A2A' : '#EEEEEE';
		} else if( $background === 'blurred_logo' || $background === 'blurry_logo'
				|| $background === 'blurred logo' || $background === 'blurry logo'
				|| ( $background === 'not set'
						&& isset( $options['stp_background'] ) && $options['stp_background'] === 'blurred_logo' ) ) {
			// Blurry logo
			$background_color = $style === 'dark' ? '#2A2A2A' : '#EEEEEE';
		} else {
			require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
			if( SPP_Utils_Color::is_hex( $background ) ) {
				$background_color = '#' . str_replace( '#', '', $background );
			} else if( isset( $options['stp_background']) && $options['stp_background'] === 'color' ) {
				$background_color = '#' . $options['stp_background_color'];
			} else {
				// Default background.  No data attribute required
				$background_color = $style === 'dark' ? '#2A2A2A' : '#EEEEEE';
			}
		}

		// Add the color class every time
		$class .= ' stp-color-' . str_replace( '#', '', $color ) . '-' . str_replace( '#', '', $background_color ) . ' ';

		$html = '<div class="' . trim( $class );
		if( $is_latest_player ) {
			$html .= '" data-feed_url="' . $url . '" ';
		} else {
			$html .= '" data-url="' . $url . '" ';
		}

		if( $view == 'mobile' )
			$html .= 'data-view="mobile" ';

		if( $style != 'light' )
			$html .= 'data-style="' . $style . '" ';
		
		if( $background === 'default' || ( $background === 'not set'
				&& (!isset( $options['stp_background']) || $options['stp_background'] === 'default' ) ) ){
			// Default background.  No data attribute required
		} else if( $background === 'blurred_logo' || $background === 'blurry_logo'
				|| $background === 'blurred logo' || $background === 'blurry logo'
				|| ( $background === 'not set' && $options['stp_background'] === 'blurred_logo' ) ) {
			// Blurry logo
			$html .= 'data-background="blurred_logo" ';
		} else {
			require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
			if( SPP_Utils_Color::is_hex( $background ) ) {
				$html .= 'data-background="#' . str_replace( '#', '', $background ) . '" ';
			} else if( $options['stp_background'] === 'color' ) {
				$html .= 'data-background="' . $options['stp_background_color'] . '" ';
			} else {
				// Default background.  No data attribute required
			}
		}
		$play_pause_color = $color;
		require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
		if( $style === 'light' && SPP_Utils_Color::get_brightness($color) < 0.1 ) {
			$play_pause_color = '#FFFFFF';
		}
		if( $style === 'dark' && SPP_Utils_Color::get_brightness($color) > 0.9 ) {
			$play_pause_color = '#000000';
		}
		$this->color_arrays[] = array(
				'$color' => $color,
				'$link_color' => isset( $options['link_color'] )
						? $options['link_color']
						: self::SPP_DEFAULT_PLAYER_COLOR,
				'$loaded_color' => $loaded_color,
				'$play_pause_color' => $play_pause_color,
				'$played_color' => $played_color,
				'$background_color' => $background_color,
		);
		add_action( 'wp_footer', array( &$this, 'add_dynamic_css' ) );

		if( $show_numbering )
			$html .= 'data-numbering="' . $show_numbering . '" ';

		if( $image )
			$html .= 'data-image="' . $image . '" ';

		if( $download )
			$html .= 'data-download="' . $download . '" ';
		
		if( $tweet_text != '' )
			$html .= 'data-tweet_text="' . $tweet_text . '" ';
		if( $hashtag != '' )
			$html .= 'data-hashtag="' . $hashtag . '" ';
		if( $twitter_username != '' )
			$html .= 'data-twitter_username="' . $twitter_username . '" ';
		if( $permalink != '' )
			$html .= 'data-permalink="' . $permalink . '" ';

		if( $color != '' ) 
			$html .= 'data-color="' . str_replace( '#', '', $color ) . '" ';

		if( $title != '' ) {
			$html .= 'data-title="' . $title . '" ';
		} else {
			if( isset( $data['title'] ) )
				$html .= 'data-title="' . $data['title'] . '" ';
			elseif( isset( $data['album'] ) )
				$html .= 'data-title="' . $data['album'] . '" ';
			elseif( isset( $data['artist'] ) )
				$html .= 'data-title="' . $data['artist'] . '" ';
			elseif( isset( $options['show_name']  ) && $options['show_name'] != '' && !empty( $data ) )
				$html .= 'data-title="' . $options['show_name']  . '" ';
		}

		if( $artist != '' ) {
			$html .= 'data-artist="' . $artist . '" ';
		} else {
			if( isset( $data['artist'] ) )
				$html .= 'data-artist="' . $data['artist'] . '" ';
			elseif( isset( $data['album'] ) )
				$html .= 'data-title="' . $data['album'] . '" ';
			elseif( isset( $options['show_name']  ) && $options['show_name'] != '' && !empty( $data ) )
				$html .= 'data-title="' . $options['show_name']  . '" ';
		}
		
		if( self::is_paid_version() )
			$html .= 'data-paid="true" ';

		if( $social )
			$html .= $this->shortcode_social_customize( $atts, false );

		if( $speedcontrol )
			$html .= 'data-speedcontrol="' . $speedcontrol . '" ';

		if( !$is_latest_player && empty( $data ) && ( $title == '' )  )
			$html .= 'data-get="true" ';

		// Only one sticky STP is allowed
		static $have_sticky_stp = false;
		if( $sticky != "" && !$have_sticky_stp ) {
			$html .= 'data-sticky="' . $sticky . '" ';
			$have_sticky_stp = true;
		}
		
		if( $episode_timer == 'up' || $episode_timer == 'none' )
			$html .= 'data-episode_timer="' . $episode_timer . '" ';
		
		if( $hover_timestamp == 'false' )
			$html .= 'data-hover_timestamp="' . $hover_timestamp . '" ';

		$html .= 'data-uid="' . $uid . '" ';
		
		// For the regular STP, we have the file's URL, so we note it for download.
		// For the latest STP, the file's URL will be noted during the AJAX call.
		if( !$is_latest_player ) {
			require_once( SPP_PLUGIN_BASE . 'classes/download.php' );
			$download_id = SPP_Download::save_download_id($url);
			$html .= 'data-download_id="' . $download_id . '" ';
		}

		$html .= '></div>';
		
		if( self::is_thrive_content_builder() ) {
			return $html . '<p>Smart Track Player</p><p>URL: ' . $url . '</p>';
		} else {
			return $html;
		}

	}

	/**
	 * Use the SPP_Download class to force file downloads based on methods available
	 * 
	 * @return void
	 */
	public function force_download() {
		if( isset( $_GET['spp_download'] ) ) {
			require_once( SPP_PLUGIN_BASE . 'classes/download.php' );
			$download_id = $_GET['spp_download'];
			$download = new SPP_Download( $download_id );
			$download->get_file();
			exit;
		}
	}

	/**
	 * Delete the internal spp_cache when the URL variables are present
	 * 
	 * @return void
	 */
	public function cache_bust() {

		$bust = filter_input( INPUT_GET, 'spp_cache' );

		if( $bust == 'bust' ) {
		
			self::clear_cache();
			
		}

	}
	
	public function clear_cache() {
	
		if (current_user_can( 'update_plugins' ) ) {

			global $wpdb;

			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE autoload='no' AND option_name LIKE %s", '%spp\_cache%' ) );
			
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE autoload='no' AND option_name LIKE %s", '%spp\_license\_chk' ) );
			
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE autoload='no' AND option_name LIKE %s", '%spp\_feed_%' ) );
			
			// Remove custom CSS files
			$to_delete = array();
			if( $dh = opendir( SPP_ASSETS_PATH . 'css/' ) ) {
				while(( $file = readdir( $dh ) ) !== false ) {
					if( preg_match( '/custom.*\.css/', $file ) ) {
						$to_delete[] = SPP_ASSETS_PATH . 'css/' . $file ;
					}
				}
			}
			foreach( $to_delete as $file ) {
				unlink( $file );
			}
			
			// Mark that for the next 5 minutes, we're not using SimplePie's cache
			set_transient( 'spp_cache_clear_simplepie', true, 5 * MINUTE_IN_SECONDS );
		}
	
	}
	
	/**
	 * Tells whether this version is the paid or free version
	 *
	 * @return true if this is the paid version of the player, false otherwise
     *
	 * @since 1.0.2
	 */

	public static function is_paid_version() {
		$settings = get_option( 'spp_player_general' );

		if( !isset( $settings[ 'license_key' ] ) || empty( $settings[ 'license_key' ] ) ) 
			return false;

		$transient = 'spp_license_chk';
		
		if ( false !== ( $check = SPP_Transients::spp_get_transient( $transient ) ) ) {
			return true;
		}

		// plugin updater class confirms valid checks
		$optionName = 'external_updates-smart-podcast-player';
		$state = get_site_option($optionName, null);
		
		if ( !empty($state) && is_object($state) && isset($state->update) && is_object($state->update) ){
			set_transient( $transient, time(), 1 * WEEK_IN_SECONDS );
			return true;
		}

		return false;
	
	}
	
	/**
	 * Gets an array of the colors included in the free version
	 *
	 * @return an array of the colors included in the free version
	 *
	 * @since 1.0.20
	 */
	public  function get_free_colors() {
		return array( 'green' => self::SPP_DEFAULT_PLAYER_COLOR ,
				'blue' => '#006cb5',
				'yellow' => '#f0af00',
				'orange' => '#e7741b',
				'red' => '#dc1a26',
				'purple' => '#943f93' );
	}

	public static function upgrade_options() {

	    $version = get_option( 'spp_version' );

	    if ( $version != self::VERSION ) {

			// Update the version number
	    	update_option( 'spp_version', self::VERSION );
			
			// If the options don't exist, create them
			if( ! get_option( 'spp_player_general' ) )
				add_option( 'spp_player_general', array() );
			if( ! get_option( 'spp_player_defaults' ) )
				add_option( 'spp_player_defaults', array() );
			if( ! get_option( 'spp_player_advanced' ) )
				add_option( 'spp_player_advanced', array() );
			if( ! get_option( 'spp_player_soundcloud' ) )
				add_option( 'spp_player_soundcloud', array() );
	        
	        // Migrate ap_player options to spp_player options.
			// This change happened around August 2014.
	        if(( 
	        	!get_option( 'spp_player_general' ) || 
	        	!get_option( 'spp_player_defaults' ) || 
	        	!get_option( 'spp_player_soundcloud' ) 
	        	) && ( 
	        	get_option( 'ap_player_general' ) !== false || 
	        	get_option( 'ap_player_defaults' ) !== false || 
	        	get_option( 'ap_player_soundcloud' ) !== false 
	        )) { 
				$options = array(
					'ap_player_general' => 'spp_player_general',
					'ap_player_default' => 'spp_player_defaults',
					'ap_player_soundcloud' => 'spp_player_soundcloud'
				);
				foreach( $options as $old => $new ) {
					$option = get_option( $old );
					if( get_option( $new ) == false && $option !== false ) {
						add_option( $new, $option );
					}
					delete_option( $old );
				}
	        }
			
			// Before version 2.0, there was an option called "Subscription URL (usually iTunes)".
			// This was put into the link containing the RSS icon.  From version 2.0, there are
			// four+ options (iTunes, Stitcher, Soundcloud, Buzzsprout, +) plus regular RSS.  This function
			// populates the new options with data from the old option, if available, to try to
			// give the users a seamless transition.
			$options = get_option( 'spp_player_defaults' );
			if( $options !== false && array_key_exists( 'subscription', $options ) ) {
				$old = $options['subscription'];
				if( strpos( $old, 'itunes' ) !== false ) {
					$options['subscribe_itunes'] = $old;
				} else if( strpos( $old, 'stitcher' ) !== false ) {
					$options['subscribe_stitcher'] = $old;
				} else if( strpos( $old, 'soundcloud' ) !== false ) {
					$options['subscribe_soundcloud'] = $old;
				} else if( strpos( $old, 'buzzsprout' ) !== false ) {
					$options['subscribe_buzzsprout'] = $old;
				} else if( strpos( $old, 'play.google' ) !== false ) {
					$options['subscribe_googleplay'] = $old;
				} else if( strpos( $old, 'iheartradio' ) !== false ) {
					$options['subscribe_iheartradio'] = $old;
				} else if( strpos( $old, 'pocketcasts' ) !== false ) {
					$options['subscribe_pocketcasts'] = $old;
				}
				unset( $options['subscription'] );
				update_option( 'spp_player_defaults', $options );
			}

	    }

	}
	
	public static function get_css_important_str() {
	
		$advanced_options = get_option( 'spp_player_advanced');
		$css_important = isset( $advanced_options['css_important'] ) ? $advanced_options['css_important'] : "false";
		if ("true" == $css_important) {
			$important_str = " !important";
		} else {
			// Regular styles
			$important_str = "";
		}
		return $important_str;
	}
	
	// Write the assets straight to the HTML.  This functionality should be done via
	// wp_localize_script and wp_enqueue_script, but sometimes it isn't (HS 3933).
	public static function add_assets_to_html() {
	
		$soundcloud = get_option( 'spp_player_soundcloud' );
		$key = isset( $soundcloud[ 'consumer_key' ] ) ? $soundcloud[ 'consumer_key' ] : '';
		$importantStr = self::get_css_important_str() === ' !important' ? 'important' : '';
		
		$output = '';
		$output .= '<script type="text/javascript" src="';
		$output .=    includes_url( 'js/underscore.min.js' ) . '"></script>';
		
		$output .= '<script type="text/javascript">';
		$output .=    '/* <![CDATA[ */';
		$output .=    'var AP_Player = {';
		$output .=       str_replace('/', '\\/', '"homeUrl":"' . home_url() . '",');
		$output .=       str_replace('/', '\\/', '"baseUrl":"' . SPP_ASSETS_URL .'js/') . '",';
		$output .=       str_replace('/', '\\/', '"ajaxurl":"' . admin_url( 'admin-ajax.php' ) ) .'",';
		$output .=       '"soundcloudConsumerKey":"' . $key . '",';
		$output .=       '"version":"' . self::VERSION . '",';
		$output .=       '"importantStr":"' . $importantStr . '",';
		$output .=       '"licensed":"' . self::is_paid_version() . '",';
		$output .=       '"html_assets":"' . 'true' . '",';
		$output .=    '};';
		$output .=    '/* ]]> */';
		
		$output .= '</script>';
		$output .= '<script type="text/javascript" src="';
		$output .=    SPP_ASSETS_URL . 'js/main.min.js"></script>';
		
		$output .= '<link rel="stylesheet" id="askpat-player-plugin-styles-css" href="';
		$output .=    SPP_ASSETS_URL . 'css/style.css" type="text/css" media="all">';
		
		echo $output;

	}
	
	public function parse_dynamic_css_fragment( $expr, $color_array ) {
	
		require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
		preg_match( '/\s*(\$?\w+),?\s*([\d\.]*)/', $expr, $matches );
		if( $matches[1] === '$color'
				|| $matches[1] === '$link_color'
				|| $matches[1] === '$loaded_color'
				|| $matches[1] === '$background_color'
				|| $matches[1] === '$play_pause_color'
				|| $matches[1] === '$played_color' ) {
			if( array_key_exists( $matches[1], $color_array ) ) {
				$color = $color_array[$matches[1]];
			} else {
				$color = str_replace( '#', '', self::SPP_DEFAULT_PLAYER_COLOR);
			}
			if( empty( $matches[2] ) ) {
				return $color;
			} else {
				// $matches[2] is the tint value.
				$tinted = SPP_Utils_Color::tint_hex( $color, $matches[2] );
				$tinted = str_replace( '#', '', $tinted);
				return $tinted;
			}
		} else if( $matches[1] === '$white_controls_url') {
			return 'url(' . SPP_ASSETS_URL . 'images/controls-white.png)';
		} else if( $matches[1] === '$black_controls_url') {
			return 'url(' . SPP_ASSETS_URL . 'images/controls-black.png)';
		} else if( $matches[1] === '$white_subdl_url') {
			return 'url(' . SPP_ASSETS_URL . 'images/sub-dl-white.png)';
		} else if( $matches[1] === '$black_subdl_url') {
			return 'url(' . SPP_ASSETS_URL . 'images/sub-dl-black.png)';
		} else if ( $matches[1] === '$importantStr') {
			return self::get_css_important_str();
		} else {
			return trim($matches[0]);
		}
	}
	
	public function callback_for_generate_dynamic_css( $matches ) {
		$color_array = $this->color_array_for_generate_dynamic_css;
		$brightness = $this->brightness_for_generate_dynamic_css;
		if( $brightness < 0.2 ) {
			$expr = $matches[1];
		} else if ($brightness > 0.6 ) {
			$expr = $matches[3];
		} else {
			$expr = $matches[2];
		}
		return self::parse_dynamic_css_fragment( $expr, $color_array );
	}
	
	public function generate_dynamic_css( $color_array ) {
		
		require_once( SPP_PLUGIN_BASE . 'classes/utils/color.php' );
		$css = file_get_contents(SPP_PLUGIN_BASE . 'classes/dynamic.css');
		
		// Replace semicolon-separated parenthesized expressions
		$this->color_array_for_generate_dynamic_css = $color_array;
		$this->brightness_for_generate_dynamic_css = SPP_Utils_Color::get_brightness( $color_array['$color'] );
		$css = preg_replace_callback(
				'/\(\((.*?);(.*?);(.*?)\)\)/',
				array( $this, 'callback_for_generate_dynamic_css' ),
				$css );
		// Replace pipe-separated parenthesized expressions
		$this->brightness_for_generate_dynamic_css = SPP_Utils_Color::get_brightness( $color_array['$background_color'] );
		$css = preg_replace_callback(
				'/\(\((.*?)\|(.*?)\|(.*?)\)\)/',
				array( $this, 'callback_for_generate_dynamic_css' ),
				$css );
		// Replace other parenthesized expressions
		$this->brightness_for_generate_dynamic_css = 0;
		$css = preg_replace_callback(
				'/\(\((.*?)\)\)/',
				array( $this, 'callback_for_generate_dynamic_css' ),
				$css );
		// Remove comments
		$css = preg_replace( '/\/\*.*?\*\//m', '', $css );
		return $css;
	}

	public function add_dynamic_css() {
		
		foreach( $this->color_arrays as $color_array ) {
			
			// The generated dynamic CSS will be stored in filename
			$filename = SPP_ASSETS_PATH . 'css/custom-' . self::VERSION;
			foreach( $color_array as &$color ) {
				$color = str_replace( '#', '', $color );
				$filename = $filename . '-' . $color;
			}
			if( self::get_css_important_str() === " !important" ) {
				$filename = $filename . '-i';
			}
			$filename = $filename . '.css';
			
			// If we have already included this CSS on the page, we're done
			static $included_already = array();
			if( in_array( $filename, $included_already ) ) {
				continue;
			}
		
			// Get the CSS from the file, if it exists.  Otherwise, generate and save it
			// Starting in 2.0, we don't save it in a file.  It's quick enough to compute.
			$css = self::generate_dynamic_css( $color_array );

			// Put the generated CSS onto the page
			echo '<style>' . "\n\t";
			echo '/* Smart Podcast Player custom styles for color ' . $color_array['$color'] . " */\n";
			echo $css;
			echo '</style>' . "\n";
			
			// Make a note that we've put this CSS on the page
			$included_already[] = $filename;
		}
		
	}

}
