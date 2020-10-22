<?php
/**
 * Display organization details
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>

<h3>Organization details</h3>

<?php
$fields = [
    'organization_name'=>'Name',
    'organization_address'=>'Address',
    'organization_zip'=>'Zip code',
    'organization_city'=>'City',
    'country_name'=>'Country',
    'visiting_address'=>'Visiting address',
    'website'=>'Website',
    'edmo'=>'EDMO',
    'dif_code'=>'Code in diff',
    'dif_name'=>'Name in diff'
];

foreach ($fields as $id=>$label) {
    if (!empty($this->data[$id])) {
        echo '<div class="inline">
        <h4>'.$label.'</h4>
        <p>'.nl2br($this->data[$id]).'</p>
        </div>';
    }
}