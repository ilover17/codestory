<?php
if (!defined('CORE_PATH')) exit();
return  array(

    /* 项目设定 */
    'DEFAULT_APPS'          =>  array('index','public','admin','home','portal'),    //默认核心应用
    'SITE_LOGO'             =>  'image/logo.png', //默认的站点logo
    
    'DEPLOY_STASTIC'        =>  false,  //是否独立部署静态文件.开发时否,生产时真.
    'DEV_MOD'               =>  true,   //开发模式
    'MANAGE_PAGE'           =>  false,  //是否使用业务管理平台
    'SHORT_URL'             =>  true,  //是否开启本地短网址服务
    'HOT_TOPIC'             =>  2,          //热门主题起始数
    'HOT_TOPIC_LIMIT'       =>  10,         //每次获取的随机数
        
    /* 搜索引擎配置 */
    'SEARCH_ENGINE'         => 'sphinx',    //搜索引擎
    'SEARCHD_HOST'          => '192.168.1.200',
    'SEARCHD_PORT'          => 9306,
    'SEARCHD_INDEX'         => 'QimingDao', //索引
    

    /* Cookie设置 */
    'COOKIE_EXPIRE'         =>  7*24*3600,      // Coodie有效期
    'COOKIE_DOMAIN'         =>  '',         // Cookie有效域名
    'COOKIE_PATH'           =>  '/',            // Cookie路径
    'COOKIE_PREFIX'         =>  'qm_',      // Cookie前缀 避免冲突

    'SECURE_CODE'           => 'qm_team',

    /* 默认设定 */
    'DEFAULT_APP'           =>  'public',       // 默认项目名称，@表示当前项目
    'DEFAULT_MODULE'        =>  'Index',        // 默认模块名称
    'DEFAULT_ACTION'        =>  'index',        // 默认操作名称
    'DEFAULT_CHARSET'       =>  'utf-8',        // 默认输出编码
    'DEFAULT_TIMEZONE'      =>  'PRC',          // 默认时区
    'DEFAULT_LANG'          =>  'zh-cn',        // 默认语言
    'DEFAULT_LANG_TYPE'     => array('zh-cn'), //默认支持的语言类型
    //不需要限制就能正常使用的应用
    'FILTER_APP'            => array('arms','blog','contact','expert','innofair','ipms','newkm','news','oa','sina','gift'),

    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'kmc_shenhua',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '',          // 密码
    'DB_PORT'               =>  3306,        // 端口
    'DB_PREFIX'             =>  'qm_',    // 数据库表前缀
    'DB_SUFFIX'             =>  '',          // 数据库表后缀
    'DB_FIELDTYPE_CHECK'    =>  false,       // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0,           // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效

    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  null,           // 数据缓存有效期
    'DATA_CACHE_COMPRESS'   =>  true,           // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  true,           // 数据缓存是否校验缓存
    'DATA_CACHE_TYPE'       =>  'File',         // 数据缓存类型,支持:File|Memcache
    'DATA_CACHE_PATH'       =>  CORE_RUN_PATH.'/datacache', // 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  true,           // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  2,              // 子目录缓存级别
    'DATA_CACHE_PREFIX'     =>  'QM_',          //缓存前缀
    'F_CACHE_PATH'          =>  CORE_RUN_PATH.'/filecache', //F函数缓存的文件目录
    'MEMCACHE_HOST'         => '127.0.0.1:11211',   //Memcache 默认的主机，支持简单的多机集群  如:  "127.0.0.1:11211,127.0.0.2:11211"


    'redis_host' => '192.168.1.200',
    'redis_port' => '6379',
    'redis_auth' => '123456',
    'redis_system_db' => 0,

    /* 日志设置 */
    'LOG_RECORD'            =>  false,   // 默认不记录日志
    'LOG_FILE_SIZE'         =>  2097152,    // 日志文件大小限制
    //'LOG_RECORD_LEVEL'      =>    array('EMERG','ALERT','CRIT','ERR'),// 允许记录的日志级别
    'LOG_RECORD_LEVEL'      =>  array('EMERG','ERR'),// 允许记录的日志级别

    /* SESSION设置 */
    'SESSION_NAME'          => 'C3SESSION',      // Session名称
    'SESSION_GC_MAXLIFETIME' => 7*24*3600,          // SESSION 默认过期时间
    'SESSION_HANDER'         => 'File',           //目前支持File和Memcache
    'SESSION_PATH_LEVEL'    => 3,               //session  文件子目录级
    'SESSION_BASE_DIR'      => DATA_PATH.'/session',   //SESSION 文件存储根目录

    /* 模板引擎设置 */
    'TMPL_DENY_FUNC_LIST'   =>  'echo,exit',    // 模板引擎禁用函数
    'TMPL_PARSE_STRING'     =>  '',          // 模板引擎要自动替换的字符串，必须是数组形式。
    'TMPL_L_DELIM'          =>  '{',            // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}',            // 模板引擎普通标签结束标记
    'TMPL_VAR_IDENTIFY'     =>  'array',     // 模板变量识别。留空自动判断,参数为'obj'则表示对象
    'TMPL_STRIP_SPACE'      =>  true,       // 是否去除模板文件里面的html空格与换行
    'TMPL_CACHE_ON'         =>  true,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_TIME'       =>  -1,         // 模板缓存有效期 -1 为永久，(以数字为值，单位:秒)
    'TMPL_ACTION_ERROR'     =>  'Public:success', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  'Public:success', // 默认成功跳转对应的模板文件
    'TMPL_TRACE_FILE'       =>  CORE_PATH.'/Tpl/PageTrace.tpl.php',     // 页面Trace的模板文件
    'TMPL_EXCEPTION_FILE'   =>  CORE_PATH.'/Tpl/ThinkException.tpl.php',// 异常页面的模板文件
    'TMPL_FILE_DEPR'        =>  '/', //模板文件MODULE_NAME与ACTION_NAME之间的分割符，只对项目分组部署有效
    'TMPL_CACHE_PATH'       =>  APP_RUN_PATH.'/tplcache', //模板文件缓存路径

    /* 模板引擎标签库相关设定 */
    'TAGLIB_BEGIN'          =>  '<',  // 标签库标签开始标记
    'TAGLIB_END'            =>  '>',  // 标签库标签结束标记
    'TAGLIB_LOAD'           =>  true, // 是否使用内置标签库之外的其它标签库，默认自动检测
    'TAGLIB_BUILD_IN'       =>  'input,business', // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔
    'TAGLIB_PRE_LOAD'       =>  'html',   // 需要额外加载的标签库(须指定标签库名称)，多个以逗号分隔
    'TAG_NESTED_LEVEL'      =>  3,    // 标签嵌套级别
    'TAG_EXTEND_PARSE'      =>  '',   // 指定对普通标签进行扩展定义和解析的函数名称。

    /* 表单令牌验证 */
    'TOKEN_ON'              =>  false,      // 开启令牌验证
    'TOKEN_NAME'            =>  '__hash__', // 令牌验证的表单隐藏字段名称
    'TOKEN_TYPE'            =>  'md5',      // 令牌验证哈希规则

    /* URL设置 */
    
    'URL_ROUTER_ON'         =>  true,   // 是否开启URL路由

    /* 系统变量名称设置 */
    'VAR_PAGE'              =>  'p',    // 默认分页跳转变量
    'VAR_AJAX_SUBMIT'       =>  'ajax', // 默认的AJAX提交变量

    /*
    * 游客访问的白名单,不需要在后台配置的项 (系统核心项目)
    */
    'DEFAULT_NO_LOGIN'      => array(
                                'index/Index/login',
                                'index/Index/doLogin',
                                ),
    /**
     * 路由的key必须写全称. 比如: 使用'wap/Index/index', 而非'wap'.
     */
    'router' => array(
        "portal/Show/index"       =>  "[channel]/[page].html",
        "index/Index/afterpay"    => "afterpay"
    ), 

    /*用户组相关*/
    'DEFAULT_GROUP_ID'              => 1,           //默认注册之后的用户组ID
    'PERSONAL_VERIFY_GROUP_ID'      => 2,           //个人认证的用户组ID -- 研究院专家
    'ORG_VERIFY_GROUP_ID'           => 3,           //机构认证的用户组ID -- 外部专家
    'ADMIN_GROUP_ID'                => 5,           //管理员用户组ID    
    'GUEST_GROUP_ID'                => 100,         //游客用户组

    'resource' => array(
        //会议室
        'lou1'  => array(
            'title'=>'教学楼',
            'attr' => 'LED横幅屏,课桌,电脑桌,方形讨论桌,圆形讨论桌,讨论桌,座椅,挂钟,讲台电脑,可移动电视,瘦终端鼠标,键盘,耳机,教室录播系统,视频会议系统,投影仪,实物投影仪,双投影仪,可移动投影仪,幕布,门外信息屏,主席台麦克风,手持麦克,头戴麦克,桌面麦克',
        ),
        'lou2'  => array(
            'title'=>'会议中心',
            'attr' => 'LED横幅屏,课桌,电脑桌,方形讨论桌,圆形讨论桌,讨论桌,座椅,挂钟,讲台电脑,可移动电视,瘦终端鼠标,键盘,耳机,教室录播系统,视频会议系统,投影仪,实物投影仪,双投影仪,可移动投影仪,幕布,门外信息屏,主席台麦克风,手持麦克,头戴麦克,桌面麦克',
        ),
    ),
    'lesson_info' => array(
        'title'     => '课程教学质量评估问卷调查',
        'sub_title' => '教务平台课程评估',
        'description'=>'亲爱的学员：请您配合我们完成以下评估内容的填写，如实反馈您对本门课程的评价。 ',
        'lesson'    => array(
            '评估内容'=>array(
                    '1. 教学内容'=>array(
                        '0'=>'满意',
                        '1'=>'较满意',
                        '2'=>'一般',
                        '3'=>'不满意'
                    ),
                    '2. 教学方法'=>array(
                        '0'=>'满意',
                        '1'=>'较满意',
                        '2'=>'一般',
                        '3'=>'不满意'
                    ),
                    '3. 教学效果'=>array(
                        '0'=>'满意',
                        '1'=>'较满意',
                        '2'=>'一般',
                        '3'=>'不满意'
                    )
                ),
            ),
    ),
    // 'lesson_info' =>array(
    //     'title'=>'课程教学质量评估问卷调查',
    //     'sub_title' => '课程名：《公司治理》 主讲：李维安 ',
    //     'description'=>'亲爱的学员：感谢您的辛苦付出，请您配合我们完成以下评估内容的填写，如实反馈您对本门课程的评价，这将有助于我们全面评估培训效果，您的建议和评价将有助于我们安排将来的培训课程，从而更好地满足您的培训需求。',
    //     'lesson'=>array(
    //         '1.教学水平'=>array(
    //             '1.1 您对该讲师的教学态度满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //             '1.2 您对该讲师的语言表达满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //             '1.3 您对该讲师在调动学员参与度方面满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             )
    //         ),
    //         '2.教学内容'=>array(
    //             '2.1 您对该课程与培训目标的一致性方面满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //             '2.2 您对该课程系统性和实用性方面满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //             '2.3 您对该课程理论联系实际方面满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //             '2.4 您对该课程的信息量方面满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //         ),

    //         '3.教学方法'=>array(
    //             '3.1 您对该课程在多样性和有效性方面满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             )
    //         ),
    //         '4.教学效果'=>array(
    //             '4.1 您对本课程对您工作方面的帮助程度满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             ),
    //             '4.2 您对本课程对您个人成长方面的帮助程度满意吗？'=>array(
    //                 '0'=>'很满意',
    //                 '1'=>'满意',
    //                 '2'=>'较满意',
    //                 '3'=>'一般',
    //                 '4'=>'不满意'
    //             )
    //         ),
    //         '5.意见建议'=>array(
    //             '5.1 您对我们的工作或本期培训有任何意见建议吗？'=>array(),
    //         )
    //     )
    // ),
    'permission'=>array(
        'index'=>array(
            'index_file_upload' => '上传资料',
            'index_file_del' => '删除资料',
            'index_survey_add' =>'发布问卷',
            'index_survey_edit'=>'编辑问卷',
            'index_survey_export'=>'导出问卷',
            'index_survey_del'=>'删除问卷',
            'index_lesson_export'=>'导出课程评估',
            'index_lesson_del'  => '删除课程评估',
        ),
        'edu'=>array(
            'edu_index_view' =>'查看一览表',
            'edu_index_export'=>'导出一览表',
            'edu_student_view'=>'查看学员管理',
            'edu_student_add'=>'录入学员',
            'edu_student_edit'=>'编辑学员',
            'edu_student_del'=>'删除学员',
            'edu_student_count'=>'学员统计',
            'edu_teacher_view'=>'查看师资',
            'edu_teacher_add'=>'录入师资',
            'edu_teacher_del'=>'删除师资',
            'edu_teacher_edit'=>'编辑师资',
            'edu_teacher_count'=>'师资统计',
            'edu_teacher_import'=>'师资导入',
            'edu_class_view'=>'查看班级',
            'edu_class_addproject'=>'新建项目',
            'edu_class_editproject'=>'编辑项目',
            'edu_class_delproject' =>'删除项目',
            'edu_class_add'=>'添加班级',
            'edu_class_edit'=>'编辑班级',
            'edu_class_export'=>'导出班级下学员',
            'edu_class_del'  =>'删除班级',
            'edu_class_quite'=>'退出班级查询',
            'edu_class_rsyc' =>'同步到集团党建',
            'edu_lesson_view'=>'查看排课',
            'edu_lesson_export'=>'导出排课',
            'edu_lesson_add'   =>'添加排课',
            'edu_lesson_del'   => '删除排课',
            'edu_resource_view'=>'查看会议室',
            'edu_resource_add'=>'录入会议室',
            'edu_resource_edit'=>'添加会议室',
            'edu_resource_del'=>'删除会议室',
            'edu_resource_order'=>'预定会议室',
            'edu_pay_view' => '支付管理',
            'edu_pay_add' => '添加特殊情况支付',
            'edu_pay_set' => '确认特殊情况支付',
            'edu_pay_lists' => '付款查询',
            'edu_ticket_view' => '发票管理',
            'edu_ticket_add' => '发票申请列表',
            'edu_ticket_lists' => '发票列表',
            'edu_ticket_doadd' => '添加发票',
        )
    ),
);
?>