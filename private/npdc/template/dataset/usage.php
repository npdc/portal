<?php
/**
 * Display of information about usability of and using a dataset
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

include 'files.php';
?>

<h4>Dataset progress</h4>
<p><?=$this->data['dataset_progress']?></p>

<h4>Data quality</h4>
<p><?=$this->data['quality']?></p>

<h4 id="access">Access constraints</h4>
<p><?=$this->data['access_constraints']?></p>

<h4 id="use">Use constraints</h4>
<p><?=$this->data['use_constraints']?></p>

<?php
$this->json['license'] = 'Access: '.($this->data['acces_constraints'] ?? 'No known constraints').'; Use: '.($this->data['use_constraints'] ?? 'No known constraints');