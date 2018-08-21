<?php

/**
 * Display of main column of dataset file page
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
include 'dataset/citation.php';
?>
<h4>Files</h4>
<?php

$files = $this->model->getFiles($this->data['dataset_id'], $this->data['dataset_version']);

if(count($files) === 0){
	echo 'No files';
} else {
	$fileModel = new \npdc\model\File();
	echo '<form method="post"><table class="files">';
	$downloadable = 0;
	$public = 0;
	$login = 0;
	$ask = 0;
	foreach($files as $file){
		$checkbox = false;
		$icon = null;
		switch($file['default_access']){
			case 'login':
				if($this->session->userLevel < 1 || $this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($dataset, $this->session->userId)){
					$icon = 'key';
				}
				if($this->session->userLevel > NPDC_PUBLIC){
					$checkbox = true;
					$login++;
				}
				break;
			case 'public':
				$checkbox = true;
				$public++;
				break;
			case 'private':
				if($this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($dataset, $this->session->userId)){
					$icon = 'eye-blocked';
					break;
				} else {
					continue 2;
				}
			case 'restricted':
			default:
				$icon = 'lock';
				$ask++;
		}
		if($checkbox){
			$downloadable++;
		}
		echo '<tr>'
		. '<td>'
			. ($checkbox ? '<input type="checkbox" name="files[]" value="'.$file['file_id'].'" id="file_'.$file['file_id'].'" /><label for="file_'.$file['file_id'].'"><div class="indicator"></div> </label>' : '')
			. (!is_null($icon) ? '<span class="icon-'.$icon.'"></span>' : '')
		. '</td>'
		. '<td><b>'.$file['title'].'</b><br/>'.$file['description'].'<br/><span style="font-size: 80%;font-style:italic">'.$file['name'].', '.$file['type'].', '.formatBytes($file['size']).', downloaded '.$fileModel->getDownloadCount($file['file_id']).' times</td></tr>';
	}
	echo '</table>'
	. '<div class="cols" style="font-size:80%;font-style:italic">'
		. ($login > 0 && ($this->session->userLevel < 1 || $this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($dataset, $this->session->userId)) ? '<div><span class="icon-key"></span> File can be downloaded when logged in</div>' : '')
		. ($restricted > 0 ? '<div><span class="icon-lock"></span> Permission has to be given by the owner of the file</div>' : '')
		. ($this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($dataset, $this->session->userId) ? '<div><span class="icon-eye-blocked"></span> File is hidden for all users except editors and admins</div>' : '')
	. '</div>'
	. ($downloadable > 0 
		? ($this->session->userLevel === 0 ? '<br/><input type="text" name="contact" placeholder="Contact details" /><span class="hint">If you wish you can leave your name and mail address here so we can contact you when a new version of a file becomes available<br/>' : '')
			. '<button type="submit" formaction="files/selected">Download selected files</button> '
			. ($public > 0 ? '<button type="submit" formaction="files/public">Download all public files</button> ' : '')
			. ($this->session->userLevel > NPDC_PUBLIC && $login > 0 ? '<button type="submit" formaction="files/all">Download all available files</button> ' : '') 
		: '')
	. ($this->session->userLevel > NPDC_PUBLIC 
		? '<button type="submit" formaction="files/request">Request access to restricted files</button> '
		: '')
	. '</form>';
}


echo '<h4>Files at other locations</h4>';
$links = $this->model->getLinks($this->data['dataset_id'], $this->data['dataset_version'], true);
if(count($links) === 0){
	echo 'No files at other locations';
} else {
	foreach($links as $link){
		$urls = $this->model->getLinkUrls($link['dataset_link_id'], $this->data['dataset_version']);
		echo '<p'
			.(count($urls) === 1 
			? ' style="margin-bottom:0px"><a href="'.checkurl($urls[0]['url']).'">'.$link['title'].'</a>' 
			: '>'.$link['title'])
			.'<br/>'.$link['description'].'</p>';
		if(count($urls) > 1){
			echo '<ul style="margin-top: 0px;">';
			foreach($urls as $url){
				echo '<li><a href="'.checkurl($url['url']).'">'.checkurl($url['url']).'</a></li>';
			}
			echo '</ul>';
		}
	}
}
echo '<hr><div class="technical">';
if(!empty($this->data['dif_id'])){
	echo '<strong>Dif id:</strong> '.$this->data['dif_id'].' | ';
}
?>
<nobr><strong>UUID:</strong> <?=$this->data['uuid']?></nobr> | <nobr><strong>Version:</strong> <?=$this->data['dataset_version']?></nobr></div>