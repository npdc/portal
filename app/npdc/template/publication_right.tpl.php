<?php

/**
 * side bar of publication
 */

if(!empty($this->data['file_id'])){
	$fileModel = new \npdc\model\File();
	$fileData = $fileModel->getFile($this->data['file_id']);
	echo '<h4>Download</h4><p><a href="'.BASE_URL.'/file/'.$fileData['file_id'].'">'.$fileData['name'].'</a> ('.formatBytes($fileData['size']).')</p>';
}
if(!empty($this->data['url'])){
	echo '<h4>External resource</h4><p><a href="'.$this->data['url'].'">Link</a></p>';
}

$fields = ['date'=>'Date'
	, 'journal'=>'Journal'
	, 'volume'=>'Volume'
	, 'issue'=>'Issue'
	, 'pages'=>'Pages'
	, 'isbn'=>'ISBN'
	, 'doi'=>'DOI'];

foreach($fields as $id=>$label){
	if(!empty($this->data[$id])){
		echo '<section class="inline">
		<h4>'.$label.'</h4>
		<p>'.($id === 'doi' ? '<a href="https://dx.doi.org/'.$this->data[$id].'">' : '').$this->data[$id].($id === 'doi' ? '</a>' : '').'</p>
		</section>';
	}
}


$keywords = $this->model->getKeywords($this->data['publication_id'], $this->data['publication_version']);
if(count($keywords) > 0){
	echo '<h4>Keywords</h4>';
	$n = 0;
	foreach($keywords as $word){
		echo ($n > 0 ? ' / ' : '').$word['keyword'];
		$n++;
	}
}
