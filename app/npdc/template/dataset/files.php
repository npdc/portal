<?php

if(count($this->model->getFiles($this->data['dataset_id'], $this->data['dataset_version'])) === 0 && count($this->model->getLinks($this->data['dataset_id'], $this->data['dataset_version'], true)) === 0){
	echo 'No files';
} else {
	echo '<button onclick="openUrl(\''.BASE_URL.'/'.$this->data['uuid'].'/files\')">Get files</button>';
}