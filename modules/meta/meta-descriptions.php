<?php
/**
 * Meta Description Editor Module
 * 
 * @since 4.0
 */

if (class_exists('SO_Module')) {

class SO_MetaDescriptions extends SO_Module {
	
	function get_module_title() { return __('Meta Description Editor', 'seo-optimization'); }
	function get_menu_title()   { return __('Meta Descriptions', 'seo-optimization'); }
	function get_settings_key() { return 'meta'; }
	
	function init() {
		add_action('so_head', array(&$this, 'head_tag_output'));
		add_filter('so_postmeta_help', array(&$this, 'postmeta_help'), 20);
	}
	
	function get_admin_page_tabs() {
		return array_merge(
			  array(
				  array('title' => __('Default Formats', 'seo-optimization'), 'id' => 'so-default-formats', 'callback' => 'formats_tab')
				, array('title' => __('Blog Homepage', 'seo-optimization'), 'id' => 'so-blog-homepage', 'callback' => 'home_tab')
				)
			, $this->get_meta_edit_tabs(array(
				  'type' => 'textarea'
				, 'name' => 'description'
				, 'term_settings_key' => 'taxonomy_descriptions'
				, 'label' => __('Meta Description', 'seo-optimization')
			))
		);
	}
	
	function get_default_settings() {
		return array(
			  'home_description_tagline_default' => true
			, 'description_posttype_post' => '{excerpt}'
			, 'description_posttype_page' => ''
			, 'description_taxonomy_category' => '{description}'
			, 'description_taxonomy_post_tag' => '{description}'
			, 'description_paged' => '{meta_description} - Page {num}'
		);
	}
	
	function formats_tab() {
		$this->admin_form_table_start();
		$this->textboxes(array(
			  'description_posttype_post' => __('Post Description Format', 'seo-optimization')
			, 'description_posttype_page' => __('Page Description Format', 'seo-optimization')
			, 'description_taxonomy_category' => __('Category Description Format', 'seo-optimization')
			, 'description_taxonomy_post_tag' => __('Post Tag Description Format', 'seo-optimization')
			, 'description_paged' => __('Pagination Description Format', 'seo-optimization')
		), $this->get_default_settings());
		
		$this->admin_form_table_end();
	}
	
	function home_tab() {
		$this->admin_form_table_start();
		$this->textarea('home_description', __('Blog Homepage Meta Description', 'seo-optimization'), 3);
		$this->checkboxes(array(
				  'home_description_tagline_default' => __('Use this blog&#8217s tagline as the default homepage description.', 'seo-optimization')
			), __('Default Value', 'seo-optimization'));
		$this->admin_form_table_end();
	}
	
	function head_tag_output() {
		
		global $post;
		
		$desc = false;
		
		//If we're viewing the homepage, look for homepage meta data.
		if (is_home()) {
			$desc = $this->get_setting('home_description');
			if (!$desc && $this->get_setting('home_description_tagline_default')) $desc = get_bloginfo('description');
		
		//If we're viewing a post or page, look for its meta data.
		} elseif (is_singular()) {
			$desc = $this->get_postmeta('description');
			
			if (!trim($desc) && !post_password_required() && $format = $this->get_setting('description_posttype_'.get_post_type())) {
				
				$auto_excerpt = $post->post_content;
				$auto_excerpt = strip_shortcodes($auto_excerpt);
				$auto_excerpt = str_replace(']]>', ']]&gt;', $auto_excerpt);
				$auto_excerpt = strip_tags($auto_excerpt);
				$auto_excerpt = sostr::truncate($auto_excerpt, 150, '', true);
				
				$desc = str_replace(
					  array('{excerpt::autogen}', '{excerpt}')
					, array($auto_excerpt, strip_tags($post->post_excerpt))
					, $format);
			}
			
		//If we're viewing a term, look for its meta data.
		} elseif (sowp::is_tax()) {
			global $wp_query;
			$tax_descriptions = $this->get_setting('taxonomy_descriptions');
			$term_id  = $wp_query->get_queried_object_id();
			$term_obj = $wp_query->get_queried_object();
			$desc = isset($tax_descriptions[$term_id]) ? $tax_descriptions[$term_id] : '';
			
			if (!trim($desc) && $format = $this->get_setting('description_taxonomy_'.$term_obj->taxonomy)) {
				
				$desc = str_replace(
					  array('{description}')
					, array($term_obj->description)
					, $format);
			}
		}
		
		//Do we have a description? If so, output it.
		if ($desc) {
			$desc = $this->get_desc_paged($desc);
			$desc = so_esc_attr($desc);
			echo "\t<meta name=\"description\" content=\"$desc\" />\n";
		}
	}
	
	function get_desc_paged($desc) {
		
		global $wp_query, $numpages;
		
		if (is_paged() || get_query_var('page')) {
			
			if (is_paged()) {
				$num = absint(get_query_var('paged'));
				$max = absint($wp_query->max_num_pages);
			} else {
				$num = absint(get_query_var('page'));
				
				if (is_singular()) {
					$post = $wp_query->get_queried_object();
					$max = count(explode('<!--nextpage-->', $post->post_content));
				} else
					$max = '';
			}
			
			return str_replace(
				array('{meta_description}', '{num}', '{max}'),
				array( $desc, $num, $max ),
				$this->get_setting('description_paged'));
		} else
			return $desc;
	}
	
	function postmeta_fields($fields) {
		$id = "_so_description";
		$value = so_esc_attr($this->get_postmeta('description'));
		$fields['20|description'] =
			  "<tr class='textarea' valign='top'>\n<th scope='row'><label for='$id'>".__('Meta Description:', 'seo-optimization')."</label></th>\n"
			. "<td><textarea name='$id' id='$id' class='regular-text' cols='60' rows='3' tabindex='2'"
			. " onkeyup=\"javascript:document.getElementById('so_meta_description_charcount').innerHTML = document.getElementById('_so_description').value.length\">$value</textarea>"
			. "<br />".sprintf(__('You&#8217;ve entered %s characters. Most search engines use up to 140.', 'seo-optimization'), "<strong id='so_meta_description_charcount'>".strlen($value)."</strong>")
			. "</td>\n</tr>\n"
		;
		
		return $fields;
	}
	
	function postmeta_help($help) {
		$help[] = __('<strong>Description</strong> &mdash; The value of the meta description tag. The description will often appear underneath the title in search engine results. Writing an accurate, attention-grabbing description for every post is important to ensuring a good search results clickthrough rate.', 'seo-optimization');
		return $help;
	}
	
	function add_help_tabs($screen) {
		
		$screen->add_help_tab(array(
			  'id' => 'so-meta-descriptions-overview'
			, 'title' => __('Overview', 'seo-optimization')
			, 'content' => __("
<ul>
	<li><strong>What it does:</strong> Meta Descriptions Editor lets you customize the text that you want to appear under your webpages&#8217; titles in search results.</li>
	<li><strong>Why it helps:</strong> Getting ranked isn&#8217;t enough; once you're ranked, you need visitors to click on your site in the results. That&#8217;s where meta descriptions can help. When you provide text that makes searchers want to visit your site, you can increase your SERP clickthrough rate and thus increase search traffic.</li>
	<li><strong>How to use it:</strong> Enter meta descriptions for your homepage, posts, pages, etc. as desired, and then click Save Changes. You can also customize the meta data of an individual post or page by using the textboxes that Meta Editor adds to the post/page editors.</li>
</ul>
", 'seo-optimization')));

		$screen->add_help_tab(array(
			  'id' => 'so-meta-descriptions-settings'
			, 'title' => __('Settings Help', 'seo-optimization')
			, 'content' => __("
<p>Here&#8217;s information on the various settings:</p>

<ul>
	<li><strong>Blog Homepage Meta Description</strong> &mdash; When your blog homepage appears in search results, it&#8217;ll have a title and a description. When you insert content into the description field below, the Meta Editor will add code to your blog homepage (the <code>&lt;meta name=&quot;description&quot; /&gt;</code> tag) that asks search engines to use what you&#8217;ve entered as the homepage&#8217;s search results description.</li>
	<li><strong>Use this blog&#8217;s tagline as the default homepage description.</strong> &mdash; If this box is checked and if the Blog Homepage Meta Description field is empty, Meta Editor will use your blog&#8217;s tagline as the meta description. You can edit the blog&#8217;s tagline under <a href='options-general.php'>Settings &rArr; General</a>.</li>
</ul>
", 'seo-optimization')));
		
		$screen->add_help_tab(array(
			  'id' => 'so-meta-descriptions-faq'
			, 'title' => __('FAQ', 'seo-optimization')
			, 'content' => __("
<ul>
	<li><strong>How do I edit the meta description of my homepage?</strong><br />If you are using a &#8220;blog homepage&#8221; (the default option of showing your blog posts on your homepage), just use the Blog Homepage field. If you have configured your <a href='options-reading.php'>Settings &rArr; Reading</a> section to use a &#8220;frontpage&#8221; (i.e. a Page as your homepage), just edit that Page&#8217;s meta description on the &#8220;Pages&#8221; tab.</li>
</ul>
", 'seo-optimization')));
		
		$screen->add_help_tab(array(
			  'id' => 'so-meta-descriptions-troubleshooting'
			, 'title' => __('Troubleshooting', 'seo-optimization')
			, 'content' => __("
<ul>
	<li>
		<p><strong>What do I do if my site has multiple meta tags?</strong><br />First, try removing your theme&#8217;s built-in meta tags if it has them. Go to <a href='theme-editor.php' target='_blank'>Appearance &rArr; Editor</a> and edit <code>header.php</code>. Delete or comment-out any <code>&lt;meta&gt;</code> tags.</p>
		<p>If the problem persists, try disabling other SEO plugins that may be generating meta tags.</p>
		<p>Troubleshooting tip: Go to <a href='options-general.php?page=seo-optimization'>Settings &rArr; SEO Optimization</a> and enable the &#8220;Insert comments around HTML code insertions&#8221; option. This will mark SEO Optimization&#8217;s meta tags with comments, allowing you to see which meta tags are generated by SEO Optimization and which aren&#8217;t.</p>
	</li>
</ul>
", 'seo-optimization')));

	}
	
}

}
?>