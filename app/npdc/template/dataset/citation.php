<?php
foreach($this->model->getCitations($this->data['dataset_id'], $this->data['dataset_version'], 'this') as $citation){
	echo '<p>'
		. $citation['creator']
		. ' ('.substr($citation['release_date'],0,4).').'
		. ' <em>'.($citation['title'] ?? $this->data['title']).'.</em>'
		. (!is_null($citation['version']) ? ' ('.$citation['version'].')' : '')
		. (!is_null($citation['release_place']) ? ' '.$citation['release_place'].'.' : '')
		. (!is_null($citation['editor']) ? ' Edited by '.$citation['editor'].'.' : '')
		. (!is_null($citation['publisher']) ? ' Published by '.$citation['publisher'].'.' : '')
		. '</p>';
}