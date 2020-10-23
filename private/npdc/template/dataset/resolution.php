<?php
/**
 * Display of data resolution
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$resolutions = $this->model->getResolution(
    $this->data['dataset_id'],
    $this->data['dataset_version']
);
$fields = [
    'latitude_resolution' => 'Latitude resolution',
    'longitude_resolution' => 'Longitude resolution',
    'vocab_res_hor_id' => 'Horizontal resolution range',
    'vertical_resolution' => 'Vertical resolution',
    'vocab_res_vert_id' => 'Vertical resolution range',
    'temporal_resolution' => 'Temporal resolution',
    'vocab_res_time_id' => 'Temporal resolution range'
];

foreach ($resolutions as $resolution) {
    echo '<fieldset><legend>Data resolution</legend>';
    foreach ($fields as $id=>$label) {
        if (!empty($resolution[$id])) {
            echo '<section class="inline"><h4>' . $label . '</h4><p>'
            . (
                substr($id, 0, 5) === 'vocab'
                ? $this->vocab->formatTerm(
                    substr($id, 0, -3),
                    $resolution
                )
                : $resolution[$id]
            )
            . '</p></section>';
        }
    }
    echo '</fieldset>';
}