<?php
/**
 * Display main column of data access request
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

if (!empty($this->controller->notice)) {
    echo '<span class="notice">'.$this->controller->notice.'</span>';
}
echo '<h4>Dataset</h4><p><a href="' . BASE_URL .'/dataset/'
    . $this->data['dataset_id'] . '">'
    . $this->controller->modelDataset->getById(
        $this->data['dataset_id']
    )['title']
    . '</a></p><section class="inline"><h4>Request date</h4><p>'
    . date('Y-m-d H:i', strtotime($this->data['request_timestamp']))
    . '</p></section><h4>Reason</h4><p>' .$this->data['reason']
    . '</p><h4>Requested files</h4>';

foreach ($this->model->getFiles($this->data['access_request_id']) as $file) {
    echo '<p>' . $file['title'] . ' (' . $file['name'] . ', ' . $file['type']
        . ', ' . formatBytes($file['size']) . ')<br/><i>' . $file['description']
        . '</i></p>';
}