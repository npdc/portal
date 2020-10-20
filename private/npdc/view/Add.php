<?php

/**
 * Allow adding content from overlay while editing other content
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Add extends Base {
    public $template = 'plain';
    public $bodyClass = 'edit add';
    public $closeButton = 'Cancel';
    public $mid;
    public $title;
    
    private $session;
    protected $controller;

    /**
     * Constructor
     *
     * @param object $session login information
     *
     * @param object $controller add controller
     */
    public function __construct($session, $controller) {
        $this->session = $session;
        $this->controller = $controller;
        $this->extraHeader = '<meta name="robots" content="noindex">';
    }
    
    /**
     * Show add form
     *
     * @param string $id content type to add
     * @return void
     */
    public function showItem($id) {
        if (is_null($this->controller->return)) {
            $this->title = 'New '.$id;
            $this->loadEditPage();
            $this->mid .= '<script language="javascript" type="text/javascript">
                $(function() {
                    $(\':input:enabled:visible:first\').focus();
                });
                </script>
                ';
        } else {
            $this->mid = '<script language="javascript" type="text/javascript">
                $(function() {
                    window.parent.closeOverlay('
                    . json_encode($this->controller->return)
                    . ');
                });
                </script>
                ';
        }
    }
}