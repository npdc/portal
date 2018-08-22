<?php
/**
 * Main page layout
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

?><!DOCTYPE html>
<html>
	<?php 
		if(!empty($_GET['overlay'])){
			$extraJS .= '<script type="text/javascript">$().ready(function(){openOverlay(\''.BASE_URL.'/'.$_GET['overlay'].'\');});</script>';
		}
		include 'head.tpl.php';
	?>
	<body class="<?=$view->class?> <?=(empty($session->userLevel) || $session->userLevel === NPDC_GUEST ? 'guest' : 'user')?>">
		<div id="overlay"><div class="inner"></div></div>
		<?=NPDC_DEV ? '<div class="debug top">Running in debug mode</div>' : ''?>
		<div id="page">
			<div id="top">
				<div id="search"><form method="post" action="<?=BASE_URL?>/search"><input type="text" placeholder="search term" name="q" /><input type="submit" value="search"/></form></div>
				<?php
				if(\npdc\config::$social['twitter_in_head'] && !empty(\npdc\config::$social['twitter'])){
					echo '<div id="social"> - <a href="https://twitter.com/'.\npdc\config::$social['twitter'].'"><span class="icon-twitter"></span></a></div>';
				}
				?>
				<div id="user"><?php
				if($session->userLevel > NPDC_PUBLIC){
					echo 'You are logged in as <a href="'.BASE_URL.'/account">'.$session->name.'</a> ('.$session->levelDetails[$session->userLevel]['name'].')';
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
				?></div>
			</div>
			<div id="title">
				<a href="<?=BASE_URL?>/"><img src="<?=BASE_URL?>/img/logo.png"  /></a>
				<h1><a href="<?=BASE_URL?>/">Netherlands Polar Data Center</a></h1>
			</div>
			<div id="menu">
				<h4>≡ Menu</h4>
				<?=\npdc\lib\Menu::getMenu($session, $args[0]);?>	
			</div>
			<div id="main">
				<?=isset($view->title) && strlen($view->title) > 0 ? '<h2>'.$view->title.'</h2>' : '';?>
				<?php if(isset($_SESSION['notice'])){
					echo '<div id="notice">'.$_SESSION['notice'].'</div>';
					if(empty($_GET['overlay'])){
						unset($_SESSION['notice']);
					}
				}
				if(property_exists($view, 'canEdit') && $view->canEdit && strpos($url, '/new') === false && strpos($url, '/edit') !== false){
					echo '<a href="'.BASE_URL.'/'.$view->baseUrl.'">&laquo; View</a>';
				}
				?>
				<?=method_exists($view, 'listStatusChanges') && ($view->canEdit ?? false) && !in_array($args[1], ['new', 'new_from_doi']) ? $view->listStatusChanges() : ''?>
				<div class="cols">
					<?=isset($view->left) ? '<div id="left">'.$view->left.'</div>' : ''?>
					<div id="mid">
						<?php
						if(property_exists($view, 'canEdit') && $view->canEdit && strpos($url, '/new') === false && strpos($url, '/edit') === false){
							echo '<div id="tools"><button onclick="openUrl(\''.BASE_URL.'/'.$view->baseUrl.'/edit\')">Edit this page</button></div>';
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
			</div>
			<?php
			if(file_exists(__DIR__.'/footer.tpl.php')){
				require 'footer.tpl.php';
			}
			?>
		</div>
		<?php if(NPDC_DEV){
			echo '<div class="debug bottom">Loading time: '.(microtime(true)-$start).'s<div>';
			var_dump($_SESSION);
			echo '</div></div>';
		}?>
	</body>
</html>