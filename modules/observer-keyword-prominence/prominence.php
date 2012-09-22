<?php
/**
 * More Link Customizer Module
 * 
 * @since 1.3
 */

if (class_exists('SO_Module')) {

class SO_Prominence extends SO_Module {
	
	function get_module_title() { return __('Observer Keyword Prominence', 'seo-optimization'); }
	
	function get_parent_module() { return 'misc'; }
	function admin_page_contents() {
		$this->child_admin_form_start();
		echo '<div style="margin-left&#58;10px;">The Observer Keyword Prominence is showing </div>';
		$this->child_admin_form_end();
	}
	
	function postmeta_fields($fields) {
		$jcode="<script type='text/javascript'>
					function Prominence_calculate() {
						var wordString = Prominence_getContent(); 
						wordString = Prominence_getReplacedWords(wordString);
						wordString = wordString.replace(/(<([^>]+)>)/ig,'');
						wordString = wordString.toLowerCase();
						var keywordPositionLetter = Prominence_getKeywordPositionLetter(wordString);
						if (keywordPositionLetter!=-1){
							var keywordPositionWord = Prominence_getKeywordPositionWord(wordString,keywordPositionLetter);
							var countedWords = Prominence_getCountedWords(wordString);
							document.getElementById('so_observer_prominence').innerHTML = Prominence_getResult(countedWords,keywordPositionWord);
						}else{
							document.getElementById('so_observer_prominence').innerHTML = 'Not found!';
						}
					}
					function Prominence_getContent(){
						return document.getElementById('content').innerHTML;
					} 
					function Prominence_getReplacedWords(words){
						return words.replace(/&lt;/ig,'<').replace(/&gt;/ig,'>').replace(/&nbsp;/ig,' ').replace(/nbsp;/ig,' ').replace(/&amp;/ig,' ');
					} 
					function Prominence_getCountedWords(words){
						return words.split(' ').length;
					} 
					function Prominence_getKeywordPositionLetter(words){
						var pos=words.indexOf(Prominence_getKeyword())
						if (pos!=-1){
							pos=Prominence_watchForEndingWord(words,pos);
						}
						return pos;
					} 
					function Prominence_watchForEndingWord(words,pos){
						var counter = pos;
						while (words.substring(counter,1+counter) != ' '){
							counter++;
							if (words.length < counter){
								break;
							}
						}
						return counter-1;
					} 
					function Prominence_getKeywordPositionWord(words,keywordPositionLetter){
						words=words.substring(0,keywordPositionLetter);
						words=Prominence_getCountedWords(words);
						return words;
					} 
					function Prominence_getResult(countedWords,keywordPositionWord){
						return 100-((keywordPositionWord-1)/countedWords*100).toFixed(2);
					} 
					function Prominence_getKeyword(){
						return document.getElementById('Prominence_textfield').value.toLowerCase();
					} 
				</script>";
		$id = "Prominence_textfield";
		$fields['61|prominence'] = "<tr class='textarea' valign='top'>\n<th scope='row'><label for='$id'>".__('Keyword Prominence:', 'seo-optimization')."</label></th>\n"
			. "<td>
            <input name='$id' id='$id' value='write in your keyword' onkeyup=\"Prominence_calculate()\" type='text'><br/>"
            .  
			sprintf(__('Your keyword prominence is %s&#037; (100&#037; stands for the best value here.)', 'seo-optimization'), "<strong id='so_observer_prominence'>"."..."."</strong>".$jcode);
		return $fields;
	}
}
}
?>