<?php
/*
Plugin Name: SEO Optizer
Plugin URI: http://www.kwoxer.de
Description: This Plugin has a few settings for better SEO.
Version: 0.0.1
Author: Curtis Mosters
Author URI: http://www.kwoxer.de
Text Domain: seo-optizer
*/

/*
Copyright (c) 2012-2012 Curtis Mosters
Original Copyright (c) 2009-2011 John Lamansky

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	die();
}

/********** CONSTANTS **********/

//The minimum version of WordPress required
define('SO_MINIMUM_WP_VER', '3.1.3');

//Reading plugin info from constants is faster than trying to parse it from the header above.
define('SO_PLUGIN_NAME', 'SEO Optizer');
define('SO_PLUGIN_URI', 'http://www.kwoxer.de/');
define('SO_VERSION', '0.0.1');
define('SO_AUTHOR', 'Curtis Mosters');
define('SO_AUTHOR_URI', 'http://www.kwoxer.de/');
define('SO_USER_AGENT', 'SeoOptizer/0.0.1');

/********** INCLUDES **********/

//Libraries
include 'includes/backcompat.php';
include 'includes/jlfunctions/jlfunctions.php';
include 'includes/jlwp/jlwp.php';

//Plugin files
include 'plugin/so-constants.php';
include 'plugin/so-functions.php';
include 'plugin/class.seo-optimization.php';

//Module files
include 'modules/class.so-module.php';
include 'modules/class.so-importmodule.php';


/********** PLUGIN FILE LOAD HANDLER **********/

global $wp_version;
if (version_compare($wp_version, SO_MINIMUM_WP_VER, '>=')) {
	global $seo_optimization;
	$seo_optimization =& new SEO_Optimization(__FILE__);
} else {
	add_action('admin_notices', 'so_wp_incompat_notice');
}

function so_wp_incompat_notice() {
	echo '<div class="error"><p>';
	printf(__('SEO Optimization requires WordPress %s or above. Please upgrade to the latest version of WordPress to enable SEO Optimization on your blog, or deactivate SEO Optimization to remove this notice.', 'seo-optimization'), SO_MINIMUM_WP_VER);
	echo "</p></div>\n";
}

?>