<?php

/**
 * display of forms
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Form {
	private $errorHead = '<div class="error head">There were errors when processing your submission</div>';
	private $adminOverrule = '<div id="adminoverrule"><p><i>As admin you can overrule the checking of a record before it is being saved to the database using the checkbox below. Some fields are required on database level, if you left one of those fields empty your record will not be saved and the site will give a server error (you will not get a message telling what wnet wrong). Please use this function with care!</i></p><input type="checkbox" id="adminoverruleinput" name="adminoverrule" value="adminoverrule"><label for="adminoverruleinput"><div class="indicator"></div>I am aware of the risks and wish to use the admin override when submitting this page.</label></div>';
	private $formId;
	private $active = 0;
	private $required;
	private $collapsible;
	/**
	 * Constructor
	 * 
	 * @param string $formId
	 */
	public function __construct($formId){
		$this->formId = $formId;
	}
	
	/**
	 * display a form
	 * 
	 * @param object $data the form
	 * @param boolean $showErrorHead display error heading or not
	 * @param boolean $give_active give the number of active fields (default: false)
	 * @return mixed returns array with form and number of active fields when $give_active is true, otherwise only returns form
	 */
	public function create($data, $showErrorHead = true, $give_active = false){
		global $session;
		$this->collapsible = $data->collapsible ?? false;
		if(!isset($_SESSION[$this->formId]['errors'])){
			$_SESSION[$this->formId]['errors'] = [];
		}
		$form = '<form method="'.$data->method.'" action="'.$data->action.'" accept-charset="utf-8" '.($data->fileupload ?? false ? 'enctype="multipart/form-data" ' : '').'>'
				.(count($_SESSION[$this->formId]['errors']) > 0 && $showErrorHead 
					? $this->errorHead 
						. ($session->userLevel >= NPDC_ADMIN ? $this->adminOverrule : '')
					: '')
				. $this->hidden('formid', $this->formId);
		$hint = null;
		$hints = [
			'simple'=>'Fields with * are required.',
			'all'=>'All fields are required',
			'group'=>'If a field group is not required fields marked with * are only required to be filled in when when filling in at least one field in the group, in that case all subfields with * in that group should be filled in.',
			'table'=>'When a table is required at least one row should be filled. New rows will be added to a table as soon as you have entered information into a field in the bottom row of a table, you can leave the last row empty before submitting, even if fields are required. When using a row all required subfields have to be filled.'
		];
		foreach($hints as $id=>$txt){
			if(strpos($data->hint, $id) !== false){
				$hint .= ' '.$txt;
			}
		}
		
		if(!empty($hint)){
			$form .= '<div style="clear:both">'.$this->hint($hint).'</div>';
		}
		foreach($data->fields as $id=>$field){
			$form .= $this->field($id, $field);
		}
		$form .= '</form>';
		$_SESSION['olderrors'] = $_SESSION[$this->formId]['errors'];
		unset($_SESSION[$this->formId]['errors']);
		return $give_active 
				? [$form, '('.$this->active.' active)'] 
				: $form;
	}
	
	/**
	 * Select right field renderer
	 *
	 * @param string $id id of the field
	 * @param object $field field details
	 * @param boolean $bare provide only field or with formatting arround it, mainly used for tables
	 * @return void
	 */
	private function field($id, $field, $bare = false){
		if(!isset($field->disabled) || !$field->disabled){
			switch ($field->type){
				case 'submit':
					return $this->submit($field);
					break;
				case 'hint':
					return $this->hint($field->hint, $field->gcmd_url ?? null);
					break;
				case 'hidden':
					return $this->hidden($id, $_SESSION[$this->formId]['data'][$id] ?? $field->value);
					break;
				case 'fieldset':
					return $this->fieldset($id, $field);
					break;
				case 'map':
					return $this->map($id, $field);
					break;
				default:
					return $this->other($id, $field, $bare);
			}
		}
	}
	
	/**
	 * make hidden field
	 * 
	 * @param string $id field id
	 * @param string $value field value
	 * @return string field
	 */
	private function hidden($id, $value){
		return '<input type="hidden" name="'.$id.'" value="'.$value.'" />';
	}

	/**
	 * format a hint
	 * 
	 * @param string $hint the hint to be formatted
	 * @param string $gcmdUrl (optional) url to GCMD documentation
	 * @param string $class (optional) extra class to give to the hint element
	 * @return string formatted hint
	 */
	private function hint($hint, $gcmdUrl = null, $class = null){
		return '<span class="hint '.$class.'">'
			. preg_replace_callback('/\$([a-z]{1,})(\[\'([a-z]{1,})\'\])?/i', function($matches){
					return count($matches) === 2 
						? \npdc\config::${$matches[1]} 
						: \npdc\config::${$matches[1]}[$matches[3]];
				}, $hint)
			. (strlen($hint) === 0 || in_array(substr($hint, -1), ['.', '?']) ? '' : '.')
			. ' '
			. (empty($gcmdUrl) ? '' : '<a href="'.$gcmdUrl.'">GCMD Help</a>')
			. '</span>';
	}
	
	/**
	 * Determine if field (group) has error(s)
	 *
	 * @param string $id field id
	 * @return boolean field has error
	 */
	private function hasError($id){
		$hasError = array_key_exists($id, $_SESSION[$this->formId]['errors']);
		//specific code for date field
		if(array_key_exists($id.' 1', $_SESSION[$this->formId]['errors']) || array_key_exists($id.' 2', $_SESSION[$this->formId]['errors'])){
			$fields = [];
			if(array_key_exists($id.' 1', $_SESSION[$this->formId]['errors'])){
				$fields[] = $_SESSION[$this->formId]['errors'][$id.' 1'];
			}
			if(array_key_exists($id.' 2', $_SESSION[$this->formId]['errors'])){
				$fields[] = $_SESSION[$this->formId]['errors'][$id.' 2'];
			}
			$hasError = true;
			$_SESSION[$this->formId]['errors'][$id] = implode(' / ', $fields);
		}
		return $hasError;
	}
	
	/**
	 * Increments active if field contains a value
	 *
	 * @param string $id field id
	 * @return void
	 */
	private function countActive($id){
		if(isset($_SESSION[$this->formId]['data'][$id]) 
				&& (!is_array($_SESSION[$this->formId]['data'][$id]) 
					|| implode('', $_SESSION[$this->formId]['data'][$id]) !== ''
				)
			){
			$this->active++;
		}
	}
	/**
	 * format a simple text field
	 * 
	 * @param string $id the field id
	 * @param object $field information about the field
	 * @param boolean $bare return only form field (when false more info is returned) (default: false)
	 * @return string the formatted field
	 */
	private function other($id, $field, $bare = false){
		$this->required = !isset($field->required) || $field->required;
		$hasError = $this->hasError($id);
		$this->countActive($id);
		
		$this->style = $hasError ? ' class="error"' : '';
		$this->class = $hasError ? ' error' : '';
		$this->attr = '';
		if(property_exists($field, 'edit') && $field->edit === false){
			$this->style .= ' class="readonly" readonly="readonly"';
			$this->class .= ' readonly';
			$this->attr .= ' readonly="readonly"';
		}
		
		$input = '';
	
		switch($field->type){
			case 'captcha':
				global $session;
				if(isset($_SESSION[$this->formId]['captcha']) 
					&& $_SESSION[$this->formId]['captcha'] === true
					){
					return;
				} elseif($session->userLevel > NPDC_PUBLIC){
					$_SESSION[$this->formId]['captcha'] = true;
					return;
				}
				if(\npdc\config::$useReCaptcha){
					global $extraJS;
					$extraJS .= '<script src="https://www.google.com/recaptcha/api.js"></script>';
					$input .= '<div class="g-recaptcha" data-sitekey="'.\npdc\config::$reCaptcha['siteKey'].'"></div>';
					break;	
				} else {
					$field->hint = 'This is to check if you are human or a bot. <nobr>Case insensitive, only the dark characters</nobr>';
					$field->placeholder = 'Answer';
					$_SESSION[$this->formId]['captcha'] = generateRandomString(5);
					$input = '<div><img src="'.BASE_URL.'/img/captcha.php?id='.$this->formId.'" /></div>';
				}
			case 'text':
			case 'mail':
			case 'number':
			case 'float':
				$input .= '<input '
					. 'type="text" '
					. 'name="'.$id.'" '
					. 'data-field-type="'.$field->type.'" '
					. (property_exists($field, 'max_length') ? 'maxlength='.$field->max_length.' ' : '')
					. (property_exists($field, 'permittedChars') ? 'data-permitted-chars="'.$field->permittedChars.'" ' : '')
					. (property_exists($field, 'mask') ? 'data-inputmask="\'mask\': \''.$field->mask.'\'" ' : '')
					. (property_exists($field, 'maskAlias') ? 'data-inputmask="\'alias\': \''.$field->maskAlias.'\'" ' : '')
					. (property_exists($field, 'placeholder') ? 'placeholder="'.$field->placeholder.'" ' : '')
					. (property_exists($field, 'trigger') ? 'onBlur="javascript:'.$field->trigger.'(\''.$id.'\')" ' : '')
					. 'value="'.(isset($_SESSION[$this->formId]['data'][$id]) 
						? $_SESSION[$this->formId]['data'][$id] 
						: '').'" '
					//. $this->style
					. ' class="'.$this->class.'"'
					. $this->attr
					. '/>';
				break;
			case 'number_with_unit':
				$input .= '<input '
					. 'type="text" '
					. 'name="'.$id.'" '
					. 'data-field-type="'.$field->type.'" '
					. (property_exists($field, 'max_length') ? 'maxlength='.$field->max_length.' ' : '')
					. (property_exists($field, 'permittedChars') ? 'data-permitted-chars="'.$field->permittedChars.'" ' : '')
					. (property_exists($field, 'mask') ? 'data-inputmask="\'mask\': \''.$field->mask.'\'" ' : '')
					. (property_exists($field, 'maskAlias') ? 'data-inputmask="\'alias\': \''.$field->maskAlias.'\'" ' : '')
					. (property_exists($field, 'placeholder') ? 'placeholder="'.$field->placeholder.'" ' : '')
					. 'value="'.(isset($_SESSION[$this->formId]['data'][$id]) 
						? $_SESSION[$this->formId]['data'][$id] 
						: '').'" '
					. ' class="'.$this->class.' number_with_unit"'
					. $this->attr
					. '/>'
					. '<select name="unit_'.$id.'" class="number_with_unit no-select2">';
				foreach($field->units as $unitid=>$unit){
					$input .= '<option value="'.$unitid.'" '.($_SESSION[$this->formId]['data']['unit_'.$id] === $unitid ? 'selected' : '').'>'.$unit.'</option>';
				}
				$input .= '</select>';
				
				break;
			case 'password':
				$input .= '<input '
					. 'type="password" '
					. 'name="'.$id.'" '
					. 'placeholder="'.$field->placeholder.'" '
					. ' class="'.$this->class.' '.($field->readable ?? false ? 'readablepassword' : '').'"'
					. $this->attr
					.'/>';
				if($field->readable ?? false){
					$input .= '<button type="button" class="readablepassword" onclick="$(\'[name='.$id.']\').attr(\'type\', $(\'[name='.$id.']\').attr(\'type\') == \'text\' ? \'password\' : \'text\');return false;">Show/hide</button>';
				}
				break;
				
			case 'textarea':
				$input = $this->textArea($id, $field);
				break;
			
			case 'options':
				$input = $this->optionList($id, $field);
				break;
			
			case 'multitext':
				$input = $this->multitext($id, $field);
				break;
				
			case 'date':
				$input = $this->date($id, $field);
				break;
				
			case 'table':
				$input = $this->table($id, $field);
				break;
			
			case 'lookup':
				$input = $this->lookup($id, $field, $bare);
				break;
			case 'bool':
			case 'checkbox':
				$input = '<input '
					. 'type="checkbox" '
					. 'name="'.$id.'" '
					. 'value="on" '
					. 'id="'.$id.'" '
					. (substr($id, -4) === '_new' ? $field->default ?? null : $_SESSION[$this->formId]['data'][$id] ? 'checked ' : '')
					. '>'
					. '<label for="'.$id.'"><div class="indicator"></div>'
					. (isset($field->sideLabel) ? $field->sideLabel : '') 
					. '</label>';
				break;
			
			case 'file':
				if(is_array($_SESSION[$this->formId]['data'][$id])){
					$input = '<div id="uploaded_'.$id.'">You already uploaded "'.$_SESSION[$this->formId]['data'][$id][0].'" - <a href="javascript:removeFile(\''.$id.'\')">remove</a></div><input type="hidden" name="keepfile_'.$id.'" value="1" />';
					$disabled = 'disabled="disabled" ';
				} else {
					$input = '';
					$disabled = '';
				}
				$maxUploadSize = min(convertToBytes($field->maxSize ?? '9PB'), maxFileUpload());
				$input .= 'Max upload size: '.formatBytes($maxUploadSize)
					. '<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxUploadSize.'" >';
				
				$input .= '<input '
					. 'type="file" '
					. 'name="'.$id.($field->multiple ?? false ? '[]' : '').'" '
					. $disabled
					. (property_exists($field, 'format') ? 'accept="'.$field->format.'" ' : '')
					. ($field->multiple ?? false ? 'multiple ' : '')
					. ' class="'.$this->class.'"'
					. $this->attr
					. '/>';
				if(property_exists($field, 'additionalFields')){
					//TODO, oplossen dat regels met n in id ook zichtbaar blijven
					$dataKeys = array_keys($_SESSION[$this->formId]['data']);
					$errorKeys = array_keys($_SESSION[$this->formId]['errors']);
					$max = 0;
					foreach(preg_grep('/'.$id.'_(\w+)_n_(\d+)/i', $dataKeys) as $key){
						$rowId = intval(substr($key, strrpos($key, '_')+1));
						$max = $rowId>=$max ? $rowId+1 : $max;
					}
					foreach($dataKeys as &$key){
						if(strpos($key, '_new_') !== false){
							$key = substr($key, 0, strrpos($key, '_')-2).'_'.(intval(substr($key, strrpos($key, '_')+1))+$max);
						}
						unset($key);
					}
					foreach($errorKeys as &$key){
						if(strpos($key, '_new_') !== false){
							$key = substr($key, 0, strrpos($key, '_')-2).'_'.(intval(substr($key, strrpos($key, '_')+1))+$max);
						}
						unset($key);
					}
					$_SESSION[$this->formId]['data'] = array_combine($dataKeys, $_SESSION[$this->formId]['data']);
					$_SESSION[$this->formId]['errors'] = array_combine($errorKeys, $_SESSION[$this->formId]['errors']);
					$input .= $this->table($id, $field);
					$input .= $this->hint($field->additionalHint);
				}
				break;
			default:
				$input = 'Can\'t render field type '.$field->type;
		}
		
		$return = '';
		if(!$bare){
			if(property_exists($field, 'label')){
				$return = '<h4 class="label'
						.($hasError 
							? ' error' 
							: '')
					.'">'.$field->label
						.($this->required 
							? '*' 
							: '')
					.'</h4>';
				if($hasError){
					$return .= '<span class="error">'.$_SESSION[$this->formId]['errors'][$id].'</span>';
				}
			}
			if(isset($field->hint)){
				$return .= $this->hint($field->hint, $field->gcmd_url);
			}
			if(($field->allowTags ?? false) && $field->type !== 'textarea'){
				$return .= $this->hint('You can use italics by putting the text you want to be italics between &lt;i&gt; and &lt;/i&gt; (e.g. for scientific names)');
			}
		}
		$return .= $input;
		return $return;
	}
	
	/**
	 * Format a text area
	 *
	 * @param string $id field id
	 * @param object $field field data
	 * @return string formatted text area
	 */
	private function textArea($id, $field){
		$input .= '<textarea rows="'
			.(isset($field->rows) ? $field->rows : 6)
			.'" '
			. (property_exists($field, 'max_length') ? 'maxlength='.$field->max_length.' ' : '')
			. (property_exists($field, 'permittedChars') ? 'data-permitted-chars="'.$field->permittedChars.'" ' : '')
			. 'placeholder="'.$field->placeholder.($field->hasSuggestions ? ' You can click a suggestion below to fill it in and edit it to your needs or provide your own value' : '').'" name="'.$id.'" '. ' class="'.$this->class.'"'
			. $this->attr
			. ($field->allowTags ?? false ? 'data-tags="'.$field->allowTags.'"' : '')
			. '>'
			.(isset($_SESSION[$this->formId]['data'][$id]) ? $_SESSION[$this->formId]['data'][$id] : '')
			.'</textarea>';
		if($field->hasSuggestions ?? false){
			$input .= '<ul class="suggestions'.(isset($_SESSION[$this->formId]['data'][$id]) ? '' : ' show').'" data-target="'.$id.'" ><li>Suggestions (Click a suggestion to put it in the field above, click this header to show/hide the suggestions)</li>';
			$model = new \npdc\model\Suggestion();
			foreach($model->getList($id) as $suggestion){
				$input .= '<li>'. htmlentities($suggestion['suggestion']).'</li>';
			}
			$input .= '</ul>';
		}
		return $input;
	}
	
	/**
	 * Format lookup field
	 *
	 * @param string $id field id
	 * @param object $field field details
	 * @return string formatted lookup field
	 */
	private function lookup($id, $field){
		if(property_exists($field, 'vocab')){
			$input = '<table class="lookuptable single" data-base-url="'.BASE_URL.'" data-lookup-url="'.$field->vocab.'"><tr><td><input '
					. 'type="hidden" '
					. 'name="'.$id.'_id" '
					. (isset($_SESSION[$this->formId]['data'][$id.'_id']) 
						? 'value="'.$_SESSION[$this->formId]['data'][$id.'_id'].'" ' : '')
			. '>';
		} 
		$input .= '<input '
				. 'type="text" '
				. 'name="'.$id.'" '
				. 'placeholder="'.$field->placeholder.'" '
				. (isset($_SESSION[$this->formId]['data'][$id]) 
					? 'value="'.$_SESSION[$this->formId]['data'][$id].'" readonly="readonly" class="'.$this->class.' readonly"'
					: ' class="'.$this->class.'"') 
				. (property_exists($field, 'onSubmit') ? ' data-onsubmit="'.$field->onSubmit.'"' : '')
				. $this->attr
				. ' autocomplete="off"/>';
		if(property_exists($field, 'vocab')){
			$input .= '</td></tr></table>';
		}
		return $input;
	}
	
	/**
	 * Format table with fields
	 *
	 * @param string $id table id
	 * @param object $field table details (including fields that should be in table)
	 * @return string formatted table
	 */
	private function table($id, $field){
		if($field->required ?? true){
			$input = $this->hint('At least one row should be filled, columns marked with * should be filled in all used rows. The last row can be left empty as long as there are filled rows above it');
		} elseif(!property_exists($field, 'additionalFields')) {
			$input = $this->hint('You are not required to enter a value. If you do fill a field all columns marked with * should be filled in that row');
		}
		if(property_exists($field, 'lookup')){
			$input .= '<table class="lookuptable" data-base-url="'.BASE_URL.'" data-source-field="'.$field->lookup->sourceField.'" data-target-field="'.$field->lookup->targetField.'" data-lookup-url="'.$field->lookup->lookupUrl.'"'
				.(property_exists($field->lookup, 'newUrl') ? ' data-new-url="'.BASE_URL.'/'.$field->lookup->newUrl.'"' : '');
		} else {
			$input .= '<table class="multivalue'.(property_exists($field, 'additionalFields') ? ' noAdd':'').'"';
		}
		$input .= ' data-sortable="'. ($field->noSort ?? false ? 'false' : 'true').'" data-n-label="'.$field->nLabel.'" ><colgroup><col><col>';
		foreach($field->fields ?? $field->additionalFields as $subfield){
			if($subfield->type !== 'hidden'){
				$input .= '<col>';
			}
		}
		
		$input .= '</colgroup><thead><tr><td colspan="2"></td>';
		foreach($field->fields ?? $field->additionalFields as $subfield){
			if($subfield->type !== 'hidden'){
				$input .= '<td>'.$subfield->label.($subfield->required ?? $subfield->type !== 'checkbox' ? '*' : '').'</td>';
			}
		}
		$input .= '</tr><tr><td colspan="2"></td>';
		foreach($field->fields ?? $field->additionalFields as $subfield){
			if($subfield->type !== 'hidden'){
				$input .= '<td>'.$this->hint($subfield->hint, $subfield->gcmd_url).'</td>';
			}
		}
		$input .= '</tr></thead><tbody>';
		if(is_array($_SESSION[$this->formId]['data']) && !array_key_exists($id, $_SESSION[$this->formId]['data'])){
			$loopId = $id.'_'.array_keys(get_object_vars($field->fields ?? $field->additionalFields))[0];
			foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
				if(substr($key, 0, strlen($loopId)) === $loopId){
					$rowid = substr($key, strlen($loopId));
					$input .= '<tr id="'.$id.'_row'.$rowid.'"><td>≡</td><td>x</td>';
					if(!property_exists($field, 'lookup') && !property_exists($field, 'additionalFields')){
						$input .= '<td onclick="cloneLine(this);">+</td>';
					}

					foreach($field->fields ?? $field->additionalFields as $subid=>$subfield){
						if(property_exists($subfield, 'placeholder') && ($subfield->required || true)){
							$subfield->placeholder .= '*';
						}
						if($subfield->type === 'hidden'){
							$input .= $this->hidden($id.'_'.$subid.$rowid, $_SESSION[$this->formId]['data'][$id.'_'.$subid.$rowid]);
						} else {
							$input .= '<td '. (property_exists($subfield, 'freeText') ? 'data-freetext="'.$subfield->freeText.'" ' :'')
							.'>'
								. $this->field($id.'_'.$subid.$rowid, $subfield, true)
								.'</td>';
						}
					}
					$input .= '</tr>'."\r\n";
				}
			}
		}
		
		$_SESSION[$this->formId]['data'][$id]['new'] = [];
		foreach($_SESSION[$this->formId]['data'][$id] as $rowid=>$row){
			$input .= '<tr id="'.$id.'_row_'.$rowid.'"><td>≡</td><td>x</td>';
			if(!property_exists($field, 'lookup') && !property_exists($field, 'additionalFields')){
				$input .= '<td onclick="cloneLine(this);">+</td>';
			}	

			foreach($field->fields ?? $field->additionalFields as $subid=>$subfield){
				$_SESSION[$this->formId]['data'][$id.'_'.$subid.'_'.$rowid] = $row[$subid] ?? null;
				if($subfield->type === 'hidden'){
					$input .= $this->hidden($id.'_'.$subid.'_'.$rowid, $row[$subid] ?? null);
				} else {
					$input .= '<td '. (property_exists($subfield, 'freeText') ? 'data-freetext="'.$subfield->freeText.'" ' :'')
					.'>'.$this->field($id.'_'.$subid.'_'.$rowid, $subfield, true).'</td>';
				}

			}
			$input .= '</tr>'."\r\n";
		}

		$input .= '</tbody><tfoot><tr><td colspan="2"></td>';
		foreach($field->fields ?? $field->additionalFields as $subfield){
			if($subfield->type !== 'hidden'){
				$input .= '<td></td>';
			}
		}
		$input .= '</tr></tfoot></table>';
		$this->required = $field->required ?? true;
		return $input;
	}
	
	/**
	 * Format option list (either select, radio or checkbox)
	 *
	 * @param string $id field id
	 * @param object $field field details
	 * @return string formatted field
	 */
	private function optionList($id, $field){
		$multiple = isset($field->multiple) ? $field->multiple : true;
		if(!empty($_SESSION[$this->formId]['data'][$id]) && !is_array($_SESSION[$this->formId]['data'][$id])){
			$_SESSION[$this->formId]['data'][$id] = [$_SESSION[$this->formId]['data'][$id]];
		}
		if(empty($_SESSION[$this->formId]['data'][$id]) && property_exists($field, 'default')){
			$_SESSION[$this->formId]['data'][$id] = [$field->default];
		}
		$fieldName = $id.($multiple ? '[]' : '');
		$showN = isset($field->showN) ? $field->showN : 5;
		if(!isset($_SESSION[$this->formId]['data'][$id])){
			$_SESSION[$this->formId]['data'][$id] = [];
		}
		$nOptions = count(is_array($field->options) 
				? $field->options 
				: get_object_vars($field->options));
		if((isset($field->asList) && $field->asList) || $nOptions > $showN){
			//display as select
			$input = '<select name="'.$fieldName.'" '
				.($multiple
					? 'multiple size="'.$showN.'" ' 
					: '')
				.(property_exists($field, 'newUrl') ? 'data-new-url="'.BASE_URL.'/add/'.$field->newUrl.'" ' : '')
				.(property_exists($field, 'ajaxUrl') ? 'data-ajax-url="'.BASE_URL.'/'.$field->ajaxUrl.'" ' : '')
				. ' class="'.$this->class. ($field->select2 ?? true ? '' : ' no-select2').'"'
				. $this->attr
				.' style="width:100%">';
			if($multiple){
				$input .= $this->required ? '<option value="please_select">=== Select one or more options by typing or clicking in this field===</option>' : '';
			} else {
				$input .= '<option value="please_select">=== '.'Please select an option'.' ===</option>';
			}
			if($multiple && substr($_SESSION[$this->formId]['data'][$id][0], 0, 1) === '['){
				$str = json_decode($_SESSION[$this->formId]['data'][$id][0]);
				if(json_last_error() === JSON_ERROR_NONE){
					$_SESSION[$this->formId]['data'][$id] = $str;
				}
			}
			foreach($field->options as $option_id=>$label){
				if(is_array($label)){
					$input .= '<optgroup label="'.$option_id.'">';
					foreach($label as $option_id=>$label){
						$input .= '<option value="'.$option_id.'" title="'.$label.'"'
							.(in_array($option_id, $_SESSION[$this->formId]['data'][$id]) 
								? ' selected' 
								: '')
							.'>'.$label.'</option>';
					}
					$input .= '</optgroup>';
				} else {
					if(strlen($label) > ($field->maxOptionLength ?? INF) && false){
						$a = strrpos($label, '(');
						if($a !== false){
							$abrLen = strlen($label)-$a;
							$remain = $field->maxOptionLength - $abrLen - 5;
							$b = strrpos($label, ' ', $remain-strlen($label));

							$label = substr($label, 0, $b).' ... '.substr($label, $a);
						} else {
							$remain = $field->maxOptionLength - 4;
							$b = strrpos($label, ' ', $remain-strlen($label));

							$label = substr($label, 0, $b).' ...';
						}
					}
					if(!property_exists($field, 'ajaxUrl') || (is_array($_SESSION[$this->formId]['data'][$id]) && in_array($option_id, $_SESSION[$this->formId]['data'][$id]) )){
						$input .= '<option value="'.$option_id.'" title="'.$label.'"'
							.(is_array($_SESSION[$this->formId]['data'][$id]) && in_array($option_id, $_SESSION[$this->formId]['data'][$id]) 
								? ' selected' 
								: '')
							.'>'.$label.'</option>';
					}
				}
			}
			$input .= $field->select2 ?? true ? ' <optgroup label=""></optgroup></select>' : '';
			if($multiple){
				$input .= $this->hint('You can select multiple options using the ctrl-key (cmd on OS X)', null, 'no_select2');
			}
		} else {
			$type = $multiple ? 'checkbox' : 'radio';
			$input = '<div class="cols">';
			foreach($field->options as $option_id=>$label){
				$input .= '<div><input name="'.$fieldName.'" '
					. 'value="'.$option_id.'" '
					. 'type="'.$type.'" '
					. 'id="'.$fieldName.'_'.$option_id.'" '
					.(in_array($option_id, $_SESSION[$this->formId]['data'][$id] ?? []) 
						? ' checked' 
						:'')
					.'><label for="'.$fieldName.'_'.$option_id.'"> <div class="indicator"></div>'.$label.'</label></div>';
			}
			$input .= '</div>';
		}
		return $input;
	}
	
	/**
	 * Multitext field
	 * 
	 * Field that allows multiple text values, used for tags/keywords
	 *
	 * @param string $id field id
	 * @param object $field field details
	 * @return string formatted field
	 */
	private function multiText($id, $field){
		$input = '<div class="multitext"><div class="values">';
		foreach($_SESSION[$this->formId]['data'][$id] ?? [] as $value){
			if(trim($value) !== ''){
				$input .= '<span><input type="hidden" name="'.$id.'[]" value="'.$value.'" />'.$value.'<span class="delete">x</span></span> ';
			}
		}
		$input .= '</div><div style="clear:both"><input type="text" name="'.$id.'[]" placeholder="'.$field->placeholder.' (submit either with enter, tab, comma or the add button, pasting multiple comma-separated values and submitting using one of those keys is also possible)" autocomplete="off"><button type="button">Add</button></div></div>';
		return $input;
	}
	
	/**
	 * Format fieldset
	 * 
	 * @param string $id fieldset id
	 * @param object $fieldset fieldset details
	 * @return string formatted fieldset
	 */
	private function fieldset($id, $fieldset){
		if(property_exists($fieldset, 'multi') && $fieldset->multi === true){
			$return = (empty($fieldset->name) ? '' : '<a name="'.$fieldset->name.'"></a>').(substr($id, -4) === '_new' || (!$this->collapsible && !$fieldset->collapsible) ? '' : '<h4 class="fieldset collapsible">'.$fieldset->label.((property_exists($fieldset, 'required') && $fieldset->required) ? '*' : '').'</h4>');
			$nrs = [];
			$loopId = $id.'_';
			foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
				if(substr($key, 0, strlen($loopId)) === $loopId){
					$nrs[] = substr($key, strlen($loopId), strpos($key, '_', strlen($loopId))-strlen($loopId));
				}
			}
			$nrs = array_diff(array_unique($nrs), ['new']);
			$nrs[] = 'new';
			
			$fieldset2 = clone($fieldset);
			$fieldset2->multi = 'repeatable';
	
			foreach($nrs as $nr){
				$this->nr = $nr;
				$return .= $this->fieldset($id.'_'.$nr, $fieldset2);
			}
		} else {
			$hasError = $this->hasError($id);
			$return .= '<fieldset id="'.$id.'"';
			$multi = property_exists($fieldset, 'multi') && $fieldset->multi === 'repeatable';
			if(property_exists($fieldset, 'min')){
				$return .= ' data-min='.$fieldset->min;
			}
			if(property_exists($fieldset, 'max')){
				$return .= ' data-max='.$fieldset->max;
			}
			if($multi){
				$return .= ' data-repeatable=true';
			}
			if(strpos($id, '_new') !== false){
				$return .= ' class="new"';
			}
			$return .= ($hasError ? ' class="error"' : '')
			. '><legend'.($hasError ? ' class="error"' : '').'>'.$fieldset->label.((property_exists($fieldset, 'min') && $fieldset->min>0) || (property_exists($fieldset, 'required') && $fieldset->required) ? '*' : '').'</legend>'
				. ($hasError ? '<span class="error">'.$_SESSION[$this->formId]['errors'][$id].'</span> - ' : '')
				.(property_exists($fieldset, 'hint') ? $this->hint($fieldset->hint, $fieldset->gcmd_url) : '');
			if(property_exists($fieldset, 'min') && property_exists($fieldset, 'max')) {
				if($fieldset->min === $fieldset->max){
					$return .= $this->hint('Please fill in exactly '.$fieldset->min.' field'.($fieldset->min !== 1 ? 's' : ''));
				} else {
					$return .= $this->hint('Please fill in '.$fieldset->min.' to '.$fieldset->max.' fields');
				}
			} elseif(property_exists($fieldset, 'min')){
				$return .= $this->hint('Please fill in at least '.$fieldset->min.' field'.($fieldset->min !== 1 ? 's' : ''));
			} elseif(property_exists($fieldset, 'max')){
				$return .= $this->hint('Please fill in at maximum '.$fieldset->max.' field'.($fieldset->max !== 1 ? 's' : ''));
			}
			foreach($fieldset->fields as $subid=>$field){
				$return .= $this->field(($fieldset->use_main_id ?? true ? $id.'_' : '').$subid, $field);
			}
			$return .= '</fieldset>';
		}
		return $return;
	}
	
	/**
	 * Format date field
	 * 
	 * Allows for data range as well
	 *
	 * @param string $id field id
	 * @param object $field field details
	 * @return string formatted field
	 */
	private function date($id, $field){
		$format = isset($field->format) 
			? $field->format 
			: (
				isset($field->fuzzy) && $field->fuzzy === true 
					? 'yyyy[-mm[-dd]]' 
					: 'yyyy-mm-dd'
			);
		$field->hint .= ' (format: '.$format.')';
		
		$input = '<div class="clearfix"></div><div class="datefield">';
		$isRange = isset($field->range) ? $field->range : true;
		$values = isset($_SESSION[$this->formId]['data'][$id]) 
				? (is_array($_SESSION[$this->formId]['data'][$id]) ? $_SESSION[$this->formId]['data'][$id] : [$_SESSION[$this->formId]['data'][$id]])
				: [];

		if($isRange){
			$input .= '<span class="sublabel">Start</span>';
		}
		$input .= '<input type="text" '
				. 'name="'.$id.'[]" '
				. 'id="'.$id.'_start" '
				. 'placeholder="'.$format.'" '
				. 'data-inputmask="\'alias\': \''.($format === 'yyyy' ? 'y' : $format).'\', \'yearrange\': {\'minyear\': 0, \'maxyear\': 9999 }" '
				. 'value="'.(isset($values[0]) ? str_replace('-00', '', $values[0]) : '').'" '
				. ((array_key_exists($id, $_SESSION[$this->formId]['errors']) && !array_key_exists($id.' 2', $_SESSION[$this->formId]['errors'])) || array_key_exists($id.' 1', $_SESSION[$this->formId]['errors'])
					? $this->style 
					: '')
				. '></div>';
		if($isRange){
			$input .= '<div class="datefield"><span class="sublabel">End</span>';
			$input .= '<input '
					. 'type="text" '
					. 'name="'.$id.'[]" '
					. 'id="'.$id.'" '
					. 'placeholder="'.$format.'" '
					. 'data-inputmask="\'alias\': \''.($format === 'yyyy' ? 'y' : $format).'\'" '
					. 'value="'.(isset($values[1]) 
						? str_replace('-00', '', $values[1])
						: '').'" '
					. ((array_key_exists($id, $_SESSION[$this->formId]['errors']) && !array_key_exists($id.' 1', $_SESSION[$this->formId]['errors'])) || array_key_exists($id.' 2', $_SESSION[$this->formId]['errors']) 
						? $this->style 
						: '')
					. '></div>';
		}
		return $input;
	}

	/**
	 * create a submit button
	 * 
	 * @param object $field Field data
	 * @return string the formatted button
	 */
	private function submit($field){
		global $session;
		$return = '<h4 class="label empty"></h4>'
				. ($session->userLevel >= NPDC_ADMIN && $_SESSION[$this->formId]['data']['record_status'] === 'published' ? '<input type="checkbox" name="rev" value="minor" id="minorRev"> <label for="minorRev"><div class="indicator"></div>This is a minor revision, don\'t create a new version</label><br/>' : '')
				. '<input type="submit" value="'.$field->value.'"'
				. ($field->includeNext ?? false ? 'class="hasGotoNext" ' : '')
				. '>';
		if(isset($field->resetLabel)){
			$return .= ' <button '
					. 'class="reset" '
					. 'type="button" '
					. 'onclick="location.href=\''.BASE_URL.'/'.$field->resetAction.'\'"'
					. '>'.$field->resetLabel.'</button>';
		}
		if($field->includeNext ?? false){
			$return .= ' <input id="gotoNext" type="hidden" name="gotoNext" value="0" /><button '
					. 'type="button" '
					. 'class="gotoNext" '
					. 'onclick="$(\'#gotoNext\').val(1);$(this).parents(\'form\').submit();" '
					. '>Save and go to next page</button>';	
		}
		$return .= isset($field->hint) 
				? '<span class="hint inline">'.$field->hint.'</span>' 
				: '';
		return $return;
	}
	
	/**
	 * Location input (including map)
	 *
	 * @param string $id field id
	 * @param object $field field details
	 * @return string formatted field
	 */
	private function map($id, $field){
		$nrs = [];
		$loopId = $id.'_'.array_keys(get_object_vars($field->fields))[1];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$nrs[] = substr($key, strlen($loopId)+1);
			}
		}
		if(!in_array('new', $nrs)){
			$nrs[] = 'new';
		}
		if($this->hasError($id)){
			$_SESSION[$this->formId]['errors'][$id.'_new'] = $_SESSION[$this->formId]['errors'][$id];
		}
		$return = substr($id, -4) === '_new' || !$this->collapsible ? '' : '<h4 class="fieldset collapsible">'.$field->label.((property_exists($field, 'required') && $field->required) ? '*' : '').'</h4>';
		
		foreach($nrs as $nr){
			$hasError = $this->hasError($id.'_'.$nr);
			$return .= '<fieldset id="'.$id.'_'.$nr.'"';
			if(property_exists($field, 'min')){
				$return .= ' data-min='.$field->min;
			}
			if(property_exists($field, 'max')){
				$return .= ' data-max='.$field->max;
			}
			if(property_exists($field, 'multi') && $field->multi){
				$return .= ' data-repeatable=true';
			}
			if(strpos($nr, 'new') !== false){
				$return .= ' class="new"';
			}
			$return .= ($hasError ? ' class="error"' : '')
			. ' data-map=true><legend>'.$field->label.((property_exists($field, 'min') && $field->min>0) || (property_exists($field, 'required') && $field->required) ? '*' : '').'</legend>'
				. (property_exists($field, 'hint') ? $this->hint($field->hint, ($field->gcmd_url ?? null)) : '')
				. ($hasError ? '<span class="error">'.$_SESSION[$this->formId]['errors'][$id.'_'.$nr].'</span>' : '');
			$return .= '<div class="map">';
			$return .= '<div id="mapContainer_'.$nr.'" class="mapContainer"></div>';
			$return .= '<div>';
			foreach($field->fields as $subid=>$subfield){
				if($subfield->type === 'group'){
					$return .= '<div id="'.$id.'_'.$subid.'_'.$nr.'">';
					foreach($subfield->fields as $subid=>$subfield){
						if($subfield->type !== 'hint'){
							$return .= 	'<h4 class="label">'.$subfield->label.'</h4>';
						}
						$return .= $this->field($id.'_'.$subid.'_'.$nr, $subfield, true);
					}
					$return .= '</div>';
				} else {
					$oldoptions = $subfield->options;
					$olddefault = $subfield->default;
						
					if($subid === 'type'){
						$newoptions = [];
						foreach($subfield->options as $key=>$option){
							$newoptions[$id.'_'.$key.'_'.$nr] = $option;
						}
						$subfield->options = $newoptions;
						if(property_exists($subfield, 'default')){
							$subfield->default = $id.'_'.$subfield->default.'_'.$nr;
						}
					}
					if($subfield->type === 'hint' || $subfield->type === 'hidden'){
						$return .= $this->field($id.'_'.$subid.'_'.$nr, $subfield, true);
					} else {
						$return .= 	'<div><h4 class="label">'.$subfield->label.'</h4>'.(property_exists($subfield, 'hint') && !empty($subfield->hint) ? $this->hint($subfield->hint) : '').$this->field($id.'_'.$subid.'_'.$nr, $subfield, true).'</div>';
					}
					$subfield->options = $oldoptions;
					$subfield->default = $olddefault;
						
				}
			}
			$return .= '</div></div></fieldset>';
		}
		return $return;
	}
}