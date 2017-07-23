/**
 * 我就是给微博里面上传图片准备的，怎么着吧
 */
core.uploadImage = {
        //给工厂调用的接口
        _init:function(attrs){
            if(attrs.length == 3){
                core.uploadImage.upload(attrs[1],attrs[2]);
            }else if(attrs.length == 2){
                core.uploadImage.upload(attrs[1]);
            }else{    
                //供其他方法使用
                return false;
            }
        },
        //api for js public
        upload:function(obj,callback){
        　  var _this = this;
            this.obj = obj;
            this.urlquery = $(obj).attr('urlquery');
            if(!core.uploadImage.checkFile()){
                ui.error( '上传类型不允许' )
                return false;
            }

            $(obj).parent().find('.loading').show();

            var uploadTimes = core.uploadImage.updataUploadTimes();

            _this.parentForm  = _this.getParentFrom();
            _this.parentForm.method = "post";
            _this.parentForm.action =  U('edu/Public/upload')+'&'+_this.urlquery+'&type=img';
            _this.parentForm.enctype = "multipart/form-data";
            $(_this.parentForm).ajaxSubmit({
                dataType:'json',
                success: function (data) {
                    core.uploadImage.afterUpload(data.data,data.status,uploadTimes,callback);
                }
            });
        },
        updataUploadTimes:function(){
            if("undefined" == typeof(this.uploadTimes)){
                this.uploadTimes = 1;
            }else{
                this.uploadTimes++;
            }
            return this.uploadTimes;
        },
        stopupload:function(){

            this.updataUploadTimes();
            $('.pic_list').find('.loading').remove();
            if("undefined" != typeof(this.oldAction)){
                this.parentForm.action = this.oldAction;
                this.parentForm.method = this.oldMethod;
            }
        },
        //afterUpload private
        afterUpload:function(data,status,times,callback){
            if(times != this.uploadTimes){
                return false;
            }

            if("undefined" != typeof(this.oldAction)){
                this.parentForm.action = this.oldAction;
                this.parentForm.method = this.oldMethod;
                this.obj.value = '';
            }else{
                var html =  $(this.parentForm).html();
                $(this.parentForm).find('input').remove();
                $(this.parentForm).html(html);
                M($(this.parentForm)[0]);
            }

            this.stopupload();
            //验证是否已经上传过了
            if(status !="1"){
                ui.error(data);
                return false;
            }


            var func = "undefined" == typeof(callback) ?  '':callback;

            if('' !=func){
                if('function'==typeof(func)){
                    func(data);
                }else{
                    eval(func+'(data)');
                }
            }else{
                this.defaultAfterUpload(data);
            }
        },
        //默认的回调
        defaultAfterUpload:function(data){
            $(this.obj).parent().parent().find('.js_show_img').find('img').attr('src',data.src);
            $(this.obj).parent().parent().find('.js_upload').hide();
            $(this.obj).parent().parent().find('.js_show_img').show();
            $(this.obj).parent().parent().find('.js_show_img').find('input').val(data.attach_id);
        },
        getParentFrom:function(_parent){
            if(this.obj.parentNode.nodeName == 'FORM'){
                return this.obj.parentNode;
            }else{
                var parent = "undefined" == typeof(_parent) ? this.obj.parentNode : _parent.parentNode;
                if(parent.nodeName == 'FORM'){
                    this.oldAction = parent.action;
                    this.oldMethod = parent.method;
                    return parent;
                }else{
                    return core.uploadImage.getParentFrom(parent);
                }
            }
        },
        //private
        checkFile:function(){
            var filename = $(this.obj).val();
            var pos = filename.lastIndexOf(".");
            var str = filename.substring(pos, filename.length)
            var str1 = str.toLowerCase();
            if (!/\.(gif|jpg|jpeg|png|bmp)$/.test(str1)) {
                return false;
            }
            return true;
        }
};
