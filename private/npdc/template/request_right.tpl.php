<?php
/**
 * Display of data access request sidebar
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
echo '<section class="inline"><h4>Access</h4><p>'
    . $this->parsePermitted($this->data['permitted']) 
    .'</p></section>';

if (!empty($this->data['response'])) {
    echo '<h4>Response</h4><p>' . $this->data['response'] . '</p>';
}

if (!empty($this->data['zip_id'])) {
    $zipModel = new \npdc\model\Zip();
    $zip = $zipModel->getById($this->data['zip_id']);
    echo '<p><a href="' . BASE_URL . '/checkDownload/' . $zip['filename']
        . '.zip">Download</a></p>';
}
if (
    $this->modelDataset->isEditor(
        $this->data['dataset_id'],
        $this->session->userId
    )
    || $this->session->userLevel === NPDC_ADMIN
) {
    $personModel = new \npdc\model\Person();
    $person = new \npdc\lib\Person();
    echo '<h4>Requested by</h4>';
    $requestor = $personModel->getById($this->data['person_id']);
    echo $person->showPerson($requestor);
    if ($this->data['permitted'] === null) {
        if (!empty($this->controller->error)) {
            echo '<span class="error">' . $this->controller->error . '</span>';
        }
        echo '<form method="post"><h4>Allow*:</h4><div class="cols">'
            . '<div><input type="radio" name="allow" id="allow_yes" value="yes" '
                . ($_POST['allow'] === 'yes' ? 'checked' : '')
                . '/><label for="allow_yes"><span class="indicator"></span>'
                . 'Yes</label></div>'
            . '<div><input type="radio" name="allow" id="allow_no" value="no" '
                . ($_POST['allow'] === 'no' ? 'checked' : '')
                . '/><label for="allow_no"><span class="indicator"></span>'
                . 'No</label></div></div><h4>Comments:</h4>'
            . '<textarea name="reason" placeholder="If no access is given '
                . 'please give a reason" rows="5">' . $_POST['reason'] 
                . '</textarea><input type="submit" value="Submit" /></form>';
    } else {
        $responder = $personModel->getById($this->data['responder_id']);
        echo '<h4>Response by:</h4>' .$responder['name'];
    }
}