<?php
/**
 * Head section of page
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>
<head>
    <title><?=isset($view->title) && strlen($view->title) > 0 ? str_replace(['<i>', '</i>'], '', $view->title).' |' : '';?> <?=\npdc\config::$siteName?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0" />
    <meta name="author" content="Marten Tacoma, Netherlands Polar Data Center, NIOZ Royal Netherlands Institute for Sea Research" />
    <meta name="funding" content="Development of the portal software is funded by grant 866.14.001 of the Dutch Research Council (NWO) Netherlands Polar Program."/>
    <meta name="application-name" content="NPDC" />
    <meta name="page-version" content="<?=APP_VERSION?>" />
    <link rel="shortcut icon" type="image/x-icon" href="<?=BASE_URL?>/img/logo.png" />
    <link rel="apple-touch-icon" href="<?=BASE_URL?>/img/logo.png" />
    <?php
    $css = [
        'ol',
        'select2.min',
        'icomoon.min',
        'npdc/style.min'
    ];
    $js = [
        'external/jquery-3.5.1.min',
        'external/jquery-ui.min',
        'external/jquery.nicescroll.min',
        'external/select2.full.min',
        'external/jquery.inputmask.bundle.min',
        'external/ol',
        'npdc/npdc.min'
    ];
    if (NPDC_ENVIRONMENT !== 'production') {
        $css[] = 'npdc/'.NPDC_ENVIRONMENT.'.min';
    }
    $v = NPDC_DEV ? time() : APP_VERSION;
    foreach ([
        'css'=>['<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/', '.css?'.$v.'" />'],
        'js'=>['<script src="'.BASE_URL.'/js/', '.js?'.$v.'"></script>']
    ] as $type=>$code) {
        foreach ($$type as $file) {
            echo $code[0].$file.$code[1]."\r\n";
        }
    }
    ?>
    <script>var baseUrl = "<?=BASE_URL?>";var controller = "<?=$controllerName?>";</script>
    <?=$extraJS?>
    <?=\npdc\config::$extraHeader?>
    <?=$view->js?>
    <?=$view->extraHeader?>
    <?=$extraCSS?>
</head>