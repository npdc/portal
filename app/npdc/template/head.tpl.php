<head>
	<title><?=isset($view->title) && strlen($view->title) > 0 ? $view->title.' |' : '';?> <?=\npdc\config::$siteName?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0,maximum-scale=1.0" />
	<meta name="author" content="Marten Tacoma, NIOZ, 2017" />
	<meta name="application-name" content="NPDC" />
	<meta name="page-version" content="<?=APP_VERSION?>" />
	<link rel="shortcut icon" type="image/x-icon" href="<?=BASE_URL?>/img/logo.png" />
	<link rel="apple-touch-icon" href="<?=BASE_URL?>/img/logo.png" />
	<?php
	$css = [
		'ol',
		['select2', '.min'],
		['icomoon', '.min'],
		['style', '.min']
	];
	$js = [
		'external/jquery-2.2.3.min',
		'external/jquery-ui.min',
		'external/jquery.nicescroll.min',
		'external/select2.full.min',
		'external/jquery.inputmask.bundle.min',
		'external/ol',
		['npdc', '.min']
	];
	if(NPDC_DEV){
		$css[] = 'debug';
	}
	foreach([
		'css'=>['<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/', '.css?'.APP_BUILD.'" />'],
		'js'=>['<script type="text/javascript" src="'.BASE_URL.'/js/', '.js?'.APP_BUILD.'"></script>']
	] as $type=>$code){
		foreach($$type as $file){
			if(is_array($file)){
				$file = NPDC_DEV ? $file[0] : implode('', $file);
			}
			echo $code[0].$file.$code[1];
		}
	}
	?>
	<script type="text/javascript">var baseUrl = "<?=BASE_URL?>";var controller = "<?=$controllerName?>";</script>
	<?=$extraJS?>
	<?=$extraCSS?>
</head>