<?php
/**
 * @package ONTRApages
 */
/*
Plugin Name: ONTRApages
Plugin URI: http://ONTRApages.com
Description: ONTRApages for WordPress allows ONTRAPORT & ONTRApages.com users to connect to their accounts and easily host their landing pages on their own WordPress sites. Get your free ONTRApages.com account today.
Version: 1.2.2
Author: ONTRAPORT
Author URI: http://ONTRAPORT.com/
License: GPLv2 or later
Text Domain: ONTRApages.com
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Don't show anything if called directly
if ( !function_exists( 'add_action' ) )
{
	exit;
}

define( 'ONTRAPAGES_VERSION', '1.2.2' );
define( 'ONTRAPAGES__MINIMUM_WP_VERSION', '4.0' );
define( 'ONTRAPAGES__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ONTRAPAGES__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPAPI', 'https://api.ontraport.com/1/' );
define( 'OPGAPI', 'https://api.ontrapages.com/' );

require_once( ONTRAPAGES__PLUGIN_DIR . 'OPCoreFunctions.php' );
require_once( ONTRAPAGES__PLUGIN_DIR . 'OPAdminSettings.php' );
require_once( ONTRAPAGES__PLUGIN_DIR . 'OPObjects.php' );
require_once( ONTRAPAGES__PLUGIN_DIR . 'ONTRApages.php' );
// require_once( ONTRAPAGES__PLUGIN_DIR . 'ONTRAforms.php' );

// Run these functions on WP plugin activation and deactivation
register_activation_hook( __FILE__, array( 'OPAdminSettings', 'ontrapagesActivation' ) );
register_deactivation_hook( __FILE__, array( 'OPAdminSettings', 'ontrapagesDeactivation' ) );

// Initialize OP page and form settings
add_action( 'init', array( 'ONTRApages', 'init' ) );
// add_action( 'init', array( 'ONTRAforms', 'init' ) );

// Admin settings
if ( is_admin() ) 
{
	require_once( ONTRAPAGES__PLUGIN_DIR . 'OPObjects.php' );
	require_once( ONTRAPAGES__PLUGIN_DIR . 'ONTRApagesAdmin.php' );
	// require_once( ONTRAPAGES__PLUGIN_DIR . 'ONTRAformsAdmin.php' );

	// Initialize OP Admin settings and add admin scripts / styles
	add_action( 'admin_menu', array( 'OPAdminSettings', 'adminSettings' ) );
	add_action( 'admin_enqueue_scripts', array( 'OPAdminSettings', 'adminScripts' ) );

	// Initialize any necessary Admin Settings &/or checks
	add_action( 'init', array( 'OPAdminSettings', 'init' ) );

	// Initalize OP Admin page and form settings
	add_action( 'init', array( 'ONTRApagesAdmin', 'init' ) );
	// add_action( 'init', array( 'ONTRAformsAdmin', 'init' ) );
	
	// Fix the wrong API settings that were set in v1.1
	if ( get_option('opAPIFix') === false )
	{
		OPAdminSettings::fixAPISettings();
	}
	//We need to hook into after plugins are loaded so we can hook into PilotPress
	add_action("plugins_loaded", array( "OPAdminSettings", "pluginsLoaded"));
	
}