<?php

/**
 * side bar of dataset
 */
include 'dataset/files.php';

$this->displayField('dif_id', 'Dif id', true);
?>

<section class="inline">
	<h4>Start date</h4>
	<p><?=$this->data['date_start']?></p>
</section>

<section class="inline">
	<h4>End date</h4>
	<p><?=$this->data['date_end']?></p>
</section>

<?php
include 'dataset/location.php';
include 'dataset/keywords.php';