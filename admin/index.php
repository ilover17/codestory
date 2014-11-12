<?php
require_once 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Language" content="zh-cn" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!--可用参数all|none|index|noindex|follow|nofollow-->
    <meta name="Robots" content="all" />
    <meta name="keywords" content="all" />
    <meta name="description" content="" />
    <meta name="author" content="slime" />
    <meta name=copyright content="2010" />
    <title><?=$title?></title>
    <link rel=stylesheet type="text/css" href="css/admin/basic.css" />
    <link type="text/css" href="css/themes/ui-lightness/jquery.ui.all.css" rel="stylesheet" />
    <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.2.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.form.js"></script>
    <script type="text/javascript" src="js/jquery.validate_cn.min.js"></script>
    <script type="text/javascript" src="js/common.js"></script>
</head>
<body id="sl-index">
<div id="sl-wrap" class="ui-widget-content">
  <div id="sl-header" class="ui-widget-header">
        <h1><?=$title?></h1>
  </div>
  <div id="sl-main-nav">
        <div id="sl-accordion">
            <? foreach($modules as $moduleKey=>$moduleName){ ?>
            <h3><a href="#"><?=$moduleName?></a></h3>
            <div>
               <ol class="sl-selectable">
                   <? foreach($items[$moduleKey] as $itemKey=>$itemName){ ?>
                    <li class = "ui-widget-content" link = "<?=$moduleKey?>-<?=$itemKey?>"><?=$itemName?></li>
                    <? }?>
                </ol>
            </div>
            <? }?>
        </div>
  </div>
  <div id="sl-content">
      <div id="sl-tabs">
	<ul>
		<li></li>
	</ul>
     </div>
  </div>
  <div id="sl-footer" class="ui-widget-header">
      <p><?=$title?><?=$edition?></p>
  </div>
</div>
  </body>
</html>
