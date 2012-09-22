function so_reset_textbox(id, d, m, e) {
	if (confirm(m+"\n\n"+d)) {
		document.getElementById(id).value=d;
		e.className='hidden';
		so_enable_unload_confirm();
	}
}

function so_textbox_value_changed(e, d, l) {
	if (e.value==d)
		document.getElementById(l).className='hidden';
	else
		document.getElementById(l).className='';
}

function so_toggle_blind(id) {
	if (document.getElementById(id)) {
		if (document.getElementById(id).style.display=='none')
			Effect.BlindDown(id);
		else
			Effect.BlindUp(id);
	}
	
	return false;
}

function so_enable_unload_confirm() {
	window.onbeforeunload = so_confirm_unload_message;
}

function so_disable_unload_confirm() {
	window.onbeforeunload = null;
}

function so_confirm_unload_message() {
	return soModulesModulesL10n.unloadConfirmMessage;
}

jQuery(document).ready(function() {
	jQuery('input, textarea, select', 'div.so-module').change(so_enable_unload_confirm);
	jQuery('form#so-admin-form').submit(so_disable_unload_confirm);
});