
core.editor = {
    start:function(contentId,contentDiv){
        var editor = $config['editor_widget'][contentId];
        editor.save_time = 0;
        editor.save   = function(){
            var d =new Date();
            if( (d.getTime()-editor.save_time)<5000 ){
                return false;
            }
            if('undefined'!=typeof(editor.saveing)){
                editor.saveing.abort();    
            }

            var content = editor.html();
            $('#'+editor.editor_tip).html(L('PUBLIC_SAVING'));
            editor.saveing = $.post(U('widget/Editor/save'), { '_url':editor.editor_query_url, '_name':editor.editor_name, 'content':content },function(){
                editor.editor_old_content = content;
                $('#'+editor.editor_tip).html(L('PUBLIC_SAVED'));

                editor.save_time = d.getTime();
            },'json');
        };
        editor.clean = function(){
            editor.cleanEditorCache();
            editor.editor_old_content = '';
            editor.html('');
        }

        editor.cleanEditorCache = function(){
            clearInterval( editor.save_interval );
            editor.setSaved(true);
            editor.save_interval = 0;
            $.ajax({
                type:"POST",
                url:U('widget/Editor/clean'),
                data:"_url="+encodeURIComponent(editor.editor_query_url)+'&_name='+editor.editor_name,
                async:false
            });
        };

        editor.html = function( html ){
            if( 'undefined'!==typeof(html) ){
                return editor.client.setValue(html);
            }else{
                return editor.client.getValue();
            }
        };
        editor.setSaved = function(res){
            editor.has_saved = res;
        };
        editor.setCacheValue = function(){
            if( editor.cache_confirm == 1 ){
                ui.confirmBox(
                    '提示', 
                    '您上次对该文档的修改还没有发布，是否要恢复这些未发布的内容？', 
                    function(){
                        editor.html( $("#"+contentId+"_cache_value").html() );
                        $("#"+contentId+"_cache_value").remove();

                        M(editor.client.body[0]);
                    },
                    { 'yes':'恢复', 'no':'放弃' },
                    function(){
                        M(editor.client.body[0]);
                        editor.cleanEditorCache();
                    }
                );               
            }else{
                M(editor.client.body[0]);
            }
        }
        
        if("undefined" != typeof(contentId)){
            var create = function(){
                editor.client = new Simditor({
                    textarea: $('textarea[name="'+contentId+'"]'),
                    toolbarFloat:true,
                    toolbarHidden:editor.hidetoolbar,
                    pasteImage:true,
                    toolbar:editor.items,
                    // placeholder:"这里输入文字...",
                    upload:{
                        url:U("widget/Attach/saveEditorImg"),
                        fileKey: 'upload_file',
                        connectionCount: 3,
                        leaveConfirm: '正在上传文件，如果离开上传会自动取消'
                    },
                    mention:{
                        url:U("widget/Search/searchUser", {'depart':1, 'limit':0})
                    },
                    minheight:parseInt($('textarea[name="'+contentId+'"]').css("min-height"))
                });

                editor.client.on('blur',function(){
                    if("undefined" != typeof editor.afterBlur){
                        editor.afterBlur();
                    }
                });
                editor.client.on('valuechanged',function(){
                   editor.save();
                });
            } 

            if( "undefined" == typeof(Simditor) ){
                core.loadFile(THEME_URL+"/js/editor/simditor/scripts/sim-all.js?v="+VERSION,function(){
                    // core.loadFile(THEME_URL+"/js/editor/simditor/scripts/simditor.js?v="+VERSION,function(){
                        create();
                        editor.setCacheValue();
                    // });
                });
            }else{
                create();
                editor.setCacheValue();
            }

        }
    }
}