<?php
		
class SPP_Ajax_Feed {
	/**
	 * Get track data via AJAX
	 * @return 	json 	JSON object representing all tracks
	 */
	public static function ajax_get_tracks() {

		$url = isset( $_POST['stream'] ) ? $_POST['stream'] : '';
		$episode_limit = isset( $_POST['episode_limit'] ) ? $_POST['episode_limit'] : 0;
		$delay = isset( $_POST['delay'] ) ? $_POST['delay'] : 0;

		if ( empty( $url ) ) {
			header('Content-Type: application/json');
			echo( json_encode( null ) ); // Better: report an error
			exit;
		}
		
		$transient_name = SPP_Transients::spp_transient_name( array(
				'purpose' => 'tracks from feed url',
				'url' => $url,
				'episode_limit' => $episode_limit ) );
		$data = SPP_Transients::spp_get_transient( $transient_name );
		$no_cache = filter_input( INPUT_GET, 'spp_no_cache' ) ? filter_input( INPUT_GET, 'spp_no_cache' ) : 'false';
		
		if( ( ( false === $data ) || !isset( $data['tracks'] ) ) || $no_cache == 'true' ) {

			$data = array(
				'url' => $url,
				'numTracks' => 0,
				'tracks' => array(),
			);
				
			// Limit the free version to ten tracks
			if( !SPP_Core::is_paid_version() ) {
				$episode_limit = 10;
			}
			
			if( strpos( $url, 'http://soundcloud.com/' ) !== false || strpos( $url, 'http://soundcloud.com/sets/' ) !== false || strpos( $url, 'https://soundcloud.com/' ) !== false || strpos( $url, 'https://soundcloud.com/sets/' ) !== false ) {

				$data['tracks'] = self::get_soundcloud_tracks( $url, $episode_limit );

			} else {
				$data['tracks'] = self::get_rss_tracks( $url, $episode_limit );

			}
			$data['numTracks'] = count( $data['tracks'] );

			if ( is_array( $data['tracks'] ) || !empty ( $data['tracks'] ) ) {
					$settings = get_option( 'spp_player_advanced' );

					$val = isset( $settings['cache_timeout'] ) ? $settings['cache_timeout'] : '15';
					if ( $val > 60 || $val < 5 || !is_numeric( $val ) )
						$val = 15;
					set_transient( $transient_name, $data, $val * MINUTE_IN_SECONDS);
			}
			else {
				// Prevent crazy load and re-fetching
				set_transient( $transient_name, $data, MINUTE_IN_SECONDS);
			}
		}
		
		sleep($delay);
		header('Content-Type: application/json');
		echo json_encode( $data['tracks'] );
		exit;

	}
	
	// Returns the tracks if they're cached, otherwise null
	public static function get_cached_tracks( $url, $episode_limit ) {
		$transient_name = SPP_Transients::spp_transient_name( array(
				'purpose' => 'tracks from feed url',
				'url' => $url,
				'episode_limit' => $episode_limit ) );
		return SPP_Transients::spp_get_transient( $transient_name );
	}

	/**
	 * Called by SPP_Core::ajax_get_tracks, specifically to retrieve SoundCloud tracks
	 * @param  string 	$url 		URL of SoundCloud feed
	 * @return array 	$tracks		Array of tracks
	 */
	public static function get_soundcloud_tracks( $url, $episode_limit ) {
		
		$tracks = array();
		$api_options = get_option( 'spp_player_soundcloud', array( 'consumer_key' => '' ) );
		$api_consumer_key = isset( $api_options['consumer_key'] ) ? $api_options['consumer_key'] : '';

		// Determine if it's a feed URL
		if( strpos( $url, '/sets/' ) === false ) {
			
			$user_id = '';

			$url_prof = SPP_Core::SPP_SOUNDCLOUD_API_URL . '/resolve?url=' . urlencode( $url ) . '&format=json&consumer_key=' . $api_consumer_key;
			$transient = 'spp_cachep_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', md5( $url_prof ) ), -32 );
			
			if(  false === ( $profile = SPP_Transients::spp_get_transient( $transient ) )  ) {

				$response = wp_remote_get( $url_prof );
				if( !is_wp_error( $response ) && ( $response['response']['code'] < 400 ) ) {

					$profile = json_decode( $response['body'] );

					if ( !empty ( $profile  ) && isset( $profile->id ) )
							set_transient( $transient, $profile, 5 * MINUTE_IN_SECONDS );

				}

			}

			$user_id = $profile->id;
			$track_count = $profile->track_count;

			if ( !is_numeric( $track_count ) || $track_count <= 0 )
				$track_count = 1;

			$offset = 0;
			$limit = 200;
			$tracks_arr = array();
			
			// Limit the number of episodes
			if( $episode_limit > 0 && $episode_limit < $track_count ) {
				$track_count = $episode_limit;
				$limit = $episode_limit;
			}

			$transient = 'spp_caches_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', SPP_Core::VERSION . $url . (string)$track_count ), -32 );

			if(  false === ( $tracks = SPP_Transients::spp_get_transient ( $transient ) ) ) {

				$url = SPP_Core::SPP_SOUNDCLOUD_API_URL . '/users/' . $user_id . '/tracks?format=json&client_id=' . $api_consumer_key . '&limit=' . $limit .'&linked_partitioning=1';

				while ( $track_count > $offset ) {

						$response = wp_remote_get( $url );
						if( !is_wp_error( $response ) && ( $response['response']['code'] < 400 ) ) {

							$json_obj = json_decode( $response['body'] );
							$tracks_arr[] = json_encode( $json_obj->collection );

							if ( empty( $json_obj->next_href ) )
								break;
							
							$url =  $json_obj->next_href; 
							
						}

					$offset += 200;

				}

				if ( is_array( $tracks_arr ) && !empty( $tracks_arr ) ) {

					if ( empty($tracks) && ( count( $tracks_arr ) == 1 ) ) {
							$tracks = json_decode( $tracks_arr[0] );
					}

					else {
							
						foreach($tracks_arr as $val) {

							if ( empty($tracks) ) {
								$tracks = $val;
							}
							else {
									if ( is_array( $tracks ) )
										$tracks = array_merge( $tracks, json_decode( $val, true ) ); 
									else
										$tracks = array_merge( json_decode( $tracks, true ), json_decode( $val, true ) ); 
							}
						}

						$tracks = json_decode( json_encode( $tracks ) );

					}

					if ( !empty ( $tracks ) )
						set_transient( $transient, $tracks , 4 * HOUR_IN_SECONDS );
				}
			}

		// Or if it's a profile URL
		} else {

			$url = SPP_Core::SPP_SOUNDCLOUD_API_URL . '/resolve?url=' . urlencode( $url ) . '&format=json&consumer_key=' . $api_consumer_key;
			$transient = 'spp_cachesu_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', md5( $url ) . (string)$track_count ), -32 );
			
			if(  false === ( $tracks = SPP_Transients::spp_get_transient( $transient ) )  ) {

				$response = wp_remote_get( $url );
				if( !is_wp_error( $response ) && ( $response['response']['code'] < 400 ) ) {

					$playlist = json_decode( $response['body'] );
					$tracks = $playlist->tracks;
					
					// Limit the number of episodes
					if( $episode_limit > 0 && count( $tracks ) > $episode_limit ) {
						$tracks = array_slice( $tracks, 0, $episode_limit );
					}

					if ( !empty ( $tracks ) )
						set_transient( $transient, $tracks , 5 * MINUTE_IN_SECONDS );

				}

			}

		}

		if ( !empty( $tracks ) ) {
			// Truncate the show notes for display
			foreach( $tracks as $track ) {
				$maxLength = strlen( $track->tag_list ) > 0 ? 140 : 280;
				$track->truncated_show_notes = self::truncate_show_notes( $track->description, $maxLength, 10 );
			}
			return $tracks;
		}
		else
		{
			for( $track_count = 0; $track_count < 10; ++$track_count ) {
				$transient = 'spp_caches_' . substr( preg_replace("/[^a-zA-Z0-9]/", '', SPP_Core::VERSION . $url . substr( $track_count, -1 ) ), -32 );
				$tracks = null;
				if( ( $tracks = SPP_Transients::spp_get_transient ( $transient ) ) && !empty( $tracks ) ) {
					return $tracks;
				}
			}
			return null;
		}
	}

	/**
	 * Rewrite of WP Core fetch_feed function, removing the WP_SimplePie_File, which was causing issues 
	 * with FeedBlitz feeds
	 * 
	 * @param  string $url Url of RSS feed
	 * @return void
	 */
	public static function fetch_feed( $url ) {

		require_once( ABSPATH . WPINC . '/class-simplepie.php' );
		require_once( ABSPATH . WPINC . '/class-feed.php' );

		$rss = new SimplePie();

		$rss->set_sanitize_class( 'WP_SimplePie_Sanitize_KSES' );

		// We must manually overwrite $feed->sanitize because SimplePie's
		// constructor sets it before we have a chance to set the sanitization class
		$rss->sanitize = new WP_SimplePie_Sanitize_KSES();
		$rss->set_cache_class( 'WP_Feed_Cache' );
		if( strpos( 'feedblitz.com', $url ) === false ) {
			$rss->set_file_class( 'WP_SimplePie_File' );
		}
		$rss->set_feed_url( $url );
		// extend for slow feed generation/hosts
		$rss->set_timeout(15);

		// If the user has cleared the cache, it was marked in this transient
		if( SPP_Transients::spp_get_transient( 'spp_cache_clear_simplepie' ) == true ) {
			$rss->enable_cache( false );
		} else {
			$rss->set_cache_duration( 5 * MINUTE_IN_SECONDS );
		}

		// The Wordpress 4.5 update broke lots of feeds by setting the type
		// to application/octet-stream instead of application/rss+xml.
		// I don't know the root cause, but I do know the fix: force_feed.
		$rss->force_feed(true);
		
		$rss->init();
		$rss->handle_content_type();

		if ( $rss->error() )
			return new WP_Error( 'simplepie-error', $rss->error() );

		return $rss;

	}

	/**
	 * Retrieve track data from RSS feeds
	 * 
	 * @param  string $url URL of the RSS feed
	 * @return array 	Data for all of the tracks
	 */
	public static function get_rss_tracks( $url, $episode_limit ) {

		$rss = self::fetch_feed( $url );

		if( is_wp_error( $rss ) )
			return array();

		$transient_name = SPP_Transients::spp_transient_name( array(
				'purpose' => 'xml from feed url',
				'url' => $url ) );
		$data = SPP_Transients::spp_get_transient( $transient_name );
		
		// See if RAW XML is already available from SimplePie. Indicates when feed new/changed too.
		if ( $rss->get_raw_data() ) {
				$data = $rss->get_raw_data();
				set_transient( $transient_name, $data, HOUR_IN_SECONDS );
		}
		else {	
			if( false === ( $data = SPP_Transients::spp_get_transient($transient_name) ) ) {
				$data = wp_remote_retrieve_body ( wp_remote_get( $url ) );

				if ( !empty ( $data ) && !is_wp_error( $data )  )
					set_transient( $transient_name, $data, 5 * MINUTE_IN_SECONDS );
			}
		}

		if ( !empty ( $data ) )
			$xml = simplexml_load_string( $data );	// URL file-access is disabled? HS1438

		if ( empty( $xml ) || empty( $data ) )
			$xml = simplexml_load_file( $url ); 	// Raw xml so we can fetch other data

		$base = new StdClass;
		$user = new StdClass;

		// Many of these fields are pulled from the data that soundcloud includes in their track player
		$attr = array( 'kind', 'id', 'created_at', 'user_id', 'duration', 'user_id', 'duration', 'commentable', 'state', 'original_content_size', 'sharing', 'tag_list', 'permalink', 'streamable', 'embeddable_by', 'downloadable', 'purchase_url', 'label_id', 'purchase_title', 'genre', 'title', 'description', 'label_name', 'release', 'track_type', 'key_signature', 'isrc', 'video_url', 'bpm', 'release_year', 'release_month', 'release_day', 'original_format', 'license', 'uri', 'user', 'permalink_url', 'artwork_url', 'waveform_url', 'stream_url', 'download_url', 'download_count', 'favoritings_count', 'comment_count', 'attachments_uri', 'episode_number', 'content' );

		$user_attr = array( 'id', 'kind', 'permalink', 'username', 'uri', 'permalink_url', 'avatar_url' );

		foreach( $attr as $a ) {
			$base->{$a} = '';
		}

		foreach( $user_attr as $a ) {
			$user->{$a} = '';
		}

		$base->user = $user;

		$channel = $xml->channel;
		$items = $channel->item;

		$tracks = array();

		$episode_number = count( $items );
		$i = 0;

		if( !is_wp_error( $rss ) ) {
		
			require_once( SPP_PLUGIN_BASE . 'classes/download.php' );

			foreach ( $rss->get_items() as $item) {
				
				$enclosures = $item->get_enclosures();
				$enclosure = $item->get_enclosure();

				foreach( $enclosures as $enc ) {
					if( $enc->handler == 'mp3' ) {
						$enclosure = $enc;
					}
				}

	 			$track = clone $base ;
				$date = new DateTime( $item->get_date() );

				$track->id = $i;
				$track->title = $item->get_title();
				
				// Set the show notes based on user's selected option
				$advanced_options = get_option( 'spp_player_advanced');
				$show_notes = isset( $advanced_options['show_notes'] )
							? $advanced_options['show_notes'] : "description";
				switch ($show_notes) {
					case "description":
						$description = $item->get_description();
						break;
					case "content":
						$description = $item->get_content();
						break;
					case "itunes_summary":
						$description = $item->get_item_tags(
								SIMPLEPIE_NAMESPACE_ITUNES, 'summary');
						$description = strip_shortcodes($description[0]["data"]);
						break;
					case "itunes_subtitle":
						$description = $item->get_item_tags(
								SIMPLEPIE_NAMESPACE_ITUNES, 'subtitle');
						$description = strip_shortcodes($description[0]["data"]);
						break;
					default:
						$description = $item->get_description();
						break;
				}
				$description = strip_tags( $description, '<p><a>' );
				$track->description = self::scrub_html( $description );
				
				// If we have keywords, set those.
				// Use the Soundcloud style (with quotes) for historical reasons
				$tags = $enclosure->get_keywords();
				$track->tag_list = "";
				if( is_array( $tags ) ) {
					foreach( $tags as $tag ) {
						$track->tag_list = $track->tag_list . ' "' . $tag . '" ';
					}
				}
				
				// Truncate the show notes for display
				$maxLength = strlen( $track->tag_list ) > 0 ? 140 : 280;
				$track->truncated_show_notes = self::truncate_show_notes( $track->description, $maxLength, 10 );
				
				// HS4058 and 4428: The string '?#' was being appended to the enclosure's link
				if( substr( $enclosure->link, -2) == "?#" ) {
					$enclosure->link = substr( $enclosure->link, 0, -2 );
				}

				$item_link = $item->get_link();
				$track->permalink_url = is_null($item_link) ? "" : $item_link;
				$track->uri = is_null($item_link) ? "" : $item_link;
				$track->stream_url = $enclosure->link;
				$track->download_url = $enclosure->link;
				$track->duration = $enclosure->duration;
				$track->created_at = $date->format( 'Y/m/d h:i:s O' );
				
				$track->artwork_url = (string) $channel->image->url;
				if ( stripos( $track->artwork_url, "http://i1.sndcdn.com" ) !== FALSE )
					$track->artwork_url = str_replace( "http://i1.sndcdn.com", "//i1.sndcdn.com", $track->artwork_url );
				
				if( $track->artwork_url == '' || empty( $track->artwork_url ) ) {

					if( is_array( $enclosure->thumbnails ) && !empty( $enclosure->thumbnails[0] ) && $enclosure->thumbnails[0] != '' ) {
						
						$track->artwork_url = $enclosure->thumbnails[0];

					} else {

						$itunes_image = $rss->get_channel_tags( SIMPLEPIE_NAMESPACE_ITUNES, 'image' );

						if( is_array( $itunes_image ) ) {
							$track->artwork_url = $itunes_image[0]['attribs']['']['href'];	
						}

					}					

				}
				
				$track->show_name = (string) $channel->title;
				$track->episode_number = $episode_number;
				$track->download_id = SPP_Download::save_download_id($enclosure->link);

				if( !empty( $track->stream_url ) && $track->stream_url != '' ) {
					$tracks[] = $track;	
					$episode_number--;
				} else {}

				$i++;
				
				// Limit the number of episodes
				if( $episode_limit > 0 && $i >= $episode_limit )
					break;
				
			}
		}

		return $tracks;
		
	}

	/**
	 * Scrub the HTML passed in for any attributes we don't want, like class, style, and ID
	 * 
	 * @param  string $input Can be any valid HTML text
	 * @return string $output Scrubbed HTML output, minus the doctype
	 */
	public static function scrub_html( $input ) {

		if( !extension_loaded( 'libxml' ) || !extension_loaded( 'dom' ) || empty( $input ) )
			return $input;

		if (function_exists("mb_convert_encoding")) {
			require_once( dirname( __FILE__ ) . '/vendor/SmartDOMDocument.php' );
			$dom = new SmartDOMDocument;
		} else {
			$dom = new DOMDocument;
		}
		
		$dom->loadHTML( $input );

		$xpath = new DOMXPath( $dom );
		$nodes = $xpath->query('//@*');

		foreach ($nodes as $node) {
			if( $node->nodeName == 'style' || $node->nodeName == 'class' || $node->nodeName == 'id' ) {
			    $node->parentNode->removeAttribute($node->nodeName);
			}
		}

		$links = $dom->getElementsByTagName('a');

		foreach ( $links as $item ) {
			$item->setAttribute('target','_blank');  
		}
		
		$output = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML() ); // Extract w/o the doctype and html/body tags
		
		return $output;

	}
	
	/**
	 * Truncate the show notes to something shorter.
	 *
	 * @param  string $input     The original show notes, stripped of all tags except <p> and <a>
	 * @param  int    $maxLength The optimal length after truncation
	 * @param  int    $variance  How far away from optimal the truncation is allowed to be
	 * @return string $output    The truncated show notes
	 */
	public static function truncate_show_notes( $input, $maxLength, $variance ) {
	
		$input_len = strlen( $input );
		$output = "";
		$output_len = 0;  // The number of visible characters in the output. (Don't count HTML tags.)
		$in_anchor = false;
		$i = 0;
		
		while( $i < $input_len ) {
			// If we're starting an HTML tag
			if( $input[$i] === '<' ) {
				if( $i + 2 > $input_len ) {
					// Something is amiss.  Return now to avoid crashing.
					return $output;
				}
				// If we're starting an anchor tag
				if( $input[ $i+1 ] === 'a' ) {
					$in_anchor = true;
				// If we're ending an anchor tag
				} else if( substr( $input, $i+1, 2 ) === '/a' ) {
					$in_anchor = false;
				}
				// Add everything within <> to the output, but don't count it as visible
				while( $i < $input_len && $input[$i] != '>' ) {
					$output .= $input[$i];
					$i++;
				}
				// Also include the closing '>'
				if( $i < $input_len ) {
					$output .= $input[$i];
					$i++;
				}
			
			// Otherwise, we're not starting an HTML tag
			} else {
			
				// If we're starting an HTML entity (like &amp;)
				if( $input[$i] === '&' ) {
					// Add everything from here to ';' to the output, but don't count it as visible
					while( $i < $input_len && $input[$i] != ';' ) {
						$output .= $input[$i];
						$i++;
					}
					// Also include the closing ';'
					if( $i < $input_len ) {
						$output .= $input[$i];
						$i++;
					}
				} else {
					// It's a regular character.  Add it to the output.
					$output .= $input[$i];
					$i++;
				}
				// Count one character added to the output
				$output_len++;
				
				// If we've reached our window to end the string
				if( $output_len > $maxLength - $variance ) {
					// Go until the next ' ', or the next '<', or until the hard max
					while( $i < $input_len ) {
						if( $input[$i] === '<' || $input[$i] === ' '
						        || $output_len >= $maxLength + $variance ) {
							break;
						} else {
							// If we're starting an HTML entity (like &amp;)
							if( $input[$i] === '&' ) {
								// Add everything from here to ';' to the output, but don't count it as visible
								while( $i < $input_len && $input[$i] != ';' ) {
									$output .= $input[$i];
									$i++;
								}
								// Also include the closing ';'
								if( $i < $input_len ) {
									$output .= $input[$i];
									$i++;
								}
							} else {
								// It's a regular character.  Add it to the output.
								$output .= $input[$i];
								$i++;
							}
							// Count one character added to the output
							$output_len++;
						}
					}
					// Close off open anchors
					if( $in_anchor ) {
						$output .= "</a>";
					}
					break;
				}
			}
		}

		// Remove any trailing '</p>' so our "more" button goes in the right place
		if( substr( $output, -4 ) == "</p>" ) {
			$output = substr( $output, 0, -4 );
		}
		return $output;
	}

}
