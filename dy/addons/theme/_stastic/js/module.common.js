M.addModelFns({
    'js_treemenu':{
        load:function(){
            $(this).find("li").find("a").click(function(){
                if ( $(this).parent().hasClass("open") ) {
                    $(this).parent().removeClass("open");
                    $(this).children("span.handle").removeClass("drop")
                }else{
                    $(this).parent().addClass("open");
                    $(this).parent().siblings().removeClass("open");
                    $(this).children("span.handle").addClass("drop")
                };
            });
        }
    },
    'string_text':{
        load:function(){
            var args = M.getModelArgs(this);
            if( args.value == $(this).attr('placeholder') ){
                args.value = '';
            }
            var _this = this;
            core.plugFunc('stringDb',function(){
                var stringText = new core.stringDb(_this,args.inputname,args.value);
                stringText.init();
                var stringInput = _this.childEvents['stringInput'][0];
                var addLink     = _this.childEvents['stringAdd'][0];
                $(addLink).click(function(){
                    stringText.add($(stringInput).val());
                    $(stringInput).val('');
                    return false;
                });
                $(stringInput).bind('keypress',function(e){
                    var keycode = e.which||e.keyCode;
                    if(keycode == 13){
                        stringText.add($(stringInput).val());
                        $(stringInput).val('');
                        return false;
                    }
                    return true;
                });
                $(stringInput).bind('blur',function(){
                    stringText.add($(stringInput).val());
                    $(stringInput).val('');
                    return false;
                });
            });    
        }
    }, 
    'search_widget':{
        load:function(){
            var obj = this;
            var args = M.getModelArgs(obj);
            core.plugFunc('search',function(){
                setTimeout(function(){
                    obj.searchObj = new CoreSearch(obj,args);
                    obj.searchObj.init();
                },'150');
            });
        }
    },
    'date_widget_load':{
        load:function(){
            core.plugFunc('date',function(){
                core.date.bindDateSelect();
            }); 
        }
    },
    'add_pay':{
        callback:function(data){
            ui.success(data.info);
        }
    }
}).addEventFns({
    'select_class':{
        click:function(){
            var args = M.getEventArgs(this);
            ui.box.load(U('edu/Classes/select',{boxtype:args.boxtype}),'班级列表');
        }
    },
    
});