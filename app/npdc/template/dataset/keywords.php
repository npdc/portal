<h4>ISO topic</h4>
<p>
<?php
foreach ($this->model->getTopics($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$topic){
	if($i > 0){
		echo '<br/>';
	}
	$cut = ':'; //':' or 'For example'
	echo strpos($topic['description'], $cut) === false ? $topic['description'] : trim(substr($topic['description'],0,strpos($topic['description'], $cut)));
}
?>
</p>

<h4>Science keywords</h4>
<ul>
<?php
foreach($this->model->getKeywords($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$keyword){
	echo '<li>'.$this->vocab->formatTerm('vocab_science_keyword', $keyword).'</li>';
}
?>
</ul>

<h4>Ancillary keywords</h4>
<p>
	<?php
	$keywords = $this->model->getAncillaryKeywords($this->data['dataset_id'], $this->data['dataset_version']);
	foreach($keywords as $word){
		echo ($n > 0 ? ' / ' : '').$word['keyword'];
		$n++;
	}
	?>
</p>