var setPlaceHolder = function(){
    var doc = document;
    var inputs = doc.getElementsByTagName('input');
    var textareas = doc.getElementsByTagName('textarea');
    var supportPlaceholder='placeholder'in doc.createElement('input');
    var supportPlaceholderText = 'placeholder'in doc.createElement('textarea');
     placeholder=function(input)
     {
         var text=input.getAttribute('placeholder'),
         defaultValue=input.defaultValue || input.value;
         if(defaultValue==''){
            input.value=text;
         }
         input.onfocus=function(){if(input.value===text){this.value=''}};
         input.onblur=function(){if(input.value===''){this.value=text}};
     };
     if(!supportPlaceholder){for(var i=0,len=inputs.length;i<len;i++){var input=inputs[i],text=input.getAttribute('placeholder');if(input.type==='text'&&text){placeholder(input)}}};
     if(!supportPlaceholderText){for(var i=0,len=textareas.length;i<len;i++){var textarea=textareas[i],text=textarea.getAttribute('placeholder');if(textarea.type==='textarea'&&text){placeholder(textarea)}}};
}
$(document).ready(function(){
    setPlaceHolder();
});