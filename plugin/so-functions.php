<?php
/**
 * Non-class functions.
 */

/********** INDEPENDENTLY-OPERABLE FUNCTIONS **********/

/**
 * Returns the plugin's User-Agent value.
 * Can be used as a WordPress filter.
 * 
 * @since 0.1
 * @uses SO_USER_AGENT
 * 
 * @return string The user agent.
 */
function so_get_user_agent() {
	return SO_USER_AGENT;
}

/**
 * Records an event in the debug log file.
 * Usage: so_debug_log(__FILE__, __CLASS__, __FUNCTION__, __LINE__, "Message");
 * 
 * @since 0.1
 * @uses SO_VERSION
 * 
 * @param string $file The value of __FILE__
 * @param string $class The value of __CLASS__
 * @param string $function The value of __FUNCTION__
 * @param string $line The value of __LINE__
 * @param string $message The message to log.
 */
function so_debug_log($file, $class, $function, $line, $message) {
	global $seo_optimization;
	if (isset($seo_optimization->modules['settings']) && $seo_optimization->modules['settings']->get_setting('debug_mode') === true) {
	
		$date = date("Y-m-d H:i:s");
		$version = SO_VERSION;
		$message = str_replace("\r\n", "\n", $message);
		$message = str_replace("\n", "\r\n", $message);
		
		$log = "Date: $date\r\nVersion: $version\r\nFile: $file\r\nClass: $class\r\nFunction: $function\r\nLine: $line\r\nMessage: $message\r\n\r\n";
		$logfile = trailingslashit(dirname(__FILE__))."seo-optimization.log";
		
		@error_log($log, 3, $logfile);
	}
}

/**
 * Joins strings into a natural-language list.
 * Can be internationalized with gettext or the so_lang_implode filter.
 * 
 * @since 1.1
 * 
 * @param array $items The strings (or objects with $var child strings) to join.
 * @param string|false $var The name of the items' object variables whose values should be imploded into a list.
	If false, the items themselves will be used.
 * @param bool $ucwords Whether or not to capitalize the first letter of every word in the list.
 * @return string|array The items in a natural-language list.
 */
function so_lang_implode($items, $var=false, $ucwords=false) {
	
	if (is_array($items) ) {
		
		if (strlen($var)) {
			$_items = array();
			foreach ($items as $item) $_items[] = $item->$var;
			$items = $_items;
		}
		
		if ($ucwords) $items = array_map('ucwords', $items);
		
		switch (count($items)) {
			case 0: $list = ''; break;
			case 1: $list = $items[0]; break;
			case 2: $list = sprintf(__('%s and %s', 'seo-optimization'), $items[0], $items[1]); break;
			default:
				$last = array_pop($items);
				$list = implode(__(', ', 'seo-optimization'), $items);
				$list = sprintf(__('%s, and %s', 'seo-optimization'), $list, $last);
				break;
		}
		
		return apply_filters('so_lang_implode', $list, $items);
	}

	return $items;
}

/**
 * Escapes an attribute value and removes unwanted characters.
 * 
 * @since 0.8
 * 
 * @param string $str The attribute value.
 * @return string The filtered attribute value.
 */
function so_esc_attr($str) {
	if (!is_string($str)) return $str;
	$str = str_replace(array("\t", "\r\n", "\n"), ' ', $str);
	$str = esc_attr($str);
	return $str;
}

/**
 * Escapes HTML.
 * 
 * @since 2.1
 */
function so_esc_html($str) {
	return esc_html($str);
}

/**
 * Escapes HTML. Double-encodes existing entities (ideal for editable HTML).
 * 
 * @since 1.5
 * 
 * @param string $str The string that potentially contains HTML.
 * @return string The filtered string.
 */
function so_esc_editable_html($str) {
	return _wp_specialchars($str, ENT_QUOTES, false, true);
}

?>