<?php
/**
 * Display personal details
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$perms = $this->model->getUserLevelDetails($this->data['user_level']);
echo '<section class="inline"><h4>User level</h4><p>'.$perms['name'].'</p></section>'.$perms['description'];
?>
<h3>Person details</h3>

<?php
$fields = [
    'titles'=>'Titles',
    'initials'=>'Initials',
    'given_name'=>'First name',
    'surname'=>'Surname',
    'mail'=>'Mail',
    'phone_personal'=>'Direct phone',
    'phone_secretariat'=>'General phone',
    'phone_mobile'=>'Mobile phone',
    'address'=>'Address',
    'zip'=>'Zip code',
    'city'=>'City',
    'orcid'=>'ORCID'
];

foreach ($fields as $id=>$label) {
    if (!empty($this->data[$id])) {
        echo '<div class="inline">
        <h4>'.$label.'</h4>
        <p>'.($id === 'orcid' ? '<a href="https://orcid.org/'.$this->data[$id].'">'.$this->data[$id].'</a>' : $this->data[$id]);
        if (substr($id, 0, 6) === 'phone_') {
            echo ' ('.($this->data[$id.'_public'] === 'yes' ? 'public' : 'hidden').')';
        }
        echo '</p></div>';
    }
}