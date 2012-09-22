<?php
/**
 * More Link Customizer Module
 * 
 * @since 1.3
 */

if (class_exists('SO_Module')) {

class SO_Headings extends SO_Module {
	
	function get_module_title() { return __('Observer Headings', 'seo-optimization'); }
	
	function get_parent_module() { return 'misc'; }
	function admin_page_contents() {
		$this->child_admin_form_start();
		echo '<div style="margin-left&#58;10px;">Observer Headings are showing </div>';
		$this->child_admin_form_end();
	}
	
	function postmeta_fields($fields) {
		$jcode="<script type='text/javascript'>
					function Headings_calculate() {
						try {
							document.getElementById('so_observer_heading_count1').innerHTML = document.getElementById('content').innerHTML.match(/h1/g).length/2;
						}catch(err){
							document.getElementById('so_observer_heading_count1').innerHTML = '-';
						}
						try {
							document.getElementById('so_observer_heading_count2').innerHTML = document.getElementById('content').innerHTML.match(/h2/g).length/2;
						}catch(err){
							document.getElementById('so_observer_heading_count2').innerHTML = '-';
						}
						try {
							document.getElementById('so_observer_heading_count3').innerHTML = document.getElementById('content').innerHTML.match(/h3/g).length/2;
						}catch(err){
							document.getElementById('so_observer_heading_count3').innerHTML = '-';
						}
						try {
							document.getElementById('so_observer_heading_count4').innerHTML = document.getElementById('content').innerHTML.match(/h4/g).length/2;
						}catch(err){
							document.getElementById('so_observer_heading_count4').innerHTML = '-';
						}
						try {
							document.getElementById('so_observer_heading_count5').innerHTML = document.getElementById('content').innerHTML.match(/h5/g).length/2;
						}catch(err){
							document.getElementById('so_observer_heading_count5').innerHTML = '-';
						}
						try {
							document.getElementById('so_observer_heading_count6').innerHTML = document.getElementById('content').innerHTML.match(/h6/g).length/2;
						}catch(err){
							document.getElementById('so_observer_heading_count6').innerHTML = '-';
						}
					}
				</script>";
		$id = "_so_observer_heading";
		$fields['62|heading'] = "<tr class='textarea' valign='top'>\n<th scope='row'><label for='$id'>".__('Headings count:', 'seo-optimization')."</label></th>\n"
			. "<td>
			<input value='Analyse for headings' onclick=\"Headings_calculate()\"
			type='button'><br/>"
            . 
			sprintf(__('You&#8217;ve entered %s Heading1, %s Heading2, %s Heading3, %s Heading4, %s Heading5 and %s Heading6  ', 'seo-optimization'), "<strong style='color:red' id='so_observer_heading_count1'>".'-'."</strong>", "<strong id='so_observer_heading_count2'>".'-'."</strong>", "<strong id='so_observer_heading_count3'>".'-'."</strong>", "<strong id='so_observer_heading_count4'>".'-'."</strong>", "<strong id='so_observer_heading_count5'>".'-'."</strong>", "<strong id='so_observer_heading_count6'>".'-'."</strong>".$jcode);
		return $fields;
	}
}
}
?>