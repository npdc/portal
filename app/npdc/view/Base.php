<?php

/**
 * helpers for project, dataset and publication
 */

namespace npdc\view;

class Base {
	
	protected $vocab;
	protected $data;
	protected $model;


	public function __construct() {
		$this->vocab = new \npdc\lib\Vocab();
	}

	public static function checkUnpublished($list = true){
		$types = ['project', 'dataset', 'publication'];
		$filter = ['editorOptions'=>['unpublished']];
		$count = 0;
		foreach($types as $type){
			if(\npdc\config::$partEnabled[$type]){
				$model = '\\npdc\\model\\'.ucfirst($type);
				$model = new $model();
				$c = count($model->getList($filter));
				if($c > 0 && $list){
					$_SESSION['notice'] .= (empty($_SESSION['notice']) ? '' : '<br/>').'You have '.$c.' <a href="'.BASE_URL.'/'.$type.'?formid='.$type.'list&amp;editorOptions=unpublished">unpublished '.$type.'s</a>';
				} else {
					$count += $c;
				}
			}
		}
		if(!$list){
			return $count;
		}
	}

	protected function doListStatusChanges($record_id, $record_version){
		$return = '<div id="statusChanges">';
		$statusChanges = $this->model->getStatusChanges($record_id, $record_version);
		if(count($statusChanges) === 0){
			$return .= '<span class="noChanges">No status changes recorded for this version</span>';
		} else {
			foreach($statusChanges as $statusChange){
				$return .= '<div>'.date('Y-m-d', strtotime($statusChange['datetime'])).' || '.$statusChange['name'].' || '.$statusChange['old_state'].' &rarr; '.$statusChange['new_state'].'<div class="comment">'.$statusChange['comment'].'</div></div>';
			}
			if(count($statusChanges) > 1){
				$return .= '<span onclick="$(\'#statusChanges\').toggleClass(\'all\')"><span>Show all '.count($statusChanges).' status changes</span><span>Show only last status change</span></span>';
			}
		}
		$return .= '</div>';
		return $return;
	}
	/**
	 * display a list of items
	 * @param array $data array of data objects
	 * @param array $columns array of columns, id refers to data
	 * @param array $url array with url elements
	 * @param string $class classes that need to be added to the table
	 * @return string table
	 */
	protected function displayTable($class, $data, $columns, $url, $showCount = true, $editTable = false){
		$return = '';
		if($this->session->userLevel >= $this->controller->userLevelAdd && in_array('list', explode(' ', $this->class))){
			$return .= '<div class="add"><a href="'.$url[0].'/new">Add '.$url[0].'</a></div>';
		}
		if(count($data) === 0){
			$return .= 'No results';
		} else {
			if($editTable && $this->session->userLevel > NPDC_PUBLIC && count($this->model->getList(['editorOptions'=>['edit']]))>0){
				$return .= '<div class="screenonly">'.ucfirst($this->controller->name).'s with a striped background are not yet published but only visible to editors of that '.$this->controller->name.' and administrators.<br/>'.ucfirst($this->controller->name).'s with an * on the edit button have a draft version</div>'
					. '<div class="printonly">'.ucfirst($this->controller->name).'s in <i>italics</i> are not yet published but only visible to editors of that '.$this->controller->name.' and administrators.</div>';
			}
            $return .= '<table class="'.$class.'"><thead><tr>';
            foreach($columns as $column){
				if(is_array($column)){
					$column = $column[0];
				}
                $return .= '<td>'.$column.'</td>';
            }
			if($editTable){
				$return .= '<td></td>';
			}
            $return .= '</tr></thead>';
            foreach($data as $item){
                if($url[0] === 'content_type'){
                    $id = strtolower($item['content_type']).'_id';
                    $link = BASE_URL.'/'.strtolower($item['content_type']).'/'.$item[$id];
                } else {
                    $link = BASE_URL.'/'.$url[0].'/'.$item[$url[1]];
                }
                $return .= '<tr onclick="javascript:location.href=\''.$link.'\'" class="link'.($item['record_status'] === 'draft' && $item[$class.'_version'] === 1 ? ' draft': '').'">';
                foreach($columns as $id=>$column){
					if(is_array($column)){
						if($column[1] === 'array'){
							$value = implode(', ', json_decode($item[$id]));
						}
					} else {
						$value = $item[$id];
					}
                    $return .= '<td>'.(strpos($value, '<') === false ? '<a href="'.$link.'">'.$value.'</a>' : $value).'</td>';
                }
				if($editTable){
					$return .= '<td>'.($item['editor'] ? '<button onclick="javascript:event.stopPropagation();location.href=\''.$link.'/edit\'">Edit'.($item['hasDraft'] ? ' *' : '').'</button>' : '').'</td>';
				}
				$return .= '</tr>';
            }
            $return .= '<tfoot><tr>';
            foreach($columns as $column){
                $return .= '<td></td>';
            }
			if($editTable){
				$return .= '<td></td>';
			}
            $return .= '</tr></tfoot></table>';
        }
		
		return $return;
	}
	
	/**
	 * display the filters with controls for showing/hiding on small screen
	 * @param type $id
	 * @return type
	 */
	protected function showFilters($id){
		$formView = new \npdc\view\Form($id);
		list($form, $active) = $formView->create($this->controller->formController->form, true, true);
		return '<h3>Filters '.$active.'</h3><div class="content">'.$form.'</div>';
	}
	
	/**
	 * parse template for part of a page
	 * @param string $tpl basename of the file
	 * @param object $model the model of the view, for getting extra data
	 * @param object $data the base data of the view
	 * @return string the formatted part of the page
	 */
	protected function parseTemplate($tpl){
		ob_start();
		include 'template/'.$tpl.'.tpl.php';
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}
	
	protected function displayField($key, $label, $inline = false){
		$output = $inline ? '<section class="inline">' : '';
		$output .= '<h4>'.$label.'</h4>
	<p>'.$this->data[$key].'</p>';
		$output .= $inline ? '</section>' : '';
		return $output;
	}

	/**
	 * loads the edit page
	 * @param type $pages side menu
	 */
	protected function loadEditPage($pages = null){
		switch($this->controller->display){
			case 'not_allowed':
				if($this->session->userLevel > NPDC_PUBLIC){
					$this->title = 'No access';
					$this->mid = 'You have insufficient privileges to access this page';
				} else {
					$this->title = 'Please login';
					$this->mid = 'Please login<script type="text/javascript" language="javascript">$.ready(openOverlay(\''.BASE_URL.'/login?notice=login\'));</script>';
				}
				break;
			case 'under_review':
				$this->title = 'Editing currently not possible';
				$this->mid = 'There is a version of this page submitted for review, editting is not possible';
				break;
			case 'screen_start':
				header('Location:'. filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT).'/'.array_keys($pages)[0]);
				die();
				break;
			default:
				$this->class = 'edit';
				$formView = new \npdc\view\Form($this->controller->formId);
				$this->mid = $formView->create($this->controller->formController->form);
				if(is_null($pages)){
					//$this->class .= ' nomenu';
				} else {
					$this->loadEditMenu($pages);
				}
		}
	}
	
	/**
	 * loads the side menu for the edit pages
	 * @param type $pages
	 */
	private function loadEditMenu($pages){
		$isNew = $this->args[1] === 'new';
		$this->left = '<ul>';
		$base_url = BASE_URL.'/'.implode('/', array_chunk($this->args, ($this->args[1] === 'new' ? 2 : 3))[0]).'/';
		$cur = !empty($this->args[3]) 
			? $this->args[3]
			: ($isNew && !empty($this->args[2])
				? $this->args[2]
				: 'general');
		foreach($pages as $url=>$page){
			$this->left .= '<li><a href="'.$base_url.$url.'"'
				. ' class="'
					. ($url === $cur ? 'active' : '')
					. ($isNew ? 'disabled' : '')
				. '"'
				. ($isNew ? ' onclick="alert(\'This menu is not available until you saved the first page\');return false;"' : '')
				. '>'.$page.'</a></li>';
			
		}
		$this->left .= '</ul><div class="line"></div>'
			.($isNew ? 'This menu will be activated as soon as you have saved general' : '');
		
	}
}