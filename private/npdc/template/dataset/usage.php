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
$licenseModel = new \npdc\model\License();
$license = $licenseModel->getById($this->data['license_id']);

if (!empty($license['free_access'])){
    $this->json['@graph'][0]['isAccessbileForFree'] = (bool)$license['free_access'];
}
$this->json['@graph'][0]['license'] = [];
foreach (['spdx_url', 'url'] as $url) {
    if (!empty($license[$url])) {
        $this->json['@graph'][0]['license'][] = $license[$url];
    }
    
}

if (empty($license['spdx_url'])) {
    $this->json['@graph'][0]['license'][] = 
        $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . BASE_URL
            . '/dataset/'.$this->data['uuid'] . '#access';
}

$this->json['@graph'][0]['license'][] = 
    'Access: '
    . (
        $this->data['access_constraints']
        ?? 'No known constraints'
    )
    .'; Use: '
    . (
        $this->data['use_constraints']
        ?? 'No known constraints'
    );