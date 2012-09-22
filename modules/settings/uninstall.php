<?php
/**
 * Uninstaller Module
 * 
 * @since 2.1
 */

if (class_exists('SO_Module')) {

class SO_Uninstall extends SO_Module {

	function get_parent_module() { return 'settings'; }
	function get_child_order() { return 40; }
	function is_independent_module() { return false; }
	function get_settings_key() { return $this->get_module_key(); }
	
	function get_module_title() { return __('Uninstaller', 'seo-optimization'); }
	function get_module_subtitle() { return __('Uninstall', 'seo-optimization'); }
	
	function init() {
		if ($this->is_action('so-uninstall'))
			add_filter('so_custom_admin_page-settings', array(&$this, 'do_uninstall'));
	}
	
	function admin_page_contents() {
		echo "\n<p>";
		_e('Uninstalling SEO Optimization will delete your settings and the plugin&#8217;s files.', 'seo-optimization');
		echo "</p>\n";
		$url = $this->get_nonce_url('so-uninstall');
		$confirm = __('Are you sure you want to uninstall SEO Optimization? This will permanently erase your SEO Optimization settings and cannot be undone.', 'seo-optimization');
		echo "<p><a href='$url' class='button-primary' onclick=\"javascript:return confirm('$confirm')\">".__('Uninstall Now', 'seo-optimization')."</a></p>";
	}
	
	function enable_post_uninstall_page() {
		add_submenu_page('so-hidden-modules', __('Uninstall SEO Optimization', 'seo-optimization'), 'Uninstall',
			'manage_options', 'seo-optimization', array(&$this->parent_module, 'admin_page_contents'));
	}
	
	function do_uninstall() {
		echo "<script type='text/javascript'>jQuery('#adminmenu .current').hide(); jQuery('#toplevel_page_seo').hide();</script>";
		echo "<div class=\"wrap\">\n";
		echo "\n<h2>".__('Uninstall SEO Optimization', 'seo-optimization')."</h2>\n";
		
		//Delete settings and do miscellaneous clean up
		$this->plugin->uninstall();
		$this->print_mini_message('success', __('Deleted settings.', 'seo-optimization'));
		
		//Deactivate the plugin
		deactivate_plugins(array($this->plugin->plugin_basename), true);
		
		//Attempt to delete the plugin's files and output result
		if (is_wp_error($error = delete_plugins(array($this->plugin->plugin_basename))))
			$this->print_mini_message('error', __('An error occurred while deleting files.', 'seo-optimization').'<br />'.$error->get_error_message());
		else {
			$this->print_mini_message('success', __('Deleted files.', 'seo-optimization'));
			$this->print_mini_message('success', __('Uninstallation complete. Thanks for trying SEO Optimization.', 'seo-optimization'));
		}
		
		echo "\n</div>\n";
		
		return true;
	}
}

}
?>