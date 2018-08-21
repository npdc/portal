<?php

/**
 * News model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\model;

class News {
	protected $fpdo;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * Get list of news items
	 *
	 * @return array list of news items
	 */
	public function getList(){
		return $this->fpdo
			->form('news')
			->fetchAll();
	}
	
	/**
	 * Get news item by id
	 *
	 * @param integer $id news item id
	 * @return array news item
	 */
	public function getById($id){
		return $this->fpdo
			->from('news', $id)
			->fetch();
	}
	
	/**
	 * Get n news items that haven't expired
	 * 
	 * For use in front page
	 *
	 * @param integer $n number of items to show
	 * @return array news items
	 */
	public function getLatest($n = 1){
		return $this->fpdo
			->from('news')
			->where('published < CURRENT_TIMESTAMP')
			->where('(show_till IS NULL OR show_till > CURRENT_TIMESTAMP)')
			->orderBy('published DESC')
			->limit($n)
			->fetchAll();
	}
	
}