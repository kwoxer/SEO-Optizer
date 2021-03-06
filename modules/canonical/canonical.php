<?php
/**
 * Canonicalizer Module
 * 
 * @since 0.3
 */

if (class_exists('SO_Module')) {

class SO_Canonical extends SO_Module {

	function get_module_title() { return __('Canonicalizer', 'seo-optimization'); }
	
	function get_parent_module() { return 'misc'; }
	function get_settings_key() { return 'canonical'; }
	
	function init() {
		//If the canonical tags are enabled, then...
		if ($this->get_setting('link_rel_canonical')) {
			
			//...remove WordPress's default canonical tags (since they only handle posts/pages/attachments)
			remove_action('wp_head', 'rel_canonical');
			
			//...and add our custom canonical tags.
			add_action('so_head', array(&$this, 'link_rel_canonical_tag'));
		}
		
		if ($this->get_setting('http_link_rel_canonical'))
			add_action('template_redirect', array(&$this, 'http_link_rel_canonical'), 11, 0);
		
		//Should we remove nonexistent pagination?
		if ($this->get_setting('remove_nonexistent_pagination'))
			add_action('template_redirect', array(&$this, 'remove_nonexistent_pagination'), 11);
	}
	
	function admin_page_contents() {
		$this->child_admin_form_start();
		$this->checkboxes(array(
				  'link_rel_canonical' => __('Generate <code>&lt;link rel=&quot;canonical&quot; /&gt;</code> meta tags.', 'seo-optimization')
				, 'http_link_rel_canonical' => __('Send <code>rel=&quot;canonical&quot;</code> HTTP headers.', 'seo-optimization')
				, 'remove_nonexistent_pagination' => __('Redirect requests for nonexistent pagination.', 'seo-optimization')
			));
		
		$this->child_admin_form_end();
	}
	
	function link_rel_canonical_tag() {
		//Display the canonical tag if a canonical URL is available
		if ($url = $this->get_canonical_url()) {
			$url = so_esc_attr($url);
			echo "\t<link rel=\"canonical\" href=\"$url\" />\n";
		}
	}
	
	function http_link_rel_canonical() {
		if (headers_sent())
			return;
		
		if ($url = $this->get_canonical_url()) {
			$url = so_esc_attr($url);
			header("Link: <$url>; rel=\"canonical\"", false);
		}
	}
	
	/**
	 * Returns the canonical URL to put in the link-rel-canonical tag.
	 * 
	 * This function is modified from the GPL-licensed {@link http://wordpress.org/extend/plugins/canonical/ Canonical URLs} plugin,
	 * which in turn was heavily based on the {@link http://svn.fucoder.com/fucoder/permalink-redirect/ Permalink Redirect} plugin.
	 */
	function get_canonical_url() {
		global $wp_query, $wp_rewrite;
		
		//404s and search results don't have canonical URLs
		if ($wp_query->is_404 || $wp_query->is_search) return false;
		
		//Are there posts in the current Loop?
		$haspost = count($wp_query->posts) > 0;
		
		//Handling special case with '?m=yyyymmddHHMMSS'.
		if (get_query_var('m')) {
			$m = preg_replace('/[^0-9]/', '', get_query_var('m'));
			switch (strlen($m)) {
				case 4: // Yearly
					$link = get_year_link($m);
					break;
				case 6: // Monthly
					$link = get_month_link(substr($m, 0, 4), substr($m, 4, 2));
					break;
				case 8: // Daily
					$link = get_day_link(substr($m, 0, 4), substr($m, 4, 2),
										 substr($m, 6, 2));
					break;
				default:
					//Since there is no code for producing canonical archive links for is_time, we will give up and not try to produce a link.
					return false;
			}
		
		//Posts and pages
		} elseif (($wp_query->is_single || $wp_query->is_page) && $haspost) {
			$post = $wp_query->posts[0];
			$link = get_permalink($post->ID);
			if (is_front_page()) $link = trailingslashit($link);
			
		//Author archives
		} elseif ($wp_query->is_author && $haspost) {
			$author = get_userdata(get_query_var('author'));
			if ($author === false) return false;
			$link = get_author_posts_url($author->ID, $author->user_nicename);
			
		//Category archives
		} elseif ($wp_query->is_category && $haspost) {
			$link = get_category_link(get_query_var('cat'));
			
		//Tag archives
		} else if ($wp_query->is_tag  && $haspost) {
			$tag = get_term_by('slug',get_query_var('tag'),'post_tag');
			if (!empty($tag->term_id)) $link = get_tag_link($tag->term_id);
		
		//Day archives
		} elseif ($wp_query->is_day && $haspost) {
			$link = get_day_link(get_query_var('year'),
								 get_query_var('monthnum'),
								 get_query_var('day'));
		
		//Month archives
		} elseif ($wp_query->is_month && $haspost) {
			$link = get_month_link(get_query_var('year'),
								   get_query_var('monthnum'));
		
		//Year archives
		} elseif ($wp_query->is_year && $haspost) {
			$link = get_year_link(get_query_var('year'));
		
		//Homepage
		} elseif ($wp_query->is_home) {
			if ((get_option('show_on_front') == 'page') && ($pageid = get_option('page_for_posts')))
				$link = trailingslashit(get_permalink($pageid));
			else
				$link = trailingslashit(get_option('home'));
			
		//Other
		} else
			return false;
		
		//Handle pagination
		$page = get_query_var('paged');
		if ($page && $page > 1) {
			if ($wp_rewrite->using_permalinks()) {
				$link = trailingslashit($link) ."page/$page";
				$link = user_trailingslashit($link, 'paged');
			} else {
				$link = add_query_arg( 'paged', $page, $link );
			}
		}
		
		//Return the canonical URL
		return $link;
	}
	
	function remove_nonexistent_pagination() {
		
		if (!is_admin()) {
			
			global $wp_rewrite, $wp_query;
			
			$url = sourl::current();
			
			if (is_singular()) {
				$num = absint(get_query_var('page'));
				$post = $wp_query->get_queried_object();
				$max = count(explode('<!--nextpage-->', $post->post_content));
				
				if ($max > 0 && ($num == 1 || ($num > 1 && $num > $max))) {
					
					if ($wp_rewrite->using_permalinks())
						wp_redirect(preg_replace('|/[0-9]{1,9}/?$|', '/', $url), 301);
					else
						wp_redirect(remove_query_arg('page', $url), 301);
				}
				
			} elseif (is_paged()) {
				$num = absint(get_query_var('paged'));
				$max = absint($wp_query->max_num_pages);
				
				if ($max > 0 && ($num == 1 || ($num > 1 && $num > $max))) {
					
					if ($wp_rewrite->using_permalinks())
						wp_redirect(preg_replace('|/page/[0-9]{1,9}/?$|', '/', $url), 301);
					else
						wp_redirect(remove_query_arg('paged', $url), 301);
				}
			}
		}
	}
	
	function add_help_tabs($screen) {
		
		$screen->add_help_tab(array(
			  'id' => 'so-canonical-overview'
			, 'title' => $this->has_enabled_parent() ? __('Canonicalizer', 'seo-optimization') : __('Overview', 'seo-optimization')
			, 'content' => __("
<ul>
	<li>
		<p><strong>What it does:</strong> Canonicalizer improves on two WordPress features to minimize possible exact-content duplication penalties. The <code>&lt;link rel=&quot;canonical&quot; /&gt;</code> tags setting improves on the canonical tags feature of WordPress 2.9 and above by encompassing much more of your site than just your posts and Pages.</p>
		<p>The nonexistent pagination redirect feature fills a gap in WordPress's built-in canonicalization functionality: for example, if a URL request is made for page 6 of a category archive, and that category doesn't have a page 6, then by default, depending on the context, WordPress will display a blank page, or it will display the content of the closest page number available, without issuing a 404 error or a 301 redirect (thus creating two or more identical webpages). This duplicate-content situation can happen when you, for example, remove many posts from a category, thus reducing the amount of pagination needed in the category's archive. The Canonicalizer's feature fixes that behavior by issuing 301 redirects to page 1 of the paginated section in question.</p>
	</li>
	<li><strong>Why it helps:</strong> These features will point Google to the correct URL for your homepage and each of your posts, Pages, categories, tags, date archives, and author archives. That way, if Google comes across an alternate URL by which one of those items can be accessed, it will be able to find the correct URL and won't penalize you for having two identical pages on your site.</li>
	<li><strong>How to use it:</strong> Just check all three checkboxes and click Save Changes. SEO Optimization will do the rest.</li>
</ul>
", 'seo-optimization')));
		
	}
}

}
?>