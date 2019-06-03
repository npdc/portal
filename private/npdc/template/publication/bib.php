<?php
/**
 * Display of citation in bibtext format
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$content_type = 'application/x-bibtex';
$output = '@'.$this->model->getTypeById($this->data['publication_type_id'])['bib'].'{'.$id.'
	author={'.$str.'},
	title={'.($citation['title'] ?? $this->data['title']).'},
	year={'.substr($citation['release_date'] ?? $this->data['insert_timestamp'],0,4).'},
	url={'.$url.'},
	abstract={'.$this->data['summary'].'}';
switch($this->model->getTypeById($this->data['publication_type_id'])['bib']){
	case 'article':
		$output .= '
	journal={'.$this->data['journal'].'}
	volume={'.$this->data['volume'].'}
	number={'.$this->data['issue'].'}
	pages={'.str_replace('-', '--', $this->data['pages']).'}';
}
$output .= '
}';