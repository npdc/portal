<?php

/**
 * Display of main column of data access request
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>
<h4>Files</h4>
<?php

$files = $this->model->getFiles($this->data['dataset_id'], $this->data['dataset_version']);
if(!empty($this->controller->error)){
	echo '<span class="error">'.$this->controller->error.'</span>';
}

if(count($files) === 0){
	echo 'No files';
} else {
	echo '<form method="post"><table class="files">';
	$downloadable = 0;
	foreach($files as $file){
		if($file['default_access'] !== 'restricted'){
			continue;
		}
		echo '<tr>'
		. '<td>'
			. '<input type="checkbox" name="files[]" value="'.$file['file_id'].'" id="file_'.$file['file_id'].'" '
			. (in_array($file['file_id'], ($_POST['files'] ?? [])) ? 'checked' : '')
			. '/><label for="file_'.$file['file_id'].'"><span class="indicator"></span> </label>'
		. '</td>'
		. '<td>'.$file['title'].' ('.$file['name'].', '.$file['type'].', '.formatBytes($file['size']).')<br/><i>'.$file['description'].'</i></td></tr>';
	}
	echo '</table>'
	. 'Please provide information on why you want access to these files and what you want to do with them'
	. '<textarea placeholder="Why do you want access?" name="request" rows="5">'.$_POST['request'].'</textarea>'
	. '<button type="submit" formaction="request">Request access to files</button> '
	. '</form>';
}