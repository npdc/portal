/* global ol, baseUrl */

var lookup, lookupfield, clone, duration=200, mapBaseId = 'spatial_coverage_', initialize;

//execute when page is loaded
$().ready(function(){
	npdc = {
		alert: function(text){
			$('#overlay .inner').addClass('box').html(text);
			$('#overlay .inner').append('<br/><br/><button onclick="npdc.close();">OK</button>');
			$('#overlay').fadeIn(100, function(){
				$(window).on('keyup', function(event){
					var code = event.keyCode || event.which;
					if(code === 13 || code === 27){
						npdc.close();
					}
				})
			});
		},
		/**
		 * takes any number of arguments, first argument needs to be the text of the confirmation question
		 * text: the question text
		 * [answer x [, action x]] (repeatable)
		 * in case 2 answers are provided answer 1 will be mapped to the return key and answer 2 to escape, in case of more the last answer will be mapped to escape, none to return
		 */
		confirm: function(text){
			$('#overlay .inner').addClass('box').text(text);
			$('#overlay .inner').append('<br/><br/>');
			for(i=1;i<arguments.length;i+=2){
				$('#overlay .inner').append('<button id="btn'+((i+1)/2)+'">'+arguments[i]+'</button> ');
			}
			$('#overlay .inner.box button').on('click', function(){
				npdc.close();
			});
			args = arguments
			for(i=2;i<arguments.length;i+=2){
				$('#btn'+(i/2)).on('click', arguments[i]);
			}
			$('#overlay').fadeIn(100, function(){
				$(window).on('keydown', function(event){
					event.preventDefault();
				})
				$(window).on('keyup', function(event){
					var code = event.keyCode || event.which;
					if(code === 13 && args.length <= 5){
						npdc.close();
						args[2]();
					}
					if(code === 27){
						npdc.close();
						if(args.length%2 === 1){
							args[args.length-1]();
						}
					}
				})
			});
		},
		close: function(val){
			$(window).off('keydown keyup');
			$('#overlay').fadeOut(100, function(){
				$('#overlay .inner').removeClass('box').text('');
			});
		}
	};

	if (window.navigator.userAgent.indexOf ( "MSIE " ) > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
		npdc.alert('We see you are using Internet Explorer. Due to some bugs specific to Internet Explorer we recommend using a different browser such as Edge, Google Chrome or Firefox');
	}
	
	$('td').has('input[type=checkbox]').click(function(){
		$(this).children('input').prop('checked', !$(this).children('input').prop('checked'));
	});
	$('td').has('input[type=radio]').click(function(){
		$(this).children('input').prop('checked', true);//TODO: Check right property and value
	});

	$('[data-freetext="this"]').each(function(){
		input = $(this).siblings('input').last();
		if($(input).attr('name').split('_').pop() !== 'new' && $(input).val() == 'quickadd'){
			$(this).children('input').removeAttr('readonly').removeClass('readonly');
			$(this).siblings('[data-freetext="hide"]').data('oldHtml', $(this).siblings('[data-freetext="hide"]').html()).text('');
			$(this).addClass('fuzzy-name');
			$(this).append(' <span class="icon-search"></span>');
		}
	});

	$('.fuzzy-name span').click(function(){
		var url = $(this).parents('table').attr('data-base-url')
			+'/lookup/'
			+$(this).parents('table').attr('data-lookup-url')
			+'?fuzzy&q='+$(this).siblings('input').val();
		$('[name^='+$(this).parents('table').attr('data-target-field')+']').each(function(){
			if($(this).val() !== '' && $.isNumeric($(this).val())){
				url += '&e[]='+$(this).val();
			}
		});

		fuzzyCaller = $(this).siblings('input').attr('name');

		$.ajax(url).done(function(data){
			l = data.length;
			if(l > 0){
				$('#overlay .inner').addClass('box').text('Please select an option below or click cancel');;
				$.each(data, function(i, value) {
					$('#overlay .inner').append('<button class="choice" style="width:100%" data-id="'+value[0]+'" data-name="'+value[1]+'" data-org="'+value[2]+'">'+value[1]+'</button><br/>');
				});
				$('#overlay .inner').append('<button onclick="npdc.close();">Cancel</button>');
				$('#overlay .box button.choice').on('click', function(){
					$('#overlay .box button.choice').off('click');
					var element = $('input[name='+fuzzyCaller+']')
					element.val($(this).attr('data-name')).addClass('readonly').attr('readonly', 'readonly')
						.parent().removeClass('fuzzy-name')
						.find('span').remove();
					$('input[name='+fuzzyCaller.replace(element.parents('table').attr('data-source-field'), element.parents('table').attr('data-target-field'))+']').val($(this).attr('data-id'));
					

					if(element.attr('data-onsubmit') !== undefined){
						var field = $('select[name='+fuzzyCaller.replace(element.parents('table').attr('data-source-field'), element.attr('data-onsubmit'))+']');
						var value = $(this).attr('data-org');
						if(field.attr('data-ajax-url') !== undefined){
							field.addClass('ajax-target');
							$.get(field.attr('data-ajax-url')+'/'+$(this).attr('data-org'), function(responseText){
								$('.ajax-target')
										.append($('<option>', { value : responseText[0][0] })
										.text(responseText[0][1].replace('&amp;', '&')));
								$('.ajax-target').val(value).trigger('change');
								$('.ajax-target').removeClass('ajax-target');
							});
						}
						element.parent().siblings('[data-freetext="hide"]').html(element.parent().siblings('[data-freetext="hide"]').data('oldHtml'));
						npdc.close();
					}
				})
				$('#overlay').fadeIn(100, function(){
					$(window).on('keyup', function(event){
						var code = event.keyCode || event.which;
						if(code === 13 || code === 27){
							npdc.close();
						}
					})
				});
				
			} else {
				npdc.alert('No match found');
			}
		});
	})
	
	lookup = {
		add: function(tbl){
			tbl.data('newCount', tbl.data('newCount')+1);
			suffix = '_'+tbl.data('newCount');
			tbl.find('[id$=_new]').each(function(){
				$(this).attr('id', $(this).attr('id')+suffix);
			});
			tbl.find('[name$=_new\\[\\]]').each(function(){
				$(this).attr('name', $(this).attr('name').slice(0,-2)+suffix+'[]');
			});
			tbl.find('[name$=_new]').each(function(){
				$(this).attr('name', $(this).attr('name')+suffix);
			});
			tbl.find('[for$=_new]').each(function(){
				$(this).attr('for', $(this).attr('for')+suffix);
			});
			tbl.find('#'+tbl.data('newRowBaseId')+suffix+' td:nth-child(2)').click(function(){
				lookup.delete($(this).parent().attr('id'));
			});
			clone = tbl.data('clone');
			tbl.data('clone', clone.clone());
			
			clone.insertAfter($('#'+tbl.data('newRowBaseId')+suffix));
			$('#'+tbl.data('newRowBaseId')+' select:not(.no-select2):not(.select2-hidden-accessible)').each(function(){
				makeSelect2(this);
			});
			if(tbl.hasClass('lookuptable')){
				initialize.lookuptable(clone);
			}
		},
		delete: function(id){
			var tbl = $('#'+id).parents('table');
			var input = $('#'+id).find('td:nth-of-type('+tbl.attr('data-n-label')+')').find('input');
			var form = input.parents('form');
			npdc.confirm('Do you want to delete '+input.val()+' here?',
				'Yes, delete',
				function(){
					$('#'+id).remove();
				},
				'No, keep'
			);
		},
		updateField: function(event){
			tbl = $(event).parents('table');
			if($(event).attr('value') === 'new' && $(event).parents('table').attr('data-new-url') !== undefined){
				name = $(event).text().replace(/^Add '|' with details$|'$/g, '');
				$('#overlay .inner').html('<iframe src="'+$(event).parents('table').attr('data-new-url')+'/'+name+'?ref='+controller+'"></iframe>');
				$('#overlay').show();
				$("form :input").prop('disabled', true);
				$('#overlay').data('element', tbl);
				$('#overlay').data('type', 'lookupfield');
			} else {
				if($(event).attr('value') === 'quickadd'){
					$(event).text($(event).text().replace(/^Quickly add '|' \(without details\)$/g, ''));
					$(event).attr('value', 'quickadd');
					$(event).parents('td').siblings('td[data-freetext="hide"]').text('');
				}
				this.saveOption(tbl, $(event).attr('value'), $(event).text(), $(event).attr('data-nextfield'), $(event).attr('value') == 'quickadd');
			}
		},
		saveOption: function(tbl, id, value, nextfield, keepEditable){
			if(tbl.hasClass('single')){
				$(tbl).data('lookupfield').val(value).siblings('input[type=hidden]').val(id);
				$(tbl).data('lookupfield').attr('readonly', 'readonly').addClass('readonly');
				if(!keepEditable){tbl.data('lookupfield').off('keyup keydown focus')};
			} else {
				$('[name='+tbl.attr('data-source-field')+'_new]').val(value);
				if(!keepEditable){$('[name='+tbl.attr('data-source-field')+'_new]').attr('readonly', 'readonly').addClass('readonly');}
				$('[name='+tbl.attr('data-target-field')+'_new]').val(id);
				tbl.data('lookupfield').off('keyup keydown focus');
				if($('[name='+tbl.attr('data-source-field')+'_new]').attr('data-onsubmit') !== undefined && nextfield !== undefined){
					var field = $('[name='+tbl.attr('data-source-field')+'_new]').attr('data-onsubmit');
					if($('[name='+field+'_new]').attr('data-ajax-url') !== undefined){
						$('[name='+field+'_new]').addClass('ajax-target');
						$.get($('[name='+field+'_new]').attr('data-ajax-url')+'/'+nextfield, function(responseText){
							$('.ajax-target')
									.append($('<option>', { value : responseText[0][0] })
									.text(responseText[0][1].replace('&amp;', '&')));
							$('.ajax-target').val(nextfield).trigger('change');
							$('.ajax-target').removeClass('ajax-target');
						});
					}
					$('[name='+field+'_new]').val(nextfield).trigger('change');
				}
				lookup.add(tbl);
			}
			$('#optionwrapper').remove();
			form = $(tbl).parents('form');
		},
		selectOption: function(element, skipStep){
			skipStep = typeof skipStep !== 'undefined' ? skipStep : false;
			clearTimeout(lookup.timer);
			fullString = $(element).attr('data-self');
			if($(element).hasClass('notSelected')){
				$('#optionwrapper div').removeClass('clicked').addClass('notSelected hidden');
				$('#optionwrapper :not([data-parent])').removeClass('hidden');
				$(element).removeClass('notSelected hidden');
				testElement = element;
				while($(testElement).is('[data-parent]')){
					$('[data-parent="'+$(testElement).attr('data-parent')+'"]').removeClass('hidden');
					testElement = '[data-self="'+$(testElement).attr('data-parent')+'"]';
					$(testElement).removeClass('notSelected').addClass('clicked');
				}
			} 
			
			if($(element).hasClass('clicked') || $('div[data-parent="'+fullString+'"]').length === 0 || skipStep){
				if($(element).attr('data-parent') === undefined){
					$(element).text($(element).text());
				} else {
					truncated = [];
					$(element).attr('data-parent').split('>').forEach(function(item){
						if(item.trim().length > 12){
							truncated.push(item.trim().substring(0,10)+'...');
						} else {
							truncated.push(item.trim());
						}
					});
					var text = $(element).text().trim();
					lof = text.lastIndexOf('>');
					if(lof > -1){
						text = text.substring(lof).trim();
					}
					$(element).text(truncated.join(' > ')+' '+text);
				}
				lookup.updateField(element);
			} else {
				$(element).addClass('clicked');
				$(element).siblings(':not(.clicked)').addClass('notSelected');
				$('[data-parent="'+fullString+'"]').removeClass('hidden notSelected');
				lookup.scrollOptionwrapper(true);
			}
		},
		positionOptionwrapper: function(){
			if($('#optionwrapper').length > 0){
				var boxPos = $('#optionwrapper').prev('input').offset().top;
				var scrollTop = document.documentElement.scrollTop;/*$('body').scrollTop()*/
				var windowHeight = $(window).height();
				
				if(boxPos-scrollTop > (windowHeight / 2)){
					$('#optionwrapper').css('max-height', boxPos-scrollTop).css('bottom', $('#optionwrapper').parent().height());
					$('#optionwrapper').addClass('above');
				} else {
					$('#optionwrapper').css('max-height', windowHeight-30-(boxPos-scrollTop)).css('bottom', 'auto');
					$('#optionwrapper').removeClass('above');
				}
			}
		},
		scrollOptionwrapper: function(top){
			if(lookup.cur === -1){
				return;
			}
			if(top){
				$('#option_'+lookup.cur).parent().scrollTop(
						$('#option_'+lookup.cur).parent().scrollTop()
							+ $('#option_'+lookup.cur).position().top
							- 15
					);
			} else if($('#option_'+lookup.cur).position().top < 0){
				$('#option_'+lookup.cur).parent().scrollTop(
						$('#option_'+lookup.cur).parent().scrollTop()
							+ $('#option_'+lookup.cur).position().top
							- 15
					);
			} else if ($('#option_'+lookup.cur).position().top > $('#option_'+lookup.cur).parent().height()-30){
				$('#option_'+lookup.cur).parent().scrollTop(
						$('#option_'+lookup.cur).parent().scrollTop()
							+ $('#option_'+lookup.cur).position().top
							- $('#option_'+lookup.cur).parent().height()
							+ 40
					);
			}
		}
	};
	
	initialize = {
		lookuptable: function(row, single){
			if(single){
				var lookupfield = $(row).find('input[type=text]');
			} else {
				var lookupfield = $('input[name='+row.parents('table').attr('data-source-field')+'_new]');
			}
			lookupfield.parents('table').find('.lookupwrapper').removeClass('lookupwrapper');
			lookupfield.parent().addClass('lookupwrapper');
			lookupfield.cur = null;
			lookupfield.parents('table').data('lookupfield', lookupfield);
			lookupfield.on('keydown keyup focus paste', function(event){
				var code = event.keyCode || event.which;
				if((code !== 13 && code !== 9 && event.type === 'keydown') || ((code === 13 || code === 9) && event.type === 'keyup')){
					return;
				}
				var value = lookupfield.val();
				if(value !== lookupfield.cur || (event.type === 'focus' && $(this).next('#optionwrapper').length === 0)){
					$('#optionwrapper').remove();
					if(value.length >= 2 || true){
						var url = lookupfield.parents('table').attr('data-base-url')
								+'/lookup/'
								+lookupfield.parents('table').attr('data-lookup-url')
								+'?q='+value;
						$('[name^='+lookupfield.parents('table').attr('data-target-field')+']').each(function(){
							if($(this).val() !== '' && $.isNumeric($(this).val())){
								url += '&e[]='+$(this).val();
							}
						});

						$.ajax(url).done(function(data){
							if(value === lookupfield.val()){
								$('#optionwrapper').remove();
								optionlist = [];
								options = '<div id="optionwrapper" class="options"></div>';
								lookupfield.after(options);
								lookup.positionOptionwrapper();
								l = data.length;
								lookup.data = data;
								lookup.cur = -1;
								if(l > 0){
									$.each(data, function(i, value) {
										displayValue = value[1];
										parent = value[1].substring(0,value[1].lastIndexOf('>')).trim();
										if(lookupfield.cur.length > 0){
											displayValue = value[1].replace(new RegExp('('+ lookupfield.cur + ')', 'gi'), '<mark>$1</mark>');
										} else if(parent.length === 0){
											parent=undefined;
										} else {
											displayValue = (value[1].match(/>/g).join('').replace(/>/g, '\xa0\xa0\xa0') || [])+value[1].substring(value[1].lastIndexOf('>')).trim();
										}
										var optionDisplay = $('<div>')
											.html(displayValue)
											.attr('id', 'option_'+i)
											.attr('value', value[0])
											.attr('data-parent', parent)
											.attr('data-self', value[1]);
										if(value.length > 2){
											optionDisplay.attr('data-nextfield', value[2]);
										}
										$('#optionwrapper').append(optionDisplay);
									});
								} else {
									$('#optionwrapper').append($('<div>').html('<i>No results found</i>'));
								}
								if($('#optionwrapper').parents('table').attr('data-new-url') !== undefined){
									$('#optionwrapper').append($('<div>')
											.html('<i>Add \''+$('#optionwrapper').siblings('input').val()+'\''
												+($('#optionwrapper').parent('td').attr('data-freetext') === 'this' ? ' with details' : '')
												+'</i>')
											.attr('id', 'option_'+l)
											.attr('value', 'new'));
									l += 1;
								}
								if($('#optionwrapper').parent('td').attr('data-freetext') === 'this'){
									$('#optionwrapper').append($('<div>')
									.html('<i>Quickly add \''+$('#optionwrapper').siblings('input').val()+'\' (without details)'
										+'</i>')
									.attr('id', 'option_'+l)
									.attr('value', 'quickadd'));
									l += 1;
								}
								$('#optionwrapper div[value]').on('mouseenter', function(){
									lookup.cur = Number($(this).attr('id').split('_')[1]);
									$('.optionHasFocus').removeClass('optionHasFocus');
									$('#option_'+lookup.cur).addClass('optionHasFocus');
								});
								
								if(value.length === 0){
									$('#optionwrapper div[data-parent]').addClass('hidden');
									
									$('#optionwrapper div')
											.on('click', function(){
												lookup.cur = Number($(this).attr('id').split('_')[1]);
												$('.optionHasFocus').removeClass('optionHasFocus');
												$('#option_'+lookup.cur).addClass('optionHasFocus');
												lookup.selectOption(this);
												$('#optionwrapper').prev('input').focus();
											}).on('mousedown', function(e){
												e.preventDefault();
											});
								} else {
									$('#optionwrapper div[value]')
											.off('click')
											.on('click', function(){
												lookup.cur = Number($(this).attr('id').split('_')[1]);
												$('.optionHasFocus').removeClass('optionHasFocus');
												$('#option_'+lookup.cur).addClass('optionHasFocus');
												lookup.selectOption(this, true);
											}).on('mousedown', function(e){
												e.preventDefault();
											});
								}
							}
						});
					}
					lookupfield.cur = value;
				} else {
					switch(code){
						case 40://down
							event.preventDefault();
							if(lookup.cur === -1){
								lookup.cur = 0;
							} else {
								nextId = $('#optionwrapper div:not(.hidden)').eq( $('#optionwrapper div:not(.hidden)').index( $('.optionHasFocus') ) + 1 ).attr('id');
								if(nextId === undefined){
									lookup.cur = -1;
								} else {
									lookup.cur = Number(nextId.substr(nextId.lastIndexOf('_')+1));
								}
							}
							$('.optionHasFocus').removeClass('optionHasFocus');
							$('#option_'+lookup.cur).addClass('optionHasFocus');
							lookup.scrollOptionwrapper();
							return false;
						case 38://up
							event.preventDefault();
							if(lookup.cur === -1){
								prevId = $('#optionwrapper div:not(.hidden)').last().attr('id');
								lookup.cur = Number(prevId.substr(prevId.lastIndexOf('_')+1));
							} else {
								prevId = $('#optionwrapper div:not(.hidden)').eq( $('#optionwrapper div:not(.hidden)').index( $('.optionHasFocus') ) - 1 ).attr('id');
								if($('#optionwrapper :not(.hidden)').index($('.optionHasFocus')) === 0){
									lookup.cur = -1;
								} else {
									lookup.cur = Number(prevId.substr(prevId.lastIndexOf('_')+1));
								}
							}
							$('.optionHasFocus').removeClass('optionHasFocus');
							$('#option_'+lookup.cur).addClass('optionHasFocus');
							lookup.scrollOptionwrapper();
							return false;
						case 13://enter
						case 9://tab
							event.preventDefault();
							if(lookup.cur > -1){
								lookup.selectOption($('#option_'+lookup.cur));
							}
							break;
						case 27:
							lookup.doBlur();
					}
				}
			});
			lookup.doBlur = function(){
				if(document.hasFocus() && $('#overlay').is(':not(:visible)')){
					if(typeof $('#optionwrapper').siblings('input').val() !== 'undefined' && $('#optionwrapper').siblings('input').val().length > 0){
						var msg = 'Please select a value from the options';
						if($('#optionwrapper').parents('table').attr('data-new-url') !== undefined){
							msg = msg + ' or use the add new option';
						}
						//msg = msg + '. When clicking cancel your input will be removed from the field.'
						npdc.confirm(msg,
							'Continue selecting',
							function(){
								setTimeout(function(){$('#optionwrapper').siblings('input').focus();},100);
							},
							'Cancel and remove input',
							function(){
								$('#optionwrapper').siblings('input').val('');
								$('#optionwrapper').hide();
							}
						);
					} else {
						$('#optionwrapper').hide();
					}
				}
			};
			lookupfield.blur(function(){
				lookup.timer = setTimeout(function(){lookup.doBlur();},10);
			});
			lookupfield.focus(function(){
				$('#optionwrapper').show();
				lookup.positionOptionwrapper();
			});
		}
	};
	
	$.expr[':'].focus = function(a){ return (a === document.activeElement); };
	
	multitext = {
		init: function(event, element){
			var code = event.keyCode || event.which;
			if((event.type === 'keydown' && [9,13,188].indexOf(code) < 0) || ((code === 13 || code === 9) && event.type === 'keyup')){
				return;
			}
			if($(element).val() === ''){
				$(element).siblings('button').prop('disabled', true).off('click');
			} else {
				$(element).siblings('button').prop('disabled', false).on('click', function(event){
					event.preventDefault();
					multitext.add(element);
				});
			}
			switch(code){
				case 13://enter
				case 188://comma
					event.preventDefault();
				case 9://tab
					this.add(element);
					break;
			}
		},
		add: function(element){
			if($(element).val() !== ''){
				$(element).siblings('button').prop('disabled', true).off('click');
				val = $(element).val();
				if(val.indexOf(',') > -1){
					val = val.split(',');
				} else if(val.indexOf(';') > -1){
					val = val.split(';');
				} else {
					val = [val];
				}
				for (var i = 0; i < val.length; i++) {
					subval = val[i].trim();
					$(element).parent().siblings('.values').append('<span><input type="hidden" name="'+$(element).attr('name')+'" value="'+subval+'">'+subval+'<span class="delete">x</span></span>');
					$(element).parent().siblings('.values').children(':last-of-type')
						.children('.delete').on('click', function(){
							multitext.delete(this);
						});
				}
				$(element).val('');
			}
		},
		delete: function(element){
			npdc.confirm('Realy delete '+$(element).siblings('input').val()+'?',
				'Delete '+$(element).siblings('input').val(),
				function(){
					form = $(element).parents('form');
					$(element).parent().remove();
				},
				'Keep '+$(element).siblings('input').val()
			);
		}
	};
	
	$('.multitext button').prop('disabled', true);
	$('.multitext .delete').on('click', function(){
		multitext.delete(this);
	});
	$('.multitext input').on('keydown keyup', function(event){
		multitext.init(event, this);
	});
	
	$('.lookuptable tbody tr:not(:last-child) td:nth-child(2), .multivalue tbody tr:not(:last-child) td:nth-child(2)').click(function(){
		lookup.delete($(this).parents('tr').attr('id'));
	});

	$('.lookuptable,.multivalue').each(function(){
		if($(this).hasClass('noAdd')){
			$(this).find('[id$=_new]').hide();
			
			if($(this).find('tbody tr:visible').length === 0){
				$(this).hide();
				$(this).next('.hint').hide();
			}
		}
		if($(this).attr('data-sortable') === 'true'){
			$(this).sortable({
				items: 'tbody tr:not(:last-child)'
			});
			$(this).find('tbody td:first-child').on('touchstart', function(){npdc.alert('Sorting is only possible using a mouse');});
			if($(this).hasClass('multivalue')){
				$(this).after('The last line can be left empty, to sort the last line add a new line below it using the + at the start of the line');
			}
		} else if(!$(this).hasClass('single')) {
			$(this).find('tbody tr td:first-child').hide();
			$(this).find('td:first-child').attr('colspan', 1);
		}
	});
	
	$('fieldset').each(function(){
		if($(this).attr('data-min') !== undefined || $(this).attr('data-max') !== undefined){
			$(this).children(':input').change(function(){
				disableFieldset($(this));
			});
			disableFieldset($(this).children(':input:first'));
		}
	});
	
	$('input[maxlength],textarea[maxlength]').each(function(){
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
						var newVal = $(this).val().replace(regex, '_');
						$(this).val(newVal);
						cur = newVal.length;
					}
				}
				var left = max-cur;
				$('#cnt'+id).html(left);
			});
	});
	$('input[data-permitted-chars],textarea[data-permitted-chars]')
			.not('[maxlength]').keyup(function(){
				if($(this).val().length > 0){
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
					}
				}
	});
	//lookup tables
	$('.lookuptable,.multivalue').each(function(){
		var single = false;
		if($(this).hasClass('single')){
			var row = $(this).find('td');
			single = true;
		} else {
			$(this).data('newRowBaseId', $(this).find('[id$=_row_new]').attr('id'));
			$(this).data('clone', $('#'+$(this).data('newRowBaseId')).clone());
			$(this).data('newCount', -1);
			$(this).find('[id*=_row_new_]').each(function(){
				var c = parseInt($(this).attr('id').substring($(this).attr('id').lastIndexOf("_") + 1));
				if(c > $(this).parents('table').data('newCount')){
					$(this).parents('table').data('newCount', c);
				}
			});
			var row = $(this).find('[id$=_row_new]');
		}
		if($(this).hasClass('lookuptable')){
			initialize.lookuptable(row, single);
		}
	});
	$(window).on('scroll resize', function(){
		lookup.positionOptionwrapper();
	});
	
	//maps and fieldsets
	$('fieldset[data-repeatable]').each(function(){
		createButton(this);
		$(this).parents('form').data('serialized', $(this).parents('form').serialize());
	});
	
	$('body.edit select:not(.no-select2):not(.select2-hidden-accessible)').each(function(){
		makeSelect2(this);
	});
	
	$('body.edit form[action$="new"], body.edit form[action$="edit"]').submit(function(e) {
		e.preventDefault();
		e.returnValue = false;
		var $form = $(this);

		$.ajax({ 
			type: 'post',
			url: baseUrl+'/lookup/session', 
			context: $form, // context will be "this" in your handlers
			success: function() { // your success handler
				this.off('submit');
				this.submit();
			},
			error: function() { // your error handler
				openOverlay(baseUrl+'/login?notice=expired');
				waitingForm = this;
			}
		});
	});
	
	fileHandler = {
		showTable: function(baseId){
			$('#'+baseId).parents('table').show();
			$('#'+baseId).parents('table').next('.hint').show();	
		},
		hideTable: function(baseId){
			if($('#'+baseId+' > tbody > tr').length === 1){
				$('#'+baseId).parents('table').hide();
				$('#'+baseId).parents('table').next('.hint').hide();
			}
		}
	};
	$('input[type=file][multiple]').on('change', function(){
		var baseId = $(this).attr('name').replace('[]', '')+'_row_new';
		size = 0;
		for(i = 0; i < $(this)[0].files.length; ++i){
			clone = $('#'+baseId).clone();
			clone.find('[name=file_file_new]').val($(this)[0].files[i].name);
			clone.attr('id', clone.attr('id')+'_'+i);
			clone.find('[name]').each(function(){
				$(this).attr('name', $(this).attr('name')+'_'+i);
			});
			clone.insertBefore('#'+baseId);
			clone.show();
			size += $(this)[0].files[i].size;
		}
		fileHandler.showTable(baseId);
		$(this).off('click');
		
		if(size > $('[name=MAX_FILE_SIZE]').val()){
			$('[id^='+baseId+'_]').remove();
			fileHandler.hideTable(baseId);
			if($(this)[0].files.length === 1){
				npdc.alert('The file is too large, please contact the NPDC to add this file');
			} else {
				npdc.alert('The combined size of the files is too large, please select fewer files to stay under the size limit');
			}
			$(this).val('');
		} else {
			$(this).on('click', function(){
				if($('[id^='+baseId+'_]').length === 0){
					$('[id^='+baseId+'_]').remove();
					fileHandler.hideTable(baseId);
					$(this).val('');
				} else {
					field = $(this);
					npdc.confirm('When selecting new files previously selected files will not be uploaded. Are you sure you wish to select new files to upload?',
						'Yes, select new files to upload',
						function(){
							$('[id^='+baseId+'_]').remove();
							fileHandler.hideTable(baseId);
							field.val('');
							field.trigger('click');
						},
						'No, keep current files'
					);
					return false;
				}
			});
		}
	});
	
	//suggestions
	$('.suggestions:not(.show) li:not(:first-child)').hide();
	$('.suggestions li:first-child').on('click', function(){
		if($(this).parent().hasClass('show')){
			$(this).parent().removeClass('show');
			$(this).siblings().slideUp();
		} else {
			$(this).parent().addClass('show');
			$(this).siblings().slideDown();
		}
	});
	$('.suggestions li:not(:first-child)').on('click', function(){
		doSet = false;
		if($('textarea[name='+$(this).parent().attr('data-target')+']').val().length === 0){
			doSet = true;
		}
		if(!doSet){
			target = $(this).parent().attr('data-target');
			value = $(this).text();
			npdc.confirm(
				'Do you want to replace \''
				+ $('textarea[name='+$(this).parent().attr('data-target')+']').val()
				+ '\' with \''
				+ $(this).text()
				+ '\' in \''
				+ $($(this).parent().prevAll('h4')[0]).text().replace('*', '')
				+ '\'?',
				'Yes, replace the text',
				function(){
					$('textarea[name='+target+']').val(value);
				},
				'No, keep the old value'
			);
		}
		if(doSet){
			$('textarea[name='+$(this).parent().attr('data-target')+']').val($(this).text());
		}
	});

	$("textarea[data-tags]").each(function(){
		switch($(this).attr('data-tags')){
			case 'extended':
				var toolbar = ["html", "|", "bold", "italic", "underline", "|", "p", "h1", "h2", "h3", "|", "subscript", "superscript", "|", "link", "unlink"];
				break;
			case 'default':
				var toolbar = ["html", "|", "bold", "italic", "underline", "|", "p", "h4", "h5", "h6", "|", "subscript", "superscript", "|", "link", "unlink"];
				break;
		}
		$(this).htmlarea({
			css: baseUrl+'/css/textarea.css',
			toolbar: toolbar
			});
	});
	
});

function createButton(element){
	var id = $(element).attr('id');
	var legend = $(element).children('legend').text();
	if(legend.substr(-1) === '*'){
		legend = legend.substr(0,legend.length-1);
	}
	var btn = '<button type="button" onclick="removeFieldset(this);return false;" class="btn_delete">Remove this '+legend+'</button>';
	$(element).children('legend').after(btn);
	var baseId = id.substr(0,id.lastIndexOf('_')+1);
	if(id.substr(id.lastIndexOf('_')) === '_new'){
		$(element).parents('form').data(id, $(element).clone());
		$(element).hide();
		var btn = '<button type="button" onclick="$(this).scrollView();addFieldset(\''+id+'\', true);return false;" id="'+id+'_btn" class="btn_add">Add new '+legend+'</button><hr class="afterbutton" />';
		$(element).after(btn);

		var suffix = 0;
		$('fieldset[data-repeatable][id^='+baseId+'new]').each(function() {
			nr = parseInt(this.id.substr(baseId.length+3));
			if(nr > suffix){
				suffix = nr;
			}
		});
		$('#'+id+'_btn').data('suffix', suffix);
		if($('fieldset[data-repeatable][id^='+baseId+']:not([id^='+baseId+'new_])').length <= 1){
			addFieldset(id);
		}
	} else {
		nr = id.substr(id.lastIndexOf('_')+1);
		if($(element).attr('data-map')){
			loadMap('#mapContainer_'+nr);
			if(getFeatureType(nr) === 'Area'){
				if($('[name='+mapBaseId+'wkt_'+nr+']').val() !== ''){
					$('[name='+mapBaseId+'type_'+nr+']:checked').data('wkt', $('[name='+mapBaseId+'wkt_'+nr+']').val());
					createFeature(nr, false);
				}
			} else {
				createFeature(nr, 'wkt');
			}
		}
	}
}

$.fn.scrollView = function (){
	return this.each(function () {
		$('html, body').animate({
			scrollTop: $(this).offset().top - 100
		}, 500);
	});
};

function removeFile(id){
	npdc.confirm('Are you sure you want to delete this file?',
		'Yes, delete',
		function(){
			$('input[name=keepfile_'+id+']').val('');
			$('input[name='+id+']').prop('disabled', false);
			$('#uploaded_'+id).remove();
			if($('input[name='+id+']').parent('fieldset').length === 1){
				disableFieldset($('input[name='+id+']'));
			}
		},
		'No, keep'
	);
}

function decodeEntities(encodedString) {
	var textArea = document.createElement('textarea');
	textArea.innerHTML = encodedString;
	return textArea.value;
}

function closeOverlay(data){
	if(data !== undefined){
		switch($('#overlay').data('type')){
			case 'select2':
				var newState = new Option(data.label, data.id, true, true);
				$($('#overlay').data('element')).append(newState).trigger('change');
				break;
			case 'lookupfield':
				lookup.saveOption($('#overlay').data('element'), data.id, decodeEntities(data.label), data.nextfield, false);
				break;
		}
	}
	$('form :input').prop('disabled', false);
	$('#overlay').hide();
	$('a, button, input').removeAttr('tabindex');
	$('a[data-tabindex], button[data-tabindex], input[data-tabindex]').each(function(){
			$(this).attr('tabindex', $(this).attr('data-tabindex'));
		});
	$('iframe').contents()
		.find('a, button, input').removeAttr('tabindex');
	$('iframe').contents()
		.find('a[data-tabindex], button[data-tabindex], input[data-tabindex]').each(function(){
			$(this).attr('tabindex', $(this).attr('data-tabindex'));
		});
}

function cloneLine(em){
	var clone = $(em).parent().clone();
	var tbl = $(em).parents('table');
	clone.find('input').val('');
		
	tbl.data('newCount', tbl.data('newCount')+1);
	suffix = '_'+tbl.data('newCount');
	tbl.find('[id$=_new]').each(function(){
		$(this).attr('id', $(this).attr('id')+suffix);
	});
	tbl.find('[name$=_new]').each(function(){
		$(this).attr('name', $(this).attr('name')+suffix);
	});
	tbl.find('[for$=_new]').each(function(){
		$(this).attr('for', $(this).attr('for')+suffix);
	});
	tbl.find('#'+tbl.data('newRowBaseId')+suffix+' td:nth-child(2)').click(function(){
		lookup.delete($(this).parent().attr('id'));
	});
	
	$(em).parent().after(clone);
	
	if(tbl.attr('data-sortable') === 'true'){
		if (tbl.data( 'ui-sortable' )) {
		   tbl.sortable('refresh');
		} else {
			tbl.sortable({
				items: 'tbody tr:not(:last-child)'
			});
		}
	}
	
	$('#'+tbl.data('newRowBaseId')+' select:not(.no-select2):not(.select2-hidden-accessible)').each(function(){
		makeSelect2(this);
	});
}