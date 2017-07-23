var CoreSearch = function(obj,args){
    //可变的对象
    this.obj  = obj;
    this.args = args;
    this.resultList = null;
    this.oldKey     = null;//上一次的key值
    this.noDataKey  = null; //上一次没查出数据的值
    this.stoploop   = 0;
    this.searchTime = 0;
    this.searchIntval = null;
    this.search_tips  = ("undefined" == typeof args.search_tips && args.search_tips != '')  
                        ? args.search_tips
                        :'搜索数据';
};
CoreSearch.prototype = {
    init:function(){
        var _this = this;
        //显示隐藏的列表数据
        if(this.args.default_ids != ''){
             $(this.obj.childModels['search_list'][0]).show();
        }
        
        $('#searchbox_'+this.args.id).remove();

        if("undefined" != typeof(this.obj.childEvents['search_link'])){
            //失去焦点停止查询
            $(this.obj.childEvents['search_link'][0]).blur(function(e){
                var e = e || window.event;
                if($(e.target).parent().hasClass('choose-user')){
                    return false;
                }
                if( $.browser.msie ){
                    if( $(e.target).closest('mod-at-wrap') 
                        && $(e.target).parent().hasClass('at-user-list')){
                        return true;
                    }
                }

                setTimeout(function(){
                    //判断_this.args.blurCallback,如果有就执行该方法
                   if(_this.args.blurCallback!=''){
                        if("undefined"!=typeof(_this.args.blurCallback)){
                            eval(_this.args.blurCallback+"()");
                        }
                   }
                    _this.stopFind();
                },250);

            });
            //有数字按下去的时候启动查询
            $(this.obj.childEvents['search_link'][0]).focus(function(){
                _this.startFind();
            });

            $(this.obj.childEvents['search_link'][0]).bind('keyup',function(event){
                var  e= event ? event : window.event; 
                var keycode = e.which||e.keyCode;  
                if(_this.stoploop == 1 && keycode != 13){
                    _this.startFind();
                }
            });

            var oSearch_link = $(this.obj.childEvents['search_link'][0]);
            oSearch_link.bind('keydown',function(event){
                if( oSearch_link.val() != '' ){
                    return true;
                }
                var lastLi = $(this.parentModel).find('ul li:last');
                if(event.keyCode == 8 || event.keyCode == 46){
                    if(lastLi.length >0){
                        lastLi.find('.ico-remove').click();
                    }
                }
            });
        }

        $(this.obj.childModels['search_list'][0]).find('.ico-remove').click(function(){
             _this.removeOne($(this).attr('search_id'),this,_this.args.id);
        });
        

        $('#search_ids_'+this.args.id).val(this.args.default_ids);
        if("undefined" != typeof(_this.args.search_tips) && _this.args.search_tips != ''){
            this.search_tips = _this.args.search_tips;
        }
        //自动开始
        if(this.args.autostart == 1){
            this.startFind();
        }
        core.plugFunc('bindkey');

    },
    startFind:function(){
        var _this = this;
        var loopSearch = function(){
            if(_this.stoploop == 1){
                return true;
            }
            var searchUser = function(searchTime,key){
                //console.log('searchTime:'+searchTime+',key:'+key);
                var args = _this.args;
                args.key = key;
                args.selected_ids = $('#search_ids_'+_this.args.id).val();
                $.post(U('edu/Public/search'),args,function(msg){
                        // console.log('after post:'+searchTime+'->'+_this.searchTime );
                        // 超时了
                        if(searchTime != _this.searchTime || _this.stoploop == 1){
                            return false;
                        }
                        if(msg.status==0 || msg.data == null || msg.data =='' || msg.data.length == 0 ){
                            _this.noDataKey = key;
                            _this._createListDiv(1);
                            return false;
                        }else{
                            var data = msg.data;
                            _this.noDataKey ='';
                            if("undefined" != typeof(_this.args.callbackSelect)){
                                eval("var html = "+_this.args.callbackSelect+"(data)");
                            }else{
                                var html = '<ul class="at-user-list">';
                                for(var i in data){
                                    if("undefined" != typeof(_this.args.selectFirst) && _this.args.selectFirst == 1){
                                        var current = i==0 ? " class='current'" : '';    
                                    }else{
                                        var current = '';
                                    }
                                    
                                    var _search_name = (_this.args.mulit == 1 && data[i].mulit != '') ?  data[i].mulit : data[i].name;
                                    html +='<li search_id="'+data[i].id+'" search_name="'+_search_name+'"  search_note="'+data[i].note+'" '+current+' search_img="'+data[i].img+'">';
                                    if(data[i].img != ''){
                                        //html +='<div class="face"><img src="'+data[i].img+'" width="20px" height="20px" /></div>';
                                    }
                                    if(data[i].note !=''){
                                        data[i].note = "（"+data[i].note+"）";
                                    }
                                    html +='<div class="content"><a href="javascript:void(0)">'+data[i].name+'</a><span>'+data[i].note+'</span></div></li>';
                                }
                                html +='</ul>';
                            }    
                            if(null == _this.resultList || _this.resultList.length < 1){
                                _this._createListDiv();
                            }
                            $('#searchbox_'+_this.args.id).show();
                            _this.resultList.find('.mod-at-list').html(html);
                            _this.resultList.find('.mod-at-list').find('li').hover(function(){
                                $(this).addClass('hover')
                            },function(){
                                $(this).removeClass('hover');
                            });

                            _this.resultList.find('.mod-at-list').find('li').click(function(e){
                                var search_id = $(this).attr('search_id');
                                var search_name = $(this).attr('search_name');
                                var search_note = $(this).attr('search_note');
                                var search_img = $(this).attr('search_img');
                                _this.insertOne(search_id,search_name,search_note,search_img);
                                stopEventPass(e);
                            });
                            //TODO 方向键控制
                            var doSelectOne = function(){
                                if( $('#searchbox_'+_this.args.id).length == 0  
                                    || $('#searchbox_'+_this.args.id).is(':hidden')
                                    ){
                                    return false;
                                }   
                                _this.selectList(_this);
                                //删除掉原来的东西
                                if(typeof(_this.resultList) == 'object' && _this.resultList != null){
                                    _this.resultList.find('.mod-at-list').html('');
                                }
                                $('#searchbox_'+_this.args.id).remove();
                                _this.resultList = null;
                                _this.stoploop = 1;
                                _this.oldKey   = '-1';

                            }
                            if("undefined" != typeof(core.bindkey)){
                                core.bindkey.init(_this.resultList.find('.mod-at-list'),'li','current',doSelectOne);    
                            }else{
                                core.plugInit('bindkey',_this.resultList.find('.mod-at-list'),'li','current',doSelectOne);
                            }
                            
                        }
                },'json');
            };

            var key = _this.obj.childEvents['search_link'][0].value;
            if((_this.noDataKey != null  && getLength(_this.noDataKey)>0 && key.indexOf(_this.noDataKey) >= 0) ){
                //不查找用户了
                //console.log('no need to search noDataKey:'+_this.noDataKey+',oldkey:'+_this.oldKey+',key:'+key);
                return false;
            }if( _this.oldKey == key ){
                if(_this.resultList){
                    _this.resultList.show();
                }
            }else{
                _this.oldKey = key;
                _this.searchTime ++;
                _this._createListDiv(0);//创建提示框
                searchUser(_this.searchTime,key);
            }
        }
        _this.stoploop = 0;
        _this.searchIntval = setInterval(loopSearch,251);
    },
    insertOne:function(search_id,search_name,search_note,search_img){
        if(search_id == '0'){
            return false;
        }
        var _this = this;
        var search_idsInput = $('#search_ids_'+this.args.id);
        var dllist    = $(this.obj.childModels['search_list'][0]);
        var search_ids  = search_idsInput.val();
        var _search_ids = search_ids.split(',');
        var _search_name = search_name.split(',');
        var html = '';
        var _getHtml = function(search_name,search_note,search_id){
            //显示的callback
            if("undefined" != typeof(_this.args.callbackHtml) && _this.args.callbackHtml!=''){
                eval("var html = "+_this.args.callbackHtml+"(search_name,search_id,search_note,search_img)");
            }else{
                var html = '<li><div class="content"><span class="search-name" title="'+search_name+'">'+subStr(search_name,_this.args.strlength,'')+'</span>';
                html += '</div><a class="ico-remove" href="javascript:;" search_id='+search_id+'></a></li>';
            }
            return html; 
        }
        _this.oldKey   = '-1';
        if(_search_name.length > 1){
            //批量的情况 如果已经存在则略过
            var _search_id = search_id.split(',');
            var _search_name_res = new Array();
            var _search_id_res = new Array();

            for(var i in _search_name){
                var found = 0;
                for(var _i in _search_ids){
                    if(_search_ids[_i] == _search_id[i]){
                        found = 1;
                    }
                }
                if(found == 0){
                    
                    html += _getHtml(_search_name[i],_search_name[i],_search_id[i]);  
                    _search_name_res[i] = _search_name[i];
                    _search_id_res[i] = _search_id[i];
                }
            }

            search_id   = _search_id_res.join(',');
            _search_name = _search_name_res;
            
            var mulit = _search_name.length;
            if(mulit == 0 ){
                return false;  
            }
            
        }else{
            //兼容旧数据
            var mulit = 0;
            for(var _i in _search_ids){
                if(_search_ids[_i] == search_id){
                    return false;
                }
            }
        }

        if(this.args.max > 0 ){
            //判断批量的情况
            if(mulit != 0 && (mulit + _search_ids.length) >  this.args.max  ){
                return false;
            }

            if(search_ids!='' && search_ids !=0){

                if(_search_ids.length >= this.args.max ){
                    return false;
                }
                if(_search_ids.length+1 >= this.max){
                    _this.stopFind();
                    //隐藏input
                    $(this.obj.childEvents['search_link'][0]).hide();
                }
            }else{
                if(this.args.max == 1){
                    _this.stopFind();
                    $(this.obj.childEvents['search_link'][0]).hide();
                }
            }
        }

        if(mulit == 0){
         
            html = _getHtml(search_name,search_note,search_id);
        }  
       
        if(this.args.insertCallback != ''){
            eval(this.args.insertCallback+'(search_id,search_name,search_note,search_img)');
        }

        dllist.append(html);
        dllist.show();
        if( search_ids !='' && search_ids !='0'){
            search_idsInput.val(search_ids + "," +search_id);
        }else{
            search_idsInput.val(search_id);
        }


        dllist.find('.ico-remove').click(function(){
           _this.removeOne($(this).attr('search_id'),this,_this.args.id);
        });

        $(this.obj.childEvents['search_link'][0]).val('');
            
        _this.stopFind();     
        return true;
    },
    removeAll:function(){
        $('#search_ids_'+this.args.id).val('');
        $(this.obj.childModels['search_list'][0]).html('');
    },
    removeOne:function(search_id,obj,name){
        var hideInput = $('#search_ids_'+name);
        $(obj).parent().remove();
        $(this.obj.childEvents['search_link'][0]).show();
        var search_ids = hideInput.val();
        var arr = search_ids.split(',');
        var val = new Array();
        for(var i in arr){
             if(arr[i] != search_id && arr[i] !='' && "string" == typeof(arr[i])){
                val[val.length] = arr[i];
             }
        }
        hideInput.val(val.join(','));
        if("undefined" != typeof(this.args.callbackRemove)){
            eval(this.args.callbackRemove+"(search_id)");
        }
     },
     selectList:function(obj){
        if( typeof(obj.resultList) == 'string' || obj.resultList == null){
            return false;
        }
        var curOne = obj.resultList.find('.mod-at-list').find('.current');
        if(curOne.length>0){
            //选人吧
            var search_id = curOne.attr('search_id');
            var search_name = curOne.attr('search_name');
            var search_note = curOne.attr('search_note');
            var search_img = curOne.attr('search_img');
            obj.insertOne(search_id,search_name,search_note,search_img);
        }else{
            return true;
        }
        return true;
    },
    _createListDiv:function(remove){

        if($('#searchbox_'+this.args.id).length > 0  && remove !=1){
            $('#searchbox_'+this.args.id).show();
            this.resultList = $('#searchbox_'+this.args.id);
            return false;
        }
        $('#searchbox_'+this.args.id).remove();
        //创建一个提示框并定位
        var html = "<div id='searchbox_"+this.args.id+"' class='mod-at-wrap' ><div class='mod-at'><div class='mod-at-list'>"
                   +"<div class='mod-at-tips'>"+this.search_tips+"</div>"
                   +"</div></div></div>";
        this.resultList = $(html);
        this.resultList.appendTo($('body'));//追加到当前对象后面

        var  x = $(this.obj.childEvents['search_link'][0]).offset(); 
        if(this.obj.childEvents['search_link'][0].style.display == 'none'){
            this.resultList.css({ 'left':x.left+'px','top':(x.top+$(this.obj.childEvents['search_link'][0]).height()+5)+'px','display':'none'});
        }else{
             this.resultList.css({ 'left':x.left+'px','top':(x.top+$(this.obj.childEvents['search_link'][0]).height()+5)+'px','display':'block'});
        }
        if( this.args.list_width != 0 ){
            this.resultList.css('width',this.args.list_width+'px');
        }
    },
    stopFind:function(){
        this.stoploop = 1;
        this.resultList = null;
        this.oldKey = '-1';
        $('#searchbox_'+this.args.id).remove();
    },
    setArgs:function(args){
        this.args = args;
    }
};
