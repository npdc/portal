/* global Inputmask, baseUrl */

//execute when page is loaded
$().ready(function(){
	$('.collapsible').each(function(){
		$(this).on('click', function(){
			if($(this).next().hasClass('hiddenSubDiv')){
				items = $(this).next();
			} else {
				items = $(this).nextUntil('hr').not('[id$=_new]');
			}
			if($(this).hasClass('hidden')){
				items.slideDown();
				$(this).removeClass('hidden');
			} else {
				items.slideUp();
				$(this).addClass('hidden');
			}
		});
	});
	$('body').addClass('no-touch');
	//add click action when device is a touch device
	$('#menu .sub span').click(function(){
		if($(this).parent().hasClass('hover')){
			$('.hover').removeClass('hover');
		} else {
			$('.hover').removeClass('hover');
			$(this).parent().addClass('hover');
		}
		event.stopPropagation();
	});
	$('#page').on('click',function(){
		$('.hover').removeClass('hover');
	});
	
	//toggle for showing or hiding filters
	$('.list #left h3').click(function(){
		$('.list #left').toggleClass('visible');
	});
	
	//toggle for showing or hiding contact form
	$('.single-col #right h3').click(function(){
		$('#right').toggleClass('visible');
	});
	
	//toggle for showing or hiding menu
	$('#menu h4').click(function(){
		$('#menu').toggleClass('hover');
		$('#menu .sub.hover').removeClass('hover');
		event.stopPropagation();
	});
	
	//paginate tables
	$('body.list table').each(function() {
		var numPerPage = 25;//= $('body').hasClass('list') ? 25 : 10;
		var minRows = 5;//only show number of rows when at least this number
		
		var currentPage = (window.location.hash.substr(1) || 1)-1;
		var $table = $(this);
		var numRows = $table.find('tbody tr').length;
		$table.bind('search', function(){
			clearTimeout($table.timer);
			$table.timer = setTimeout(function(){$table.trigger('doSearch');},250);
		});
		$table.bind('doSearch', function(){
			$('.odd').removeClass('odd');
			$('.even').removeClass('even');
			var s = $(this).prev('input').val().toUpperCase();
			var n = 0;
			if(s.length > 0){
				$('.pager').hide();
				$table.find('tbody tr').hide();
				$table.find('tbody tr').each(function(){
					if($(this).html().toUpperCase().indexOf(s) > -1){
						$(this).show();
						$(this).addClass(n % 2 ? 'even' : 'odd');
						n++;
					}
				});
			} else {
				$('.pager').show();
				$(this).trigger('repaginate');
			}
		});
		$table.bind('repaginate', function() {
			$('.pager .numbers .page-number').show();
			$('.numberdots').remove();
			$table
					.find('tbody tr').hide()
					.slice(currentPage * numPerPage, (currentPage + 1) * numPerPage).show();
			$('.counter1').html(currentPage * numPerPage + 1);
			var max = (currentPage + 1) * numPerPage;
			$('.counter2').html(max > numRows ? numRows : max);
			$('span.page-number').removeClass('active');
			$('span.page-number:nth-child('+(currentPage + 1)+')').addClass('active');
			location.hash = currentPage+1;
			var l = $('.pager .numbers').children('.page-number').length/2;
			var ba = 3;
			if(l > 10) {
				if(currentPage > ba+1){
					$('.pager .numbers').each(function(){
						$(this).children('.page-number').slice(1, currentPage-ba).hide();
						$(this).children('.page-number:first-child()').after('<span class="numberdots">...</span>');
					});
				}
				if(currentPage < l-ba-3){
					$('.pager .numbers').each(function(){
						$(this).children('.page-number').slice(currentPage+ba+1, l-1).hide();
						$(this).children('.page-number:last-child').before('<span class="numberdots">...</span>');
					});
				}
			}
		});
		var $topPager = $('<div class="pager"></div>');
		if(numRows > numPerPage){
			var $pager = $('<span class="numbers">Page: </span>');
			var numPages = Math.ceil(numRows / numPerPage);
			for (var page = 0; page < numPages; page++) {
				$('<span class="page-number"></span>').text(page + 1).appendTo($pager);
			}
			$('<span class="hint">Showing result <span class="counter1">'+(1+currentPage*numPerPage)+'</span> to <span class="counter2">'+((currentPage+1)*numPerPage)+'</span> of '+numRows+'</span>').appendTo($topPager);
			$pager.appendTo($topPager);
			$pager.find('span.page-number:nth-child('+(currentPage+1)+')').addClass('active');
		} else if(numRows >= minRows) {
			$('<span class="hint">Showing all '+numRows+' results</span>').appendTo($topPager);
		}
		$topPager.insertBefore($table);
		$topPager.clone().insertAfter($table);
		$('span.page-number').click(function(){
			currentPage = $(this).text()-1;
			$table.trigger('repaginate');
		});
		if($table.hasClass('searchbox')){
			$topPager.after('<input type="text" placeholder="Type to search" onKeyup="$(this).next(\'table\').trigger(\'search\')" />');
		}
		$(window).on('hashchange', function(){
			currentPage = (window.location.hash.substr(1) || 1)-1;
			$table.trigger('repaginate');
		});
		$table.trigger('repaginate');
	});
	$("a[href*='://']").attr("target","_blank");
	$('input[type=reset]').click(function(){
		return confirm('Are you sure you want to reset the form?');
	});
	
	$('#left select[multiple]').each(function(){
		$(this).hide();
		$(this).wrap('<div></div>');
		var fieldName = $(this).attr('name').substring(0,$(this).attr('name').length-2);
		$(this).after('<div id="select_'+fieldName+'" class="select"></div>');
		$(this).children('option').each(function(){
			var option = '<div data-value="'+$(this).val()+'" '+($(this).is('[selected]') ? 'class="selected"' : '')+'>'
					+$(this).text()
					+'</div>';
			$('#select_'+fieldName).append(option);
		});
		$('#select_'+fieldName+' div').click(function(){
			var option = $(this).parent().siblings('select').children('[value='+$(this).attr('data-value')+']');
			if(option.prop('selected')){
				option.prop('selected', false);
				$(this).removeClass('selected');
			} else {
				option.prop('selected', true);
				$(this).addClass('selected');
			}
			$(this).parent().parent().prev().children('.count').text('('+$(this).parent().find('.selected').length+' selected)').css('font-style', 'italic');
		});
		$(this).parent().prev().append(' <span class="count">('+$(this).find('[selected]').length+' selected)</span>');
	});
	
	//toggle for showing or hiding full debug log
	$('.debug.bottom').click(function(){
		$('.debug.bottom').toggleClass('expanded');
	});
	
	Inputmask.extendDefinitions({
		"X": {
			validator: "[0-9xX]",
            cardinality: 1,
            casing: "upper"
		}
	});
	Inputmask.extendAliases({
		"yyyy[-mm[-dd]]": {
            mask: "y[-1[-d]]",
            placeholder: "yyyy-mm-dd",
            leapday: "-02-29",
            separator: "-",
            alias: "yyyy/mm/dd"
        },
		"orcid": {
			mask: "9999-9999-9999-999X"
		}
	});
	$('input[data-inputmask]').inputmask();
	
	$('input.error:first').focus();
	
	if($('body').hasClass('nomenu')){
		//window.top.location === window.location && (window.top.location = baseUrl+'/');
	} else {
		window.top.location !== window.location && (window.top.location = window.location);
	}
//	$('div.select').niceScroll({
//		cursorcolor: '#3b6daa',
//		cursoropacitymin: 0.5,
//		boxzoom: true
//	});
});

function getParameterByName(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function openOverlay(url){
	$('a, button, input').each(function(){
		if($(this).is('[tabindex]')){
			$(this).attr('data-tabindex', $(this).attr('tabindex'));
		}
		$(this).attr('tabindex', '-1');
	});
	$('iframe').contents().find('a, button, input').each(function(){
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

function toggleRelated(visible){
	if(visible){
		$('.parent, .child, .noRelated').hide();
		$('.related').css('display', 'inline-block');
	} else {

		$('.parent, .child, .noRelated').show();
		$('.related').css('display', 'none');
	}
}