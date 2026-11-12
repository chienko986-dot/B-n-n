<?php

class HomeController
{
    public function index()
    {
        $title = 'AnLaMe - Đặt món ngon online, giao nhanh 20 phút';
        $view  = 'home';

        require_once PATH_VIEW_MAIN;
    }
}
