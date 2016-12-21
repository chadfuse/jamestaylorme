<?php
// Manages the FE aspect of displaying the forms.
class ONTRAforms
{
	public static function init()
	{
		if ( !is_admin() )
		{
			self::initHooks();
		}
	}

	// Initializes WP hooks
	private static function initHooks()
	{
		add_action( 'wp_footer', array ( 'ONTRAforms', 'addFormCode') );
		add_shortcode( 'ontraforms', array ( 'ONTRAforms', 'ontraFormShortcode' ) );
	}


	// Pulls together the options required to show the right form along with any specified parameters
	public static function addFormCode() 
	{
		global $post;
		$postId = $post->ID;
		$formID = get_post_meta( $postId, 'ontraform', TRUE );
		$opLightboxType = get_post_meta( $postId, 'opLightboxType', TRUE );

		if ( isset($formID) && $formID != 'Choose your desired ONTRAform' )
		{
			$metadata = get_metadata( 'post', $postId );

			$params = array(
				'opPopPosition' => $metadata['opPopPosition'][0],
				'opScrollPercent' => $metadata['opScrollPercent'][0],
				'opScroll' => $metadata['opScroll'][0],
				'opSecondsonpage' => $metadata['opSecondsonpage'][0],
				'opSeconds' => $metadata['opSeconds'][0],
				'opExitintent' => $metadata['opExitintent'][0],
				'opIfClosed' => $metadata['opIfClosed'][0],
				'opMaxDisplay' => $metadata['opMaxDisplay'][0],
				'opMaxTime' => $metadata['opMaxTime'][0],
				'opMaxRefresh' => $metadata['opMaxRefresh'][0] );

			$params = json_encode( $params );

			$ontraForm = self::createScript( $formID, $opLightboxType, $params );

			echo $ontraForm;
		}
	}


	// Creates the script that will get dropped in the footer of the page
	private static function createScript( $formId, $type, $params=false )
	{
		$formObject = OPObjects::getOPObject('form', false, $formId );
		$formObject = json_decode( $formObject );
		$accountId = $formObject->account_id;
		$params = json_decode($params);

		switch( $type )
		{
			case 'automatic':

				$parameters = '';
				if ( !empty($params->opPopPosition) )
				{
					$parameters .= 'popPosition=' . $params->opPopPosition . '&';
				}
				if ( !empty($params->opExitintent) )
				{
					$parameters .= 'onExitIntent=true&';
				}
				if ( !empty($params->opScrollPercent) )
				{
					$parameters .= 'onScrollTo=' . $params->opScroll . '&';
				}
				if ( !empty($params->opSecondsonpage) )
				{
					$parameters .= 'onVisitDuration=' . $params->opSeconds . '&';
				}
				if ( !empty($params->opIfClosed) )
				{
					$parameters .= 'filloutRestrictions=true&';
				}
				if ( !empty($params->opMaxDisplay) )
				{
					$parameters .= 'maxTriggers=' . $params->opMaxRefresh . '&';
					$parameters .= 'timeframe=' . $params->opMaxTime;
				}

				$script = '<script type="text/javascript" async="true" src="https://app.ontraport.com/js/ontraport/opt_assets/drivers/opf.js" data-opf-uid="p2c' . $accountId . 'f' . $formId . '" data-opf-params="' . $parameters . '"></script>';
			break;

			case 'link':
				$script = '<script type="text/javascript" async="true" src="https://app.ontraport.com/js/ontraport/opt_assets/drivers/opf.js" data-opf-uid="p2c' . $accountId . 'f' . $formId . '" data-opf-params="popPosition=mc"></script>';
			break;
		}

		return $script;
	}


	public static function ontraFormShortcode( $atts, $content = null ) 
	{
    	global $post;
		$postId = $post->ID;
		$formID = get_post_meta( $postId, 'ontraform', TRUE );
		$type = get_post_meta( $postId, 'opLightboxType', TRUE );
		$accountID = 29694;

		if ( ( $formID != 'Choose your desired ONTRAform' ) && $type != 'embedded' )
		{
			return '<a href="javascript://"" data-opf-trigger="p2c' . $accountID . 'f' . $formID . '">' . $content . '</a>';
		}
		else if ( ( $formID != 'Choose your desired ONTRAform' ) && $type == 'embedded' )
		{
			$script = $content . '<script type="text/javascript" async="true" src="https://app.ontraport.com/js/ontraport/opt_assets/drivers/opf.js" data-opf-uid="p2c' . $accountID . 'f' . $formID . '" data-opf-params="embed=true&popPosition=mc"></script>';

			return $script;
		}
		else
		{
			return $content;
		}
    }

}