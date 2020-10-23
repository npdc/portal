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
        if (!empty($_GET['overlay'])) {
            $extraJS .= '<script type="text/javascript">
                $().ready(function() {openOverlay(\'' . BASE_URL . '/' 
                    . $_GET['overlay'] . '\');});
            </script>';
        }
        include 'head.tpl.php';
    ?>
    <body class="<?=$view->class?> <?=(
        empty($session->userLevel)
        || $session->userLevel === NPDC_PUBLIC
        ? 'guest'
        : 'user'
        )?>">
        <div id="smallscreen">
            This site works best on a screen of at least 620 pixels wide
        </div>
        <div id="overlay"><div class="inner"></div></div>
        <div id="page">
            <div id="head">
            <?php
            if (file_exists(__DIR__.'/header.tpl.php') && false) {
                require 'header.tpl.php';
            } else {
                require 'header.default.php';
                
            }
            ?>
            </div>
            <div id="main">
                <?php
                echo (
                    isset($view->title)
                    && strlen($view->title) > 0
                    ? '<h2>' . $view->title . '</h2>'
                    : ''
                );
                if (isset($_SESSION['notice'])) {
                    echo '<div id="notice">' . $_SESSION['notice'] . '</div>';
                    if (empty($_GET['overlay'])) {
                        unset($_SESSION['notice']);
                    }
                }
                if (
                    $view->canEdit ?? false
                    && \npdc\lib\Args::get('action')==='edit'
                ) {
                    echo '<a href="' . BASE_URL . '/' . $view->baseUrl
                        . '">&laquo; View</a>';
                }
                echo (
                    (
                        method_exists($view, 'listStatusChanges')
                        && ($view->canEdit ?? false)
                        && !in_array(
                            \npdc\lib\Args::get('action'),
                            ['new', 'new_from_doi', 'duplicate']
                        )
                        ? $view->listStatusChanges()
                        : ''
                    )
                );
                ?>
                <div class="cols">
                    <?=(
                        isset($view->left)
                        ? '<div id="left">' . $view->left . '</div>'
                        : ''
                    )?>
                    <div id="mid">
                        <?php
                        if (
                            $view->canEdit ?? false
                            && !\npdc\lib\Args::exists('action')
                        ) {
                            echo '<div id="tools">';
                            if ($view->takeover ?? false) {
                                echo '<button onclick="openUrl(\'' . BASE_URL .'/'
                                . ($view->baseEditUrl ?? $view->baseUrl)
                                . '/takeover\')">Take over user rights</button> ';
                            }
                            if (
                                $view->allowDuplicate ?? false
                                && $session->userLevel >= NPDC_EDITOR
                            ) {
                                echo '<button onclick="openUrl(\'' . BASE_URL. '/' 
                                    . (
                                        \npdc\lib\Args::exists('uuid')
                                        ? \npdc\lib\Args::get('type') . '/' . \npdc\lib\Args::get('uuid')
                                        : $view->baseUrl
                                    )
                                     . '/duplicate\')" class="secondary">Duplicate this page</button> ';
                            }
                            echo '<button onclick="openUrl(\'' . BASE_URL . '/'
                                . ($view->baseEditUrl ?? $view->baseUrl)
                                . '/edit\')">Edit this page</button></div>';
                        }
                        echo (
                            isset($_SESSION['errors'])
                            ? '<div class="errors">' . $_SESSION['errors'] . '</div>'
                            : ''
                        )
                        . $view->mid;
                        ?>
                    </div>
                    <?=isset($view->right)
                        ? '<div id="right">' . $view->right . '</div>'
                        : ''
                    ?>
                </div>
                <?php
                if (isset($view->frontblocks)) {
                    echo '<div class="cols frontblocks">';
                        foreach ($view->frontblocks as $id=>$block) {
                            echo '<div class="' . $id . '">' . $block . '</div>';
                        }
                    echo '</div>';
                }
                ?>
                <?=isset($view->bottom)
                    ? '<div id="bottom">' . $view->bottom . '</div>'
                    : ''
                ?>
            </div>
            <?php
            if (file_exists(__DIR__ . '/footer.tpl.php')) {
                require 'footer.tpl.php';
            }
            ?>
        </div>
        <?php if (NPDC_DEV) {
            echo '<div class="debug bottom">Loading time: '.(microtime(true)-$start).'s<div>';
            var_dump(['args'=>\npdc\lib\Args::getAll(), 'session'=>$_SESSION]);
            echo '</div></div>';
        }?>
    </body>
</html>