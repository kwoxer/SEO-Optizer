<?php
/**
 * SEO Optimization Plugin Settings Module
 * 
 * @since 0.2
 */

if (class_exists('SO_Module')) {

class SO_Settings extends SO_Module {
	
	function get_module_title() { return __('Plugin Settings', 'seo-optimization'); }
	function get_page_title() { return __('SEO Optimization Plugin Settings', 'seo-optimization'); }
	function get_menu_title() { return __('SEO Optimization', 'seo-optimization'); }
	function get_menu_parent(){ return 'options-general.php'; }	
	function admin_page_contents() { $this->children_admin_page_tabs(); }
	
	function add_help_tabs($screen) {
		
		$screen->add_help_tab(array(
			  'id' => 'so-settings-overview'
			, 'title' => __('Overview', 'seo-optimization')
			, 'content' => __("
<p>The Settings module lets you manage settings related to the SEO Optimization plugin as a whole.</p>
", 'seo-optimization')));
		
		$screen->add_help_tab(array(
			  'id' => 'so-settings-settings'
			, 'title' => __('Global Settings', 'seo-optimization')
			, 'content' => __("
<p>Here&#8217;s information on some of the settings:</p>
<ul>
	<li><strong>Enable nofollow&#8217;d attribution link</strong> &mdash; If enabled, the plugin will display an attribution link on your site.</li>
	<li><strong>Notify me about unnecessary active plugins</strong> &mdash; If enabled, SEO Optimization will add notices to your &#8220;Plugins&#8221; administration page if you have any other plugins installed whose functionality SEO Optimization replaces.</li>
	<li><strong>Insert comments around HTML code insertions</strong> &mdash; If enabled, SEO Optimization will use HTML comments to identify all code it inserts into your <code>&lt;head&gt;</code> tag. This is useful if you&#8217;re trying to figure out whether or not SEO Optimization is inserting a certain piece of header code.</li>
</ul>
", 'seo-optimization')));
	
		$screen->add_help_tab(array(
			  'id' => 'so-settings-faq'
			, 'title' => __('FAQ', 'seo-optimization')
			, 'content' => __("
<ul>
	<li>
		<p><strong>Why doesn&#8217;t the settings exporter include all my data in an export?</strong><br />The settings export/import system is designed to facilitate moving settings between sites. It is NOT a replacement for keeping your database backed up. The settings exporter doesn&#8217;t include data that is specific to your site. For example, logged 404 errors are not included because those 404 errors only apply to your site, not another site. Also, post/page titles/meta are not included because the site into which you import the file could have totally different posts/pages located under the same ID numbers.</p>
		<p>If you&#8217;re moving a site to a different server or restoring a crashed site, you should do so with database backup/restore.</p>
	</li>
</ul>
", 'seo-optimization')));
	}
}

}
?>