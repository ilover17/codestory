<?php
require_once 'config.php';
$version    =   apache_get_version();
$array      =   explode(' ', $version);
$apacheVersion  = array_shift($array);
$apacheVersion .= array_shift($array);
?>
<ul id="sl-systeminfo" class="sl-table">
<li><label>程序版本</label> <?=$title?><?=$edition?></li>
<li><label>在线服务</label><a href="http://www.cn09.com">查看最新版本</a> <a href="http://bbs.cn09.com">支持与交流</a> </li>
<li><label>操作系统及 PHP</label> <?=PHP_OS?> / PHP <?=PHP_VERSION?> </li>
<li><label>服务器软件</label><?=$apacheVersion?></li>
<li><label>上传许可</label> <?=ini_get('upload_max_filesize')?>  </li>
</ul>