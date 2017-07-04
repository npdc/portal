<?php

include 'dataset/citation.php';
?>
<button onclick="$('.hiddenSubDiv').slideDown();$('.collapsible').removeClass('hidden');">Expand all</button> <button onclick="$('.hiddenSubDiv').slideUp();$('.collapsible').addClass('hidden');">Collapse all</button>
<section class="inline">
	<h4>Dif id</h4>
	<p><?=$this->data['dif_id']?></p>
</section>
	
<?php
$parts = [
	['Summary', 'field', 'summary'],
	['Purpose', 'field', 'purpose'],
	['Temporal coverage', 'file', 'time'],
	['Platform &amp; Instruments', 'file', 'platform'],
	['Data resolution', 'file', 'resolution'],
	['Involved people and organizations', 'file', 'people'],
	['Dataset progress and usage', 'file', 'usage'],
	['Projects, publications and other links', 'file', 'references']
];
$parseDown = new \Parsedown();
foreach($parts as $part){
	$content = '';
	if($part[1] === 'field' && !empty($this->data[$part[2]])){
		$content = '<p>'.str_replace(['h1', 'h2', 'h3'], ['h4', 'h5', 'h6'],$parseDown->text($this->data[$part[2]])).'</p>';
	} elseif($part[1] === 'file'){
		ob_start();
		include 'dataset/'.$part[2].'.php';
		$content = ob_get_clean();
	}
	if(!empty($content)){
		echo '<h3 class="collapsible hidden">'.$part[0].'</h3><div style="display:none" class="hiddenSubDiv">'
			. $content
			. '</div><hr/>';
	}
}