<?php

/**
 * Generate main menu of site
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

class Menu{
    private $model;
    private $userLevel;
    private $current;
    private $session;
    
    /**
     * Constructor
     *
     * @param object $session login information
     * @param string $current current url
     */
    private function __construct($session, $current) {
        $this->model = new \npdc\model\Menu();
        $this->current = $current === 'front' ? '' : $current;
        $this->userLevel = array_slice(
            $session->levels,
            0,
            $session->userLevel + 1
        );
        $this->session = $session;
    }
    
    /**
     * Generate (sub)menu
     *
     * @param integer $parent id of parent menu item
     * @return array menu and indicator if active page is in the menu
     */
    public function generate($parent = null) {
        $active = false;
        $return = '<ul>';
        foreach ($this->model->getItems($parent, $this->userLevel) as $item) {
            if (
                !(\npdc\config::$partEnabled[$item['url']] ?? true) 
                && $this->session->userLevel < NPDC_ADMIN
            ) {
                continue;
            }
            if (is_null($item['url'])) {
                $res = $this->generate($item['menu_id']);
                $return .= '<li class="sub' 
                    . (
                        $res[1] 
                        ? ' active-child' 
                        : ''
                    )
                    . '"><span>' . $item['label'] . '</span>'
                    . $res[0] . '</li>';
            } else {
                $active = $this->current === $item['url'] || $active;
                $return .= '<li'
                    . (
                        $this->current === $item['url']
                        ? ' class="active"'
                        : ''
                    )
                    . '><a href="' . BASE_URL . '/' . $item['url'] . '">'
                    . $item['label'] . '</a></li>';
            }
        }
        $return .= '</ul>';
        return [$return, $active];
    }

    /**
     * Generate the menu
     *
     * @param object $session login information
     * @param string $current current url
     * @return string the formatted menu
     */
    public static function getMenu($session, $current) {
        $instance = new \npdc\lib\Menu($session, $current);
        return $instance->generate()[0];
    }
}