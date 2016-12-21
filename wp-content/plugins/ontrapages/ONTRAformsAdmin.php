<?php
// Manages the BE aspect of setting the ONTRAforms to be displayed.
class ONTRAformsAdmin
{
	public static function init()
	{
		if ( is_admin() )
		{
			self::initHooks();
		}
	}


	// Initializes WP hooks
	private static function initHooks()
	{
		add_action( 'add_meta_boxes', array ( 'ONTRAformsAdmin', 'addOPMetabox') );
		add_action( 'save_post', array ( 'ONTRAformsAdmin', 'updateONTRAFormData' ), 1, 2 );

		// Add TinyMCE buttons and register the new shortcode to make forms linkable
		add_filter('mce_buttons', array ( 'ONTRAformsAdmin', 'ofTinyMCEButtons' ) );
		add_filter('mce_external_plugins', array ( 'ONTRAformsAdmin', 'ofRegisterMCEJS' ) );
	}


	// Adds the Metabox to the pages and posts
	public static function addOPMetabox()
	{
		add_meta_box('opMetabox', 'ONTRAforms', array ( 'ONTRAformsAdmin', 'getOPMetabox'), 'page', 'normal', 'high');
		add_meta_box('opMetabox', 'ONTRAforms', array ( 'ONTRAformsAdmin', 'getOPMetabox'), 'post', 'normal', 'high');
	}


	// Contains the content for the ONTRAform Object dropdown select
	public static function getOPMetabox()
	{
		global $post;
		$postId = get_the_ID();
		
		$html = '<div ng-app="ontraPages" class="op-metabox-opselect op-metabox-ontraforms">
					<div ng-controller="ONTRAFormsController" class="opm-opselect-items">';

		$html .=  self::opFormSettings( $postId );

		$html .= '	</div>
				</div>';

		echo $html;

	}


	// Contains an ONTRAform Object dropdown select component
	protected static function ONTRAformSelect( $ONTRAforms )
	{
		if ( is_object( $ONTRAforms ) )
		{
			$selected = '';
			$postId = get_the_ID();
			$opID = get_post_meta( $postId, 'ontraform', true);
			$num = 0;
			$html = 
				'<div class="op-of-form-select" ng-show="lightboxType">
					<div class="op-of-setting-title">Select a form.</div>
					<input type="hidden" name="ontraformnonce" value="' . wp_create_nonce('ontraformnonce') . '" />
					<select name="ontraform"><option value="">Choose your desired ONTRAform</option>';

			// Loop through all ONTRAform objects
			foreach ( $ONTRAforms as $ONTRAform )
			{
				if ( ( !empty($ONTRAform) && is_object($ONTRAform) ) && 
						(( $ONTRAform->form_id !== null || $ONTRAform->form_id !== '' ) && 
							( $ONTRAform->formname !== null || $ONTRAform->formname !== '' )) )
				{
					if ( ($opID !== '' && $opID !== null) && intval($opID) === intval($ONTRAform->form_id) )
					{
						$selected = 'selected';
					}

					$html .= 
						'<option id="oform-' . $num . '" value="' . $ONTRAform->form_id . '" ' . $selected . '>' . $ONTRAform->formname . '</option>';

					$num++;
					$selected = '';
				}
			}
			$html .= 
					'</select>
					<script type="text/javascript">
						angular.element(document).ready(function() 
						{
							var formsController = document.querySelector("[ng-controller=ONTRAFormsController]");
  							var $scope = angular.element(formsController).scope();
  							$scope.ontraforms = ' . json_encode($ONTRAforms) . ';
						});
					</script>
				</div>';

			return $html;
		}
		else 
		{
			return -1;
		}

	}


	private static function getONTRAformPostMeta( $postId )
	{
		$keys = array(
			'ontraform',
			'opLightboxType',
			'opPopPosition',
			'opScrollPercent',
			'opScroll',
			'opSecondsonpage',
			'opSeconds',
			'opExitintent',
			'opIfClosed',
			'opMaxDisplay',
			'opMaxTime',
			'opMaxRefresh');

		$postMeta = array();
		foreach ( $keys as $key )
		{
			$postMeta[$key] = get_post_meta( $postId, $key, TRUE );
		}

		return json_encode( $postMeta );
	}


	// Save or updates which page gets which ONTRAform
	public static function opFormSettings( $postId )
	{
		// Check API Creds first then proceed if ok
		$error = OPCoreFunctions::checkAPICreds( 'code' );

		// Get ONTRAforms Objects
		$ONTRAforms = OPObjects::getOPObjects( 'forms' );

		if ( is_object( $ONTRAforms ) )
		{
			$style = 
				'<style>.op-of-pposition { background-image: url(\'' . ONTRAPAGES__PLUGIN_URL . '_inc/images/ontraform_positions.png\')}</style>';

			$displayType = 
				'<div class="op-of-setting-title">How should your form be displayed?</div>
				<div class="op-of-display-type">
					<div class="op-of-dtype" ng-click="updateLightboxType(\'automatic\')" ng-class="{selected: lightboxType==\'automatic\'}">
						<img src="' . ONTRAPAGES__PLUGIN_URL . '_inc/images/publish--lightbox.png" />
						<div class="op-of-dtype-name">Automatic Lightbox</div>
					</div>
					<div class="op-of-dtype" ng-click="updateLightboxType(\'link\')" ng-class="{selected: lightboxType==\'link\'}">
						<img src="' . ONTRAPAGES__PLUGIN_URL . '_inc/images/publish--as-a-link.png" />
						<div class="op-of-dtype-name">Click to Pop Lightbox</div>
					</div>
					<div class="op-of-dtype" ng-click="updateLightboxType(\'embedded\')" ng-class="{selected: lightboxType==\'embedded\'}">
						<img src="' . ONTRAPAGES__PLUGIN_URL . '_inc/images/publish--embedded.png" />
						<div class="op-of-dtype-name">Embedded in Page</div>
					</div>
					<input type="hidden" name="opLightboxType" ng-value="lightboxType" />
				</div>';

			// Get the ONTRAforms Select component
			$ONTRRAformSelect = self::ONTRAformSelect( $ONTRAforms );

			$automaticSettings = 
				'<div class="op-of-automatic-settings" ng-show="lightboxType==\'automatic\'">
					<div class="op-of-setting-title">Where, when & how often should it appear?</div>
					<div class="op-of-appearance">
						<div class="op-of-pop-position op-of-appearance-option">
							<div class="op-of-ts-title">Select popup position</div>
							<div class="op-of-positions">
								<div class="op-of-pposition op-of-pp-tl" ng-click="updatePopPosition(\'tl\')" ng-class="{selected: opPopPosition==\'tl\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-tc" ng-click="updatePopPosition(\'tc\')" ng-class="{selected: opPopPosition==\'tc\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-tr" ng-click="updatePopPosition(\'tr\')" ng-class="{selected: opPopPosition==\'tr\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-ml" ng-click="updatePopPosition(\'ml\')" ng-class="{selected: opPopPosition==\'ml\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-mc" ng-click="updatePopPosition(\'mc\')" ng-class="{selected: opPopPosition==\'mc\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-mr" ng-click="updatePopPosition(\'mr\')" ng-class="{selected: opPopPosition==\'mr\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-bl" ng-click="updatePopPosition(\'bl\')" ng-class="{selected: opPopPosition==\'bl\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-bc" ng-click="updatePopPosition(\'bc\')" ng-class="{selected: opPopPosition==\'bc\'}"> &nbsp;</div>
								<div class="op-of-pposition op-of-pp-br" ng-click="updatePopPosition(\'br\')" ng-class="{selected: opPopPosition==\'br\'}"> &nbsp;</div>
								<input type="hidden" name="opPopPosition" ng-value="opPopPosition" />
							</div>
						</div>
						<div class="op-of-timing-settings op-of-appearance-option">
							<div class="op-of-ts-title">When to trigger lightbox</div>
							<div class="op-of-ts-option">
								<label><input type="checkbox" name="opScrollPercent" ng-model="opScrollPercent" ng-true-value="\'on\'"><span class="op-of-ts-text">Display after user scrolls down </span></label>
								<input type="number" name="opScroll" min="0" max="100" ng-model="opScroll" ng-required="opScrollPercent" /><span class="op-of-ts-text"> percent of the page.</span>
							</div>
							<div class="op-of-ts-option">
								<label><input type="checkbox" name="opSecondsonpage" ng-model="opSecondsonpage" ng-true-value="\'on\'"><span class="op-of-ts-text">Display after </span></label><input type="number" name="opSeconds" min="0" max="720" ng-model="opSeconds" ng-required="opSecondsonpage" /><span class="op-of-ts-text"> seconds.</span>
							</div>
							<div class="op-of-ts-option">
								<label><input type="checkbox" name="opExitintent" ng-model="opExitintent" ng-true-value="\'on\'"><span class="op-of-ts-text">Display popup on exit intent.</span></label>
							</div>
							<div class="op-of-ts-title">Repeat display settings</div>
							<div class="op-of-ts-option">
								<label><input type="checkbox" name="opIfClosed" ng-model="opIfClosed" ng-true-value="\'on\'"><span class="op-of-ts-text">If popup has been closed or filled out, don\'t show to this visitor again.</span></label>
							</div>
							<div class="op-of-ts-option">
								<label><input type="checkbox" name="opMaxDisplay" ng-model="opMaxDisplay" ng-true-value="\'on\'"><span class="op-of-ts-text">Display this poup once during a </span></label><input type="number" name="opMaxTime" min="0" max="744" ng-model="opMaxTime" ng-required="opMaxDisplay" /> <span class="op-of-ts-text">hour time frame and a maximum of </span><input type="number" name="opMaxRefresh" min="0" max="10" ng-model="opMaxRefresh" ng-required="opMaxDisplay" /> <span class="op-of-ts-text">times per page refreshes.</span>
							</div>
						</div>
					</div>
				</div>';

			$embeddedSettings =
				'<div class="op-of-embedded-settings" ng-show="lightboxType==\'embedded\'">
					Embedded settings
				</div>';

			$linkSettings =
				'<div class="op-of-link-settings" ng-show="lightboxType==\'link\'">
					Link settings
				</div>';

			$postMeta = self::getONTRAformPostMeta( $postId );
			$postMeta = 
				'<script type="text/javascript">
					angular.element(document).ready(function() 
					{
						var formsController = document.querySelector("[ng-controller=ONTRAFormsController]");
						var $scope = angular.element(formsController).scope();
						$scope.ofPostMeta = ' . $postMeta . ';
						$scope.opPopPosition = $scope.ofPostMeta.opPopPosition;
						$scope.lightboxType = $scope.ofPostMeta.opLightboxType;
						$scope.opScrollPercent = $scope.ofPostMeta.opScrollPercent;
						$scope.opScroll = parseInt($scope.ofPostMeta.opScroll);
						$scope.opSecondsonpage = $scope.ofPostMeta.opSecondsonpage;
						$scope.opSeconds = parseInt($scope.ofPostMeta.opSeconds);
						$scope.opExitintent = $scope.ofPostMeta.opExitintent;
						$scope.opIfClosed = $scope.ofPostMeta.opIfClosed;
						$scope.opMaxDisplay = $scope.ofPostMeta.opMaxDisplay;
						$scope.opMaxTime = parseInt($scope.ofPostMeta.opMaxTime);
						$scope.opMaxRefresh = parseInt($scope.ofPostMeta.opMaxRefresh);
					});
				</script>';

			$fullSettings = 
				'<div class="op-ontraform-settings">' .
					$style .
					$displayType .
					$ONTRRAformSelect .
					$automaticSettings .
					$embeddedSettings .
					$linkSettings .
					$postMeta .
				'</div>';

			return $fullSettings;
		}
		else if ( $ONTRAforms === 'auth-error' )
		{
			return '<div class="op-error-message">Unfortunately it appears that your API credentials are invalid. <a href="edit.php?post_type=ontrapage&page=opsettings">Click here</a> to update them & then try again.</div>';
		}
		else
		{
			
			return '<div class="op-error-message">Unfortunately it appears that you don\'t have any ONTRAforms available. Create one by logging into your <a href="https://ontrapages.com" target="_blank">ONTRApages account</a> and then return when it has been saved!</div>';
		}
	}


	// Save or updates which page gets which ONTRAform
	public static function updateONTRAFormData( $post_id, $post )
	{
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( isset( $_POST['ontraformnonce'] ) )
		{
			$keys = array(
				'ontraform' => $_POST['ontraform'],
				'opLightboxType' => $_POST['opLightboxType'],
				'opPopPosition' => $_POST['opPopPosition'],
				'opScrollPercent' => $_POST['opScrollPercent'],
				'opScroll' => $_POST['opScroll'],
				'opSecondsonpage' => $_POST['opSecondsonpage'],
				'opSeconds' => $_POST['opSeconds'],
				'opExitintent' => $_POST['opExitintent'],
				'opIfClosed' => $_POST['opIfClosed'],
				'opMaxDisplay' => $_POST['opMaxDisplay'],
				'opMaxTime' => $_POST['opMaxTime'],
				'opMaxRefresh' => $_POST['opMaxRefresh']);

			if ( !wp_verify_nonce( $_POST['ontraformnonce'], 'ontraformnonce' ) ) 
			{
				return $post->ID;
			}

			// Is the user allowed to edit the post or page?
			if ( !current_user_can( 'edit_post', $post->ID ) )
			{
				return $post->ID;
			}		
			
			// Don't store custom data twice
			if( $post->post_type == 'revision' )
			{
				return;
			}

			foreach ( $keys as $key=>$value )
			{
				if ( get_post_meta( $post->ID, $key, FALSE ) ) 
				{ 
					update_post_meta( $post->ID, $key, $value );
				} 
				else 
				{ 
					add_post_meta( $post->ID, $key, $value );
				}

				if ( !$value ) 
					delete_post_meta( $post->ID, $key );
			}
		}
	}
	

	public static function ofTinyMCEButtons( $buttons ) 
	{
		array_push( $buttons, 'separator', 'ofclicktopop', 'embeddedONTRAform' );

		return $buttons;
	}


	public static function ofRegisterMCEJS( $plugin_array ) 
	{
		$plugin_array['of-click-to-pop'] = plugins_url('/_inc/js/of-tinymce-addon.js',__FILE__);
		$plugin_array['embeddedONTRAform'] = plugins_url('/_inc/js/of-tinymce-addon.js',__FILE__);

		return $plugin_array;
	}

}