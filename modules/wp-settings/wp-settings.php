<?php
/**
 * Settings Monitor Module
 * 
 * @since 6.9
 */

if (class_exists('SO_Module')) {

class SO_WpSettings extends SO_Module {
	
	var $results = array();

	function get_module_title() { return __('Settings Monitor (Beta)', 'seo-optimization'); }
	function get_menu_title() { return __('Settings Monitor', 'seo-optimization'); }
	
	function has_menu_count() { return true; }
	function get_menu_count() {
		$count = 0;
		foreach ($this->results as $data) {
			if ($data[0] == SO_RESULT_ERROR) $count++;
		}
		return $count;
	}
	
	function init() {
		
		if (is_admin()) {
			if (get_option('blog_public'))
				$this->results[] = array(SO_RESULT_OK, __('Blog is visible to search engines', 'seo-optimization'),
					__('WordPress will allow search engines to visit your site.', 'seo-optimization'));
			else
				$this->results[] = array(SO_RESULT_ERROR, __('Blog is hidden from search engines', 'seo-optimization'),
					__('WordPress is configured to block search engines. This will nullify your site&#8217;s SEO and should be resolved immediately.', 'seo-optimization'), 'options-privacy.php');
			
			switch (sowp::permalink_mode()) {
				case SOWP_QUERY_PERMALINKS:
					$this->results[] = array(SO_RESULT_ERROR, __('Query-string permalinks enabled', 'seo-optimization'),
						__('It is highly recommended that you use a non-default and non-numeric permalink structure.', 'seo-optimization'), 'options-permalink.php');
					break;
					
				case SOWP_INDEX_PERMALINKS:
					$this->results[] = array(SO_RESULT_WARNING, __('Pathinfo permalinks enabled', 'seo-optimization'), 
						__('Pathinfo permalinks add a keyword-less &#8220;index.php&#8221; prefix. This is not ideal, but it may be beyond your control (since it&#8217;s likely caused by your site&#8217;s web hosting setup).', 'seo-optimization'), 'options-permalink.php');
					
				case SOWP_PRETTY_PERMALINKS:
					
					if (strpos(get_option('permalink_structure'), '%postname%') !== false)
						$this->results[] = array(SO_RESULT_OK, __('Permalinks include the post slug', 'seo-optimization'),
							__('Including a version of the post&#8217;s title helps provide keyword-rich URLs.', 'seo-optimization'));
					else
						$this->results[] = array(SO_RESULT_ERROR, __('Permalinks do not include the post slug', 'seo-optimization'),
							__('It is highly recommended that you include the %postname% variable in the permalink structure.', 'seo-optimization'), 'options-permalink.php');
					
					break;
			}
		}
	}
	
	function admin_page_contents() {
		
		echo "\n<p>";
		_e("Settings Monitor analyzes your blog&#8217;s settings and notifies you of any problems. If any issues are found, they will show up in red or yellow below.", 'seo-optimization');
		echo "</p>\n";
		
		echo "<table class='report'>\n";
		
		$first = true;
		foreach ($this->results as $data) {
			
			$result = $data[0];
			$title  = $data[1];
			$desc   = $data[2];
			$url    = isset($data[3]) ? $data[3] : false;
			$action = isset($data[4]) ? $data[4] : __('Go to setting &raquo;', 'seo-optimization');
			
			switch ($result) {
				case SO_RESULT_OK: $class='success'; break;
				case SO_RESULT_ERROR: $class='error'; break;
				default: $class='warning'; break;
			}
			
			if ($result == SO_RESULT_OK || !$url)
				$link='';
			else {
				if (substr($url, 0, 7) == 'http://') $target = " target='_blank'"; else $target='';
				$link = "<a href='$url'$target>$action</a>";
			}
			
			if ($first) { $firstclass = " class='first'"; $first = false; } else $firstclass='';
			echo "\t<tr$firstclass>\n\t\t<td><div class='so-$class'><strong>$title</strong></div><div>$desc</div><div>$link</div></td>\n\t</tr>\n";
		}
		
		echo "</table>\n\n";
	}
}

}
?>