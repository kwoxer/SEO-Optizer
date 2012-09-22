<?php
/**
 * Module Manager Module
 * 
 * @since 0.7
 */

if (class_exists('SO_Module')) {

class SO_Modules extends SO_Module {
	
	function get_module_title() { return __('Module Manager', 'seo-optimization'); }
	function get_menu_title() { return __('Modules', 'seo-optimization'); }
	function get_menu_pos()   { return 0; }
	function is_menu_default(){ return true; }
	
	function init() {
		
		if ($this->is_action('update')) {
			
			$psdata = (array)get_option('seo_optimization', array());
			
			foreach ($_POST as $key => $value) {
				if (substr($key, 0, 3) == 'so-') {
					$key = str_replace(array('so-', '-module-status'), '', $key);
					$value = intval($value);
					
					$psdata['modules'][$key] = $value;
				}
			}
			
			update_option('seo_optimization', $psdata);
			
			wp_redirect(add_query_arg('so-modules-updated', '1', sourl::current()), 301);
			exit;
		}
	}
	
	function admin_page_contents() {
		echo "<p>";
		_e('SEO Optimization&#8217;s features are located in groups called &#8220;modules.&#8221; By default, most of these modules are listed in the &#8220;SEO&#8221; menu on the left. Whenever you&#8217;re working with a module, you can view documentation by clicking the tabs in the upper-right-hand corner of your administration screen.', 'seo-optimization');
		echo "</p><p>";
		_e('The Module Manager lets you  disable or hide modules you don&#8217;t use. You can also silence modules from displaying bubble alerts on the menu.', 'seo-optimization');
		echo "</p>";
		
		if (!empty($_GET['so-modules-updated']))
			$this->print_message('success', __('Modules updated.', 'seo-optimization'));
		
		$this->admin_form_start(false, false);
		
		$headers = array(
			  __('Status', 'seo-optimization')
			, __('Module', 'seo-optimization')
		);
		echo <<<STR
<table class="widefat" cellspacing="0">
	<thead><tr>
		<th scope="col" class="module-status">{$headers[0]}</th>
		<th scope="col" class="module-name">{$headers[1]}</th>
	</tr></thead>
	<tbody>

STR;
		
		$statuses = array(
			  SO_MODULE_ENABLED => __('Enabled', 'seo-optimization')
			, SO_MODULE_SILENCED => __('Silenced', 'seo-optimization')
			, SO_MODULE_HIDDEN => __('Hidden', 'seo-optimization')
			, SO_MODULE_DISABLED => __('Disabled', 'seo-optimization')
		);
		
		$modules = array();
		
		foreach ($this->plugin->modules as $key => $x_module) {
			$module =& $this->plugin->modules[$key];
			
			//On some setups, get_parent_class() returns the class name in lowercase
			if (strcasecmp(get_parent_class($module), 'SO_Module') == 0 && !in_array($key, array('modules')) && $module->is_independent_module())
				$modules[$key] = $module->get_module_title();
		}
		
		foreach ($this->plugin->disabled_modules as $key => $class) {
			
			if (call_user_func(array($class, 'is_independent_module')))
				$modules[$key] = call_user_func(array($class, 'get_module_title'));
		}
		
		asort($modules);
		
		//Do we have any modules requiring the "Silenced" column? Store that boolean in $any_hmc
		$any_hmc = false;
		foreach ($modules as $key => $name) {
			if ($this->plugin->call_module_func($key, 'has_menu_count', $hmc) && $hmc) {
				$any_hmc = true;
				break;
			}
		}
		
		$psdata = (array)get_option('seo_optimization', array());
		
		foreach ($modules as $key => $name) {
			
			$currentstatus = $psdata['modules'][$key];
			
			echo "\t\t<tr>\n\t\t\t<td class='module-status' id='module-status-$key'>\n";
			echo "\t\t\t\t<input type='hidden' name='so-$key-module-status' id='so-$key-module-status' value='$currentstatus' />\n";
			
			foreach ($statuses as $statuscode => $statuslabel) {
				
				$hmc = ($this->plugin->call_module_func($key, 'has_menu_count', $_hmc) && $_hmc);
				
				$is_current = false;
				$style = '';
				switch ($statuscode) {
					case SO_MODULE_ENABLED:
						if ($currentstatus == SO_MODULE_SILENCED && !$hmc) $is_current = true;
						break;
					case SO_MODULE_SILENCED:
						if (!$any_hmc) continue 2; //break out of switch and foreach
						if (!$hmc) $style = " style='visibility: hidden;'";
						break;
					case SO_MODULE_HIDDEN:
						if ($this->plugin->call_module_func($key, 'get_menu_title', $module_menu_title) && $module_menu_title === false)
							$style = " style='visibility: hidden;'";
						break;
				}
				
				if ($is_current || $currentstatus == $statuscode) $current = ' current'; else $current = '';
				$codeclass = str_replace('-', 'n', strval($statuscode));
				echo "\t\t\t\t\t<span class='status-$codeclass'$style>";
				echo "<a href='javascript:void(0)' onclick=\"javascript:set_module_status('$key', $statuscode, this)\" class='$current'>$statuslabel</a></span>\n";
			}
			
			if (!$this->plugin->module_exists($key) || !$this->plugin->call_module_func($key, 'get_admin_url', $admin_url))
				$admin_url = false;
			
			if ($currentstatus > SO_MODULE_DISABLED && $admin_url) {
				$cellcontent = "<a href='{$admin_url}'>$name</a>";
			} else
				$cellcontent = $name;
			
			echo <<<STR
				</td>
				<td class='module-name'>
					$cellcontent
				</td>
			</tr>

STR;
		}
		
		echo "\t</tbody>\n</table>\n";
		
		$this->admin_form_end(null, false);
	}
	
	function add_help_tabs($screen) {
		
		$screen->add_help_tab(array(
			  'id' => 'so-modules-options'
			, 'title' => __('Options Help', 'seo-optimization')
			, 'content' => __("
<p>The Module Manager lets you customize the visibility and accessibility of each module; here are the options available:</p>
<ul>
	<li><strong>Enabled</strong> &mdash; The default option. The module will be fully enabled and accessible.</li>
	<li><strong>Silenced</strong> &mdash; The module will be enabled and accessible, but it won't be allowed to display numeric bubble alerts on the menu.</li>
	<li><strong>Hidden</strong> &mdash; The module's functionality will be enabled, but the module won't be visible on the SEO menu. You will still be able to access the module's admin page by clicking on its title in the Module Manager table.</li>
	<li><strong>Disabled</strong> &mdash; The module will be completely disabled and inaccessible.</li>
</ul>
", 'seo-optimization')));
		
		$screen->add_help_tab(array(
			  'id' => 'so-modules-faq'
			, 'title' => __('FAQ', 'seo-optimization')
			, 'content' => __("
<ul>
	<li><strong>What are modules?</strong><br />SEO Optimization&#8217;s features are divided into groups called &#8220;modules.&#8221; SEO Optimization&#8217;s &#8220;Module Manager&#8221; lets you enable or disable each of these groups of features. This way, you can pick-and-choose which SEO Optimization features you want.</li>
	<li><strong>Can I access a module again after I&#8217;ve hidden it?</strong><br />Yes. Just go to the Module Manager and click the module&#8217;s title to open its admin page. If you&#8217;d like to put the module back in the &#8220;SEO&#8221; menu, just re-enable the module in the Module Manager and click &#8220;Save Changes.&#8221;</li>
	<li><strong>How do I disable the number bubbles on the &#8220;SEO&#8221; menu?</strong><br />Just go to the Module Manager and select the &#8220;Silenced&#8221; option for any modules generating number bubbles. Then click &#8220;Save Changes.&#8221;</li>
</ul>
", 'seo-optimization')));
	}
}

}
?>