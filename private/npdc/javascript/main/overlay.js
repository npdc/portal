function openOverlay(url){
	$('a, button, input').each(function(){
		if($(this).is('[tabindex]')){
			$(this).attr('data-tabindex', $(this).attr('tabindex'));
		}
		$(this).attr('tabindex', '-1');
	});
	$("iframe:not([src^='http'])").contents().find('a, button, input').each(function(){
		if($(this).is('[tabindex]')){
			$(this).attr('data-tabindex', $(this).attr('tabindex'));
		}
		$(this).attr('tabindex', '-1');
	});
	if(getParameterByName('referer') === null){
		var u = encodeURIComponent(window.location);
	} else {
		var u = getParameterByName('referer');
	}
	$('#overlay .inner').html('<iframe src="'+url+(url.indexOf('?') === -1 ? '?' : '&')+'u='+u+'"></iframe>');
	$('#overlay').fadeIn(250);
}

function closeOverlay(){
	if($('#overlay iframe').contents().find('body').hasClass('user')){
		window.location = window.location.href
			.replace(/#[a-zA-Z0-9]*$/, '') //remove anchor (otherwise nothing will happen)
			.replace(/overlay=[a-zA-Z0-9]*[&]?/g, '') //remove overlay trigger (to prevent overlay from showing up again)
			.replace(/&$/,''); //remove trailing ampersand (just to make urls look better)
	} else {
		$('#overlay').fadeOut(250);
	}
}

function openUrl(url){
	parent.window.location.href = url;
}