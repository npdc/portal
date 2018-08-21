<?php
/**
 * Display of dataset citation as string with links to bib and ris
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$citation = $this->model->getCitations($this->data['dataset_id'], $this->data['dataset_version'], 'this')[0];
$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/'.$this->data['uuid'];
echo '<div id="citation"><span style="font-weight:bold">&ldquo;</span>'
	. ($citation['creator'] ?? $this->model->getAuthors($this->data['dataset_id'], $this->data['dataset_version']))
	. ' ('.substr($citation['release_date'] ?? $this->data['insert_timestamp'],0,4).').'
	. ' <em>'.($citation['title'] ?? $this->data['title']).'.</em>'
	. ' (v'.($citation['version'] ?? $this->data['dataset_version']).')'
	. (!is_null($citation['release_place']) ? ' '.$citation['release_place'].'.' : '')
	. (!is_null($citation['series_name']) ? ' Part of <i>'.$citation['series_name'].'</i>.' : '')
	. (!is_null($citation['editor']) ? ' Edited by '.$citation['editor'].'.' : '')
	. (!is_null($citation['publisher']) ? ' Published by '.$citation['publisher'].'.' : '')
	. ' <a href="'.$url.'">'.$url.'</a><span style="font-weight:bold">&rdquo;</span>
	<div>Please use the citation above when using this dataset. Download  as: <a href="'.BASE_URL.'/'.$this->data['uuid'].'.bib" style="font-variant:small-caps">BibTex</a> or <a href="'.BASE_URL.'/'.$this->data['uuid'].'.ris"><abbr title="EndNote/ProCite/Reference Manager">RIS</abbr></a></div></div>';