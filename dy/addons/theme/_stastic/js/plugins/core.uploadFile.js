/**
 * uploadFile js
 * @author jason
 * @exapmle <form><input type="file" name="attach" onchange="core.uploadFile.upload(this,'','image')" urlquery='attach_type=feed_image'></form>
 *  如果自定义回调函数为myback　则<form><input type="file" name="attach" onchange="core.uploadFile.upload(this,myback,'image')" urlquery='attach_type=feed_image'></form>
 *
 */
core.uploadFile = {
		//给工厂调用的接口
		_init:function(attrs){
			if(attrs.length == 6){
				core.uploadFile.upload(attrs[1],attrs[2],attrs[3],attrs[4],attrs[5]);
			}else if(attrs.length == 5){
				core.uploadFile.upload(attrs[1],attrs[2],attrs[3],attrs[4]);
			}else if(attrs.length == 4){
				core.uploadFile.upload(attrs[1],attrs[2],attrs[3]);
			}else if(attrs.length == 3){
				core.uploadFile.upload(attrs[1],attrs[2]);
			}else{
				//供其他方法使用
				return false;
			}
		},
		//init private
		init:function(obj,callback,type,flag,allowType){
			if("undefined" == typeof(obj)){
				return false;
			}
			this.obj = obj;
			this.callback = "function" != typeof(callback)  ?  '' : callback;
			this.type = "undefined"==typeof(type) || '' == type ? "all":type;
			this.flag = "undefined"==typeof(flag) || '' == flag ? "0":"1";	//0 只能上传图片或者附件，1，允许同时上传附件和图片
			this.allowFileType = "undefined"==typeof(allowType) || '' == allowType ? "":allowType;
			this.urlquery = $(obj).attr('urlquery');
			this.stop = false;
			this.limit = $(obj).attr('limit');
			if("undefined" == typeof(this.limit)){
				this.limit = 4;
			}

			if("undefined" == typeof(this.obj.filehash)){
				this.obj.filehash = new Array();
			}

			//结果展示 保留div
			var hasCreateDiv = false;
			var _this = this;

			this.parentModel = this.getParentDiv();

			$(this.parentModel).parent().find('div').each(function(){
				if($(this).attr('uploadcontent') == _this.type){
					hasCreateDiv = true;
					_this.resultDiv = this;
				}
			});

			if(!hasCreateDiv){
				if($(this.parentModel).parent().find('.input-content').length > 0){
					this.resultDiv = $(this.parentModel).parent().find('.input-content')[0];
					return true;
				}
				//未创建过DIV
				this.createResultDiv();
			}


		},
		createResultDiv:function(){

			this.resultDiv = document.createElement("div");
			$(this.resultDiv).attr('uploadcontent',this.type);
			$(this.resultDiv).addClass('input-content');
			if(this.type == 'image'){
				$(this.resultDiv).html('<ul class="image-list" ></ul>');
			}else{
				$(this.resultDiv).html('<ul class="weibo-file-list"></ul>');
			}

			var hideId = $(this.obj).attr('inputname')+'_ids';

			$(this.resultDiv).append('<input type="hidden" class="attach_ids" value="|" feedtype="'+this.type+'" name="'+hideId+'" id="'+hideId+'">');
			$(this.parentModel).parent().append($(this.resultDiv));

		},
		//api for js public
		upload:function(obj,callback,type,flag,allowType){
			　var _this = this;
			 core.loadFile(THEME_URL+'/js/jquery.form.js?v='+VERSION,function(){
				core.uploadFile.init(obj,callback,type,flag,allowType);
				if(!core.uploadFile.checkFile()){
					if($(_this.resultDiv).find('li').size() <1 ){
						$(_this.resultDiv).remove();
					}
					ui.error( '上传失败' );
					return false;
				}

				//取消原来的
				$(_this.parentModel).parent().find('.input-content').each(function(){
					if( $(this).attr('uploadcontent').length>0 ){
						if( $(this).attr('uploadcontent') != _this.type){
							_this.obj.filehash = new Array();
							$(this).remove();
							_this.createResultDiv();
						}
					}
				});

				//验证附件上传个数 不能大于4个 0为不限制
				var uploadNums = _this.getAttachNums();

				if(_this.limit !=0 && uploadNums >= _this.limit){
					ui.error( '最多上传 '+_this.limit+' 个附件' );
					return false;
				}

				if($(_this.resultDiv).find('.loading').size() < 1 && type=='file'){
					$(_this.resultDiv).find('ul').eq(0).append('<li class="loading"><div class="loads"><img src="'+THEME_URL+'/image/load.gif" style="width:auto;height:auto"></div><p class="tips upload_tips" style="padding:5px"><a href="javascript:core.uploadFile.stopupload()">删除</a></p></li>');
				}

				var uploadTimes = core.uploadFile.updataUploadTimes();

				_this.parentForm  = _this.getParentFrom();
				_this.parentForm.method = "post";
				_this.parentForm.action =  U('edu/Public/upload')+'&'+_this.urlquery+'&type=file';
                 _this.parentForm.enctype = "multipart/form-data";
				$(_this.parentForm).ajaxSubmit({
					dataType:'json',
			        success: function (data) {
			        	core.uploadFile.afterUpload(data.data,data.status,uploadTimes,callback);
			        }
			    });

			});
		},
		clean:function(){
			this.obj.filehash = new Array();
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

			if(this.stop == true){
				return false;
			}
			$(this.resultDiv).find('.loading').remove();
			if($(this.resultDiv).find('li').size() < 1){
				$(this.resultDiv).remove();
			}
			if("undefined" != typeof(this.oldAction)){
				this.parentForm.action = this.oldAction;
				this.parentForm.method = this.oldMethod;
				//$(this.parentForm).attr('id',this.oldId);
			}

			this.stop = true;
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
				M(this.parentForm);
			}

			$(this.resultDiv).find('.loading').remove();

			//验证是否已经上传过了
			var hasUpload = false;
			for(var i in this.obj.filehash){
				if(this.obj.filehash[i] == data.hash){
					hasUpload = true;
				}
			}

			if(hasUpload == true && this.limit != 0){
				ui.error( L('PUBLIC_UPLOAD_ISNOT_TIPIES') );
				return false;
			}

			if(status !="1"){
				var type = this.type;
				$(this.parentModel).parent().find('.input-content').each(function(){
					if($(this).attr('uploadcontent') == type){
						  if($(this).find('.attach_ids').length <0 || $(this).find('.attach_ids').val() == "|"){
							  $(this).remove();
						  }
					}
				});
        		ui.error(data);
        		return false;
        	}

			if(this.stop == true){
				this.stop = false;
				return false;
			}

			// var _now_count = this.getAttachNums()+1;
			// var _limit = this.limit;
			// $("#upload_num").html( _limit-_now_count );

			var func = "undefined" == typeof(this.callback ) ?  '':this.callback ;

			//hash 处理
			this.obj.filehash[data.attach_id] = data.hash;


			if('' !=func){
				if('function'==typeof(func)){
					func(data,this.obj);//执行回调函数
				}
				core.uploadFile.addAttachId(data.attach_id);
			}else{
				this.defaultAfterUpload(data);
			}
		},
		defaultAfterUpload:function(data,notAddId){
			if(this.type=='image'){
				var html = '<li><a class="pic" href="javascript:void(0)"><img src="'+data.src+'" width="100"></a>'
						  +'<a class="name" href="javascript:void(0)" onclick="core.uploadFile.removeAttachId(this,\''+this.type+'\','+data.attach_id+')">'+L('PUBLIC_DELETE')+'</a></li>';
			}else{
				var html = '<li><i class="F-ico ico-'+data.extension+' left"></i><a class="ico-close right" href="javascript:void(0)" onclick="core.uploadFile.removeAttachId(this,\''+this.type+'\','+data.attach_id+')"></a>'
						  +'<div class="name"><a href="javascript:void(0)">'+data.name+'</a><span>('+data.size+')</span></div></li>';
			}
			var _this = this;

			$(this.parentModel).parent().find('.input-content').each(function(){
				if( $(this).attr('uploadcontent').length>0 ){
					if( $(this).attr('uploadcontent') == _this.type){
						$(this).find('ul').append(html);
					}else{
						$(this).remove();
					}
				}
			});
			if("undefined" == typeof(notAddId)){
				core.uploadFile.addAttachId(data.attach_id);
			}
		},
		//public func for after post form
		removeParentDiv:function(){
			if("undeifned" != typeof(this.parentModel)){
				$(this.parentModel).parent().find('.input-content').each(function(){
					if($(this).attr('uploadcontent').length > 0){
						$(this).remove();
					}
				});
			}
		},
		//private
		addAttachId:function(id){
			var _this = this;
			$(this.parentModel).parent().find('.input-content').each(function(){
				//$(this.parentModel).find('.input-content').each(function(){
				if($(this).attr('uploadcontent') == _this.type){
					$(this).find('.attach_ids').val($(this).find('.attach_ids').val()+id+'|');
				}
			});
		},
		getAttachNums:function(){
			var _this = this;
			var nums = 0;
			$(this.parentModel).parent().find('.input-content').each(function(){
				if($(this).attr('uploadcontent') == _this.type){
					var attach_ids =  $(this).find('.attach_ids').val();
					attach_ids = attach_ids.split('|');
					for(var i in attach_ids){
						if(attach_ids[i] != ''){
							nums++;
						}
					}
				}
			});
			return nums;
		},
		//public
		removeAttachId:function(obj,type,id,parentObj,fileinput){
			
			var _this = this;
			var no_give_parent = 0;
			if("undefined" == typeof(parentObj)){
				var parentObj = $(obj).parents('.input-content');	

				no_give_parent = 1;
				$(obj).parent().remove();
			}
			
			if("undefined" != typeof(fileinput)){
				if("undefined" != typeof(fileinput.filehash)){
					fileinput.filehash[id] = '';
				}
			}else{
				this.obj.filehash[id] = '';
			}
			if(parentObj.attr('uploadcontent') == type){
				var ids = parentObj.find('.attach_ids').val();
				parentObj.find('.attach_ids').val(ids.replace('|'+id+'|','|'));
				if(parentObj.find('.attach_ids').val() == "|" && parentObj.find('.loading').size()<1){
					if(no_give_parent==0)
						parentObj.remove();
				}
			}
		},
		//private 默认为父DIV的上一层div
		getParentDiv:function(_parent){

			var parent = "undefined" == typeof(_parent) ? this.obj.parentNode : _parent.parentNode;
			if(parent.nodeName == 'SPAN' || parent.nodeName == 'DIV' || parent.nodeName =='UL'){
				return parent;
			}else{
				return core.uploadFile.getParentDiv(parent);
			}
		},
		getParentFrom:function(_parent){
			if(this.obj.parentNode.nodeName == 'FORM'){
				return this.obj.parentNode;
			}else{
				var parent = "undefined" == typeof(_parent) ? this.obj.parentNode : _parent.parentNode;
				if(parent.nodeName == 'FORM'){
					this.oldAction = parent.action;
					this.oldMethod = parent.method;
					//this.oldId = $(parent).attr('id');
					return parent;
				}else{
					return core.uploadFile.getParentFrom(parent);
				}
			}
		},
		//private
		checkFile:function(){
			var filename = $(this.obj).val();
			var pos = filename.lastIndexOf(".");
		    var str = filename.substring(pos, filename.length)
		    var str1 = str.toLowerCase();
		    if(this.type == 'image'){
    		    if (!/\.(gif|jpg|jpeg|png|bmp)$/.test(str1)) {
    		        return false;
    		    }
    		    return true;
		    }
		    //..else file type
		    return true;
		}
};
