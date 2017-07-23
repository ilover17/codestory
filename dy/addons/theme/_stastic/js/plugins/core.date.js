core.date ={
    //绑定日历
    bindDateSelect:function(){
        $('div').not('.rcalendar').bind('click',this._bindDateSelect);
    },
    _bindDateSelect:function(event){
        if($(event.target).hasClass('rcalendar_input')){
            return false;
        }
        if($(this).closest('.rcalendar').length >0){
            return false;
        }
        if( $('#rcalendar').length > 0 ){
            $('#rcalendar').hide();
        }
    },
    unbindDateSelect:function(){
        $('div').not('.rcalendar').unbind('click',this._bindDateSelect);
    }
};
