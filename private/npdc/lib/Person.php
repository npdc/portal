<?php

/**
 * helpers for working with persons
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

class Person {
	/**
	 * display a person
	 * @param object $person person details
	 * @param boolean $url link the name to the persons contact page (default: true)
	 * @return string formatted person
	 */
	public function showPerson($person, $url = true){
		$return = '<p>'
			.($url === true
				? '<a href="'.BASE_URL.'/contact/'.$person['person_id'].'">'.$person['name'].'</a>'
				: $person['name'])
			.'<br/><em>'.$person['organization_name'].'</em>';
		if(!empty($person['phone_personal']) && $person['phone_personal_public'] === 'yes'){
			$return	.= '<br/>'.$person['phone_personal'].' (direct)';
		}
		if(!empty($person['phone_secretariat']) && $person['phone_secretariat_public'] === 'yes'){
			$return	.= '<br/>'.$person['phone_secretariat'].' (general)';
		}
		if(!empty($person['phone_mobile']) && $person['phone_mobile_public'] === 'yes'){
			$return	.= '<br/>'.$person['phone_mobile'].' (mobile)';
		}
		$return .= '</p>';
		
		return $return;
		
	}
	
	/**
	 * display an address
	 * @param object $person
	 * @return string
	 */
	public function showAddress($person){
		$return = '';
		$prefix = 'Address';
		
		if(!is_null($person['visiting_address'])){
			$return .= '<div><h4>Visiting address</h4><p>'.nl2br($person['visiting_address']).'</p></div>';
			$prefix = 'Postal address';
		}
		$return .= '<div><h4>'.$prefix.'</h4><p>'.$person['organization_address'].'<br/>'.$person['organization_zip'].' '.$person['organization_city'].'</p></div>';
		return $return;
	}
}