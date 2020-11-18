
function doubleTitle(id){
	if($('input[name="formid"]').val() != 'publication_new') {return}
	url = baseUrl+'/lookup/publication?fuzzy&q='+$('input[name="'+id+'"]').val();
	$.ajax(url).done(function(data){
		if(data != false){
			if(data.length == 1){
				alert = 'A publication with a similar title has been found. Please check if this is a different publication. If so, please proceed with adding your publication.';
			} else {
				alert = 'Several publications with similar titles have been found. Please check if these are different publications. If so, please proceed with adding your publication';
			}
			$.each(data, function(index, value){
				alert = alert + '<br/><a href="'+baseUrl+'/publication/'+index+'">- '+value+'</a>';
			});
			npdc.alert(alert);
		}
	});
}

function doubleDOI(id){
	if($('input[name="formid"]').val() != 'publication_new') {return}
	url = baseUrl+'/lookup/publication?doi='+$('input[name="'+id+'"]').val();
	$.ajax(url).done(function(data){
		console.log(data);
		if(data != false){
			npdc.alert('There is alreay a publication with this DOI. You can find it <a href="'+baseUrl+'/publication/'+data.publication_id+'">here</a>. If the site indicates you don\'t have permission to view it or it appears to be a different publication please contact the NPDC');
		}
	});
}