<?php
    /**
     * 核心编译文件列表，会统一压缩编译到 runtime 中去
     */
	if (!defined('SITE_PATH')) exit();
    
    $coreFileList[] = CORE_LIB_PATH.'/Log.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Qiming.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Model.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/QimingDao.php';
    $coreFileList[] = CORE_LIB_PATH.'/functions.inc.php';
    $coreFileList[] = CORE_LIB_PATH.'/Db.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Action.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Cache.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Session.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/App.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Modelite.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Page.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/Template.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/TagLib.class.php';
    $coreFileList[] = CORE_LIB_PATH.'/TagLibCx.class.php';

    
    $coreFileList[] = ADDON_PATH.'/liberary/UploadFile.class.php';
    //以下两个文件可以根据环境部署的需要加载其中一个就可以
    $coreFileList[] = ADDON_PATH.'/liberary/cache/CacheFile.class.php';
    //$coreFileList[] = ADDON_PATH.'/liberary/cache/CacheMemcache.class.php';

    return $coreFileList;
  ?> 