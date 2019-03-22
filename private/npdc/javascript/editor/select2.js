
function makeSelect2(element){
	placeholder = '';
	$(element).children('option').each(function(){
		if($(this).val() === 'please_select') {
			placeholder = $(this).html();
			$(this).after('<option></option>');
			$(this).remove();
		}
	});
	var builder = {
		placeholder: placeholder,
		minimumResultsForSearch: 10
	};
	if($(element).hasClass('error')){
		builder.containerCssClass = "error";
	}
	$(element).siblings('.no_select2').remove();
	builder.placeholder = 'Type here to select one or more options';
	if($(element).attr('data-ajax-url') !== undefined) {
		builder.ajax = {
			url: $(element).attr('data-ajax-url'),
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term, // search term
					output: 'object',
					add: $(element).attr('data-new-url') !== undefined
				};
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 2,
			processResults: function (data) {
				return {
					results: data.items
				};
			}
		};
		builder.templateResult = function(data){
			return data.text;
		};
		builder.templateSelection = function(data){
			return data.text;
		};
	} 
	if($(element).attr('data-new-url') !== undefined) {
		builder.placeholder = 'Type here to select an option or add a new one';
		builder.tags = true;
		builder.minimumResultsForSearch = 1;
		builder.createSearchChoice = function (params) {
			return {
				id: 'new',
				text: 'Add '+params.term
			};
		};
		builder.createTag = function (params) {
			return {
				id: 'new',
				text: 'Add '+params.term
			};
		};
		builder.insertTag = function (data, tag) {
			data.push(tag);
		};
		
		$(element).select2(builder).on("select2:selecting", function(event) {
			if(event.params.args.data.id === 'new'){
				event.preventDefault();
				name = event.params.args.data.text.substring(4);
				$("form :input").prop('disabled', true);
				$('#overlay .inner').html('<iframe src="'+$(this).attr('data-new-url')+'/'+name+'"></iframe>');
				$('#overlay').show();
				$('#overlay').data('element', $(this));
				$('#overlay').data('type', 'select2');
				$(this).select2('close');
			}
		});
	} else {
		$(element).select2(
			builder
		);
	}
}