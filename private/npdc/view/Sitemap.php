<?php

/**
 * the view for the sitemap
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Sitemap {
	private $pages = [];
	private $page_last = null;
	private $projects = [];
	private $project_last = null;
	private $datasets = [];
	private $dataset_last = null;
	private $publications = [];
	private $publication_last = null;

	/**
	 * Show the list
	 *
	 * @return void
	 */
	public function showList(){
		$this->getPages();
		$this->getProjects();
		$this->getDatasets();
		$this->getPublications();

		switch(NPDC_OUTPUT){
			case 'xml':
			case 'txt':
				$map = array_merge(
					$this->pages,
					['project'=>['Projects', $this->project_last]],
					$this->projects,
					['dataset'=>['Datasets', $this->dataset_last]],
					$this->datasets,
					['publication'=>['Publications', $this->publication_last]],
					$this->publications
				);
				break;
			default:
				$map = [
					'Pages' => [null,$this->pages],
					'Projects' => [['project', $this->project_last], $this->projects],
					'Data sets' => [['dataset', $this->dataset_last], $this->datasets],
					'Publications' => [['publication', $this->publication_last], $this->publications]
				];
		}

		switch(NPDC_OUTPUT){
			case 'txt':
				header('Content-Type: text/plain');
				foreach($map as $url=>$line){
					echo $this->url($url)."\r\n";
				}
				die();
			
			case 'xml':
				$xml =  new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
				<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> </urlset>');
				foreach($map as $url=>$data){
					$line = $xml->addChild('url');
					$line->addChild('loc', $this->url($url));
					$line->addChild('lastmod', str_replace(' ', 'T', $data[1]).'+01:00');
				}
				header('Content-Type: application/xml');
				$dom = new \DOMDocument('1.0');
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = true;
				$dom->loadXML($xml->asXML());
				echo $dom->saveXML();
				die();

			default:
			foreach($map as $title=>$group){
				$this->title = 'Sitemap';
				$this->mid .= '<h3>'.$title.'</h3><ul>';
				foreach($group[1] as $url=>$data){
					$this->mid .= '<li><a href="'.$this->url($url,false).'">'.$data[0].'</a><span style="font-size: 80%;font-style:italic"> (Updated: '.$data[1].')</span></li>';
				}
				$this->mid .= '</ul>';
			}
		}
	}

	/**
	 * Build url
	 *
	 * @param string $url the last part of the url
	 * @return string full valid url
	 */
	private function url($url, $include_domain = true){
		return ($include_domain ? $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] : '').BASE_URL.'/'.$url;
	}

	/**
	 * Get list of pages
	 *
	 * @return void
	 */
	private function getPages(){
		$model = new \npdc\model\Page();
		foreach($model->getList() as $page){
			if($page['url'] != 'home'){
				$this->pages[$page['url']] = [$page['title'], $page['last_update']];
				$this->page_last = $page['last_update'] > $this->page_last ? $page['last_update'] : $this->page_last;
			}
		}
	}

	/**
	 * Get list of projects
	 *
	 * @return void
	 */
	private function getProjects(){
		$model = new \npdc\model\Project();
		foreach($model->getList() as $project){
			$this->projects['project/'.$project['uuid']] = [$project['title'], $project['published']];
			$this->project_last = $project['published'] > $this->project_last ? $project['published'] : $this->project_last;
		}
	}

	/**
	 * Get list of datasets
	 *
	 * @return void
	 */
	private function getDatasets(){
		$model = new \npdc\model\Dataset();
		foreach($model->getList() as $dataset){
			$this->datasets['dataset/'.$dataset['uuid']] = [$dataset['title'], $dataset['published']];
			$this->dataset_last = $dataset['published'] > $this->dataset_last ? $dataset['published'] : $this->dataset_last;
		}
	}

	/**
	 * Get list of publications
	 *
	 * @return void
	 */
	private function getPublications(){
		$model = new \npdc\model\Publication();
		foreach($model->getList() as $publication){
			$this->publications['publication/'.$publication['uuid']] = [$publication['title'], $publication['published']];
			$this->publication_last = $publication['published'] > $this->publication_last ? $publication['published'] : $this->publication_last;
		}
	}
}