<?php

class SPP_Transients {
	
	public static function spp_transient_name( $name_args ) {
		if( !isset( $name_args['purpose'] ) ) {
			return null;
		}
		
		if( $name_args['purpose'] === 'tracks from feed url' ) {
			if( !isset( $name_args['url'] ) || !isset( $name_args['episode_limit'] ) ) {
				return null;
			}
			$transient_name = 'spp_cachea_' . md5(
				SPP_Core::VERSION . $name_args['url'] . (string) $name_args['episode_limit']);
				
		} else if( $name_args['purpose'] === 'xml from feed url' ) {
			if( !isset( $name_args['url'] ) ) {
				return null;
			}
			$transient_name = 'spp_cachesx_' . md5(
				SPP_Core::VERSION . $name_args['url']);
		}
		
		return $transient_name;
	}
	
	public static function spp_get_transient( $transient_name ) {
		// Sometimes, the transient timeouts disappear.  I don't know the cause.
		// This will pretend there's no transient there when there's no associated timeout.
		if( !isset( $transient_name ) )
			return false;
		$transient_option = get_option( '_transient_' . $transient_name );
		$timeout_option = get_option( '_transient_timeout_' . $transient_name );
		if( $timeout_option == false ) {
			return false;
		} else {
			return get_transient( $transient_name );
		}
	}
}
