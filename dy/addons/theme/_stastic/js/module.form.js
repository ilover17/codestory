var ajaxSubmit = function( form ) {
        var args = M.getModelArgs( form );
        var ajaxSubmitIng = $(form).attr('ajaxSubmitIng');
        //此值只是用来处理防止按钮重复点击，如果不希望响应后重复提交，
        //还需要在对应表单节点的callback里面加上 $(this).attr('ajaxSubmitIng',1);  
        //才可以防止重复提交数据
        if("undefined" == typeof(ajaxSubmitIng)){
            ajaxSubmitIng = 0;
        }else if(ajaxSubmitIng > 0){
            //重复提交
            return false;
        }
        ajaxSubmitIng =1;
        $(form).attr('ajaxSubmitIng',ajaxSubmitIng);
        var submit_tips = $(form).find('.js_submit_btn').html();
        $(form).find('.js_submit_btn').html(submit_tips+'中...');
        if( $.browser.msie){
            //如果IE下 处理PLACEHOLDER
            for(var i=0;i<form.length;i++){
                if(undefined != $(form[i]).attr('placeholder') 
                    && form[i].value == $(form[i]).attr('placeholder') ){
                    form[i].value = '';
                }
            }
        }
        M.getJS( THEME_URL + '/js/jquery.form.js?v='+VERSION, function() {
                var options = {
                    dataType:'json',
                    success: function( txt ) {
                        if($.browser.msie ){
                            setPlaceHolder();
                        }
                        $(form).find('.js_submit_btn').html(submit_tips);
                        $(form).attr('ajaxSubmitIng',0);
                        if ( 1 == txt.status ) {
                            if ("function" ===  typeof form.callback ) {
                                form.callback( txt );
                            } else {
                                if("string" == typeof(args.callback)){
                                    eval(args.callback+'()');
                                }else{
                                    ui.success(txt.info);
                                }
                            }
                        } else {
                            ui.error(txt.info, 1.5);
                        }
                        if("undefined" != typeof args.ajaxbutton){
                            $(form.childEvents[args.ajaxbutton][0]).removeClass('btn-ing');
                        }
                    }
                };
                //$(form).append('<input type="hidden" name="token" value="'+$config['token']+'">');
                $( form ).ajaxSubmit( options );
        });
};

(function(){

M.addModelFns({
normal_form: {
    submit: function() {
        var oCollection = this.elements,
            nL = oCollection.length,
            bValid = true, dFirstError;

        for ( var i = 0; i < nL; i ++) {
            var dInput = oCollection[i],
                sName  = dInput.name;

            if ( ! sName || ! dInput.getAttribute( "event-node" ) ) continue;

            ( "function" === typeof dInput.onblur ) && dInput.onblur();

            if ( ! dInput.bIsValid ) {
                bValid = false;
                dFirstError = dFirstError || dInput;
            }
        }

        dFirstError && dFirstError.focus();

        return bValid;
    }
}

}).addEventFns({
input_text: {
    focus: function() {
        this.className='q-txt';
        return false;
    },
    blur: function() {
        this.className='q-txt';

        var oArgs  = M.getEventArgs( this ),
            min    = oArgs.min ? parseInt( oArgs.min ) : 0,
            max    = oArgs.max ? parseInt( oArgs.max ) : 0;

        // 最大和最小长度均小于或等于0，则不进行长度验证
        if ( min <= 0 && max <= 0) {
            return ;
        }

        var dTips  = ( this.parentModel.childEvents[this.getAttribute( "name" ) + "_tips"] || [] )[0],
            sValue = this.value;
        sValue = sValue.replace(/(^\s*)|(\s*$)/g, "");
        var nL = sValue.replace(/[^\x00-\xff]/ig,'xx').length / 2;

        if ( nL <= min-1 || ( max && nL > max ) ) {
            dTips && ( dTips.style.display = "none" );
            tips.error( this, oArgs.error );
            this.bIsValid = false;
        } else {
            //判断是否为用户昵称
            var _this = this;
            if(oArgs.uname != 1){
                tips.success( this );
                dTips && ( dTips.style.display = "" );
                this.bIsValid = true;
            }
        }
        return false;
    },
    load: function() {
        this.className='q-txt';
    }
},
input_nums:{
    focus: function() {
        this.className='q-txt';
        return false;
    },
    blur: function() {
        this.className='q-txt';

        var oArgs  = M.getEventArgs( this ),
            min    = oArgs.min ? parseInt( oArgs.min ) : 0,
            type   = oArgs.type,
            max    = oArgs.max ? parseInt( oArgs.max ) : 0;

        // 最大和最小长度均小于或等于0，则不进行长度验证
        if ( min <= 0 && max <= 0) {
            return ;
        }

        var dTips  = ( this.parentModel.childEvents[this.getAttribute( "name" ) + "_tips"] || [] )[0],
            sValue = this.value;

        //纯数字验证
        var re = /^[0-9]*$/;
        if(!re.test(sValue)){
            dTips && ( dTips.style.display = "none" );
            tips.error(this,L('PUBLIC_TYPE_ISNOT'));
            this.bIsValid = false;
            return false;
        }

        var pholen = sValue.length;
        if(type!='QQ'){
            if(pholen>11){
                tips.error(this,L('PUBLIC_NUMBER_ERROR'));
                this.bIsValid = false;
                return false;
            }
        }
        
        sValue = sValue.replace(/(^\s*)|(\s*$)/g, "");
        var nL = sValue.replace(/[^\x00-\xff]/ig,'xx').length / 2;

        if ( nL <= min-1 || ( max && nL > max ) ) {
            dTips && ( dTips.style.display = "none" );
            tips.error( this, oArgs.error );
            this.bIsValid = false;
        } else {
            tips.success( this );
            dTips && ( dTips.style.display = "" );
            this.bIsValid = true;

        }
        return false;
    },
    load: function() {
        this.className='q-txt';
    }
},
textarea: {
    focus: function() {
        this.className='q-textarea-focus';
    },
    blur: function() {
        this.className='q-textarea';

        var oArgs  = M.getEventArgs( this ),
            min    = oArgs.min ? parseInt( oArgs.min ) : 0,
            max    = oArgs.max ? parseInt( oArgs.max ) : 0;

        // 最大和最小长度均小于或等于0，则不进行长度验证
        if ( min <= 0 && max <= 0) {
            return ;
        }

        if ( $.trim(this.value) ) {
            tips.success( this );
            this.bIsValid = true;
        } else {
            if("undefined" != typeof(oArgs.error )){
                tips.error( this, oArgs.error );
                this.bIsValid = false;
            }
        }
    },
    load: function() {
        this.className='q-textarea';
    }
},
input_date: {
    focus: function() {
        this.className='q-txt';

        var dDate = this,
            oArgs  = M.getEventArgs(this);

        M.getJS( THEME_URL + '/js/rcalendar.js?v='+VERSION, function() {
            rcalendar( dDate, oArgs.mode );
        });
    },
    blur: function() {
        this.className='q-txt';

        var dTips  = ( this.parentModel.childEvents[this.getAttribute( "name" ) + "_tips"] || [] )[0],
            oArgs  = M.getEventArgs(this);
        if(oArgs.min == 0){
                return true;
        }
        var _this = this;
        setTimeout(function(){
            sValue = _this.value;

            if ( ! sValue ) {
                dTips && ( dTips.style.display = "none" );
                tips.error( _this, oArgs.error );
                this.bIsValid = false;
            } else {
                tips.success( _this );
                dTips && ( dTips.style.display = "" );
                _this.bIsValid = true;
            }
        },250);
    },
    load: function() {
        this.className='q-txt';
    }
},
email: {
    focus: function() {
        this.className='q-txt';
        var  x = $(this).offset();

        $(this.dTips).css({'left':x.left+'px','top':x.top+$(this).height()+12+'px','width':$(this).width()+'px'});

    },
    blur: function() {
        this.className='q-txt';

        var dEmail = this;

        var sUrl = dEmail.getAttribute( "checkurl" ),
            sValue = dEmail.value;

        if ( ! sUrl || (  this.dSuggest && this.dSuggest.isEnter ) ) return ;

        $.post( sUrl, { email: sValue }, function( sTxt ) {
            oTxt = eval( "(" + sTxt + ")" );
            var oArgs = M.getEventArgs( dEmail );
            if ( oTxt.status ) {
                "false" == oArgs.success ? tips.clear( dEmail ) : tips.success( dEmail );
                dEmail.bIsValid = true;
            } else {
                "false" == oArgs.error ? tips.clear( dEmail ) : tips.error( dEmail, oTxt.info );
                dEmail.bIsValid = false;
            }
            return true;
        } );
        $(this.dTips).hide();
    },
    load: function() {
        this.className='q-txt';

        var dEmail = this,
            oArgs  = M.getEventArgs( this );

        if ( !oArgs.suffix ) return false;

        var aSuffix = oArgs.suffix.split( "," ),
            dFrag = document.createDocumentFragment(),
            dTips = document.createElement( "div" ),
            dUl = document.createElement( "ul" );


        this.dTips = $(dTips);
        $('body').append(this.dTips);

        dTips.className = "mod-at-wrap";

        dDiv = dTips.appendChild( dTips.cloneNode( false ) );
        dDiv.className = "mod-at";

        dDiv = dDiv.appendChild( dTips.cloneNode( false ) );
        dDiv.className = "mod-at-list";

        dUl = dDiv.appendChild( dUl );
        dUl.className = "at-user-list";

        dTips.style.display = "none";
        dEmail.parentNode.appendChild( dFrag );

        M.addListener( dTips, {
            mouseenter: function() {
                this.isEnter = 1;
            },
            mouseleave: function() {
                this.isEnter = 0;
            }
        } );

        // 附加到Input DOM 上
        dEmail.dSuggest = dTips;

        setInterval( function() {
            var sValue = dEmail.value,
                sTips  = dEmail.dSuggest;
            if ( dEmail.sCacheValue === sValue ) {
                return ;
            } else {
                // 缓存值
                dEmail.sCacheValue = sValue;
            }
            // 空值判断
            if ( ! sValue ) {
                dTips.style.display = "none";
                return ;
            }
            var aValue = sValue.split( "@" ),
                dFrag = document.createDocumentFragment(),
                l = aSuffix.length, sSuffix;

            sInputSuffix = [ "@", aValue[1] ].join( "" ); // 用户输入的邮箱的后缀

            for ( var i = 0; i < l; i ++ ) {
                sSuffix = aSuffix[i];
                if ( aValue[1] && ( "" != aValue[1] ) && ( sSuffix.indexOf( aValue[1] ) !== 1 ) || ( sSuffix === sInputSuffix ) ) {
                    continue;
                }
                var dLi = dLi ? dLi.cloneNode( false ) : document.createElement( "li" ),
                    dA  = dA ? dA.cloneNode( false ) : document.createElement( "a" ),
                    dSpan  = dSpan ? dSpan.cloneNode( false ) : document.createElement( "span" ),
                    dText = dText ? dText.cloneNode( false ) : document.createTextNode( "" );

                dText.nodeValue = [ aValue[0], sSuffix ].join( "" );

                dSpan.appendChild( dText );

                dA.appendChild( dSpan );

                dLi.appendChild( dA );

                dLi.onclick = (function( dInput, sValue, sSuffix ) {
                    return function(e) {
                        dInput.value = [ sValue, sSuffix ].join( "" );
                        // 选择完毕，状态为离开选择下拉条
                        dTips.isEnter = 0;
                        // 自动验证
                        dInput.onblur();
                        return false;
                    };
                })( dEmail, aValue[0], sSuffix );

                dFrag.appendChild( dLi );
            }
            if ( dLi ) {
                dUl.innerHTML = "";
                dUl.appendChild( dFrag );
                dTips.style.display = "";
                $(dUl).find('li').hover(function(){
                    $(this).addClass('hover');
                },function(){
                    $(this).removeClass('hover');
                });

            } else {
                dTips.style.display = "none";
            }
        }, 200 );
    }
},
password: {
    focus: function() {
        this.className='';
    },
    blur: function() {
        this.className='';
        var dWeight = this.parentModel.childModels["password_weight"][0],
            sValue = this.value + "",
            nL = sValue.length;
        var min = 6, max = 15;
        if ( nL < min ) {
            dWeight.style.display = "none";
            tips.error( this, L('PUBLIC_PASSWORD_TIPES_MIN',{'sum':min}));
            this.bIsValid = false;
        } else if ( nL > max ) {
            dWeight.style.display = "none";
            tips.error( this, L('PUBLIC_PASSWORD_TIPES_MAX',{'sum':max}) );
            this.bIsValid = false;
        } else {
            tips.clear( this );
            dWeight.style.display = "";
            this.bIsValid = true;
            this.parentModel.childEvents["repassword"][0].onblur();
        }
    },
    keyup:function(){
        this.value = this.value.replace(/^\s+|\s+$/g,"");
    },
    load: function() {
        this.value = '';
        this.className='';

        var dPwd = this,
            dWeight = this.parentModel.childModels["password_weight"][0],
            aLevel = [ "psw-state-empty", "psw-state-poor", "psw-state-normal", "psw-state-strong" ];

        setInterval( function() {
            var sValue = dPwd.value;
            // 缓存值
            if ( dPwd.sCacheValue === sValue ) {
                return ;
            } else {
                dPwd.sCacheValue = sValue;
            }
            // 空值判断
            if ( ! sValue ) {
                dWeight.className = aLevel[0];
                dWeight.setAttribute('className',aLevel[0]);
                return ;
            }
            var nL = sValue.length;

            if ( nL < 6 ) {
                dWeight.className = aLevel[0];
                dWeight.setAttribute('className',aLevel[0]);
                return ;
            }

            var nLFactor = Math.floor( nL / 10 ) ? 1 : 0;
            var nMixFactor = 0;

            sValue.match( /[a-zA-Z]+/ ) && nMixFactor ++;
            sValue.match( /[0-9]+/ ) && nMixFactor ++;
            sValue.match( /[^a-zA-Z0-9]+/ ) && nMixFactor ++;
            nMixFactor > 1 && nMixFactor --;

            dWeight.className = aLevel[nLFactor + nMixFactor];
            dWeight.setAttribute('className',aLevel[nLFactor + nMixFactor]);

        }, 200 );
    }
},
repassword: {
    focus: function() {
        this.className='';
    },
    keyup:function(){
        this.value = this.value.replace(/^\s+|\s+$/g,"");
    },
    blur: function() {
        this.className='';

        var sPwd = this.parentModel.childEvents["password"][0].value,
            sRePwd = this.value;

        if ( ! sRePwd ) {
            tips.error( this, L('PUBLIC_PLEASE_PASSWORD_ON') );
            this.bIsValid = false;
        } else if ( sPwd !== sRePwd ) {
            tips.error( this, L('PUBLIC_PASSWORD_ISDUBLE_NOT') );
            this.bIsValid = false;
        } else {
            tips.success( this );
            this.bIsValid = true;
        }
    },
    load: function() {
        this.className='';
    }
},
radio: {
    click: function() {
        this.onblur();
    },
    blur: function() {
        var sName  = this.name,
            oRadio = this.parentModel.elements["sex"],
            oArgs  = M.getEventArgs( oRadio[0] ),
            dRadio, nL = oRadio.length, bIsValid = false,
            dLastRadio = oRadio[nL - 1];

        for ( var i = 0; i < nL; i ++ ) {
            dRadio = oRadio[i];
            if ( dRadio.checked ) {
                bIsValid = true;
                break;
            }
        }

        if ( bIsValid ) {
            tips.clear( dLastRadio.parentNode );
        } else {
            tips.error( dLastRadio.parentNode, oArgs.error );
        }

        for ( var i = 0; i < nL; i ++ ) {
            oRadio[i].bIsValid = bIsValid;
        }
    }
},
checkbox: {
    click: function() {
        this.onblur();
    },
    blur: function() {
        var oArgs = M.getEventArgs( this );
        if ( this.checked ) {
            tips.clear( this.parentNode );
            this.bIsValid = true;
        } else {
            tips.error( this.parentNode, oArgs.error );
            this.bIsValid = false;
        }
    }
},
submit_btn: {
    click: function(){
        var args  = M.getEventArgs(this);
        if ( args.info && ! confirm( args.info )) {
            return false;
        }
        try{
            (function( node ) {
                var parent = node.parentNode;
                // 判断node 类型，防止意外循环
                if ( "FORM" === parent.nodeName ) {
                    if ( "false" === args.ajax ) {
                        ( ( "function" !== typeof parent.onsubmit ) || ( false !== parent.onsubmit() ) ) && parent.submit();
                    } else {
                        ajaxSubmit( parent );
                    }
                } else if ( 1 === parent.nodeType ) {
                    arguments.callee( parent );
                }
            })(this);
        }catch(e){
            return true;
        }
        return false;
    }
},
sendBtn: {
    click: function() {
        var parent = this.parentModel;
        return false;
    }
}

});

var tips = {
    init: function( D ) {
        this._initError( D );
        this._initSuccess( D );
    },
    error: function( D, txt ) {
        this.init( D );
        D.dSuccess.style.display = "none";
        D.dError.style.display = "";
        D.dErrorText.nodeValue = txt;
    },
    success: function( D ) {
        this.init( D );
        D.dError.style.display = "none";
        D.dSuccess.style.display = "";
    },
    clear: function( D ) {
        this.init( D );
        D.dError.style.display = "none";
        D.dSuccess.style.display = "none";
    },
    _initError: function( D ) {
        if ( ! D.dError || ! D.dErrorText ) {
            var dFrag = document.createDocumentFragment(),
                dText = document.createTextNode( "" ),
                dB    = document.createElement( "b" ),
                dSpan = document.createElement( "span" ),
                dDiv  = document.createElement( "div" );

            D.dError = dFrag.appendChild( dDiv );
            dDiv.className = "box-ver";
            dDiv.style.display = "none";

            dDiv.appendChild( dSpan );

            dSpan.appendChild( dB );
            dB.className="ico-error";
            D.dErrorText = dSpan.appendChild( dText );

            var dParent = D.parentNode
                dNext   = D.nextSibling;
            if ( dNext ) {
                dParent.insertBefore( dFrag, dNext );
            } else {
                dParent.appendChild( dFrag );
            }
        }
    },
    _initSuccess: function( D ) {
        if ( ! D.dSuccess ) {
            var dFrag = document.createDocumentFragment(),
                dSpan = document.createElement( "span" );

            D.dSuccess = dFrag.appendChild( dSpan );
            dSpan.className = "ico-ok";
            dSpan.style.display = "none";

            var dParent = D.parentNode
                dNext   = D.nextSibling;
            if ( dNext ) {
                dParent.insertBefore( dFrag, dNext );
            } else {
                dParent.appendChild( dFrag );
            }
        }
    }
};

window.tips = tips;

})();