<?php

class HomeController
{
    public function index()
    {
        $title = 'AnLaMe - Đặt món ngon online, giao nhanh 20 phút';
        $view  = 'home';

        require_once PATH_VIEW_MAIN;
    }

    public function notFound()
    {
        http_response_code(404);
        $title = 'Không tìm thấy trang - AnLaMe';
        $view  = 'not-found';

        require_once PATH_VIEW_MAIN;
    }
}
