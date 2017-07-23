<?php
// +----------------------------------------------------------------------
// | QimingDao [ Social business software! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.qimingcx.com All rights reserved.
// +----------------------------------------------------------------------
// | QimingDao Commercial Licensed
// +----------------------------------------------------------------------
// $Id$

/**
 * 简洁Modelite接口抽象类
 * @category	core
 * @package		QimingDao
 * @version		$0.1$
 */
abstract class Modelite {

	// 最近错误信息
    protected $error = '';

    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     * @param string $name 模型名称
     * @access public
     */
    public function __construct($name='') {
        // 模型初始化
        $this->_initialize();
    }

    // 回调方法 初始化模型
    protected function _initialize() {}
	
	// 获取最近的错误信息
	public function getError() {
        return $this->error;
    }
}
?>