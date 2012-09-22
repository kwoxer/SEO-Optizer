
function so_toggle_select_children(select) {
	var i=0;
	for (i=0;i<select.options.length;i++) {
		var option = select.options[i];
		var c = ".so_" + select.name.replace("_so_", "") + "_" + option.value + "_subsection";
		if (option.index == select.selectedIndex) jQuery(c).show(); else jQuery(c).hide();
	}
}