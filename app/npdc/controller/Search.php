<?php

/**
 * search controller 
 * takes post data and converts it into clean url, then redirects to that clean url
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Search {
	public $formId = 'search';
	public $form;
	private $formController;
	public $userLevelAdd = NPDC_NOBODY;
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args){
		$this->formController = new \npdc\controller\Form($this->formId);
		$this->form = $this->formController->getForm('search');
		foreach($this->form->fields->type->options as $id=>$label){
			if(!\npdc\config::$partEnabled[$id]){
				unset($this->form->fields->type->options->$id);
			}
		}
		if(array_key_exists('q', $_POST)){
			$url = $this->processPost();
			header('Location: '.$url);
			die();
		}
	}
	
	/**
	 * gives the clean url to which to redirect
	 * @return string
	 */
	private function processPost(){
		$url = BASE_URL.'/search';
		$this->formController->doCheck();
		if(array_key_exists('type', $_POST) && count($_POST['type']) > 0){
			$url .= '/'.implode('+', $_POST['type']);
		}
		$qclean = preg_replace("/[^. \-0-9a-zA-Z]/", " ", strtolower(trim($_POST['q'])));
		while (strstr($qclean, "  ")) {
		   $qclean = trim(str_replace("  ", " ", $qclean));
		}
		$url .= '/'.str_replace(" ", "+", $qclean);
		return $url;
	}
}
