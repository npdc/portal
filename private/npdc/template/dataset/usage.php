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
if (substr($this->data['license'], 0, 2) == 'cc') {
    $this->json['@graph'][0]['isAccessbileForFree'] = true;
    $this->json['@graph'][0]['license'] = $this->data['license'] == 'ccby' 
        ? [
            'https://spdx.org/licenses/CC-BY-4.0',
            'https://creativecommons.org/licenses/by/4.0/'
        ] 
        : [
            'https://spdx.org/licenses/CC0-1.0',
            'https://creativecommons.org/publicdomain/zero/1.0/'
        ];
} else {
    $this->json['@graph'][0]['license'] = [
        $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . BASE_URL
            . '/dataset/'.$this->data['uuid'] . '#access',
        'Access: '
            . (
                $this->data['access_constraints']
                ?? 'No known constraints'
            )
            .'; Use: '
            . (
                $this->data['use_constraints']
                ?? 'No known constraints'
            )
    ];
}