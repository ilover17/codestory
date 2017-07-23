/**
 * 窗体对象
 * @edit by yangjs
 */
var ui = {
    //选择部门
   //浮屏显示消息
    showMessage:function(message,error,lazytime){
        
        var style = (error=="1")?"html_clew_box clew_error ":"html_clew_box";
        var ico = (error == "1") ? 'ico-question-sign' : 'ico-ok-sign';
        var html   =  '<div class="'+style+'" id="ui_messageBox" style="display:none">'
                       + '<div class="html_clew_box_con" id="ui_messageContent">'
                       + '<i class="'+ico+'"></i>'
                       + message + '</div></div>';

        var _u = function(){
            for (var i = 0; i < arguments.length; i++)
                if (typeof arguments[i] != 'undefined') return false;
            return true;
        };


        $(html).appendTo(document.body);

        var _h = $('#ui_messageBox').height();
        var _w = $('#ui_messageBox').width();


        var left = ($('body').width() - _w)/2 ;
        var top  = $(window).scrollTop() + ($(window).height()-_h)/2;

        $( '#ui_messageBox' ).css({
            left:left + "px",
            top:top + "px"
        }).fadeIn("slow",function(){
            $('#ui_messageBox');
        });

        setTimeout( function(){
            $('#ui_messageBox').find('iframe').remove();
            $('#ui_messageBox' ).fadeOut(function(){
              $('#ui_messageBox').remove();
            });
        } , lazytime*1000);
    },
    showblackout:function(){
        if($('.boxy-modal-blackout').length > 0 ){
        }else{
            var height = $('body').height() > $(document).height() ? $('body').height() : $(document).height();
            $('<div class ="boxy-modal-blackout" style=""></div>').css({
                height:height+'px',width:$('body').width()+'px',zIndex: 10000
            }).appendTo(document.body);
        }
    },
    removeblackout:function(){
        if($('#uibox').length > 0){
            if(document.getElementById('uibox').style.display == 'none'){
                $('.boxy-modal-blackout').remove();
            }
         }else{
            $('.boxy-modal-blackout').remove();
         }
    },
    //操作成功显示
    success:function(message,time){
        var t = "undefined" == typeof(time) ? 1 : time;
        ui.showMessage(message,0,t);
    },
    //操作出错显示
    error:function(message,time){
        var t = "undefined" == typeof(time) ? 1.5 : time;
        ui.showMessage(message,1,t);
    },
    //confrim包修改 可传入callback也可以在对象中指定callback属性
    confirm:function(o,text,_callback, button){
        if(typeof button=='undefined'){
            button = { 'yes':'确定', 'no':'取消'}
        }

        if($('#ui_box_confirm').length > 0){
            $('#ui_box_confirm').remove();
        }
        var callback = "undefined" == typeof(_callback) ? $(o).attr('callback') : _callback;
        text = text || L('PUBLIC_ACCONT_TIPES');
        text = "<i class='ico-question-sign'></i>"+text;
        this.html = '<div id="ui_box_confirm" class="ui_confirm"><div class="layer-mini-info"><dl><dt class="txt"> </dt><dd class="action"><a class="btn-blue mr10" href="javascript:void(0)"><span>'+button.yes+'</span></a><a class="btn-gray" href="javascript:void(0)"><span>'+button.no+'</span></a></dd></dl></div></div>';
        $('body').append(this.html);
        var position = $(o).offset();

        if(document.documentElement.clientWidth < (position.left + $('#ui_box_confirm').width())){
            var left =  position.left - $('#ui_box_confirm').width();
        }else{
            var left = (position.left-$('#ui_box_confirm').width()+8+$(o).width()/2);
        }
    
        if( $(document).height() < (position.top + $('#ui_box_confirm').height())){
            var top = position.top - $('#ui_box_confirm').height();
        }else{
            var top = position.top;
        }

        $('#ui_box_confirm').css({"top":top+"px","left":left+"px","display":"none"});
        $("#ui_box_confirm .txt").html(text);
        $('#ui_box_confirm').fadeIn("fast");
        $("#ui_box_confirm .btn-gray").one('click',function(){
            $('#ui_box_confirm').fadeOut("fast");
            // 修改原因: ui_box_confirm .btn_b按钮会重复提交
            $('#ui_box_confirm').remove();
            return false;
        });
        $("#ui_box_confirm .btn-blue").one('click',function(){
            $('#ui_box_confirm').fadeOut("fast");
            // 修改原因: ui_box_confirm .btn_b按钮会重复提交
            $('#ui_box_confirm').remove();
            if("undefined" == typeof(callback)){
                return true;
            }else{
                if("function"==typeof(callback)){
                    callback();
                }else{
                    eval(callback);
                }
            }
        });
        return false;
    },
    confirmBox:function(title,text,callback,button, cancle_callback){
        if(typeof button=='undefined'){
            button = { 'yes':'确定', 'no':'取消'}
        }

        this.box.init(title);
        text = text || L('PUBLIC_ACCONT_TIPES');
        text = "<i class='ico-question-sign'></i>"+text;

        var content = '<div class="pop-create-group"><dl><dt class="txt">'+ text + '</dt><dd class="action"><a class="btn-blue mr10" href="javascript:void(0)"><span>'+button.yes+'</span></a><a class="btn-gray" href="javascript:void(0)"><span>'+button.no+'</span></a></dd></dl></div>';

        this.box.setcontent(content);
        this.box.center();


        var _this = this;
        $("#uibox .btn-gray").one('click',function(){
            $('#uibox').fadeOut("fast",function(){
                $('#uibox').addClass("hideSweetAlert").remove();
            });
            _this.box.close();

            if("undefined" == typeof(cancle_callback)){
                return true;
            }else{
                if("function"==typeof(cancle_callback)){
                    cancle_callback();
                }else{
                    eval(cancle_callback);
                }
            }

            return false;
        });
        $("#uibox .btn-blue").one('click',function(){
            $('#uibox').fadeOut("fast",function(){
                $('#uibox').addClass("hideSweetAlert").remove();
            });
            _this.box.close();

            if("undefined" == typeof(callback)){
                return true;
            }else{
                if("function"==typeof(callback)){
                    callback();
                }else{
                    eval(callback);
                }
            }
        });

        return false;
    },
    box:{
        WRAPPER:     '<table class="M-layer div-focus showSweetAlert" id="uibox" cellspacing="0" cellpadding="0" style="display:none"><tbody><tr><td>'+
         '<div class="M-content">'+
         '<div class="bd" id="layer-content"></div>'+
         '</div></td></tr></tbody></table>',
        simple_box: '<table class="M-layer div-focus showSweetAlert" id="uibox" cellspacing="0" cellpadding="0" style="display:none;"><tbody><tr><td id="layer-content">'+
                    '</td></tr></tbody></table>',
        inited:             false,
        IE6:                 'undefined' == typeof(document.body.style.maxHeight),
        init:function(title,callback,notshow){

            this.callback = callback;


            if($('#uibox').length >0){
                return false;
            }else{
                if("undefined" == typeof(title) || title == ''){
                    $('body').prepend( this.simple_box );
                }else{
                    $('body').prepend( this.WRAPPER );
                }
            }

            if("undefined" != typeof(title) || title == ''){
                $("<div class='hd'><a class='icon-close' href='javascript:;'></a><div class='M-title'>"+title+"</div></div>").insertBefore($('#uibox .bd'));
                $('.hd').mousedown(function(){
                    $('.mod-at-wrap').remove();
                });
                $('#uibox').find('.icon-close').click(function() {

                    ui.box.close(callback);
                    return false;
                });
            }

            ui.showblackout();

            $('#uibox').stop().css({width: '', height: '','z-index':999999});

            var _key = /opera/.test(navigator.userAgent.toLowerCase()) ?  "keypress" : "keydown";

            $(document.body).bind(_key+'.uibox', function(event) {
                var key = event.keyCode?event.keyCode:event.which?event.which:event.charCode;
                if (key == 27) {
                    $(document.body).unbind(_key+'.uibox');
                    ui.box.close(callback);
                    return false;
                }
            });

            this.center();
            if("undefined" == typeof(notshow)){
                var show = function(){
                    $('#uibox').show();
                }
                setTimeout(show,200);
            }
        },

        setcontent:function(content){
            $('#layer-content').html(content);
        },

        close:function(fn){

            //this.inited = false;
            //$('#ui-fs .ui-fs-all .ui-fs-allinner div.list').find("a").die("click");
            $('#uibox').addClass("hideSweetAlert").remove();
            //$('.talkPop').remove();
            $('.mod-at-wrap').remove();
            $('#recipientsTips').hide();
            jQuery('.boxy-modal-blackout').remove();
            //日历隐藏了
            if($('#rcalendar').length > 0){
               $('#rcalendar').hide();
            }
            var back ='';
             if("undefined" != typeof(fn)){
                  back = fn;
             }else if("undefined" != typeof(this.callback)){
                 back = this.callback;
             }
             if("function" == typeof(back)){
                    back();
             }else{
                 eval(back);
             }
        },

        alert:function(data,title,callback){
            this.init(title,callback);
            this.setcontent('<div class="question">'+data+'</div>');
            this.center();
        },

        show:function(content,title,callback){
            this.init(title,callback);
            this.setcontent(content);
            this.center();
        },

        showImage:function(src,alt){
            // this.init(L('PUBLIC_CLICK_ESC_TO_EXIT'));
            this.init();

            if("undefined" == typeof(alt) || alt == '' || alt.indexOf('http') < 0){
                alt = src;
            }

            var temp_arr = alt.split('|');
            if( temp_arr.length>1 ){
                alt = temp_arr[0];
                var width = temp_arr[1];
                var height = temp_arr[2];
            }else{
                var width = 0;
                var height =0;
            }

            

            var _this = this;
            var showImageCenter = function(alt, image_w, image_h){
                var _getReturn = function(){
                    var _return = {
                                width:0,
                                height:0
                            };
                    var window_w = screen.width;
                    var window_h = screen.height;

                    var scale = Math.min((window_w*0.8)/image_w, (window_h*0.8)/image_h); // 计算缩放比例，并以屏幕的80%为最大大小
                   
                    if(scale>=1) {
                        //没有超过允许的最大值
                        _return.width   =  image_w;
                        _return.height  =  image_h;
                    }else{
                        _return.width  = parseInt(image_w*scale);
                        _return.height = parseInt(image_h*scale);
                    }
                    return _return;
                }
                var _showImage = function(size){
                    if(size.width == 0 || size.height == 0){
                        var _size = '';
                    }else{
                        var _size = 'width="'+size.width+'" height="'+size.height+'"';
                    }
                    var source_href = alt.replace(/.jpg/, "_self.jpg");
                    content = '<div class="layer-close"><a class="icon-close" href="javascript:;"></a></div><div class="pop-show-img"><dl>'
                               +'<dd><img src="'+alt+'" '+_size+'/></dd>'
                               +'<dd class="img-info"><a href="'+source_href+'" target="_blank" class="qg-link">'+L('PUBLIC_LOOK_SOURCE')
                               +'</a></dd>'
                               +'</dl></div>';

                    _this.setcontent(content);
                    _this.center();    
                    $('#uibox').find('.icon-close').click(function() {
                        ui.box.close();
                        return false;
                    });
                }
                if( image_w==0 || image_h==0 ){
                    //先来个预览的
                    _this.setcontent("<img src='"+THEME_URL+"/image/load.gif' >");
                    _this.center();
                    var image=new Image();
                    image.onload = function(){
                        image_w =image.width;
                        image_h =image.height;
                        var size =  _getReturn();
                        _showImage(size);
                    }
                    image.src = alt;
                }else{
                    var size = _getReturn();
                    _showImage(size);
                }
            };
            showImageCenter(alt, width, height);
        },
        //requreUrl 请求地址
        //title 弹窗标题
        //callback 窗口关闭后的回调事件
        //requestData 请求附带的参数
        //type ajax请求协议 默认为GET

        load:function(requestUrl,title,callback,requestData,type){

            this.init(title,callback);
            if(  "undefined" != typeof(type) ){
                   var ajaxType = type;
            }else{
                   var ajaxType = "GET";
            }
            this.setcontent('<div style="width:150px;height:70px;text-align:center"><div class="load">&nbsp;</div></div>');
            var obj = this;

            if("undefined" == requestData){
                   var requestData = {};
            }

            jQuery.ajax({url:requestUrl,
                      type:ajaxType,
                      data:requestData,
                      cache:false,
                      dataType:'html',
                      success:function(html){
                          obj.setcontent(html);
                          obj.center();

                          if($.browser.msie ){
                            //如果是IE
                            setPlaceHolder();
                          }else{
                            setTimeout(function(){
                                $('#uibox').find('input[type="text"]').eq(0).focus();
                              },100);  
                          }
                    }
                });
            },

        _viewport: function() {
             var d = document.documentElement, b = document.body, w = window;
             var wtop = w.pageYOffset > 0 ? w.pageYOffset : 0;
             var btop = b.scrollTop || d.scrollTop;
             if(btop <0){ btop = 0;}
             return jQuery.extend(
                 /msie/.test(navigator.userAgent.toLowerCase()) ?
                     { left: b.scrollLeft || d.scrollLeft, top:btop } :
                     { left: w.pageXOffset, top: wtop },
                 !ui.box._u(w.innerWidth) ?
                     { width: w.innerWidth, height: w.innerHeight } :
                     (!ui.box._u(d) && !ui.box._u(d.clientWidth) && d.clientWidth != 0 ?
                         { width: d.clientWidth, height: d.clientHeight } :
                         { width: b.clientWidth, height: b.clientHeight }) );
        },

        _u: function() {
         for (var i = 0; i < arguments.length; i++)
             if (typeof arguments[i] != 'undefined') return false;
         return true;
        },

        _cssForOverlay: function() {
            if (ui.box.IE6) {
                return ui.box._viewport();
            } else {
                return {width: '100%', height: jQuery(document).height()};
            }
        },

        center: function(axis,boxSize) {
            
            var db=document.body,
                dd=document.documentElement,
                top = db.scrollTop>0?db.scrollTop:dd.scrollTop;

            var v = ui.box._viewport();
            var o =  [v.left, top];
            if(boxSize){
                v.width = v.width - boxSize.width;
                v.height = v.height - boxSize.height;
            }
            if (!axis || axis == 'x') this.centerAt(o[0] + v.width / 2 , null);
            if (!axis || axis == 'y') this.centerAt(null, o[1] + v.height / 2);
            return this;
        },


        moveToX: function(x) {
            if (typeof x == 'number')
                $('#uibox').css({left: x});
            else
                this.centerX();
            return this;
        },

        // Move this dialog (y-coord only)
        moveToY: function(y) {
            if (typeof y == 'number'){
                if( y<=0 ){
                    y = 0;
                }
                $('#uibox').css({top: y});
            }else{
                this.centerY();
            }
            return this;
        },
        centerAt: function(x, y) {
            var s = this.getSize();
            if (typeof x == 'number') this.moveToX(x - s[0]/2 );
            if (typeof y == 'number') this.moveToY(y - s[1]/2 );
            return this;
        },

        centerAtX: function(x) {
            return this.centerAt(x, null);
        },

        centerAtY: function(y) {
            return this.centerAt(null, y);
        },

        getSize: function() {
            return [$('#uibox').width(), $('#uibox').height()];
        },

        getContent: function() {
            return $('#uibox').find('.boxy-content');
        },

        getPosition: function() {
            var b = $('#uibox');
            return [b.offsetLeft, b.offsetTop];
        },

        getContentSize: function() {
            var c = this.getContent();
            return [c.width(), c.height()];
        },
        _getBoundsForResize: function(width, height) {
            var csize = this.getContentSize();
            var delta = [width - csize[0], height - csize[1]];
            var p = this.getPosition();
            return [Math.max(p[0] - delta[0] / 2, 0),
                    Math.max(p[1] - delta[1] / 2, 0), width, height];
        }
    }
};