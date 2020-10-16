<?php
/**
 * Display of citation in bibtext format
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$content_type = 'application/x-bibtex';
$output = '@misc{'.$id.'
	author={'.$str.'},
	title={'.($citation['title'] ?? $this->data['title']).'},
	year={'.substr($citation['release_date'] ?? $this->data['insert_timestamp'],0,4).'},
	url={'.$url.'},
	type={data set},
	publisher={'.($citation['publisher'] ?? \npdc\config::$siteName).'},
	abstract={'.$this->data['summary'].'}
}';