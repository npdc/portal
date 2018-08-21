<?php
/**
 * Display of various type of dataset keywords
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>

<h4>ISO topic</h4>
<ul>
<?php
foreach ($this->model->getTopics($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$topic){
	$cut = ':'; //':' or 'For example'
	echo '<li>'.(strpos($topic['description'], $cut) === false ? $topic['description'] : trim(substr($topic['description'],0,strpos($topic['description'], $cut)))).'</li>';
}
?>
</ul>

<h4>Science keywords</h4>
<ul>
<?php
foreach($this->model->getKeywords($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$keyword){
	echo '<li>'.$this->vocab->formatTerm('vocab_science_keyword', $keyword).'</li>';
}
?>
</ul>

<h4>Ancillary keywords</h4>
<ul>
	<?php
	$keywords = $this->model->getAncillaryKeywords($this->data['dataset_id'], $this->data['dataset_version']);
	foreach($keywords as $word){
		echo '<li>'.$word['keyword'].'</li>';
	}
	?>
</ul>