<?php

/**
 * Contact model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Contact {
    private $dsql;
    /**
     * Constructor
     */
    public function __construct() {
        $this->dsql = \npdc\lib\Db::getDSQLcon();
    }

    public function insert($data) {
        $this->dsql->dsql()->table('contact')
            ->set('receiver', $data['receiver'])
            ->set('sender_mail', $data['mail'])
            ->set('sender_name', $data['name'])
            ->set('subject', $data['subject'])
            ->set('text', base64_encode($data['message']))
            ->set('country', $data['country'])
            ->set('ip', $_SERVER['REMOTE_ADDR'])
            ->set('browser', $_SERVER['HTTP_USER_AGENT'])
            ->insert();
    }
}