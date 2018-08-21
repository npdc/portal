<?php

/**
 * Editor page view
 * 
 * Uses the overlay template, but is a full page
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\view;

class Editor extends Base{

	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args){
		$this->session = $session;
		$this->args = $args;
	}
	
	/**
	 * Display the page
	 *
	 * @return void
	 */
	public function showList(){
		if($this->session->userLevel < NPDC_EDITOR){
			$this->title = 'No access';
			$this->mid = 'You have insufficient privileges to access this page';
		} else {
			ob_start();
			$hideFoot = true;
			include 'template/overlay_editor.php';
			$this->title = 'Editor tools';
			$this->mid = ob_get_clean();
			ob_end_clean();
		}
	}
}