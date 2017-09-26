<?php

/**
 * Form controller
 */

namespace npdc\controller;

require __DIR__.'/../../vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php';
use Symm\Gisconverter, HTMLPurifier_Config, HTMLPurifier;

class Form {
	public $ok = true;
	public $errors = [];
	private $value;
	private $filter;
	private $formId;
	public $form;
	private $formData;
	private $active;
	private $dataType;
	private $adminOverrule;

	private $purifier_allowed = [
		'full'=>null,//allows all
		'default'=>'p,b,a[href],i,u,h4,h5,h6,sup,sub,br,div,*[style], em,strong', 
		'italics'=>'i,em',
		'none'=>''//allows none
	];
	private $purifiers = [];
	
	/** 
	 * The basic constructor
	 * @param string $formId the id of the form used in the $_SESSION
	 * @return object \npdc\controller\Form
	 */
	public function __construct($formId){
		$this->formId = $formId;
		if($_POST['formid'] !== $formId){
			//unset($_SESSION[$formId]['data']);
		}
	}
	/**
	 * get the form from the directory
	 * @param string $form the basename of the form (without folder or .json)
	 * @return object form
	 */
	public function getForm($form){
		$this->form = json_decode(file_get_contents(__DIR__.'/../form/'.$form.'.json'));
		return $this->form;
	}
	
	/**
	 * check submitted values against form definition
	 * @param string $data either post (default) or get
	 */
	public function doCheck($data = 'post'){
		if(is_array($data)){
			$this->dataType = 'array';
			$this->formData = $data;
		} else {
			$this->dataType = 'global';
			$this->formData = $data === 'post' ? $_POST : $_GET;
		}
		
		$this->adminOverrule = array_key_exists('adminoverrule', $this->formData) && $this->formData['adminoverrule'] = 'adminoverrule';
		
		foreach($this->form->fields as $id=>$field){
			if($field->disabled ?? false){
				continue;
			}
			switch($field->type){
				case 'table':
					$this->table($id, $field);
					break;
				case 'fieldset':
					$this->fieldset($id, $field);
					break;
				case 'map':
					$this->map($id, $field);
					break;
				case 'file':
					$this->file($id, $field);
					break;
				default:
					$this->formData[$id] = $this->field($id, $field, $field->required ?? true);
			}
		}
		unset($this->formData['formid']);
		unset($this->formData['submit']);
		foreach($this->formData as $key=>$val){
			if($val === ''){
				$this->formData[$key] = null;
			}
		}
		$_SESSION[$this->formId]['errors'] = $this->errors;
		$_SESSION[$this->formId]['data'] = $this->formData;
		global $session;
		if($session->userLevel >= NPDC_ADMIN && $this->adminOverrule){
			unset($_SESSION[$this->formId]['data']['adminoverrule']);
			$this->ok = true;
		} else {
			$this->ok = count($this->errors) === 0;
		}
	}

	private function purified($value, $allowTags = null){
		$allowTags = $allowTags ?? 'none';
		if(!array_key_exists($allowTags, $this->purifiers)){
			$config = HTMLPurifier_Config::createDefault();
			if(!is_null($this->purifier_allowed[$allowTags])){
				$config->set('HTML.Allowed', $this->purifier_allowed[$allowTags]);
			}
			$this->purifers[$allowTags] = new HTMLPurifier($config);
		}
		if(in_array($allowTags,['full', 'default'])){
			$value = '<p>'.$value.'</p>';
		}
		return str_replace(['<div><br /></div>','div', '<p></p>'], ['','p', ''], $this->purifers[$allowTags]->purify($value));
	}
	
	private function table($id, $field){
		$baseId = $id.'_'.array_keys(get_object_vars($field->fields))[0];
		$c = 0;
		if($this->dataType === 'global'){
			foreach(array_keys($this->formData) as $key){
				if(substr($key, 0, strlen($baseId)) === $baseId){
					if(property_exists($field, 'lookup') && substr($key, -4) === '_new'){
						unset($this->formData[$key]);
					} else {
						$c2 = 0;
						$rowid = substr($key, strlen($baseId));
						foreach($field->fields as $subfieldId=>$subfield){
							$key = $id.'_'.$subfieldId.$rowid;
							if($rowid === '_new'){
								$this->formData[$key.'_'.$c] = $this->formData[$key];
								unset($this->formData[$key]);
								$key = $key.'_'.$c;
							}
							$this->formData[$key] = $this->field($key
								, $subfield
								, $subfield->required ?? true);
							if(!empty($this->formData[$key])){
								$c2++;
							}
						}
						if($c2 === 0){
							foreach($field->fields as $subfieldId=>$subfield){
								$key = $id.'_'.$subfieldId.$rowid.($rowid === '_new' ? '_'.$c : '');
								unset($this->formData[$key]);
								unset($this->errors[$key]);
							}
						} else {
							$c++;
						}
					}
				}
			}
		} else {
			$c = count($this->formData[$id]);
			//TODO implement check on subfields
		}
		if($c === 0 && ($field->required ?? true)){
			$this->addError($id, 'Please provide a value', $field->label.' is required');
		}
		return $c;
	}
	
	private function countActive($id){
		if(!empty($this->formData[$id]) || !empty($_FILES[$id]['tmp_name'])){
			$this->active++;
		}
	}
	
	private function fieldset($id, $field){
		$this->active = 0;
		if($field->multi ?? false){
			$loopId = $id.'_';
			$serials = [];
			foreach (array_keys($this->formData) as $key){
				if(substr($key, 0, strlen($loopId)) === $loopId){
					$serial = substr($key, strlen($loopId), strpos($key, '_', strlen($loopId))-strlen($loopId));
					if($serial === 'new'){
						unset($this->formData[$key]);
					} else {
						$serials[] = $serial;
					}
				}
			}
			$c2 = 0;
			foreach(array_unique($serials) as $serial){
				$c = 0;
				foreach($field->fields as $subfieldId=>$subfield){
					$baseId = $id.'_'.$serial.'_'.$subfieldId;
					switch($subfield->type){
						case 'table':
							if($this->table($baseId, $subfield) > 0){
								$c++;
							}
							break;
						case 'fieldset':
							$this->fieldset($baseId, $subfield);
							if(($subfield->required ?? false) && !array_key_exists($baseId, $this->errors ?? [])) {
								$c++;
							}
							break;
						case 'hidden':
							if(!empty($subfield->value)){
								$this->formData[$baseId] = $this->field($baseId, $subfield, $subfield->required ?? true);
								break;
							}
						default:
							$this->formData[$baseId] = $this->field($baseId, $subfield, $subfield->required ?? true);
							if(!empty($this->formData[$baseId])){
								$c++;
							}
					}
				}
				if($c === 0 && ($this->adminOverrule || (!$field->required ?? false))){
					foreach($this->formData as $key=>$val){
						if(strpos($key, $id.'_'.$serial) !== false){
							unset($this->formData[$key]);
						}
					}
					foreach($this->errors as $key=>$val){
						if(strpos($key, $id.'_'.$serial) !== false){
							unset($this->errors[$key]);
						}
					}
				} else {
					if($c > 0){
						$c2++;
					}
					if($c < ($field->min ?? 0)){
						$this->addError($id.'_'.$serial, 'Not enough fields filled', 'Not enough fields filled for '.$field->label);
					}
					if($c > ($field->max ?? INF)){
						$this->addError($id.'_'.$serial, 'Too many fields filled', 'Too many fields filled for '.$field->label);
					}
				}
			}
			if($field->required && $c2 === 0){
				$this->addError($id, 'A value is required', $field->label.' is required');
			}
			$this->active=$c2;
		} else {
			foreach($field->fields as $subfieldId=>$subfield){
				$baseId = ($field->use_main_id ?? true ? $id.'_' : '').$subfieldId;
				$this->formData[$baseId] = $this->field($baseId, $subfield, $subfield->required ?? true);
				$this->countActive($baseId);
			}
		}
		
		if(($field->required ?? false) && $this->active === 0){
			$this->addError($id, 'Please give a value', $field->label.' is required');
		} elseif($this->active < ($field->min ?? 0)){
			$this->addError($id, 'Not enough fields filled', 'Not enough fields filled for '.$field->label);
		}
		if($this->active > ($field->max ?? INF)){
			$this->addError($id, 'Too many fields filled', 'Too many fields filled for '.$field->label);
		}
		if($this->active === 0 && ($this->adminOverrule || (!$field->required ?? false))){
			foreach($field->fields as $subfieldId=>$subfield){
				$baseId = ($field->use_main_id ?? true ? $id.'_' : '').$subfieldId;
				unset($this->formData[$baseId]);
				unset($this->errors[$baseId]);
			}
		}
	}
	
	private function file($id, $field){
		$this->value = [];
		$this->formData['newFiles'] = [];
		if($this->dataType === 'global'){
			foreach($_FILES[$id]['error'] as $key=>$error){
				switch($error){
					case 4:
						if($field->required ?? true){
							$this->addError($id, 'No file uploaded');
						}
						break;
					case 1:
					case 2:
						$this->addError($id, 'The file '.$_FILES[$id]['name'][$key].' is too large');
						break;
					case 0:
						if(property_exists($field, 'format') && !in_array(mime_content_type($_FILES[$id]['tmp_name'][$key]), explode(',', $field->format))){
							$this->addError($id, 'The filetype '.mime_content_type($_FILES[$id]['tmp_name'][$key]).' of '.$_FILES[$id]['name'][$key].' is not allowed');
						} elseif(property_exists($field, 'maxSize') && filesize($_FILES[$id]['tmp_name'][$key]) > convertToBytes($field->maxSize)) {
							$this->addError($id, 'The file '.$_FILES[$id]['name'][$key].' is too large, it is '.\formatBytes(filesize($_FILES[$id]['tmp_name'][$key])).' where only '.$field->maxSize.' is allowed');
						} else {
							$this->saveFile($field, $id, $key);
						}
						break;
					default:
						$this->addError($id, 'Unspecified error when uploading file '.$_FILES[$id]['name'][$key]);
				}
			}
			if(property_exists($field, 'additionalFields')){
				$loopId = $id.'_'.array_keys(get_object_vars($field->additionalFields))[0];
				foreach(array_keys($this->formData) as $key){
					if(substr($key, 0, strlen($loopId)) === $loopId && strpos($key, '_new') === false){
						$rowid = substr($key, strlen($loopId));
						if(!empty($this->formData[$id.'_id'.$rowid])){
							foreach($field->additionalFields as $subId=>$subField){
								$this->field($id.'_'.$subId.$rowid, $subField, $subField->required ?? true);
							}
							$fileModel = new \npdc\model\File();
							$fileModel->updateFile($this->formData[$id.'_id'.$rowid], [
								'title'=>$this->formData[$id.'_title'.$rowid]
								, 'description'=>$this->formData[$id.'_description'.$rowid]
								, 'default_access'=>$this->formData[$id.'_perms'.$rowid]
							]);
						}
					}
				}
			}
			foreach(array_keys($this->formData) as $key){
				if(substr($key,0,strlen($id)+1) === $id.'_' && substr($key, -4) === '_new'){
					unset($this->formData[$key]);
				}
			}
		}
	}
	
	private function map($id, $field){
		$c = 0;
		$loopId = $id.'_wkt';
		foreach(array_keys($this->formData) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				if(substr($key, -4) === '_new'){
					unset($this->formData[$key]);
				} else {
					if($this->formData[$key] !== ''){
						$decoder = new Gisconverter\Decoders\WKT();
						try {
							$decoder->geomFromText($this->formData[$key]);
						} catch (Gisconverter\Exceptions\OutOfRangeLon $ex) {
							//IGNORE THIS ERROR;
						} catch (Gisconverter\InvalidText $itex) {
							$this->addError($id.substr($key, strrpos($key, '_')), 'The WKT was not formed properly, please select your spatial coverage again');
						} catch (Gisconverter\Exceptions $ex) {
							die('Something went wrong when checking the points');
						}
						$c++;
					} else {
						unset($this->formData[$key]);
					}
				}
			}
		}
		
		if($c === 0 && ($field->required ?? true)){
			$this->addError($id, 'Please provide a value', 'Spatial coverage is required');
		}
	}

	/**
	 * check a field
	 * @param type $id
	 * @param type $field
	 * @param type $required
	 * @return type
	 */
	private function field($id, $field, $required = true){
		$this->value = isset($this->formData[$id]) 
				? $this->formData[$id] 
				: null;
		if(is_array($this->value)) {
			foreach ($this->value as $key => $value) {
				if (empty($value)) {
					unset($this->value[$key]);
				}
			}
			$hasValue = !empty($this->value);
		} else {
			$hasValue = !empty(trim($this->value));
		}
		
		$this->filter = FILTER_DEFAULT;
		if(
				(isset($field->disabled) && $field->disabled)
				|| (isset($field->edit) && $field->edit === false)
				|| ($field->type === 'captcha' && $_SESSION[$this->formId]['captcha'] === true) 
				|| in_array($field->type, ['hint', 'submit']) 
				|| (!$required && !$hasValue)
			){
			//no validation needed
		} elseif(!$hasValue){
			$this->addError($id, 'Please provide a value', $field->label.' is required');
		} elseif(property_exists($field, 'max_length') && strlen($this->value) > $field->max_length){
			$this->addError($id, 'This answer is too long, maximum '.$field->max_length.' allowed', $field->label.' is too long, maximum '.$field->max_length.' allowed');
		} else {
			switch($field->type){
				case 'mail':
					if(!filter_var($this->value, FILTER_VALIDATE_EMAIL)){
						$this->addError($id, 'Please make sure you provide a valid mail address', $this->label.' is not valid');
						$this->filter = FILTER_SANITIZE_EMAIL;
					} else {
						$this->value = strtolower($this->value);
					}
					break;
				case 'captcha':
					if($this->value === $_SESSION[$this->formId]['captcha']){
						$_SESSION[$this->formId]['captcha'] = true;
					} else {
						$this->addError('captcha', 'The answer is not correct', null);
					}
					$this->value = '';
					break;
				case 'date':
					$res = [$this->checkDate($this->formData[$id][0], $field->fuzzy ? 'down' : 'none')];
					if(isset($field->range) && $field->range){
						if($required && empty($res[0])){
							$this->addError($id.' 1', 'The start date you provided is not valid', 'Invalid start date for '.$field->label);
						}
						$res[1] = $this->checkDate($this->formData[$id][1], $field->fuzzy ? 'up' : 'none');
						if(($field->endRequired ?? $required) && empty($res[1])){
							$this->addError($id.' 2', 'The end date you provided is not valid', 'Invalid end date for '.$field->label);
						}
						if(!empty($res[0]) && !empty($res[1]) && $res[0] > $res[1]){
							$this->addError($id.' 2', 'The end date has to be after the start date', 'End date before start date for '.$field->label);
						}
						$this->value = $res;
					} else {
						if($required && empty($res[0])){
							$this->addError($id, 'The date you provided is not valid', 'Invalid date provided for '.$field->label);
						}
						$this->value = $res[0];
					}
					break;
				case 'options':
					if(is_array($this->value)){
						foreach($this->value as $c=>$value){
							if(!array_key_exists_r($value, $field->options)){
								$this->addError($id, 'Illegal value selected', $field->label.' has a illegal value');
								unset($this->value[$c]);
							}
						}
					} else {
						if($this->value === 'na'){
							unset($this->value);
						}
						if($this->value === 'please_select' && $field->required ?? true){
							$this->addError($id, 'Please select a value', $field->label.' is required');
						} elseif(!array_key_exists_r($this->value, $field->options)){
							$this->addError($id, 'Illegal value selected', $field->label.' has a illegal value');
							unset($this->value);
						}
						
					}
					break;
				case 'multitext':
					$this->value = array_filter($this->value);
					break;
			}
		}
		if(empty($this->value)){
			$return = null;
		} elseif(is_array($this->value)){
			$return = $this->value;
			foreach($return as &$value){
				if(!empty($value)){
					$value = $this->purified($value, $field->allowTags);
				}
			}
		} else {
			if($this->filter === FILTER_DEFAULT){
				$return = $this->purified($this->value, $field->allowTags);
			} else {
				$return = filter_var($this->value, $this->filter);
			}
		}
		return $return;
	}
	
	/**
	 * 
	 * @param string $date date in format yyyy[-[m]m[-[d]d]]
	 * @param string $dir direction of rounding of the date, either up (default) or down
	 * @return string formatted date in format yyyy-mm-dd
	 */
	private function checkDate($date, $dir = 'up'){
		switch($dir){
			case 'up':
				$month_default = 12;
				$day_default = 31;
				break;
			case 'down':
				$month_default = 1;
				$day_default = 1;
			case 'none':
				$month_default = 0;
				$day_default = 0;
		}
		$dateParts = explode('-', $date);
		$valid = false;
		if(count($dateParts) === 3){
			list($year, $month, $day) = explode('-', $date);
			$valid = checkdate($month, $day, $year);
		}
		
		if(!$valid){
			$pattern = '/^(?P<year>[0-9]{2,4})([-\/](?P<month>([1-9][0-9])|0?[1-9])([-\/](?P<day>([1-9][0-9])|0?[1-9]))?)?/';
			if(preg_match($pattern, $date, $matches)){
				$year = $matches['year'];
				$month = array_key_exists('month', $matches) 
						? $matches['month'] 
						: $month_default;
				$day = array_key_exists('day', $matches) 
						? $matches['day'] 
						: $day_default;
				if(checkdate($month, $day, $year) || $dir === 'none'){
					$date = $year.'-'.$month.'-'.$day;
				} else {
					$date = null;
					while($day > 28){
						$day--;
						if(checkdate($month, $day, $year)){
							$date = $year.'-'.$month.'-'.$day;
							break;
						}
					}
				}
			} else {
				$date = null;
			}
		}
		return empty($date) ? null : sprintf('%1$04d-%2$02d-%3$02d',$year, $month, $day);
	}
	
	/**
	 * record error
	 * @param string $id field id
	 * @param string $error the error to record
	 */
	private function addError($id, $error, $error2){
		$this->errors[$id] = $this->dataType === 'global' ? $error : $error2;
		$this->ok = false;
	}
	
	private function saveFile($field, $id, $key){
		$location = uniqid().'_'.$_FILES[$id]['name'][$key];
		$dest = $_SERVER['DOCUMENT_ROOT']
			.'/'.\npdc\config::$fileDir
			.'/'.$location;
		if(move_uploaded_file($_FILES[$id]['tmp_name'][$key], $dest)){
			foreach($field->additionalFields ?? [] as $subId=>$subField){
				$this->field($id.'_'.$subId.'_new_'.$key, $subField, $subField->required ?? true);
			}
			$fileModel = new \npdc\model\File();
			$this->formData[$id.'_id_new_'.$key] = $fileModel->insertFile([
				'name'=>$_FILES[$id]['name'][$key]
				, 'location'=>$location
				, 'type'=> mime_content_type($dest)
				, 'size'=>filesize($dest)
				, 'title'=>$this->formData[$id.'_title_new_'.$key]
				, 'description'=>$this->formData[$id.'_description_new_'.$key]
				, 'default_access'=>$this->formData[$id.'_perms_new_'.$key]
				, 'form_id'=>str_replace('_', ':', $this->formId)
			]);
			$this->formData['newFiles'][] = $this->formData[$id.'_id_new_'.$key];
		} else {
			$this->addError($id, 'Error when moving file');
		}
	}
}