<?php
/**
 * Main page layout
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

?><!DOCTYPE html>
<html lang="en">
	<?php 
		if(!empty($_GET['overlay'])){
			$extraJS .= '<script type="text/javascript">$().ready(function(){openOverlay(\''.BASE_URL.'/'.$_GET['overlay'].'\');});</script>';
		}
		include 'head.tpl.php';
	?>
	<body class="<?=$view->class?> <?=(empty($session->userLevel) || $session->userLevel === NPDC_PUBLIC ? 'guest' : 'user')?>">
		<div id="smallscreen">This site works best on a screen of at least 620 pixels wide</div>
		<div id="overlay"><div class="inner"></div></div>
		<div id="page">
			<div id="head">
				<div>
					<div id="top">
						<div id="search"><form method="post" action="<?=BASE_URL?>/search"><input type="text" placeholder="search term" name="q" /><input type="submit" value="search"/></form></div>
						<div><span id="user"><?php
						if($session->userLevel > NPDC_PUBLIC){
							if(array_key_exists('adminUser', $_SESSION)){
								echo 'You are logged in as admin '.$session->adminName.', and have taken over the rights of <a href="'.BASE_URL.'/account">'.$session->name.'</a> ('.$session->levelDetails[$session->userLevel]['name'].') - <a href="'.BASE_URL.'/?endtakeover">Go back to admin session</a>';
							} else {
								echo 'You are logged in as <a href="'.BASE_URL.'/account">'.$session->name.'</a> ('.$session->levelDetails[$session->userLevel]['name'].')';
							}
							if($session->userLevel >= NPDC_EDITOR){
								if($session->userLevel > NPDC_PUBLIC){
									$unpublished = \npdc\view\Base::checkUnpublished(false);
								}
								echo ' - <a href="#" onclick="openOverlay(\''.BASE_URL.'/overlay/editor\')">Editor tools'
									. ($unpublished > 0 ? '<span class="unpublished">'.$unpublished.'</span>' : '')
									. '</a>';
							}
							echo ' - <a href="?logout">Log out</a>';
						} else {
							if(\npdc\config::$allowRegister){
								echo '<a href="#" onclick="openOverlay(\''.BASE_URL.'/register\')">Create account</a> - ';
							}
							echo '<a href="#" onclick="openOverlay(\''.BASE_URL.'/login\')">Log in</a>';
						}
						?></span>
						<?php
						if(\npdc\config::$social['twitter_in_head'] && !empty(\npdc\config::$social['twitter'])){
							echo '<span id="social"> - <a href="https://twitter.com/'.\npdc\config::$social['twitter'].'"><span class="icon-twitter"></span></a></span>';
						}
						?></div>
					</div>
					<div id="title">
						<a href="<?=BASE_URL?>/"><img src="<?=BASE_URL?>/img/logo.png" alt="NPDC" /></a>
						<h1><a href="<?=BASE_URL?>/"><?=\npdc\config::$siteName?></a></h1>
					</div>
					<div id="toplink">Top</div>
					<div id="menu">
						<h4>≡ Menu</h4>
						<?=\npdc\lib\Menu::getMenu($session, \npdc\lib\Args::get('type'));?>
					</div>
				</div>
			</div>
			<div id="main">
				<?=isset($view->title) && strlen($view->title) > 0 ? '<h2>'.$view->title.'</h2>' : '';?>
				<?php if(isset($_SESSION['notice'])){
					echo '<div id="notice">'.$_SESSION['notice'].'</div>';
					if(empty($_GET['overlay'])){
						unset($_SESSION['notice']);
					}
				}
				if(property_exists($view, 'canEdit') && $view->canEdit && \npdc\lib\Args::get('action')==='edit'){
					echo '<a href="'.BASE_URL.'/'.$view->baseUrl.'">&laquo; View</a>';
				}
				?>
				<?=method_exists($view, 'listStatusChanges') && ($view->canEdit ?? false) && !in_array(\npdc\lib\Args::get('action'), ['new', 'new_from_doi', 'duplicate']) ? $view->listStatusChanges() : ''?>
				<div class="cols">
					<?=isset($view->left) ? '<div id="left">'.$view->left.'</div>' : ''?>
					<div id="mid">
						<?php
						if(property_exists($view, 'canEdit') && $view->canEdit && !\npdc\lib\Args::exists('action')){
							echo '<div id="tools">';
							if(property_exists($view, 'takeover') && $view->takeover){
								echo '<button onclick="openUrl(\''.BASE_URL.'/'.($view->baseEditUrl ?? $view->baseUrl).'/takeover\')">Take over user rights</button> ';
							}
							if(property_exists($view, 'allowDuplicate') && $view->allowDuplicate && $session->userLevel >= NPDC_EDITOR){
								echo '<button onclick="openUrl(\''.BASE_URL.'/'.(\npdc\lib\Args::exists('uuid') ? \npdc\lib\Args::get('type').'/'.\npdc\lib\Args::get('uuid') : $view->baseUrl).'/duplicate\')">Duplicate this page</button> ';
							}
							echo '<button onclick="openUrl(\''.BASE_URL.'/'.($view->baseEditUrl ?? $view->baseUrl).'/edit\')">Edit this page</button></div>';
						}
						?>
						<?=isset($_SESSION['errors']) ? '<div class="errors">'.$_SESSION['errors'].'</div>': '' ?>
						<?=$view->mid?>
					</div>
					<?=isset($view->right) ? '<div id="right">'.$view->right.'</div>' : ''?>
				</div>
				<?php if(isset($view->frontblocks)){
					echo '<div class="cols frontblocks">';
						foreach($view->frontblocks as $id=>$block){
							echo '<div class="'.$id.'">'.$block.'</div>';
						}
					echo '</div>';
				}?>
				<?=isset($view->bottom) ? '<div id="bottom">'.$view->bottom.'</div>' : ''?>
			</div>
			<?php
			if(file_exists(__DIR__.'/footer.tpl.php')){
				require 'footer.tpl.php';
			}
			?>
		</div>
		<?php if(NPDC_DEV){
			echo '<div class="debug bottom">Loading time: '.(microtime(true)-$start).'s<div>';
			var_dump(['args'=>\npdc\lib\Args::getAll(), 'session'=>$_SESSION]);
			echo '</div></div>';
		}?>
	</body>
</html>