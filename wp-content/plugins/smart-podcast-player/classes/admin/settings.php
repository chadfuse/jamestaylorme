<?php

class SPP_Admin_Settings {

	public $plugin_slug;
	
	public function __construct() {

		$plugin = SPP_Core::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'admin_init', array( $this, 'settings_sections' ) );

	}


	public function register() {
		
		$plugin = SPP_Core::get_instance();
		$plugin->upgrade_options();
		
		register_setting( 'spp-player-soundcloud', 'spp_player_soundcloud' );
		register_setting( 'spp-player', 'spp_player_social' );
		register_setting( 'spp-player-general', 'spp_player_general' );
		register_setting( 'spp-player-defaults', 'spp_player_defaults' );
		register_setting( 'spp-player-advanced', 'spp_player_advanced' );
		
		add_options_page( 'Smart Podcast Player Settings', 'Smart Podcast Player', 'manage_options', 'spp-player', array( $this, 'settings_page' ) );

	}

	public function settings_page() {
		require_once( SPP_ASSETS_PATH . 'views/settings.php' );
	}

	public function settings_sections() {

		add_settings_section(  
	        'spp_player_general_settings',
	        '',
	        array( $this, 'general_section' ),
	        'spp-player-general'
	    ); 

			add_settings_field(   
			    'spp_player_general[license_key]',
			    'License Key: ',
			    array( $this, 'field_license_key' ),
			    'spp-player-general',
			    'spp_player_general_settings'
			);

		add_settings_section(  
	        'spp_player_soundcloud_settings',
	        '',
	        array( $this, 'soundcloud_section' ),
	        'spp-player-soundcloud'
	    ); 

			add_settings_field(
			    'spp_player_soundcloud[consumer_key]',
			    'API Consumer Key: ',
			    array( $this, 'field_soundcloud_api_key' ),
			    'spp-player-soundcloud',
			    'spp_player_soundcloud_settings'
			);
		
		/* In the player defaults page, there are many "sections" whose only
		   purpose is to display some text.  These are the dummy sections. */
		add_settings_section(
		    'spp_player_defaults_dummy_section_header_text',
			'',
			array( $this, 'spp_player_defaults_dummy_section_header_text_callback' ),
			'spp-player-defaults'
		);

		add_settings_section(  
	        'spp_player_feed_settings',
	        'Podcast Feed Settings',
	        array( $this, 'spp_player_defaults_feed_settings' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[url]',
			    'Podcast RSS Feed URL: ',
			    array( $this, 'field_default_url' ),
			    'spp-player-defaults',
			    'spp_player_feed_settings'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_feed_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_feed_help_callback' ),
	        'spp-player-defaults'
	    );
		
		add_settings_section(
			'spp_player_subscription_urls',
			'Subscription URLs',
			array( $this, 'spp_player_defaults_subscription_urls' ),
			'spp-player-defaults'
		);

			add_settings_field(   
			    'spp_player_defaults[subscribe_itunes]',
			    'iTunes: ',
			    array( $this, 'field_default_subscribe_itunes' ),
			    'spp-player-defaults',
			    'spp_player_subscription_urls'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_itunes_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_itunes_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[subscribe_buzzsprout]',
			    'Buzzsprout: ',
			    array( $this, 'field_default_subscribe_buzzsprout' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_itunes_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_buzzsprout_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_buzzsprout_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[subscribe_googleplay]',
			    'Google Play: ',
			    array( $this, 'field_default_subscribe_googleplay' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_buzzsprout_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_googleplay_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_googleplay_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[subscribe_iheartradio]',
			    'iHeartRadio: ',
			    array( $this, 'field_default_subscribe_iheartradio' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_googleplay_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_iheartradio_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_iheartradio_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[subscribe_pocketcasts]',
			    'PocketCasts: ',
			    array( $this, 'field_default_subscribe_pocketcasts' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_iheartradio_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_pocketcasts_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_pocketcasts_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[subscribe_soundcloud]',
			    'Soundcloud: ',
			    array( $this, 'field_default_subscribe_soundcloud' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_pocketcasts_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_soundcloud_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_soundcloud_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[subscribe_stitcher]',
			    'Stitcher: ',
			    array( $this, 'field_default_subscribe_stitcher' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_soundcloud_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_subscribe_stitcher_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_subscribe_stitcher_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[show_name]',
			    'Show Name (for the full player): ',
			    array( $this, 'field_default_show_name' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_subscribe_stitcher_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_show_name_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_show_name_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[artist_name]',
			    'Artist Name (for the track player): ',
			    array( $this, 'field_default_artist_name' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_show_name_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_artist_name_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_artist_name_help_callback' ),
	        'spp-player-defaults'
	    );

		add_settings_section(  
	        'spp_player_defaults_player_design_section',
	        'Player Design Settings',
	        array( $this, 'spp_player_defaults_player_design_section_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[style]',
			    'Theme Style: ',
			    array( $this, 'field_default_style' ),
			    'spp-player-defaults',
			    'spp_player_defaults_player_design_section'
			);

	    	add_settings_field(   
			    'spp_player_defaults[bg_color]',
			    'Highlight Color: ',
			    array( $this, 'field_default_color' ),
			    'spp-player-defaults',
			    'spp_player_defaults_player_design_section'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_bg_color',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_bg_color_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[spp_background]',
			    'Full Player Background: ',
			    array( $this, 'field_default_spp_background' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_bg_color'
			);

			add_settings_field(   
			    'spp_player_defaults[stp_background]',
			    'Track Player Background: ',
			    array( $this, 'field_default_stp_background' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_bg_color'
			);

			add_settings_field(   
			    'spp_player_defaults[stp_image]',
			    'Track Player Image URL: ',
			    array( $this, 'field_default_stp_image' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_bg_color'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_stp_image_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_stp_image_help_callback' ),
	        'spp-player-defaults'
	    );

		add_settings_section(  
	        'spp_player_defaults_dummy_section_buttons_header',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_buttons_header_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[sort_order]',
			    'Sort Order: ',
			    array( $this, 'field_default_sort_order' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_buttons_header'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_sort_order_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_sort_order_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[download]',
			    'Download: ',
			    array( $this, 'field_default_download' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_sort_order_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_download_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_download_help_callback' ),
	        'spp-player-defaults'
	    );

			add_settings_field(   
			    'spp_player_defaults[episode_limit]',
			    'Episode Limit: ',
			    array( $this, 'field_default_episode_limit' ),
			    'spp-player-defaults',
			    'spp_player_defaults_dummy_section_download_help'
			);

		add_settings_section(  
	        'spp_player_defaults_dummy_section_episode_limit_help',
	        '',
	        array( $this, 'spp_player_defaults_dummy_section_episode_limit_help_callback' ),
	        'spp-player-defaults'
	    );

		add_settings_section(  
			'spp_player_advanced_settings',
			'',
			array( $this, 'advanced_section' ),
			'spp-player-advanced'
		); 

			add_settings_field(   
			    'spp_player_advanced[show_notes]',
			    'RSS Show notes field: ',
			    array( $this, 'field_advanced_show_notes' ),
			    'spp-player-advanced',
			    'spp_player_advanced_settings'
			);

		add_settings_section(  
	        'spp_advanced_dummy_section_show_notes_help',
	        '',
	        array( $this, 'spp_advanced_dummy_section_show_notes_help_callback' ),
	        'spp-player-advanced'
	    );

			add_settings_field(   
			    'spp_player_advanced[cache_timeout]',
			    'Cache Timeout: ',
			    array( $this, 'field_advanced_cache_timeout' ),
			    'spp-player-advanced',
			    'spp_advanced_dummy_section_show_notes_help'
			);

		add_settings_section(  
	        'spp_advanced_dummy_section_cache_timeout_help',
	        '',
	        array( $this, 'spp_advanced_dummy_section_cache_timeout_help_callback' ),
	        'spp-player-advanced'
	    );

			add_settings_field(   
			    'spp_player_advanced[downloader]',
			    'Download Method: ',
			    array( $this, 'field_advanced_downloader' ),
			    'spp-player-advanced',
			    'spp_advanced_dummy_section_cache_timeout_help'
			);

		add_settings_section(  
	        'spp_advanced_dummy_section_download_method_help',
	        '',
	        array( $this, 'spp_advanced_dummy_section_download_method_help_callback' ),
	        'spp-player-advanced'
	    );

			add_settings_field(   
			    'spp_player_advanced[css_important]',
			    'Use "!important" in CSS: ',
			    array( $this, 'field_advanced_css_important' ),
			    'spp-player-advanced',
			    'spp_advanced_dummy_section_download_method_help'
			);

		add_settings_section(  
	        'spp_advanced_dummy_section_important_css_help',
	        '',
	        array( $this, 'spp_advanced_dummy_section_important_css_help_callback' ),
	        'spp-player-advanced'
	    );

			add_settings_field(   
			    'spp_player_advanced[color_pickers]',
			    'Show color pickers: ',
			    array( $this, 'field_advanced_color_pickers' ),
			    'spp-player-advanced',
			    'spp_advanced_dummy_section_important_css_help'
			);

		add_settings_section(  
	        'spp_advanced_dummy_section_color_pickers_help',
	        '',
	        array( $this, 'spp_advanced_dummy_section_color_pickers_help_callback' ),
	        'spp-player-advanced'
	    );

			add_settings_field(   
			    'spp_player_advanced[html_assets]',
			    'Inject assets into HTML: ',
			    array( $this, 'field_advanced_html_assets' ),
			    'spp-player-advanced',
			    'spp_advanced_dummy_section_color_pickers_help'
			);

		add_settings_section(  
	        'spp_advanced_dummy_section_html_assets_help',
	        '',
	        array( $this, 'spp_advanced_dummy_section_html_assets_help_callback' ),
	        'spp-player-advanced'
	    );

	}

	public function spp_player_defaults_dummy_section_header_text_callback() {
		echo "<h4>Save yourself time! Put in your default information so that you " .
		     "don’t have to add it each time you create a new shortcode.</h4>";
	}
	
	public function spp_player_defaults_feed_settings() {
	}
	
	public function spp_player_defaults_dummy_section_feed_help_callback() {
	    echo 'Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/54-getting-started-6-finding-your-rss-feed"> find my podcast feed URL</a>.';
	}
	
	public function spp_player_defaults_subscription_urls() {
	}
	
	public function spp_player_defaults_dummy_section_subscribe_itunes_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on iTunes.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_subscribe_buzzsprout_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on Buzzsprout.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_subscribe_googleplay_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on Google Play.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_subscribe_iheartradio_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on iHeartRadio.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_subscribe_pocketcasts_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on PocketCasts.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_subscribe_soundcloud_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on Soundcloud.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_subscribe_stitcher_help_callback() {
		echo '<span class="spp-indented-option">Leave this blank if you\'re not on Stitcher.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/40-setting-up-the-subscription-button#subscription-link"> find my subscription URL</a>.</span>';
	}
	
	public function spp_player_defaults_dummy_section_show_name_help_callback() {
		echo 'Show me <a target="_blank" href="http://support.smartpodcastplayer.com/article/30-show-name"> where the Show Name goes</a>. Leave this blank to get your show name from your RSS feed.';
	}
	
	public function spp_player_defaults_dummy_section_artist_name_help_callback() {
		echo 'Show me <a target="_blank" href="http://support.smartpodcastplayer.com/article/25-change-artist-name-episode-title"> where the Artist Name goes</a>.<hr>';
	}
	
	public function spp_player_defaults_player_design_section_callback() {
		echo '<p>For more on how to customize the look of your players, visit <a target="_blank" href="http://support.smartpodcastplayer.com/article/91-start-here-customize-the-smart-podcast-player"> this support article</a>.</p>'
		   . '<h4>Colors and Image</h4><p>Watch <a target="_blank" href="http://support.smartpodcastplayer.com/article/91-start-here-customize-the-smart-podcast-player">this video</a> to see how you can customize different colors and themes.</p>';
	}
	
	public function spp_player_defaults_dummy_section_bg_color_callback() {
		echo '<em class="spp-indented-option">Previously named "Progress Bar Color"</em>';
	}
	
	public function spp_player_defaults_dummy_section_stp_image_help_callback() {
		echo '<div class="spp-indented-option">Enter a URL.  Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/28-change-player-image">format this image properly.</a></div>';
	}
	
	public function spp_player_defaults_dummy_section_buttons_header_callback() {
		echo "<h4>Buttons and Display Styles</h4>";
	}
	
	public function spp_player_defaults_dummy_section_sort_order_help_callback() {
		echo '<div class="spp-indented-option">Help me <a target="_blank" href="http://support.smartpodcastplayer.com/article/55-getting-started-7-setting-up-your-player-defaults#other">choose which to use</a>.</div>';
	}
	
	public function spp_player_defaults_dummy_section_download_help_callback() {
		echo '<div class="spp-indented-option">Selecting "No" will remove the download button.</div>';
	}
	
	public function spp_player_defaults_dummy_section_episode_limit_help_callback() {
		echo '<div class="spp-indented-option">Enter a number to limit the display to that many of your most recent episodes.</div>';
	}
	
	public function spp_player_defaults_dummy_section_playback_timer_help_callback() {
		echo "<div class='spp-indented-option'>Help me choose which to use [link]</div>";
	}
	
	public function spp_advanced_dummy_section_show_notes_help_callback() {
		echo "For each item in your RSS feed, SPP will look in this field for your show notes.";
	}
	
	public function spp_advanced_dummy_section_cache_timeout_help_callback() {
		echo "This adjusts how often SPP checks your feed for new episodes.";
	}
	
	public function spp_advanced_dummy_section_download_method_help_callback() {
		echo "This adjusts how Smart Podcast Player requests files from your podcast host.";
	}
	
	public function spp_advanced_dummy_section_important_css_help_callback() {
		echo 'Add the CSS "!important" declaration to all of Smart Podcast Player\'s styles.';
	}
	
	public function spp_advanced_dummy_section_color_pickers_help_callback() {
		echo "Prevent SPP from loading the Wordpress color picker Javascript.";
	}
	
	public function spp_advanced_dummy_section_html_assets_help_callback() {
		echo "Echo the Javascript loading and localization strings straight to the HTML, instead of using Wordpress's enqueue functions.";
	}
	
	public function social_section() {}
	public function general_section() {}
	public function soundcloud_section() {}
	
	public function advanced_section() {
		echo '<strong><em>We do not recommend making any changes to the items below unless you are experiencing problems. Before making changes, <a target="_blank" href="http://support.smartpodcastplayer.com/article/84-understanding-the-advanced-settings-menu">please consult this support article.</a></em></strong>';
	}

	public function field_default_url() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['url'] ) ? $settings['url'] : '';
		
        $html .= '<input type="text" name="spp_player_defaults[url]" class="spp-wider-left-column" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_itunes() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_itunes'] ) ? $settings['subscribe_itunes'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_itunes]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_buzzsprout() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_buzzsprout'] ) ? $settings['subscribe_buzzsprout'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_buzzsprout]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_googleplay() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_googleplay'] ) ? $settings['subscribe_googleplay'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_googleplay]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_iheartradio() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_iheartradio'] ) ? $settings['subscribe_iheartradio'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_iheartradio]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_pocketcasts() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_pocketcasts'] ) ? $settings['subscribe_pocketcasts'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_pocketcasts]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_soundcloud() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_soundcloud'] ) ? $settings['subscribe_soundcloud'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_soundcloud]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_subscribe_stitcher() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['subscribe_stitcher'] ) ? $settings['subscribe_stitcher'] : '';

        $html .= '<input type="text" name="spp_player_defaults[subscribe_stitcher]" class="spp-wider-left-column spp-indent-ancestor-table" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_show_name() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['show_name'] ) ? $settings['show_name'] : '';

        $html .= '<input type="text" name="spp_player_defaults[show_name]" class="spp-wider-left-column" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_artist_name() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['artist_name'] ) ? $settings['artist_name'] : '';

        $html .= '<input type="text" name="spp_player_defaults[artist_name]" class="spp-wider-left-column" value="' . $val . '" size="40" />';
        
		echo $html;

	}

	public function field_default_color() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );
		
        $color = isset( $settings['bg_color'] ) && !empty( $settings['bg_color'] ) ? $settings['bg_color'] : SPP_Core::SPP_DEFAULT_PLAYER_COLOR;

        $html .= '<div class="spp-color-picker spp-indent-ancestor-table">';
		$html .= '<input type="text" class="color-field" name="spp_player_defaults[bg_color]" value="' . $color . '" />';
        $html .= '</div>';

		echo $html;

	}
	
	public function field_default_spp_background() {
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['spp_background'] ) ? $settings['spp_background'] : 'default';

        $html .= '<select name="spp_player_defaults[spp_background]" class="spp-indent-ancestor-table">';
        	$html .= '<option value="default" ' . selected( $val, 'default', false ) . ' >Default</option>';
        	$html .= '<option value="blurred_logo" ' . selected( $val, 'blurred_logo', false ) . ' >Blurred Logo</option>';
        $html .= '</select>';
        
		echo $html;
	}

	public function field_default_stp_image() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['stp_image'] ) ? $settings['stp_image'] : '';

        $html .= '<input type="text" name="spp_player_defaults[stp_image]" class="spp-indent-ancestor-table"';
		$html .= ' value="' . $val . '" size="40" />';
        
		echo $html;

	}
	
	public function field_default_stp_background() {
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['stp_background'] ) ? $settings['stp_background'] : 'default';
        $color = isset( $settings['stp_background_color'] ) ? $settings['stp_background_color'] : SPP_Core::SPP_DEFAULT_PLAYER_COLOR;
		
        $color_selected = '';
		if( $val != 'default' && $val != 'blurred_logo' ) {
			$color_selected = 'selected="selected"';
		}

		// Construct the drop-down menu of options
        $html .= '<div class="spp-color-picker spp-indent-ancestor-table">';
        $html .= '<select name="spp_player_defaults[stp_background]">';
        	$html .= '<option value="default" ' . selected( $val, 'default', false ) . ' >Default</option>';
        	$html .= '<option value="blurred_logo" ' . selected( $val, 'blurred_logo', false ) . ' >Blurred Logo</option>';
        	$html .= '<option value="color" ' . $color_selected . ' >Color (select)</option>';
        $html .= '</select>';

		// Color picker
		$html .= '&nbsp;';
		$html .= '<input type="text" class="color-field" name="spp_player_defaults[stp_background_color]" value="' . $color . '" />';
	
        $html .= '</div>';
        
		echo $html;
	}

	public function field_default_style() {
		
		$html = '';
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['style'] ) ? $settings['style'] : '';

        $html .= '<select name="spp_player_defaults[style]">';
        	$html .= '<option ' . selected( $val, 'light', false ) . ' value="light">Light</option>';
        	$html .= '<option value="dark" ' . selected( $val, 'dark', false ) . ' >Dark</option>';
        $html .= '</select>';
        
		echo $html;

	}

	public function field_default_sort_order() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['sort_order'] ) ? $settings['sort_order'] : '';

        $html .= '<select name="spp_player_defaults[sort_order]" class="spp-indent-ancestor-table">';
        	$html .= '<option ' . selected( $val, 'newest', false ) . ' value="newest">Newest to Oldest</option>';
        	$html .= '<option value="oldest" ' . selected( $val, 'oldest', false ) . ' >Oldest to Newest</option>';
        $html .= '</select>';
        
		echo $html;

	}
	

	public function field_default_playback_timer() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['playback_timer'] ) ? $settings['playback_timer'] : 'true';

        $html .= '<select name="spp_player_advanced[playback_timer]" class="spp-indent-ancestor-table">';
        	$html .= '<option ' . selected( $val, 'true', false ) . ' value="true">Yes</option>';
        	$html .= '<option value="false" ' . selected( $val, 'false', false ) . ' >No</option>';
        $html .= '</select>';
        
		echo $html;

	}
	

	public function field_default_download() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_defaults' );

        $val = isset( $settings['download'] ) ? $settings['download'] : 'true';

        $html .= '<select name="spp_player_defaults[download]" class="spp-indent-ancestor-table">';
        	$html .= '<option ' . selected( $val, 'true', false ) . ' value="true">Yes</option>';
        	$html .= '<option value="false" ' . selected( $val, 'false', false ) . ' >No</option>';
        $html .= '</select>';
        
		echo $html;

	}

	public function field_default_episode_limit() {
		
		$html = '';
        $settings = get_option( 'spp_player_defaults' );
        $val = isset( $settings['episode_limit'] ) ? $settings['episode_limit'] : '';
        $html .= '<input type="text" name="spp_player_defaults[episode_limit]" class="spp-indent-ancestor-table" ';
		$html .= 'value="' . $val . '" />';
        
		echo $html;

	}

	public function field_license_key() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_general' );

        if( isset( $settings[ 'license_key' ] ) || !empty( $settings[ 'license_key' ] ) )  {
        	// Facilitate fresh "live" license key check after key is entered
        	$optionName = 'external_updates-smart-podcast-player';
        	delete_site_option($optionName);
    	}

        $html .= '<input type="text" name="spp_player_general[license_key]" value="' . $settings['license_key'] . '" size="50" />';
        $html .= '<p class="description"><small>Your license key was delivered to you at the time of purchase, and in your email receipt. If you have any difficulty locating your license key, please email <a href="mailto:support@smartpodcastplayer.com">support@smartpodcastplayer.com</a>.</small></p>';

		echo $html;

	}

	public function field_soundcloud_api_key() {
		
		$html = '';
        $html .= '<p>Update! SoundCloud users no longer need to enter an API key.</p>';
		echo $html;
		
	}

	public function field_soundcloud_url() {
		$html = '';  
        
        $settings = is_array( get_option( 'spp_player_soundcloud' ) ) ? get_option( 'spp_player_soundcloud' ) : array();
        $url = isset( $settings['url'] ) ? $settings['url'] : '';

        $html .= '<input type="text" name="spp_player_soundcloud[url]" value="' . $url . '" />';
        $html .= '<p class="description"><small>Paste a link to your Soundcloud profile (ex. <a target="_blank" href="https://soundcloud.com/askpat">https://soundcloud.com/askpat</a>) to play all the tracks in your account, or a link to a single playlist (ex. <a target="_blank" href="https://soundcloud.com/askpat/sets/askpat">https://soundcloud.com/askpat/sets/askpat</a>.</small></p>';

		echo $html;
		
	}

	public function field_twitter_hashtag() {

		$html = '';  
        
        $settings = get_option( 'spp_player_social' );

        $html .= '#<input type="text" name="spp_player_social[twitter_hashtag]" value="' . $settings['twitter_hashtag'] . '" />';

		echo $html;

	}

	public function field_advanced_show_notes() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_advanced' );

        $val = isset( $settings['show_notes'] ) ? $settings['show_notes'] : 'description';

        $html .= '<select name="spp_player_advanced[show_notes]">';
        	$html .= '<option ' . selected( $val, 'description', false ) . ' value="description">description</option>';
        	$html .= '<option ' . selected( $val, 'content', false ) . ' value="content">content</option>';
        	$html .= '<option ' . selected( $val, 'itunes_summary', false ) . ' value="itunes_summary">itunes:summary</option>';
        	$html .= '<option ' . selected( $val, 'itunes_subtitle', false ) . ' value="itunes_subtitle">itunes:subtitle</option>';
        $html .= '</select>';
        
		echo $html;

	}

	public function field_advanced_cache_timeout() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_advanced' );

        $val = isset( $settings['cache_timeout'] ) ? $settings['cache_timeout'] : '15';

        $html .= '<input type="text" name="spp_player_advanced[cache_timeout]" value="' . $val . '" /> minutes';
        
		echo $html;

	}

	public function field_advanced_downloader() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_advanced' );

        $val = isset( $settings['downloader'] ) ? $settings['downloader'] : 'fopen';

        $html .= '<select name="spp_player_advanced[downloader]">';
        	$html .= '<option ' . selected( $val, 'smart', false ) . ' value="smart">Automatic (Recommended)</option>';
        	$html .= '<option ' . selected( $val, 'fopen', false ) . ' value="fopen">Stream (fopen)</option>';
        	$html .= '<option ' . selected( $val, 'local', false ) . ' value="local">Local File Cache</option>';
        $html .= '</select>';
        
		echo $html;

	}

	public function field_advanced_css_important() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_advanced' );

        $val = isset( $settings['css_important'] ) ? $settings['css_important'] : 'false';

        $html .= '<select name="spp_player_advanced[css_important]">';
        	$html .= '<option ' . selected( $val, 'true', false ) . ' value="true">Yes</option>';
        	$html .= '<option value="false" ' . selected( $val, 'false', false ) . ' >No (Recommended)</option>';
        $html .= '</select>';
        
		echo $html;

	}

	public function field_advanced_color_pickers() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_advanced' );

        $val = isset( $settings['color_pickers'] ) ? $settings['color_pickers'] : 'true';

        $html .= '<select name="spp_player_advanced[color_pickers]">';
        	$html .= '<option ' . selected( $val, 'true', false ) . ' value="true">Yes (Recommended)</option>';
        	$html .= '<option value="false" ' . selected( $val, 'false', false ) . ' >No</option>';
        $html .= '</select>';
        
		echo $html;

	}

	public function field_advanced_html_assets() {
		
		$html = '';  
        
        $settings = get_option( 'spp_player_advanced' );

        $val = isset( $settings['html_assets'] ) ? $settings['html_assets'] : 'false';

        $html .= '<select name="spp_player_advanced[html_assets]">';
        	$html .= '<option ' . selected( $val, 'true', false ) . ' value="true">Yes</option>';
        	$html .= '<option value="false" ' . selected( $val, 'false', false ) . ' >No (Recommended)</option>';
        $html .= '</select>';
        
		echo $html;

	}

}
