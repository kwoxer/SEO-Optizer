<?php
/**
 * More Link Customizer Module
 * 
 * @since 1.3
 */

if (class_exists('SO_Module')) {

class SO_Density extends SO_Module {
	
	function get_module_title() { return __('Observer Keyword Density', 'seo-optimization'); }
	
	function get_parent_module() { return 'misc'; }
	function admin_page_contents() {
		$this->child_admin_form_start();
		echo '<div style="margin-left&#58;10px;">The Observer Keyword Density is showing </div>';
		$this->child_admin_form_end();
	}
	
	function postmeta_fields($fields) {
		$jcode="<script type='text/javascript'>
					function Density_calculate() {
						var words = '';
						words = Density_getContent(); 
						words = Density_getReplacedWords(words);
						words = words.replace(/(<([^>]+)>)/ig,'');	
						var keywordCount = Density_countKeyword(words);
						words = Density_getCountedWords(words);	
						document.getElementById('so_observer_density').innerHTML = Density_getResult(words,keywordCount) ;
					}
					function Density_getCountedWords(words){
						return words.split(' ').length;
					} 
					function Density_countKeyword(words){
						var help = words.toLowerCase();
						return help.split(Density_getKeyword()).length-1;
					} 
					function Density_getResult(words,keywordCount){
						return ((keywordCount * 100) / words).toFixed(2);
					} 
					function Density_getKeyword(){
						return document.getElementById('Density_textfield').value.toLowerCase();
					} 
					function Density_getContent(){
						return document.getElementById('content').innerHTML;
					} 
					function Density_getReplacedWords(words){
						return words.replace(/&lt;/ig,'<').replace(/&gt;/ig,'>').replace(/&nbsp;/ig,' ').replace(/nbsp;/ig,' ').replace(/&amp;/ig,' ');
					} 
				</script>";
		$id = "Density_textfield";
		$fields['60|density'] = "<tr class='textarea' valign='top'>\n<th scope='row'><label for='$id'>".__('Keyword Density:', 'seo-optimization')."</label></th>\n"
			. "<td>
            <input name='$id' id='$id' value='write in your keyword' onkeyup=\"Density_calculate()\" type='text'><br/>"
            .  
			sprintf(__('Your keyword density is %s&#037; (3-5&#037; stands for the best value here.)', 'seo-optimization'), "<strong id='so_observer_density'>"."..."."</strong>".$jcode);
		return $fields;
	}
}
}
?>