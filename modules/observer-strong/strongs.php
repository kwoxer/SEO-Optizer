<?php
/**
 * More Link Customizer Module
 * 
 * @since 1.3
 */

if (class_exists('SO_Module')) {

class SO_Strongs extends SO_Module {
	
	function get_module_title() { return __('Observer Strongs', 'seo-optimization'); }
	
	function get_parent_module() { return 'misc'; }
	function admin_page_contents() {
		$this->child_admin_form_start();
		echo '<div style="margin-left&#58;10px;">Observer Strongs are showing </div>';
		$this->child_admin_form_end();
	}
	
	function postmeta_fields($fields) {
		$id = "_so_observer_strongs";
		$fields['63|strongs'] = "<tr class='textarea' valign='top'>\n<th scope='row'><label for='$id'>".__('Strongs count:', 'seo-optimization')."</label></th>\n"
			. "<td>
			<input value='Analyse for strongs' onclick=
			\"
			javascript:document.getElementById('so_observer_strong_count').innerHTML = document.getElementById('content').innerHTML.match(/strong/g).length/2\"
			type='button'><br/>"
            . 
			sprintf(__('You&#8217;ve entered %s Strongs  ', 'seo-optimization'), "<strong id='so_observer_strong_count'>".strlen($value)."</strong>");
		return $fields;
	}
}
}
?>