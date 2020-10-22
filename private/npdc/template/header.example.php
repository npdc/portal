<div>
    <div id="top">
        <div id="search"><form method="post" action="<?=BASE_URL?>/search"><input type="text" placeholder="Search" name="q" /><button class="icon-search"></button></form></div>
        <div><span id="user"><?php
        if ($session->userLevel > NPDC_PUBLIC) {
            if (array_key_exists('adminUser', $_SESSION)) {
                echo 'You are logged in as admin '.$session->adminName.', and have taken over the rights of <a href="'.BASE_URL.'/account">'.$session->name.'</a> ('.$session->levelDetails[$session->userLevel]['name'].') - <a href="'.BASE_URL.'/?endtakeover">Go back to admin session</a>';
            } else {
                echo 'You are logged in as <a href="'.BASE_URL.'/account">'.$session->name.'</a> ('.$session->levelDetails[$session->userLevel]['name'].')';
            }
            if ($session->userLevel >= NPDC_EDITOR) {
                if ($session->userLevel > NPDC_PUBLIC) {
                    $unpublished = \npdc\view\Base::checkUnpublished(false);
                }
                echo ' - <a href="#" onclick="openOverlay(\''.BASE_URL.'/overlay/editor\')">Editor tools'
                    . ($unpublished > 0 ? '<span class="unpublished">'.$unpublished.'</span>' : '')
                    . '</a>';
            }
            echo ' - <a href="?logout">Log out</a>';
        } else {
            if (\npdc\config::$allowRegister) {
                echo '<a href="#" onclick="openOverlay(\''.BASE_URL.'/register\')">Create account</a> - ';
            }
            echo '<a href="#" onclick="openOverlay(\''.BASE_URL.'/login\')">Log in</a>';
        }
        ?></span>
        <?php
        if (\npdc\config::$social['twitter_in_head'] && !empty(\npdc\config::$social['twitter'])) {
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
