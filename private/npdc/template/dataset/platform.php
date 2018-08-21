<?php
/**
 * Display of platform, instrument and sensor information for dataset
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$platforms = $this->model->getPlatform($this->data['dataset_id'], $this->data['dataset_version']);
if(count($platforms) > 0){
	foreach($platforms as $platform){
		echo '<fieldset><legend>Platform</legend>'.$this->vocab->formatTerm('vocab_platform', $platform);
		$this->showCharacteristics('platform', $platform['platform_id'], $this->data['dataset_version']);
		$instruments = $this->model->getInstrument($platform['platform_id'], $this->data['dataset_version']);
		if(count($instruments) > 0){
			foreach ($instruments as $instrument){
				echo '<fieldset><legend>Instrument</legend>'.$this->vocab->formatTerm('vocab_instrument', $instrument);
				if(!is_null($instrument['technique'])){
					echo '<section class="inline"><h4>Technique</h4><p>'.$instrument['technique'].'</p></section>';
				}
				$this->showCharacteristics('instrument', $instrument['instrument_id'], $this->data['dataset_version']);
				if(!is_null($instrument['number_of_sensors'])){
					echo '<section class="inline"><h4>Number of sensors</h4><p>'.$instrument['number_of_sensors'].'</p></section>';
				}
				$sensors = $this->model->getSensor($instrument['instrument_id'], $this->data['dataset_version']);
				if(count($sensors) > 0){
					foreach ($sensors as $sensor){
						echo '<fieldset><legend>Sensor</legend>'.$this->vocab->formatTerm('vocab_instrument', $sensor);
						if(!is_null($sensor['technique'])){
							echo '<section class="inline"><h4>Technique</h4><p>'.$sensor['technique'].'</p></section>';
						}
						$this->showCharacteristics('sensor', $sensor['sensor_id'], $this->data['dataset_version']);
						echo '</fieldset>';
					}
				}
				echo '</fieldset>';
			}
		}
		echo '</fieldset>';
	}
}
