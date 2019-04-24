<?php

/**
 * display front page
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Front{
	public $title;
	public $mid;
	public $right;
	public $class = 'front';
	public $accessLevel;
	private $session;
	public $extraHeader;
	
	/**
	 * Constructor
	 * 
	 * @param object $session login information
	 *
	 */
	public function __construct($session){
		$this->session = $session;
		$this->canEdit = $session->userLevel >= NPDC_ADMIN;
		$this->baseUrl = 'home';
	}
	
	/**
	 * alias of showItem()
	 */
	public function showList(){
		$this->showItem();
	}
	
	/**
	 * get the front page
	 */
	public function showItem(){
		$model = new \npdc\model\Page();

		$data = $model->getByUrl('home');
		$this->mid = '<div class="frontPageContent">'.$data['content'].'</div><div>';
		
		$newsModel = new \npdc\model\News();
		$news = $newsModel->getLatest();
		if(count($news) > 0){
			$this->mid .= '<div><h4>'.$news[0]['title'].'</h4>'
				. '<p class="info">Published: '.date('j F Y H:i', strtotime($news[0]['published'])).'</p>'
				. '<p>'.$news[0]['content'].'</p>'
				. '<a href="'.$news[0]['link'].'">More info</a></div>';
		}

		$c = new \npdc\controller\Search($this->session);
		$v = new \npdc\view\Search($this->session, $c);
		$this->mid .= '<div><div>'.$v->getForm().'</div></div></div>';

		$list = [];
		foreach(['project', 'dataset', 'publication'] as $type){
			$modelName = 'npdc\\model\\'.ucfirst($type);
			$model = new $modelName();
			$res = $model->getList();
			if(!is_null($res)){
				$list = array_merge($list, $res);
			}
		}
		$keys = [];
		foreach($list as $data){
			$key = $data['published'];
			$i = 0;
			while(in_array($key, $keys)){
				$i++;
				$key = $data['published'].' '.$i;
			}
			$keys[] = $key;
		}
		$list = array_combine($keys, $list);
		krsort($list);
		$list = array_slice($list, 0, \npdc\config::$showNew);
		$block = '<h3>Recently added or updated</h3>';
		foreach($list as $item){
			$block .= '<hr/>';
			if(array_key_exists('project_id', $item)){
				$type = 'Project';
			} elseif(array_key_exists('dataset_id', $item)){
				$type = 'Dataset';
			} elseif(array_key_exists('publication_id', $item)){
				$type = 'Publication';
			}
			$block .= '<a href="'.BASE_URL.'/'.strtolower($type).'/'.$item['uuid'].'">'.trim($item['title']).'</a> <span class="info"><span class="type">'.$type.'</span> <span class="time">'.date('j F Y H:i', strtotime($item['published'])).'</span></span>';
		}
		
		$this->right = $block;
		$this->json = array_merge([
			'@context' => ['@vocab'=>'http://schema.org/'],
			'@type' => ['Organization'],
			'legalName' => \npdc\config::$siteName,
			'url' => $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL,
			'logo' => $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/img/logo.png'
		], \npdc\config::$organizationSchemaOrg);
		$this->extraHeader .= '<script id="schemaorg" type="application/ld+json">'.json_encode($this->json,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).'</script>';
	}	
}
