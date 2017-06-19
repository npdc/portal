<head>
	<title><?=isset($view->title) && strlen($view->title) > 0 ? $view->title.' |' : '';?> <?=\npdc\config::$siteName?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0,maximum-scale=1.0">
	<meta name="author" content="Marten Tacoma, NIOZ, 2017">
	<meta name="application-name" content="NPDC">
	<meta name="page-version" content="<?=APP_VERSION?>">
	<link rel="shortcut icon" type="image/x-icon" href="<?=BASE_URL?>/img/logo200.png">
	<link rel="apple-touch-icon" href="<?=BASE_URL?>/img/logo200.png">
	<?php
	if(NPDC_DEV){
		require 'css_js.php';
		$css[] = 'debug';
		foreach([
			'css'=>['<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/', '.css" />'],
			'js'=>['<script type="text/javascript" src="'.BASE_URL.'/js/', '.js"></script>']
		] as $type=>$code){
			foreach($$type as $file){
				if(is_array($file)){
					$file = $file[0];
				}
				echo $code[0].$file.$code[1];
			}
		}
	} else {
		?>
		<link rel="stylesheet" type="text/css" href="<?=BASE_URL?>/build/css.css?v=<?=APP_BUILD?>" />
		<script type="text/javascript" src="<?=BASE_URL?>/build/js.js?v=<?=APP_BUILD?>"></script>
		<?php
	}
	?>
	<script type="text/javascript">var baseUrl = "<?=BASE_URL?>";var controller = "<?=$controllerName?>";</script>
	<?=$extraJS?>
	<?=$extraCSS?>
</head>