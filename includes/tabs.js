jQuery(function() 
{
	so_init_tabs();		
 });

function so_init_tabs()
{
	/* if this is not the SEO Optimization admin page, quit */
	if (!jQuery("#so-tabset").length) return;		
	
	/* init markup for tabs */
	jQuery('#so-tabset').prepend("<ul><\/ul>");
	jQuery('#so-tabset > fieldset').each(function(i)
	{
		id      = jQuery(this).attr('id');
		caption = jQuery(this).find('h3').text();
		jQuery('#so-tabset > ul').append('<li><a href="#'+id+'"><span>'+caption+"<\/span><\/a><\/li>");
		jQuery(this).find('h3').hide();					    
	});
	
	/* init the tabs plugin */
	var jquiver = undefined == jQuery.ui ? [0,0,0] : undefined == jQuery.ui.version ? [0,1,0] : jQuery.ui.version.split('.');
	switch(true) {
		// tabs plugin has been fixed to work on the parent element again.
		case jquiver[0] >= 1 && jquiver[1] >= 7:
			jQuery("#so-tabset").tabs();
			break;
		// tabs plugin has bug and needs to work on ul directly.
		default:
			jQuery("#so-tabset > ul").tabs(); 
	}
	
	if (location.hash.length) {
		jQuery(document).ready(function() {
			so_hash_form(location.hash);
		});
		window.scrollTo(0,0);
	}
	
	/* handler for opening the last tab after submit (compability version) */
	jQuery('#so-tabset ul a').click(function(i){
		so_hash_form(jQuery(this).attr('href'));
	});
}

function so_hash_form(hash) {
	var form   = jQuery('#so-admin-form');
	var action = form.attr("action").split('#', 1) + hash;
	// an older bug pops up with some jQuery version(s), which makes it
	// necessary to set the form's action attribute by standard javascript 
	// node access:						
	form.get(0).setAttribute("action", action);
}