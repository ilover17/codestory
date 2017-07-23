/**
 * 这是一个插件的demo写法,可以独立使用，也可以跟widget、module相互结合使用.
 * 
 * 目标：1.可以让一个页面展示多个widget而不冲突
 * 		2.让复杂js规范化
 */

//第一步：在widget实现的如下内容,其中XXX为调用该widget的唯一id参数
//HTML
/**
 <div model-node='demo_widget' model-args='name=XXX'></div>
 */
/* JS：
	if("undefined" == typeof($config['demo_widget'])){
		$config['demo_widget'] = { };
	}	
	//该widget的一些特殊参数和变量,如一些不适合在model-args中传递的，
	//传入到core.demo里面使用
	$config['demo_widget'][XXX] = 'ssss';
	//....
 */
//第二步：在对应的module.js 里面实现监听
/*
'demo_widget':{
	'load':function(){
		//标准的Js写法
		var args = M.getModelArgs(this);
		core.plugFunc('demo',function(){
			//类似于一个注册操作
			core.demo.start(args.name);
		});
		//其他一些监听或者额外操作
	}
}	
*/
//第三步：在js/plugins/core.XXX.js 里面实现如下内容方法，其中start为初始化方法
core.demo = {
	//标准初始化方法
	start:function(id){
		//创建一个内部变量
		var demo = $config['demo_widget'][id];
		demo.method1 = function(){
			//第一个方法
			return 1;
		}
		demo.method2 = function(){
			//第二个方法
			return 2;
		}
		//其他方法... 
		
		//这个时候,我们可以看到
		console.log($config['demo_widget'][id]);		
	},
	//其他一些供外部调用的方法，传入页面唯一ID值
	demo_method1:function(id){
		//可以直接通过此方法调用到id下widget里面的方法
		console.log( $config['demo_widget'][id].method1() );
	}
}