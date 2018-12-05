<?php
/**
 * Display of temporal information of dataset
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$this->json['temporalCoverage'] = [];

foreach($this->model->getTemporalCoverages($this->data['dataset_id'], $this->data['dataset_version']) as $tc){
	echo '<fieldset><legend>Temporal coverage</legend>';
	foreach($this->model->getTemporalCoveragesGroup('period', $tc['temporal_coverage_id'], $this->data['dataset_version']) as $group){
		echo '<section class="inline"><h4>Period</h4><p>'.date('j F Y', strtotime($group['date_start'])).' to '.date('j F Y', strtotime($group['date_end'])).'</p></section>';
		$this->json['temporalCoverage'][] = date('Y-m-d', strtotime($group['date_start'])).'/'.date('Y-m-d', strtotime($group['date_end']));
	}
	$units = [
		's'=>'seconds',
		'i'=>'minutes',
		'h'=> 'hours',
		'd'=> 'days',
		'w'=> 'weeks',
		'm'=> 'months',
		'y'=> 'years'
	];
	foreach($this->model->getTemporalCoveragesGroup('cycle', $tc['temporal_coverage_id'], $this->data['dataset_version']) as $group){
		echo '<fieldset><legend>Sampling cycle</legend>'
		. '<section class="inline"><h4>Name</h4><p>'.$group['name'].'</p></section>'
		. '<section class="inline"><h4>Period</h4><p>'.date('j F Y', strtotime($group['date_start'])).' to '.date('j F Y', strtotime($group['date_end'])).'</p></section>'
		. '<section class="inline"><h4>Frequency</h4><p>'.$group['sampling_frequency'].' '.$units[$group['sampling_frequency_unit']].'</p></section>'
		. '</fieldset>';
	}
	foreach($this->model->getTemporalCoveragesGroup('paleo', $tc['temporal_coverage_id'], $this->data['dataset_version']) as $group){
		echo '<fieldset><legend>Paleo temporal coverage</legend>'
		. (!empty($group['start_value']) ? '<section class="inline"><h4>Start</h4><p>'.$group['start_value'].' '.$group['start_unit'].'</p></section>' : '')
		. (!empty($group['end_value']) ?'<section class="inline"><h4>End</h4><p>'.$group['end_value'].' '.$group['end_unit'].'</p></section>' : '');
		$chronounits = $this->model->getTemporalCoveragePaleoChronounit($group['temporal_coverage_paleo_id'], $this->data['dataset_version']);
		if(count($chronounits) > 0){
			echo '<h4>Chronostratigraphic unit</h4><ul>';
			foreach($chronounits as $unit){
				echo '<li>'.$this->vocab->formatTerm('vocab_chronounit', $unit).'</li>';
				$this->json['temporalCoverage'][] = $this->vocab->formatTerm('vocab_chronounit', $unit);
			}
			echo '</ul>';
		}
		echo '</fieldset>';
	}
	foreach($this->model->getTemporalCoveragesGroup('ancillary', $tc['temporal_coverage_id'], $this->data['dataset_version']) as $group){
		echo '<section class="inline"><h4>Keyword</h4><p>'.$group['keyword'].'</p></section>';
	}
	echo '</fieldset>';
}