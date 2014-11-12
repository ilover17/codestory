<?php
error_reporting(0);
$title='jQuery UI标准后台页面演示系统';
$edition=' 0.1 Release 20100702';

$modules=array(
    'kenel'=>'核心系统',
    'pages'=>'单页',
    'articles'=>'文章'
);
$items=array(
    'kenel'=>array(
        'admin'         =>  '后台首页',
        'systemsetting' =>  '系统设置',
        'modules'       =>  '组件管理',
        'templates'     =>  '模板管理',
        'users'         =>  '用户管理',
        'classes'       =>  '分类管理'
    ),
    'pages'=>array(
        'aboutus'       =>  '关于我们',
        'address'       =>  '联系方式'
    ),
    'articles'=>array(
        'news'          =>  '新闻动态',
        'message'       =>  '站内通知',
        'faq'           =>  '常见问题'
    )
);
$tabs=array(
    'kenel-admin'=>array(
        0=>array('url'=>'admin.php','tabName'=>'后台首页'),
        1=>array('url'=>'systeminfo.php','tabName'=>'系统信息'),
        2=>array('url'=>'helper.html','tabName'=>'演示说明')
    ),
    'kenel-systemsetting'=>array(
        0=>array('url'=>'systemsetting.php','tabName'=>'基本设置'),
        1=>array('url'=>'address.php','tabName'=>'联系方式')
    ),
);
