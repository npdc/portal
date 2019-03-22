
function addFieldset(id, setFocus){
	if(typeof setFocus === 'undefined'){
		setFocus = false;
	}
	clone = $('#'+id).parents('form').data(id).clone();
	var suffix = $('#'+id+'_btn').data('suffix')+1;
	if(suffix > 1){
		clone.removeClass('error');
		clone.find('span.error').remove();
	}
	clone.attr('id', id+suffix);
	if($('#'+id).data('map')){
		clone.find('[id$=_new]').each(function(){
			$(this).attr('id', $(this).attr('id')+suffix);
		});
		clone.find('[name$=_new]').each(function(){
			$(this).attr('name', $(this).attr('name')+suffix);
		});
		clone.find('[value$=_new]').each(function(){
			$(this).attr('value', $(this).attr('value')+suffix);
		});
		clone.find('[for$=_new]').each(function(){
			$(this).attr('for', $(this).attr('for')+suffix);
		});
	} else {
		clone.find('[id^='+id+']').each(function(){
			$(this).attr('id', $(this).attr('id').replace(id, id+suffix));
		});
		clone.find('[name^='+id+']').each(function(){
			$(this).attr('name', $(this).attr('name').replace(id, id+suffix));
		});
		clone.find('[name^=unit_'+id+']').each(function(){
			$(this).attr('name', $(this).attr('name').replace(id, id+suffix));
		});
		clone.find('[value^='+id+']').each(function(){
			$(this).attr('value', $(this).attr('value').replace(id, id+suffix));
		});
		clone.find('[for^='+id+']').each(function(){
			$(this).attr('for', $(this).attr('for').replace(id, id+suffix));
		});
		clone.find('.lookuptable,.multivalue').each(function(){
			$(this).data('newRowBaseId', $(this).find('[id$=_row_new]').attr('id'));
			$(this).data('clone', $('#'+$(this).data('newRowBaseId')).clone());
			$(this).data('newCount', -1);
			$(this).find('[id*=_row_new_]').each(function(){
				var c = parseInt($(this).attr('id').substring($(this).attr('id').lastIndexOf("_") + 1));
				if(c > $(this).parents('table').data('newCount')){
					$(this).parents('table').data('newCount', c);
				}
			});
			if($(this).hasClass('single')){
				var row = $(this).find('td');
				single = true;
			} else {
				var row = $(this).find('[id$=_row_new]');
			}
			if($(this).hasClass('lookuptable')){
				$(window).keydown(function(event){
					if(event.keyCode === 13 && lookup.cur > -1) {
						event.preventDefault();
						return false;
					}
				});
				initialize.lookuptable(row, $(this).hasClass('single'));
			}
		});
		clone.find('.counter').remove();
		clone.find('input[maxlength],textarea[maxlength]').each(function(){
			var id = $(this).attr('name');
			var cur = $(this).val().length;
			var max = $(this).attr('maxlength');
			var left = max-cur;
			$(this)
				.after('<span class="counter">You have <span  id="cnt'+id+'">'+left+'</span> of '+max+' characters remaining</span>')
				.keyup(function(){
					var id = $(this).attr('name');
					var cur = $(this).val().length;
					var max = $(this).attr('maxlength');
					if(cur > 0 && $(this).is('[data-permitted-chars]')){
						var pat = new RegExp('^['+$(this).attr('data-permitted-chars')+']{0,}$');
						if(!pat.test($(this).val())){
							var p = $(this).attr('data-permitted-chars');
							if(p.substr(0,1) === '^'){
								p = p.substr(1);
							} else {
								p = '^'+p;
							}
							regex = new RegExp('['+p+']', 'g');
							var newVal = $(this).val().replace(regex, '');
							$(this).val(newVal);
							cur = newVal.length;
						}
					}
					var left = max-cur;
					$('#cnt'+id).html(left);
				});
		});
	}
	
	clone.find('.fieldset').each(function(){
		$(this).on('click', function(){
			if($(this).hasClass('hidden')){
				$(this).nextUntil('hr').not('[id$=_new]').slideDown();
				$(this).removeClass('hidden');
			} else {
				$(this).addClass('hidden');
				$(this).nextUntil('hr').not('[id$=_new]').slideUp();
			}
		});
	});
	
	clone.find('input[data-inputmask]').inputmask();
	var name = clone.find('input[type!=hidden]:first').attr('name');
	$('#'+id+'_btn').before(clone);
	clone.find('fieldset[data-repeatable]').each(function(){
		createButton(this);
	});
	$('body').find('#'+id+suffix+' select:not(.no-select2):not(.select2-hidden-accessible)').each(function(){
		makeSelect2(this);
	});

	$('#'+id+'_btn').data('suffix', suffix);
	if($('#'+id).attr('data-map')){
		loadMap('#mapContainer_new'+suffix);
	}
	
	if(setFocus){
		$('input[name="'+name+'"]:first').focus();
	}
}

function removeFieldset(element){
	$(element).parent('fieldset').siblings(':visible').fadeTo(0, 0.25);
	$(element).parent('fieldset').css('box-shadow', '0 0 10px black');
	$(element).parent('fieldset').children('legend').hide();
	var legend = $(element).parent('fieldset').children('legend').text();
	if(legend.substr(-1) === '*'){
		legend = legend.substr(0,legend.length-1);
	}
	npdc.confirm('Do you really want to remove this '+legend+'?',
		'Remove '+legend,
		function(){
			form = $(element).parents('form');
			$(element).parent('fieldset').siblings(':visible').fadeTo(200, 1);
			var pId = $(element).parents('fieldset').attr('id');
			if($(element).parent('fieldset').siblings('fieldset[id^='+pId.substring(0, pId.lastIndexOf('_')+1)+']:visible').length === 0){
				addFieldset(pId.substring(0, pId.lastIndexOf('_')+1)+'new');
			}
			$(element).parent('fieldset').remove();
		},
		'Keep '+legend, 
		function (){
			$(element).parent('fieldset').siblings(':visible').fadeTo(200, 1);
			$(element).parent('fieldset').css('box-shadow', '');
			$(element).parent('fieldset').children('legend').show();
		}
	);
}

function disableFieldset(element){
	var c = 0;
	if(element.val() !== '' && element.attr('name') !== 'MAX_FILE_SIZE'){
		c++;
	}
	element.siblings(':input').each(function(){
		if($(this).val() !== '' && $(this).attr('name') !== 'MAX_FILE_SIZE'){
			c++;
		}
	});
	if(element.parent('fieldset').attr('data-max') !== undefined){
		if(c >= element.parent('fieldset').attr('data-max')){
			element.siblings(':input').addBack().each(function(){
				if($(this).val() === ''){
					$(this).prop('disabled', true);
				}
			});
		} else {
			element.siblings(':input').prop('disabled', false);
		}
	}
}