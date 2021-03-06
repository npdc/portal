<?php

/**
 * Download view
 * 
 * Allows the download dir to be outside the webroot
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Download {
    public function __construct($session, $controller) {
        $this->session = $session;
        $this->controller = $controller;
    }
    /**
     * Retreive the file and offer as download
     *
     * @param string $item name of the file to be downloaded
     * @return void
     */
    public function showList() {
        $file = \npdc\config::$downloadDir . '/' . \npdc\lib\Args::get('file')
            . '.' . \npdc\lib\Args::get('ext');
        if (file_exists($file)) {
            header('Content-type: application/octet-stream'); 
            header(
                'Content-Disposition: attachment; filename='
                . \npdc\lib\Args::get('file') . '.' . \npdc\lib\Args::get('ext')
            );
            header('Pragma: no-cache'); 
            header('Expires: 0');
            readfile($file);
        } else {
            http_response_code(404);
            echo 'File not found';
        }
        die();
    }
}