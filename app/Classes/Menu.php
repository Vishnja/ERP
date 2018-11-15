<?php
namespace App\Classes;

use Log;
use Request;
use Gate;

class Menu
{
    public $menu = [],
           $class,

           $html,
           $routeName,
           $routeNameWithDashes,
           $pageTitle,                      // is defined in generateLi method
           $actualMenuItems = [];           // flat array of actual names with routes used for ACL

    /**
     * Example of $menu:
     *
     * 'Главная'       => [ 'dashboard', 'fa-dashboard' ],
     * 'Test'          => [ 'folder', 'fa-folder', [
     *     'Test1'     => 'test.test1',
     *     'Test2'     => 'test.test2'
     *  ]],
     * 'Настройки'     => [ null, 'fa-cogs', [
     *     'TestLevel2'=> [ null, 'fa-circle-o', [
     *         'Test1'     => 'test2.test1',
     *         'Test2'     => 'test2.test2'
     *     ]],
     *     'Test1'     => 'test2.test3',
     * ]]
     *
     */
    public function init($class, $menu)
    {
        // load
        $this->class = $class;
        $this->menu = $menu;
        $this->routeName = Request::route()->getName();
        $this->routeNameWithDashes = str_replace(".", "-", $this->routeName);

        // render
        $res = $this->generateUl($this->menu, $this->class);
        $this->html = $res['html'];
    }

    public function generateUl($menu, $class = 'treeview-menu')
    {
        $isActive = false;
        $html = '';

        foreach ($menu as $name => $params) {
            $res = $this->generateLi($name, $params);
            $html .= $res['html'];
            if ($res['isActive']) $isActive = true;
        }

        return [
            // if all routes from ul are Gate::denied - don't display ul
            'html' => $html ? "<ul class=\"$class\">$html</ul>" : "",
            'isActive' => $isActive
        ];
    }

    public function generateLi($name, $params)
    {
        if (! is_array($params)) $params = [ $params ];

        $hasSubmenu = isset($params[2]) ? true : false;

        // generate submenu if exists, define whether this is active branch / item
        $submenu = '';
        $isActive = false;

        if ($hasSubmenu) {
            $res = $this->generateUl($params[2]);
            $submenu = $res['html'];
            $isActive = $res['isActive'];
        }
        else {
            // current page
            if ($this->routeName == $params[0]) {
                $isActive = true;
                $this->pageTitle = $name;
            }

            // generate actual menu items array for ACL
            $this->actualMenuItems[] = [ "name"  => $name,
                                         "route" => str_replace(".", "-",  $params[0]) ];
        }

        // li classes
        $liClasses = [];
        if ($hasSubmenu) $liClasses[] = 'treeview';
        if ($isActive) $liClasses[] = 'active';
        $liClasses = implode(' ', $liClasses);

        $href = $hasSubmenu ? '#' : route($params[0]);
        $icon = isset($params[1]) ? $params[1] : 'fa-circle-o';
        $additionalIcon = $hasSubmenu ? '<i class="fa fa-angle-left pull-right"></i>' : '';

        // return empty html if:
        // a) returning from submenu (params[0] empty) and $submenu is empty
        //    (user doesn't have rights to access submenu items)
        // b) it is item (! hasSubmenu) but user doesn't have access

        $html = '';
        if ( ( $hasSubmenu && ! empty($submenu) ) ||
             ( ! $hasSubmenu && Gate::allows('access', str_replace(".", "-", $params[0]) ) ) )
            $html =
                "<li class=\"$liClasses\">" .
                    "<a href=\"$href\">" .
                        "<i class=\"fa $icon\"></i> <span>$name</span>" .
                        $additionalIcon .
                    "</a>" .
                    $submenu .
                "</li>";


        return [
            'html' => $html,
            'isActive' => $isActive,
        ];
    }

    public function isActive($params)
    {
        $hasSubmenu = isset($params[2]) ? true : false;

        if ($hasSubmenu) {
            if (in_array($this->routeName, array_values($params[2]))) return true;
        } else {
            if ($this->routeName == $params[0]) return true;
        }

        return false;
    }

    public function routeInActualMenuItems(){
        $result = false;

        foreach ($this->actualMenuItems as $menuItem) {
            if ($menuItem['route'] == $this->routeNameWithDashes) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}