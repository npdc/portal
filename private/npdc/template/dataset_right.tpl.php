<?php
/**
 * side bar of dataset description
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

include 'dataset/files.php';
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