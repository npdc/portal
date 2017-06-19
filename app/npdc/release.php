<?php

require('template/css_js.php');

foreach(['js'=>'../../build/js.js', 'css'=>'../../build/css.css'] as $type=>$target){
    $output = '';
    foreach($$type as $file){
        if(is_array($file)){
            $file = implode('', $file);
        }
        $output .= file_get_contents('../../'.$type.'/'.$file.'.'.$type);
        file_put_contents($target, $output);
    }
}

file_put_contents('../../build/build', date('YmdHis'));