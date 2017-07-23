if (!window.console) {
    window.console = {};
}
if (!console.log) {
    console.log = function () {};
}
$.browser = {};
$.browser.mozilla = /firefox/.test(navigator.userAgent.toLowerCase());
$.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
$.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
$.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());

function getScript(url, callback)
{
    jQuery.ajax({
            type: "GET",
            url: url,
            success: callback,
            dataType: "script",
            cache: true
    });
};

/**
 * 核心函数源对象
 * @author yangjs
 */
var _core = function(){
    var obj = this;
    this._coreLoadFile = function(){
        var temp = new Array();
        var tempMethod = function(url,callback){
            var flag = 0;
            for(i in temp){
                if(temp[i] == url){
                    flag = 1;
                }
            }
            if(flag == 0){
                temp[temp.length] = url;
                getScript(url,function(){
                    if("undefined" != typeof(callback)){
                        if("function"==typeof(callback)){
                            callback();
                        }else{
                            eval(callback);
                        }
                    }
                });
            }else{
                if("undefined" != typeof(callback)){
                    if("function"==typeof(callback)){
                        setTimeout(callback,500);
                    }else{
                        setTimeout(function(){
                            eval(callback);
                        },500);
                    }
                }
            }
        };
        return tempMethod;
    };

    this._coreLoadCss = function(){
        var temp = [];
        //返回内部包函数,供外部调用并可以更改temp的值
        return function( url ){
            var head = document.getElementsByTagName("head")[0],
                flag = 0,
                link,
                i = temp.length - 1;
            for ( ; i >= 0; i -- ) {
                flag = ( temp[i] == url ) ? 1 : 0;
            }
            if ( flag == 0 ) {
                link  = document.createElement( "link" );
                link.setAttribute( "rel", "stylesheet" );
                link.setAttribute( "type", "text/css" );
                link.setAttribute( "href", url );
                head.appendChild( link );
                temp.push( url );
            }
        };
    };

    /**
     * 时间插件源函数,
     *
     * 利用必包原理只载入一次js文件,其他类似功能都可以参照此方法
     * 需要提前引入jquery.js文件
     *
     * @author yangjs
     */

    this._rcalendar = function(text,mode,refunc){

        //标记值
        var temp = 0;
        var tempMethod = function(t,m,r){

            //第二次调用的时候就不=0了
            if(temp==0){
                //JQuery的ajax载入文件方式,如果有样式文件,同理在此引入相关样式文件
                getScript(THEME_URL+'/js/rcalendar.js?v='+VERSION,function(){   
                    rcalendar(t,m,r);
                });
            }else{
                
                getScript(THEME_URL+'/js/rcalendar.js?v='+VERSION,function(){   
                    rcalendar(t,m,r);
                });
            }
            temp++;
        };
        //返回内部包函数,供外部调用并可以更改temp的值
        return tempMethod;
    };
}

//核心对象
var core = new _core();
core.loadFile = core._coreLoadFile();
core.loadCss  = core._coreLoadCss();
//日期控件,调用方式 core.rcalendar(this,'YmdHis')
//this 也可以替换为具体ID,full表示时间显示模式,也可以参考rcalendar.js内的其他模式
core.rcalendar = core._rcalendar();
//初始化js插件
core.plugInit = function(){
    if(arguments.length>0){
        var arg = arguments;
        var back = function(){
            eval("var func = core."+arg[0]+";");
            if("undefined" != typeof(func)){
                func._init(arg);
            }
        };
        var file = THEME_URL+'/js/plugins/core.'+arguments[0]+'.js?v=123'+VERSION;
        core.loadFile(file,back);
    }
};
//与上面方法类似 只不过可以自己写回调函数（不主动执行init）
core.plugFunc = function(plugName,callback){
    var file = THEME_URL+'/js/plugins/core.'+plugName+'.js?v=123'+VERSION;
    core.loadFile(file,callback);
};
//获取id下所有选中的checkbox的值
core.getChecked = function(id) {
    var ids = new Array();
    $.each($('#'+id+' input:checked'), function(i, n){
        if($(n).val() !='0' && $(n).val()!='' ){
            ids.push( $(n).val() );
        }
    });
    return ids;
};
//checkbox框选中后样式修改
core.checkOn = function(o){
    if( o.checked == true ){
        $(o).parents('tr').addClass('bg_on');
    }else{
        $(o).parents('tr').removeClass('bg_on');
    }
};
//选中某个Id下的所有checkbox
core.checkAll = function(o,id){
    if( $(o).prop('checked') == true ){
        $('#'+id+' input[type=checkbox]').prop('checked',true);
        $('tr[overstyle="on"]').addClass("bg_on");
    }else{
        $('#'+id+' input[type=checkbox]').prop('checked',false);
        $('tr[overstyle="on"]').removeClass("bg_on");
    }
};
//标准 ajax 请求
core.SAjax = function(url,param,callback){
   $.post(url,param,function(msg){
        if(msg.status == 1){
            ui.success(msg.info);
            if("undefined" != typeof(callback)){
                if("function"==typeof(callback)){
                    callback(msg);
                }else{
                    eval(callback);
                }
            }else{
                setTimeout(function(){
                    location.href = location.href;
                },'1500')
            }
        }else{
            ui.error(msg.info);
        }
    },'json');
};

core.selectProvice = function(obj){
    $.post(U('edu/Public/getCity'),{pro:obj.value},function(data){
        var html = '';
        for(var i in data){
            html +='<option value="'+data[i]+'">'+data[i]+'</option>';
        }
        $(obj).siblings().html(html);
    },'json');
}

core.uploadRemove = function(obj){
    $(obj).parent().find('input').val('');
    $(obj).parent().hide();
    $(obj).parent().parent().find('.js_upload').show()
}

var U =function(url,params){
    
    url = url.split('/');
    if (url[0]=='' || url[0]=='@')
        url[0] = $config['app_name'];
    if (!url[1])
        url[1] = 'Index';
    if (!url[2])
        url[2] = 'index';
    var website = $config['url_mod'] == 1
                    ? $config['host_url']+'/index.php'+'?app='+url[0]+'&mod='+url[1]+'&act='+url[2]
                    : $config['host_url']+'/'+url[0]+'/'+url[1]+'/'+url[2];
    if(params){
        var _params = '';
        for(var i in params){
            if(/[^\d]/.test(i)){
                //i不是数字
                _params += (_params=='' ? i+'='+params[i]: '&'+i+'='+params[i]);
            }else{
                //i是数字
                _params += (_params=='' ? params[i]: '&'+params[i]);
            }
        }
        
        website += ( $config['url_mod'] == 1 ? '&' :'?') + _params;

    }else{
        if( $config['url_mod'] == 2){
            website +="?";
        }
    }

    return website;
};
//字符串长度-中文和全角符号为1，英文、数字和半角为0.5
var getLength = function(str, shortUrl) {
    if (true == shortUrl) {
        // 一个URL当作十个字长度计算
        return Math.ceil(str.replace(/((news|telnet|nttp|file|http|ftp|https):\/\/){1}(([-A-Za-z0-9]+(\.[-A-Za-z0-9]+)*(\.[-A-Za-z]{2,5}))|([0-9]{1,3}(\.[0-9]{1,3}){3}))(:[0-9]*)?(\/[-A-Za-z0-9_\$\.\+\!\*\(\),;:@&=\?\/~\#\%]*)*/ig, 'xxxxxxxxxxxxxxxxxxxx')
                            .replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
    } else {
        return Math.ceil(str.replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
    }
};

//截取字符串
var subStr = function (str, len,ext) {
    var ext = "undefined"==typeof(ext) ? '' : ext;
    if(!str) { return ''; }
        len = len > 0 ? len*2 : 280;
    var count = 0,  //计数：中文2字节，英文1字节
        temp = '';  //临时字符串
    for (var i = 0;i < str.length;i ++) {
        if (str.charCodeAt(i) > 255) {
            count += 2;
        } else {
            count ++;
        }
        //如果增加计数后长度大于限定长度，就直接返回临时字符串
        if(count > len) { return temp+ext; }
        //将当前内容加到临时字符串
         temp += str.charAt(i);
    }
    return str;
};


//获取距离顶部的滚动条高度
var getScrollTop = function(doc,win) {
    if("undefined" == typeof(doc)){
        var doc = document;
    }
    if("undefined" == typeof(win)){
        var win = window;
    }
    return !('pageYOffset' in win)
                ? (doc.compatMode === "BackCompat")
                ? doc.body.scrollTop
                : doc.documentElement.scrollTop
                : win.pageYOffset;
}

/**
 * 绑定Body的事件，一般用于点击Body还原之前的页面，如点Body隐藏评论
 * @param  {[type]} EventFun   具体的执行动作
 * @param  {[type]} exceptObj  不希望响应该动作的块
 */
var bindBodyClickEvent = function(EventFun, exceptObj, bindObj){
    var $obj;
    setTimeout(function(){
        if("undefined"!=typeof(bindObj)){
            $obj = $(bindObj);
        }else{
            $obj = $("body");
        }

        $obj.one('click',EventFun);
        if("undefined" == typeof(exceptObj) || "" == exceptObj){
            return false;
        }
        if( exceptObj.length > 1 ){
            for (var i = exceptObj.length - 1; i >= 0; i--) {
                $(exceptObj[i]).click(function(e){
                    stopEventPass(e);
                }); 
            };
        }else{
            $(exceptObj).click(function(e){
                stopEventPass(e);
            });  
        }
        
    },100);
}

var stopEventPass = function(e){
    if(document.all){
        window.event.cancelBubble = true;
    }else{
        e.stopPropagation();
    }
}

function inArray(needle,array,bool){
    if(typeof needle=="string"||typeof needle=="number"){
        for(var i in array){
            if(needle===array[i]){
                if(bool){
                    return i;
                }
                return true;
            }
        }
        return false;
    }
}

function submitCheck(form){
    if( $.browser.msie  ){
        form.find('input[type="text"]').each(function(){
            if( $(this).attr('placeholder') == $(this).val() ){
                $(this).val('');
            }
        });
    }
    form.submit();
    if( $.browser.msie  ){
        setPlaceHolder();
    }
}