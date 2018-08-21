<?php

/**
 * page layout without headers etc
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

?><!DOCTYPE html>
<html>
	<?php 
		$extraCSS = '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/plain.css" />';
		include 'head.tpl.php';
	?>
	<body class="<?=$view->bodyClass?> nomenu <?=$view->class?> <?=(empty($session->userLevel) || $session->userLevel === NPDC_GUEST ? 'guest' : 'user')?>">
		<button onclick="window.parent.closeOverlay();" style="float:right"><?=$view->closeButton ?? 'X'?></button>
		<div id="overlay"><div class="inner"></div></div>
		<?php if(isset($_SESSION['notice'])){
			echo '<div id="notice" style="margin-top: 35px">'.$_SESSION['notice'].'</div>';
			unset($_SESSION['notice']);
		}?>
		<?=isset($view->title) && strlen($view->title) > 0 ? '<h2>'.$view->title.'</h2>' : '';?>
		<div id="mid"><?=isset($_SESSION['errors']) ? '<div class="errors">'.$_SESSION['errors'].'</div>': '' ?><?=$view->mid?></div>
	</body>
</html>
