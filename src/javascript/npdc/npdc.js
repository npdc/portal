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
			if($(this).hasClass('collapsed')){
				items.slideDown();
				$(this).removeClass('collapsed');
			} else {
				items.slideUp();
				$(this).addClass('collapsed');
			}
		});
	});
	$('body').addClass('no-touch');
	//add click action when device is a touch device
	$('#menu .sub span').click(function(event){
		if($(this).parent().hasClass('hover')){
			$('li.hover').removeClass('hover');
		} else {
			$('li.hover').removeClass('hover');
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
	$('#menu h4').click(function(event){
		$('#menu').toggleClass('hover');
		$('#menu .sub.hover').removeClass('hover');
		event.stopPropagation();
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
	
	if($('body').hasClass('nomenu')){
		//window.top.location === window.location && (window.top.location = baseUrl+'/');
	} else {
		window.top.location !== window.location && (window.top.location = window.location);
	}

	//version chooser for editors
	$('#versionSelect').change(function(){
		window.location = $('#versionSelect').val();
	});

	$('div.overflow').each(function(){
		if($(this).height() > 150){
			$(this).addClass('overheight').append('<div class="bottombar"></div>');
		}
		
	});
	$('.bottombar').click(function(){
		$(this).parent().toggleClass('expanded');
	});
	if($('#menu').length === 1){
		var menuPos, headHeight;
		var setHeadMargin = function(event){
			if($(window).scrollTop() === 0 || event.type === 'load'){
				menuPos = $('#menu').position().top+$('#head').position().top;
				headHeight = Math.max(125, $('#head').height());
				$('#page').css('padding-top', headHeight+15+'px');
			}
		}
		var setSticky = function(event){
			if($(window).scrollTop() > menuPos){
				$('body').addClass('sticky');
			} else {
				$('body').removeClass('sticky');
				setHeadMargin(event);
			}
		}
		$(window).on('load resize', function(event){
			setHeadMargin(event);
			setSticky(event);
		});
		$(window).on('scroll touchmove', function (event){
			setSticky(event);
		});
		$('#toplink').on('click', function(){
			$("html, body").animate({ scrollTop: 0 }, 200, "swing", setSticky);
		});
	}
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

function toggleRelated(visible){
	if(visible){
		$('.parent, .child, .noRelated').hide();
		$('.related').css('display', 'inline-block');
	} else {

		$('.parent, .child, .noRelated').show();
		$('.related').css('display', 'none');
	}
}