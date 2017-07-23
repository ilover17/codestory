/**
 * 图片切换js
 */
//大图切换
//core.imgshow.scrollImg('<php> echo $id;</php>');
var imgScroll  = function(id){
    this.id = id;
    this.init();
};

//图片一直滚动的JS
var imgScrolling = (function(){
    var todo = {};
    var newsScroll = function(id){
        if(todo[id].scrollimg.parentNode.scrollLeft<(todo[id].scrollimg.clientWidth/2))
            todo[id].scrollimg.parentNode.scrollLeft++;
            else
            todo[id].scrollimg.parentNode.scrollLeft=0;
    }
    var _start=function(id){
        _stopscroll(id);
        todo[id].tm  = window.setInterval(function() {
            newsScroll(id);
        }, 30);
    }
    var _stopscroll = function(id){
        clearInterval(todo[id].tm);
        todo[id].tm = null;
    }
    var _startscroll = function(id){
        _start(id);
    }
    return  {
        init:function(DivId,TableId){
            var simgHtml =  $('#'+DivId+' .simg').html();
            $('#'+DivId+' .simg1').html(simgHtml);
            $('#'+DivId+' .simg2').html(simgHtml);
            $('#'+DivId+' .simg3').html(simgHtml);
            todo[DivId] = {scrollimg:null,tm:null}
            todo[DivId].scrollimg = document.getElementById(TableId);
            _start(DivId);
            $('#'+DivId).mousemove(function(){_stopscroll(DivId)});
            $('#'+DivId).mouseout(function(){_startscroll(DivId)});
        }
    }
})();

var imgTab = function(){};
//图片切换（带移动）
// 最外层添加 mode-node = 'imgAnimate'
var imgAnimate = function(){};


imgScroll.prototype ={
    init:function(){
    var id = this.id
    var sWidth = $("#"+id).width();
        var len = $("#"+id+" ul li").length;
        var index = 0;
        var picTimer;

        $("#"+id).find('img').css({'width':sWidth+'px'});

        var btn = "<div class='btn'>";
        for(var i=0; i < len; i++) {
            btn += "<span></span>";
        }
        btn += "</div><div class='move'><div class='preNext pre'></div><div class='preNext next'></div></div>";

        $("#"+id).append(btn);
        $('#'+id+' .move').css({'width':sWidth+'px'});

        var t = $("#"+id).height()/2 - $('#'+id+' .preNext').height();

        $('#'+id+' .preNext').css({'top':t+'px'});

        $("#"+id+" .btnBg").css("opacity",0.5);

        $("#"+id+" .btn span").css("opacity",0.4).mouseenter(function() {
            index = $("#"+id+" .btn span").index(this);
            showPics(index);
        }).eq(0).trigger("mouseenter");

        $("#"+id+"  .preNext").css("opacity",0.8).hover(function() {
            $(this).stop(true,false).animate({"opacity":"0.5"},300);
        },function() {
            $(this).stop(true,false).animate({"opacity":"0.8"},300);
        });

        $("#"+id+"  .pre").click(function() {
            index -= 1;
            if(index == -1) {
                index = len - 1;
            }
            showPics(index);
        });

        $("#"+id+"  .next").click(function() {
            index += 1;
            if(index == len) {index = 0;}
            showPics(index);
        });

        $("#"+id+"  ul").css("width",sWidth * (len));

        $("#"+id).hover(function() {
            clearInterval(picTimer);
        },function() {
            picTimer = setInterval(function() {
                showPics(index);
                index++;
                if(index == len) {index = 0;}
            },4000);
        }).trigger("mouseleave");

        function showPics(index) {
            var nowLeft = -index*sWidth;
            $("#"+id+" ul").stop(true,false).animate({"left":nowLeft},300);
            $("#"+id+" .btn span").stop(true,false).animate({"opacity":"0.4"},300).eq(index).stop(true,false).animate({"opacity":"1"},300); //
        }
    }
};

core.imgshow ={
        //给工厂调用的接口
        _init:function(attrs){
            if(attrs.length == 3){

            }else{
                return false;   //只是未了加载文件
            }
        },
        loginImg:function(t){
            //登录页面图片切换
            var sWidth = $(".slide-con").width(); //获取焦点图的宽度（显示面积）
            var len = $(".slide-con ul.slide li").length; //获取焦点图个数
            var index = 0;
            var picTimer;
            if("undefined" == typeof(t)){
                t = 4;
            }

            $("#slide-title ul li").css("opacity",0.4).mouseenter(function() {
                index = $("#slide-title li").index(this);
                showPics(index);
            }).eq(0).trigger("mouseenter");

            //本例为左右滚动，即所有li元素都是在同一排向左浮动，所以这里需要计算出外围ul元素的宽度
            $(".slide-con ul.slide").css("width",sWidth * (len));

            var setPicTimer = function(){
                picTimer = setInterval(function() {
                    showPics(index);
                    index++;
                    if(index == len) {index = 0;}
                },t*1000); //此4000代表自动播放的间隔，单位：毫秒
            };

            //鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
            $("#focus").hover(function() {
                clearInterval(picTimer);
                picTimer = null;
            },function() {
                setPicTimer();
            });

            //显示图片函数，根据接收的index值显示相应的内容
            function showPics(index) {
                var nowLeft = -index*sWidth;
                $(".slide-con ul.slide").stop(true,false).animate({"left":nowLeft},300);
                $("#slide-title li").stop(true,false).animate({"opacity":"0.4"},300).eq(index).stop(true,false).animate({"opacity":"1"},300);
                switch(index) {
                    case 0:
                        $('#focus').addClass('bg-blue');
                        $('#focus').removeClass('bg-black');
                        break;
                    case 1:
                        $('#focus').addClass('bg-black');
                        $('#focus').removeClass('bg-blue');
                        break;
                }
            }

            setPicTimer();
        },
        scrollImg:function(id){
            var t = new imgScroll(id);
        },
        slider:function( slider_obj, pnums , mnums,prev_obj,next_obj){
            //左右滚动显示插件
            slider_obj.easySlider({
                prev_obj:prev_obj,
                next_obj:next_obj,
                vertical: false,
                movenum: mnums,
                photonum:pnums,
                speed:1000
            });
        },
        scrolling:function(divId,tabId){
            imgScrolling.init(divId,tabId);
        }
};

//图片左右滚动插件
(function($) {

    $.fn.easySlider = function(options){
        var defaults = {
            prev_obj:'',
            next_obj:'',
            vertical:       false,
            speed:          800,
            auto:           false,
            pause:          2000,
            movenum :       1,
            photonum:     9
        };

        var options = $.extend(defaults, options);

        var t = 0;

        this.each(function() {
            var obj = $(this);
            var s = options.photonum;     //相片总数21
            var w = $("li", obj).width();
            var h = $("li", obj).height();
            var tt =  t = 0;            //总共可以跑动的次数

            if(s<=options.movenum){
                tt = 0;                                  //0 不动
            }else{
                tt = Math.ceil( s / options.movenum);   //21/8
            }

            var wl = w*options.movenum;                 //每次滚动的宽度

            var temp_height = h*options.movenum;        //每次滚动的高度

            if(!options.vertical){
                obj.width(wl);                          //显示的宽度
                obj.height(h);
                $("li", obj).css('float','left');
                $("ul", obj).css('width',s*w);          //总共的宽度
            }else{
                obj.width(w);
                obj.height(temp_height);                //显示的高度
                $("ul", obj).css('width',w);
                if(t){
                    $("ul", obj).css('margin-top',-h*tt*options.movenum);
                }
            }

            obj.css("overflow","hidden");
            obj.css("width",wl);

            $(options.next_obj).find('a').click(function(){
                if( (s - t*options.movenum - options.movenum)  >0  && tt >0){
                    animate("next",true);
                }
            });
            $(options.prev_obj).find('a').click(function(){
                if(tt >0 && t>0){
                    animate("prev",true);
                }
            });

            function animate(dir,clicked){

                switch(dir){
                    case "next":
                        t++;
                        if(t == tt){
                            t = 0; //重新开始
                        }
                        break;
                    case "prev":
                        t--;
                        break;
                    default:
                        break;
                };

                if(!options.vertical) {
                    var p = (-1*t*w*options.movenum);
                    if(p == 0 && dir == 'next'){
                        $("ul",obj).animate({ marginLeft: p }, 500);
                    }else{
                        $("ul",obj).animate({ marginLeft: p }, options.speed);
                    }
                } else {
                    var p = (-1*t*h*options.movenum);
                    if( p == 0 && dir =='next'){
                        $("ul",obj).animate({ marginTop: p }, 500);
                    }else{
                        $("ul",obj).animate({ marginTop: p }, options.speed);
                    }
                };

                if(clicked) clearTimeout(timeout);
                if(options.auto && dir=="next" && !clicked){
                    timeout = setTimeout(function(){
                        animate("next",false);
                    },options.speed+options.pause);
                };
            };
            // init
            var timeout;
            if(options.auto && tt> 0 ){
                timeout = setTimeout(function(){
                    animate("next",false);
                },options.pause);
            };
        });
    };

})(jQuery);
