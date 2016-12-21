<?php
		
class SPP_Ajax_Tracks {

	public static function fetch_track_data() {

		require_once( SPP_PLUGIN_BASE . 'classes/mp3.php' );

		$url = isset( $_POST['url'] ) ? $_POST['url'] : false;

		if( !$url ) {
			echo '0';
			die();
		}

		// Check if last fetch was last successful
		$transient_success = 'spp_cachem_' . 'track_fetch_check';

		$fallback = false;

		$transient = 'spp_cachem_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', md5($url) ), -32 );

		$no_cache = filter_input( INPUT_GET, 'spp_no_cache' ) ? filter_input( INPUT_GET, 'spp_no_cache' ) : 'false';

		if( ( false === ( $data = get_transient( $transient ) ) ) || $no_cache == 'true' ) {

			$cache_time = DAY_IN_SECONDS;

			if ( get_transient( $transient_success ) ) {
				$fallback = true;
				$data = self::fetch_track_data_fallback( $url );
			}

			if( ( false === ( $check = get_transient( $transient_success ) ) ) || !is_array( $data ) ) {
				$fallback = false;
				set_transient( $transient_success, $transient_success, DAY_IN_SECONDS );
				$data = SPP_MP3::get_data( $url );
				$cache_time = 4 * WEEK_IN_SECONDS;
			}

			if( is_array( $data ) ) {

				if ( !empty( $data ) || isset( $data['title'] ) || isset( $data['artist'] ) || count( $data , COUNT_RECURSIVE) >= 2 )
					$cache_time = YEAR_IN_SECONDS;

				set_transient( $transient, $data, $cache_time );

				if ( !$fallback )
					delete_transient( $transient_success );
			}
			else {
				// Prevent continous re-fetching
				set_transient( $transient, $data, MINUTE_IN_SECONDS );
			}

		}

		echo is_array( $data ) ? json_encode( $data ) : '0';

		die();

	}

	public static function fetch_track_data_fallback ( $url = null) {

		$transient = 'spp_cachef_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', md5($url) ), -32 );
		if ( $data = get_transient( $transient ) )
			return $data;

		$settings = get_option( 'spp_player_general' );
		$license_key = isset( $settings[ 'license_key' ] ) ? trim($settings[ 'license_key' ]) : 'nokey';

		$response = wp_remote_get(
		    "https://go.smartpodcastplayer.com/trackdata/?url=" . $url . "&license_key=" . $license_key,
		    array(
		        'timeout' => 10,
		        'sslverify' => false
		    )
		);

		if( !is_wp_error( $response ) && ( $response['response']['code'] < 400 ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) , true );
			set_transient( $transient, $data, HOUR_IN_SECONDS );
			return $data;
		}
		return null;

	}
	
	/**
	 * Return the data for an array of Soundcloud stream URLs
	 * 
	 * @return JSON Array
	 */
	public static function ajax_get_soundcloud_track() {

		$api_options = get_option( 'spp_player_soundcloud', array( 'consumer_key' => '' ) );
		$api_consumer_key = isset( $api_options['consumer_key'] ) ? $api_options['consumer_key'] : '';
		if( $api_consumer_key == '' ) {
			$api_consumer_key = 'b38b3f6ee1cdb01e911c4d393c1f2f6e';
		}

		$url_array = isset( $_POST['streams'] ) ? $_POST['streams'] : '';

		$track_array = array();
		foreach( $url_array as $url ) {
			if ( !empty( $url ) )
				$transient = 'spp_cachet_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', md5($url) ), -32 );
			
			// User in HS 3788 had a feed in which each enclosure matched the regexp below.  Using the resolve
			// URL didn't work for this one, so I added this specific match.  There is likely a better way.
			// It would involve finding out all of the possible Soundcloud URLs.
			if( 1 == preg_match( '/feeds\.soundcloud\.com\/stream\/(\d+)/', $url, $matches ) ) {
				$url = SPP_Core::SPP_SOUNDCLOUD_API_URL . '/tracks/' . $matches[1] . '?consumer_key=' . $api_consumer_key;
			} else {
				$url = SPP_Core::SPP_SOUNDCLOUD_API_URL . '/resolve.json?url=' . urlencode( $url ) . '&consumer_key=' . $api_consumer_key;
			}

			if(  false === ( $track = SPP_Transients::spp_get_transient( $transient ) )  ) {
				$response = wp_remote_get( $url );
				if( !is_wp_error( $response ) && ( $response['response']['code'] < 400 ) ) {
					$track = json_decode( $response['body'] );

					if ( !empty ( $track  ) ) {
						
						$settings = get_option( 'spp_player_advanced' );

						$val = isset( $settings['cache_timeout'] ) ? $settings['cache_timeout'] : '15';
						if ( $val > 60 || $val < 5 || !is_numeric( $val ) )
							$val = 15;
						set_transient( $transient, $track, $val * HOUR_IN_SECONDS );
					}
				}
			}
			$track_array[] = $track;
		}
		
		header('Content-Type: application/json');
		echo json_encode( $track_array );

		exit;

	}
}
