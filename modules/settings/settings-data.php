<?php
/**
 * Settings Data Manager Module
 * 
 * @since 2.1
 */

if (class_exists('SO_Module')) {

class SO_SettingsData extends SO_Module {

	function get_parent_module() { return 'settings'; }
	function get_child_order() { return 20; }
	function is_independent_module() { return false; }
	
	function get_module_title() { return __('Settings Data Manager', 'seo-optimization'); }
	function get_module_subtitle() { return __('Manage Settings Data', 'seo-optimization'); }
	
	function get_admin_page_tabs() {
		return array(
			  array('title' => __('Import', 'seo-optimization'), 'id' => 'so-import', 'callback' => 'import_tab')
			, array('title' => __('Export', 'seo-optimization'), 'id' => 'so-export', 'callback' => 'export_tab')
			, array('title' => __('Reset', 'seo-optimization'),  'id' => 'so-reset',  'callback' => 'reset_tab')
		);
	}
	
	function portable_options() {
		return array('settings', 'modules');
	}
	
	function init() {
		
		if ($this->is_action('so-export')) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="SEO Optimization Settings ('.date('Y-m-d').').dat"');
			
			$export = array();
			
			$psdata = (array)get_option('seo_optimization', array());
			
			//Module statuses
			$export['modules'] = apply_filters("so_modules_export_array", $psdata['modules']);
			
			//Module settings
			$modules = array_keys($psdata['modules']);
			$module_settings = array();
			foreach($modules as $module) {
				$msdata = (array)get_option("so_$module", array());
				if ($msdata) $module_settings[$module] = $msdata;
			}
			$export['settings'] = apply_filters("so_settings_export_array", $module_settings);
			
			//Encode
			$export = base64_encode(serialize($export));
			
			//Output
			echo $export;
			die();
			
		} elseif ($this->is_action('so-import')) {
			
			if (strlen($_FILES['settingsfile']['name'])) {
				
				$file = $_FILES['settingsfile']['tmp_name'];			
				if (is_uploaded_file($file)) {
					$import = base64_decode(file_get_contents($file));
					if (is_serialized($import)) {
						$import = unserialize($import);
						
						//Module statuses
						$psdata = (array)get_option('seo_optimization', array());
						$psdata['modules'] = array_merge($psdata['modules'], $import['modules']);
						update_option('seo_optimization', $psdata);
						
						//Module settings
						foreach ($import['settings'] as $module => $module_settings) {
							$msdata = (array)get_option("seo_optimization_module_$module", array());
							$msdata = array_merge($msdata, $module_settings);
							update_option("seo_optimization_module_$module", $msdata);
						}
						
						$this->queue_message('success', __('Settings successfully imported.', 'seo-optimization'));
					} else
						$this->queue_message('error', __('The uploaded file is not in the proper format. Settings could not be imported.', 'seo-optimization'));
				} else
					$this->queue_message('error', __('The settings file could not be uploaded successfully.', 'seo-optimization'));
					
			} else
				$this->queue_message('warning', __('Settings could not be imported because no settings file was selected. Please click the &#8220;Browse&#8221; button and select a file to import.', 'seo-optimization'));
			
		} elseif ($this->is_action('so-reset')) {
			
			$psdata = (array)get_option('seo_optimization', array());
			$modules = array_keys($psdata['modules']);
			foreach ($modules as $module) delete_option("so_$module");
			unset($psdata['modules']);
			update_option('seo_optimization', $psdata);
			
			$this->load_default_settings();
			
		} elseif ($this->is_action('dj-export')) {
			header('Content-Disposition: attachment; filename="Deeplink Juggernaut Content Links ('.date('Y-m-d').').csv"');
			
			$djlinks = $this->get_setting('links', array(), 'autolinks');
			$csv_headers = array(
				  'anchor' => 'Anchor'
				, 'to_type' => 'Destination Type'
				, 'to_id' => 'Destination'
				, 'title' => 'Title'
				, 'sitewide_lpa' => 'Site Cap'
				, 'nofollow' => 'Nofollow'
				, 'target' => 'Target'
			);
			if (is_array($djlinks) && count($djlinks))
				$djlinks = soarr::key_replace($djlinks, $csv_headers, true, true);
			else
				$djlinks = array(array_fill_keys($csv_headers, ''));
			
			soio::export_csv($djlinks);
			die();
			
		} elseif ($this->is_action('dj-import')) {
			
			if (strlen($_FILES['settingsfile']['name'])) {
			
				$file = $_FILES['settingsfile']['tmp_name'];			
				if (is_uploaded_file($file)) {
					$import = soio::import_csv($file);
					if ($import === false)
						$this->queue_message('error', __('The uploaded file is not in the proper format. Links could not be imported.', 'seo-optimization'));
					else {
						$import = soarr::key_replace($import, array(
							  'Anchor' => 'anchor'
							, 'Destination Type' => 'to_type'
							, 'Destination' => 'to_id'
							, 'URL' => 'to_id'
							, 'Title' => 'title'
							, 'Site Cap' => 'sidewide_lpa'
							, 'Nofollow' => 'nofollow'
							, 'Target' => 'target'
						), true, true);
						$import = soarr::value_replace($import, array(
							  'No' => false
							, 'Yes' => true
							, 'URL' => 'url'
						), true, false);
						
						$djlinks = array();
						foreach ($import as $link) {
							
							//Validate destination type
							if ($link['to_type'] != 'url'
									&& !sostr::startswith($link['to_type'], 'posttype_')
									&& !sostr::startswith($link['to_type'], 'taxonomy_'))
								$link['to_type'] = 'url';
							
							//Validate nofollow
							if (!is_bool($link['nofollow']))
								$link['nofollow'] = false;
							
							//Validate target
							$link['target'] = ltrim($link['target'], '_');
							if (!in_array($link['target'], array('self', 'blank'))) //Only _self or _blank are supported  right now
								$link['target'] = 'self';
							
							//Add link!
							$djlinks[] = $link;
						}
						
						$this->update_setting('links', $djlinks, 'autolinks');
						
						$this->queue_message('success', __('Links successfully imported.', 'seo-optimization'));
					}	
				} else
					$this->queue_message('error', __('The CSV file could not be uploaded successfully.', 'seo-optimization'));
					
			} else
				$this->queue_message('warning', __('Links could not be imported because no CSV file was selected. Please click the &#8220;Browse&#8221; button and select a file to import.', 'seo-optimization'));
			
		}
	}
	
	function import_tab() {
		$this->print_messages();
		$hook = $this->plugin->key_to_hook($this->get_module_or_parent_key());
		
		//SEO Optimization
		$this->admin_subheader(__('Import SEO Optimization Settings File', 'seo-optimization'));
		echo "\n<p>";
		_e('You can use this form to upload and import an SEO Optimization settings file stored on your computer. (These files can be created using the Export tool.) Note that importing a file will overwrite your existing settings with those in the file.', 'seo-optimization');
		echo "</p>\n";
		echo "<form enctype='multipart/form-data' method='post' action='?page=$hook&amp;action=so-import#so-import'>\n";
		echo "\t<input name='settingsfile' type='file' /> ";
		$confirm = __('Are you sure you want to import this settings file? This will overwrite your current settings and cannot be undone.', 'seo-optimization');
		echo "<input type='submit' class='button-primary' value='".__('Import Settings File', 'seo-optimization')."' onclick=\"javascript:return confirm('$confirm')\" />\n";
		wp_nonce_field($this->get_nonce_handle('so-import'));
		echo "</form>\n";
		
		if ($this->plugin->module_exists('content-autolinks')) {
			//Deeplink Juggernaut
			$this->admin_subheader(__('Import Deeplink Juggernaut CSV File', 'seo-optimization'));
			echo "\n<p>";
			_e('You can use this form to upload and import a Deeplink Juggernaut CSV file stored on your computer. (These files can be created using the Export tool.) Note that importing a file will overwrite your existing links with those in the file.', 'seo-optimization');
			echo "</p>\n";
			echo "<form enctype='multipart/form-data' method='post' action='?page=$hook&amp;action=dj-import#so-import'>\n";
			echo "\t<input name='settingsfile' type='file' /> ";
			$confirm = __('Are you sure you want to import this CSV file? This will overwrite your current Deeplink Juggernaut links and cannot be undone.', 'seo-optimization');
			echo "<input type='submit' class='button-primary' value='".__('Import CSV File', 'seo-optimization')."' onclick=\"javascript:return confirm('$confirm')\" />\n";
			wp_nonce_field($this->get_nonce_handle('dj-import'));
			echo "</form>\n";
		}
		
		//Import from other plugins
		$importmodules = array();
		foreach ($this->plugin->modules as $key => $x_module) {
			$module =& $this->plugin->modules[$key];
			if (is_a($module, 'SO_ImportModule')) {
				$importmodules[$key] =& $module;
			}
		}
		
		if (count($importmodules)) {
			$this->admin_subheader(__('Import from Other Plugins', 'seo-optimization'));
			echo "\n<p>";
			_e('You can import settings and data from these plugins. Clicking a plugin&#8217;s name will take you to the importer page, where you can customize parameters and start the import.', 'seo-optimization');
			echo "</p>\n";
			echo "<table class='widefat'>\n";
			
			$class = '';
			foreach ($importmodules as $key => $x_module) {
				$module =& $importmodules[$key];
				$title = $module->get_op_title();
				$desc = $module->get_import_desc();
				$url = $module->get_admin_url();
				$class = ($class) ? '' : 'alternate';
				echo "\t<tr class='$class'><td><a href='$url'>$title</a></td><td>$desc</td></tr>\n";
			}
			
			echo "</table>\n";
		}
	}
	
	function export_tab() {
		//SEO Optimization
		$this->admin_subheader(__('Export SEO Optimization Settings File', 'seo-optimization'));
		echo "\n<p>";
		_e('You can use this export tool to download an SEO Optimization settings file to your computer.', 'seo-optimization');
		echo "</p>\n<p>";
		_e('A settings file includes the data of every checkbox and textbox of every installed module. It does NOT include site-specific data like logged 404s or post/page title/meta data (this data would be included in a standard database backup, however).', 'seo-optimization');
		echo "</p>\n<p>";
		$url = $this->get_nonce_url('so-export');
		echo "<a href='$url' class='button-primary'>".__('Download Settings File', 'seo-optimization')."</a>";
		echo "</p>\n";
		
		if ($this->plugin->module_exists('content-autolinks')) {
			//Deeplink Juggernaut
			$this->admin_subheader(__('Export Deeplink Juggernaut CSV File', 'seo-optimization'));
			echo "\n<p>";
			_e('You can use this export tool to download a CSV file (comma-separated values file) that contains your Deeplink Juggernaut links. Once you download this file to your computer, you can edit it using your favorite spreadsheet program. When you&#8217;re done editing, you can re-upload the file using the Import tool.', 'seo-optimization');
			echo "</p>\n<p>";
			$url = $this->get_nonce_url('dj-export');
			echo "<a href='$url' class='button-primary'>".__('Download CSV File', 'seo-optimization')."</a>";
			echo "</p>\n";
		}
	}
	
	function reset_tab() {
		if ($this->is_action('so-reset'))
			$this->print_message('success', __('All settings have been erased and defaults have been restored.', 'seo-optimization'));
		echo "\n<p>";
		_e('You can erase all your SEO Optimization settings and restore them to &#8220;factory defaults&#8221; by clicking the button below.', 'seo-optimization');
		echo "</p>\n<p>";
		$url = $this->get_nonce_url('so-reset');
		$confirm = __('Are you sure you want to erase all module settings? This cannot be undone.', 'seo-optimization');
		echo "<a href='$url#so-reset' class='button-primary' onclick=\"javascript:return confirm('$confirm')\">".__('Restore Default Settings', 'seo-optimization')."</a>";
		echo "</p>\n";
	}
}

}

?>