<?php
/**
 * Display of dataset citation as string with links to bib and ris
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

echo '<div id="citation"><span style="font-weight:bold">&ldquo;</span>'
	. $this->model->getCitationString($this->data)
	. '<span style="font-weight:bold">&rdquo;</span>
	<div>Please use the citation above when using this dataset. Download  as: <a href="'.BASE_URL.'/dataset/'.$this->data['uuid'].'.bib" style="font-variant:small-caps">BibTex</a> or <a href="'.BASE_URL.'/dataset/'.$this->data['uuid'].'.ris"><abbr title="EndNote/ProCite/Reference Manager">RIS</abbr></a></div></div>';