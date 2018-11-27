<?php
namespace app\index\controller;
use think\Db;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        $data = ['title' => input('post.title')];
	$result = Db::name('user') -> insert($data);
               
    }
}