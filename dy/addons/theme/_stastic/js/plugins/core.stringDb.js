//临时存储机制 -- 用于页面上添加标签之类
core.stringDb = function(obj,inputname,tags){
    this.inputname = inputname;
    this.obj  = obj;
    if(tags != ''){
        this.tags = tags.split(',');
    }else{
        this.tags = new Array();
    }
    this.taglist = $(obj).find('ul');
    this.inputhide = $(obj).find("input[name='"+inputname+"']");
};

core.stringDb.prototype = {
    init:function(){
        if(this.tags.length > 0){
            var html = '';
            for(var i in this.tags){
                if(this.tags[i] != ''){
                    html +='<li><a class="tag" href="javascript:;">'+this.tags[i]+'</a><a title="删除" class="icon-close" href="javascript:;"></a></li>';
                }
            }
            this.taglist.html(html);
            this.bindUl();
        }
    },
    bindUl:function(){
        var _this = this;
        this.taglist.find('.ico-close').unbind('click');
        this.taglist.find('.ico-close').bind('click',function(){
            _this.remove($(this).parent().find('.tag').html());
        });
        this.taglist.find('.icon-close').unbind('click');
        this.taglist.find('.icon-close').bind('click',function(){
            _this.remove($(this).parent().find('.tag').html());
        });

    },
    add:function(tag){
        tag = tag.replace(/，/g,',');
        var _tag = tag.split(',');
        var _this = this;
        var add = function(t){
            for(var i in _this.tags){
                if(_this.tags[i] == t){
                    return false;
                }
            }
            if(getLength(t) > 0){
                var html = '<li><a class="tag" href="javascript:;">'+t+'</a><a title="删除" class="icon-close" href="javascript:;"></a></li>';
                _this.tags[_this.tags.length] = t;
                _this.taglist.append(html);
            }

        };

        for(var ii in _tag){
            if(_tag[ii] != ''){
                add(_tag[ii]);
            }
        }

        this.inputhide.val(this.tags.join(','));
        this.bindUl();
    },
    remove:function(tag){
        var del = function(arr,n){
            if(n<0){
                return arr;
            }else{
                return arr.slice(0,n).concat(arr.slice(parseInt(n)+1,arr.length))
            }
        }
        var removeNums = 0;
        for(var i in this.tags){
            if(this.tags[i] == tag){
                this.tags = del(this.tags,i);
                this.taglist.find('li').eq(i).remove();
                this.inputhide.val(this.tags.join(','));
                removeNums ++;
            }
        }
    }
};