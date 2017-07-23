// rili(textObj,'id','YmdHis', null)
var rhash = {};
function getAgent(){
	 return new function () {
        var matchs;
        if (matchs = navigator.userAgent.match(/MSIE (\d+(?:\.\d+){0,})/)) {
            this.name = "MSIE";
            this.version = matchs[1];
        }
        else if (matchs = navigator.userAgent.match(/Firefox\/(\d+(?:\.\d+){0,})/)) {
            this.name = "Firefox";
            this.version = matchs[1];
        }
        else if (matchs = navigator.userAgent.match(/Version\/(\d+(?:\.\d+){0,}) Safari/)) {
            this.name = "Safari";
            this.version = matchs[1];
        }
        else if (matchs = navigator.userAgent.match(/Opera\/(\d+(?:\.\d+){0,})/)) {
            this.name = "Opera";
            this.version = matchs[1];
        }
        else if (matchs = navigator.userAgent.match(/Chrome\/(\d+(?:\.\d+){0,})/)) {
            this.name = "Chrome";
            this.version = matchs[1];
        }
        else {
            this.name = "unknown";
            this.version = "unknown";
        }
        return this;
    }
}

function rget_offset_left_top(obj) {
    var offset = $(obj).offset();
    return new Array(offset.left,offset.top);
}

function getRStr(id){
	var str = "";
    str += '<div id="'+id+'_rcalendar_ym">';
    str += '  <div id="'+id+'_prev_month" onclick="rili_change_month(\''+id+'\',this,-1)"><span class="ico-chevron-left"></span>';
    str += '  </div>';
    str += '  <div id="'+id+'_rcalendar_y">';
    str += '    <span id="'+id+'_ryear" style="cursor:pointer" onclick="rili_rselect_years(\''+id+'\',this)" class="r_year"></span>';
    str += '  </div>';
    str += '  <div id="'+id+'_rcalendar_m">';
    str += '    <span id="'+id+'_rmonth" style="cursor:pointer" onclick="rili_rselect_months(\''+id+'\',this)" class="r_month"></span>';
    str += '  </div>';
    str += '  <div id="'+id+'_next_month" onclick="rili_change_month(\''+id+'\',this,1)"><span class="ico-chevron-right"></span>';
    str += '  </div>';
    str += '</div>';
    str += '<div id="'+id+'_rweeks">';
    str += '  <div class="rweek">一</div>';
    str += '  <div class="rweek">二</div>';
    str += '  <div class="rweek">三</div>';
    str += '  <div class="rweek">四</div>';
    str += '  <div class="rweek">五</div>';
    str += '  <div class="rweek">六</div>';
    str += '  <div class="rweek">日</div>';
    str += '</div>';
    str += '<div id="'+id+'_rdates"></div>';
    if(rhash[id].rmode == 'Ymd'){
        str += '<div style="width:265px;height:40px;line-height:40px;overflow:hidden;border-top:#ccc solid 1px;display:none">';
    }else{
        str += '<div style="width:265px;height:40px;line-height:40px;overflow:hidden;border-top:#ccc solid 1px;">';    
    }
    str += '  <div id="'+id+'_rtime">';
    if(rhash[id].rmode == "YmdHis"){
        str += '    <span id="'+id+'_rhour" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rili_rselect_hours(\''+id+'\',this)"></span>:<span id="'+id+'_rminute" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rili_rselect_minutes(\''+id+'\',this)"></span> :<span id="'+id+'_rsecond" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rili_rselect_seconds(\''+id+'\',this)"></span>';
    }else if(rhash[id].rmode == "YmdHi"){
        str += '    <span id="'+id+'_rhour" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rili_rselect_hours(\''+id+'\',this)"></span>:<span id="'+id+'_rminute" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rili_rselect_minutes(\''+id+'\',this)"></span><span id="'+id+'_rsecond" style="display:none"></span>';
    }else{
        str += '    <span id="'+id+'_rhour"  style="display:none"></span><span id="'+id+'_rminute"  style="display:none"></span><span id="'+id+'_rsecond"  style="display:none"></span>';
    }
    str += '  </div>';
    str += '  <div id="'+id+'_rbtns">';
    str += '  </div>';
    str += '</div>';
    return str;
}
function getRCss(id){
	var css = "";
    if (document.compatMode == "BackCompat" && navigator.userAgent.indexOf("MSIE") != -1) {
        css += "#"+id+"_rcalendar{overflow:hidden;padding:4px;width:200px;height:200px;background:#fff;font-size:12px;}";
        css += "#"+id+"_rcalendar_ym{overflow:hidden;margin-bottom:4px;width:216px;height:14px;}";
        css += "#"+id+"_rcalendar_y{float:left;padding-left:2px;width:50px;color:#777;font-weight:bold;}";
        css += "#"+id+"_rcalendar_m{float:left;width:50px;color:#777;}";
        css += "#"+id+"_rweeks{overflow:hidden;width:217px;height:20px;}";
        css += "#"+id+"_rdates{overflow:hiddenborder:#bbb solid 1px;width:210px;border-radius:0 0 2px 2px;}";
        css += ".rweek{float:left;overflow:hidden;padding:4px 0 4px 0;width:31px;height:20px;border-top:# C54333 solid 1px;background:#dd4b39;color:#fff;text-align:center;font-size:10px;}";
        css += ".rdate{float:left;overflow:hidden;padding:4px 0 4px 0;width:29px;height:20px;text-align:center;cursor:pointer;}";
        css += "#"+id+"_ryears{border:1px solid #ccc;border-top:0;background:#fff;color:#fff;text-align:center;}";
        css += ".ryear{overflow:hidden;padding:4px 4px 4px 4px;width:36px;height:20px;font-weight:bold;cursor:pointer;}";
        css += "#"+id+"_ryear_add{overflow:hidden;padding:0;width:36px;height:12px;cursor:pointer;}";
        css += "#"+id+"_rmonths{overflow:hidden;width:80px;height:81px;border:1px solid #ccc;border-top:0;background:#fff;color:#fff;text-align:center;font-weight:bold;}";
        css += ".rmonth{float:left;overflow:hidden;padding:4px 4px 4px 4px;width:26px;height:20px;cursor:pointer;}";
        css += "#"+id+"_rtime{float:left;overflow:hidden;width:90px;height:13px;}";
        css += "#"+id+"_rhour{padding:0 7px 0 7px;background:#e9e9e9;cursor:pointer;}";
        css += "#"+id+"_rminute{padding:0 7px 0 7px;background:#e9e9e9;cursor:pointer;}";
        css += "#"+id+"_rsecond{padding:0 7px 0 7px;background:#e9e9e9;cursor:pointer;}";
        css += "#"+id+"_rbtns{float:right;overflow:hidden;margin-left:10px;width:90px;height:13px;text-align:right;}";
        css += "#"+id+"_rhours{overflow:hidden;width:104px;height:145px;}";
        css += ".rhour{float:left;overflow:hidden;padding:3px 7px 3px 7px;height:18px;background:#fff;color:#2b2b2b;cursor:pointer;}";
        css += "#"+id+"_rminutes{overflow:hidden;width:104px;height:75px;}";
        css += ".rminute{float:left;overflow:hidden;padding:3px 7px 3px 7px;height:18px;background:#fff;color:#2b2b2b;cursor:pointer;}";
        css += "#"+id+"_rseconds{overflow:hidden;width:104px;height:54px;}";
        css += ".rsecond{float:left;overflow:hidden;padding:3px 7px 3px 7px;height:18px;background:#fff;color:#2b2b2b;cursor:pointer;}";
    }
    else {//173  17  190
        css += "#"+id+"_rcalendar{overflow:hidden;width:265px;background:#fff;box-shadow:0 7px 21px rgba(0,0,0,0.1);font-size:12px;border:#ccc solid 1px;border-radius:3px;}";
        css += "#"+id+"_rcalendar_ym{position:relative;overflow:hidden;width:265px;height:45px;text-align:center;font-family:arial;}";
        css += "#"+id+"_rcalendar_y{display:inline-block;margin-top:7px;color:#333;}";
        css += "#"+id+"_ryear{display:inline-block;width:55px;border-radius:2px;font-size:18px;line-height:25px;}";
        css += "#"+id+"_ryear:hover{width:55px;height:25px;background:#eee;}";
        css += "#"+id+"_rcalendar_m{display:inline-block;width:32px;color:#333;}";
        css += "#"+id+"_rmonth{display:inline-block;width:32px;font-size:18px;line-height:25px;}";
        css += "#"+id+"_rmonth:hover{border-radius:2px;background:#eee;}";
        css += "#"+id+"_rweeks{overflow:hidden;padding:0 10px;width:245px;color:#333;}";
        css += "#"+id+"_rdates{overflow:hidden;padding:0 10px 10px;width:245px;}";
        css += ".rweek{float:left;overflow:hidden;padding:4px 0 4px 0;width:35px;height:16px;border-top:# C54333 solid 1px;color:#999;text-align:center;font-size:10px;line-height:16px;}";
        css += ".rdate{float:left;overflow:hidden;padding:6px 0 6px 0;width:35px;height:17px;border-radius:2px;text-align:center;line-height:17px;cursor:pointer;}";
        css += ".rdate:hover{border-radius:2px;background:#eee;color:#333 !important;}";
        css += "#"+id+"_ryears{border-radius:2px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);color:#2b2b2b;text-align:center;}";
        css += ".ryear{overflow:hidden;padding:6px 0 6px 0;width:52px;height:16px;border-bottom:#e6e6e6 solid 1px;line-height:16px;cursor:pointer;}";
        css += ".ryear:hover{background:#0398db;color:#fff;}";
        css += "#"+id+"_ryear_add{overflow:hidden;padding:5px 0;height:16px;background:#eee;line-height:16px;cursor:pointer;}";
        css += "#"+id+"_rmonths{overflow:hidden;width:60px;border-radius:2px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);text-align:center;}";
        css += ".rmonth{float:left;overflow:hidden;padding:6px 0 6px 0;width:29px;height:17px;border-right:#e6e6e6 solid 1px;border-bottom:#e6e6e6 solid 1px;line-height:17px;cursor:pointer;}";
        css += ".rmonth:hover{background:#0398db;color:#fff !important;}";
        css += "#"+id+"_rtime{float:left;overflow:hidden;margin:0 10px;}";
        css += "#"+id+"_rhour{display:inline-block;padding:0 5px;margin:0 5px 0 0;height:20px;border-radius:2px;background:#e9e9e9;line-height:20px;cursor:pointer;}";
        css += "#"+id+"_rminute{display:inline-block;padding:0 5px;margin:0 0 0 5px;height:20px;border-radius:2px;background:#e9e9e9;line-height:20px;cursor:pointer;}";
        css += "#"+id+"_rsecond{display:inline-block;padding:0 5px;margin:0 0 0 5px;height:20px;border-radius:2px;background:#e9e9e9;line-height:20px;cursor:pointer;}";
        css += "#"+id+"_rbtns{float:right;overflow:hidden;margin:0 10px;width:90px;text-align:right;}";
        css += "#"+id+"_rhours{overflow:hidden;padding:10px;width:245px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);}";
        css += ".rhour{float:left;overflow:hidden;width:30px;height:30px;border-radius:2px;color:#333;text-align:center;line-height:30px;cursor:pointer;}";
        css += ".rhour:hover{background:#eee;}";
        css += "#"+id+"_rminutes{overflow:hidden;padding:10px;width:245px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);}";
        css += ".rminute{float:left;overflow:hidden;width:30px;height:30px;border-radius:2px;color:#333;text-align:center;line-height:30px;cursor:pointer;}";
        css += ".rminute:hover{background:#eee;}";
        css += "#"+id+"_rseconds{overflow:hidden;padding:10px;width:245px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);}";
        css += ".rsecond{float:left;overflow:hidden;width:30px;height:30px;border-radius:2px;color:#333;text-align:center;line-height:30px;cursor:pointer;}";
        css += ".rsecond:hover{background:#eee;}";
        css += ".day_cur{width:29px;height:29px;background:url("+THEME_URL+"/image/calendar.png) no-repeat 0 -61px;}";
        css += "#"+id+"_prev_month{position:absolute;top:7px;left:10px;display:inline-block;width:25px;height:25px;border-radius:2px;vertical-align:-7px;line-height:25px;cursor:pointer;}";
        css += "#"+id+"_prev_month span{line-height:25px;}";
        css += "#"+id+"_next_month span{line-height:25px;}";
        css += "#"+id+"_prev_month:hover{position:absolute;top:7px;left:10px;display:inline-block;width:25px;height:25px;background:#eee;color:#0398db;vertical-align:-7px;line-height:25px;cursor:pointer;}";
        css += "#"+id+"_next_month{position:absolute;top:7px;right:10px;display:inline-block;width:25px;height:25px;border-radius:2px;vertical-align:-7px;line-height:25px;cursor:pointer;}";
        css += "#"+id+"_next_month:hover{position:absolute;top:7px;right:10px;display:inline-block;width:25px;height:25px;background:#eee;color:#0398db;vertical-align:-7px;line-height:25px;cursor:pointer;}";
    }
    return css;
}
function rili(text,id,mode,retfunction){
	var r = {
		'id':id,
		'div_rcalendar':null,
		'ryears':null,
		'rmonths':null,
		'rdates':null,
		'rhours':null,
		'rminutes':null,
		'rseconds':null,
		'ryear':null,
		'rmonth':null,
		'rhour':null,
		'rminute':null,
		'rsecond':null,
		'robj_date':null,
		'rnow':null,
		'rc_browser':getAgent(),
		'rtext_date':null,
		'rmode':mode,
		'rcalendar_function':retfunction
	}

    rhash[r.id] = r;

	r.rnow = new Date();
    r.rtext_date = typeof(text)=='object' ? text :document.getElementById(text);
    if(r.rmode == '1'){
        r.rmode = 'Ymd';
    }else if(r.rmode == 'full'){
        r.rmode = 'YmdHis';
    }


    try { //获取文本域中的日期
        if(r.rtext_date.value == ''){
            r.robj_date = new Date(r.rnow.getTime());
        }else{
            var ymdhis = r.rtext_date.value.split(/[^\d]+/);
            ymdhis[0] = parseInt(ymdhis[0]);
            ymdhis[1] = parseInt(ymdhis[1].replace(/^0(\d)/, '$1'));
            ymdhis[2] = parseInt(ymdhis[2].replace(/^0(\d)/, '$1'));
            ymdhis[3] = (ymdhis[3] == null || ymdhis[3] == "") ? 0 : parseInt(ymdhis[3].replace(/^0(\d)/, '$1'));
            ymdhis[4] = (ymdhis[4] == null || ymdhis[4] == "") ? 0 : parseInt(ymdhis[4].replace(/^0(\d)/, '$1'));
            ymdhis[5] = (ymdhis[5] == null || ymdhis[5] == "") ? 0 : parseInt(ymdhis[5].replace(/^0(\d)/, '$1'));
            r.robj_date = new Date(ymdhis[0], ymdhis[1] - 1, ymdhis[2], ymdhis[3], ymdhis[4], ymdhis[5]);
            if (isNaN(r.robj_date.getTime())) {
                r.robj_date = new Date(r.rnow.getTime());
            }
        }
    }
    catch (e) {
        r.robj_date = new Date(r.rnow.getTime());
    }

    //设置颜色选择框的样式 BEGIN
    var css = getRCss(r.id);
    if(typeof(document.createStyleSheet) != "undefined"){
        var style = document.createStyleSheet();
        style.cssText = css;
    }else{
        var style = document.createElement("style");
        style.type = "text/css";
        style.textContent = css;
        document.getElementsByTagName("HEAD").item(0).appendChild(style);
    }
    //设置颜色选择框的样式 END

    r.div_rcalendar = document.createElement("div");
    r.div_rcalendar.setAttribute("id", r.id+"_rcalendar");
    r.div_rcalendar.setAttribute('class','rcalendar');
    // r.div_rcalendar.style.position = "absolute";
    // r.div_rcalendar.style.zIndex = 999999;
    r.div_rcalendar.style.background = "#FFFFFF";
    //r.div_rcalendar.style.display = "none";

    var str = getRStr(r.id);
   
    r.div_rcalendar.innerHTML = str;

    r.ryears = document.createElement("div");
    r.ryears.setAttribute("id", r.id+"_ryears");
    r.ryears.style.position = "absolute";
    r.ryears.style.display = "none";

    r.rmonths = document.createElement("div");
    r.rmonths.setAttribute("id", r.id+"_rmonths");
    r.rmonths.style.position = "absolute";
    r.rmonths.style.display = "none";
    rili_rfill_rmonths(r.id);

    if( r.rmode != 'Ymd'){
        r.rhours = document.createElement("div");
        r.rhours.setAttribute("id", r.id+"_rhours");
        r.rhours.style.position = "absolute";
        r.rhours.style.display = "none";
        rili_rfill_rhours(r.id);    
    }

    if(r.rmode == 'YmdHi' || r.rmode =='YmdHis'){
        r.rminutes = document.createElement("div");
        r.rminutes.setAttribute("id", r.id+"_rminutes");
        r.rminutes.style.position = "absolute";
        r.rminutes.style.display = "none";
        rili_rfill_rminutes(r.id);
    }

    if(r.rmode =='YmdHis'){
        r.rseconds = document.createElement("div");
        r.rseconds.setAttribute("id", r.id+"_rseconds");
        r.rseconds.style.position = "absolute";
        r.rseconds.style.display = "none";
        rili_rfill_rseconds(r.id);
    }
    if($('#'+r.id).length > 0){
    	$('#'+r.id).append(r.div_rcalendar);
    }else{
    	document.body.appendChild(r.div_rcalendar);	
    }
    
    r.div_rcalendar.appendChild(r.ryears);
    r.div_rcalendar.appendChild(r.rmonths);
    r.rdates = document.getElementById(r.id+"_rdates");
    if( r.rmode != 'Ymd'){
        r.div_rcalendar.appendChild(r.rhours);
        if(r.rmode == 'YmdHis' || r.rmode == 'YmdHi'){
            r.div_rcalendar.appendChild(r.rminutes);
            if(r.rmode == 'YmdHis'){
                r.div_rcalendar.appendChild(r.rseconds);
            }
        }
    }
    r.ryear = document.getElementById(r.id+"_ryear");
    r.rmonth = document.getElementById(r.id+"_rmonth");
    r.rhour = document.getElementById(r.id+"_rhour");
    r.rminute = document.getElementById(r.id+"_rminute");
    r.rsecond = document.getElementById(r.id+"_rsecond");

    if (r.rmode != "Ymd"){
        document.getElementById(r.id+"_rtime").style.visibility = "visible";
    }else{
        document.getElementById(r.id+"_rtime").style.visibility = "hidden";    
    }
        

    //填写年和月
    r.ryear.innerHTML = r.robj_date.getFullYear();
    r.rmonth.innerHTML = r.robj_date.getMonth() + 1 < 10 ? '0' + (r.robj_date.getMonth() + 1) : r.robj_date.getMonth() + 1;
    if(r.rmode != 'Ymd'){
        r.rhour.innerHTML = r.robj_date.getHours() < 10 ? '0' + r.robj_date.getHours() : r.robj_date.getHours();
        if(r.rmode == 'YmdHis' || r.rmode == 'YmdHi'){
            r.rminute.innerHTML = r.robj_date.getMinutes() < 10 ? '0' + r.robj_date.getMinutes() : r.robj_date.getMinutes();
            if(r.rmode == "YmdHis"){
                r.rsecond.innerHTML = r.robj_date.getSeconds() < 10 ? '0' + r.robj_date.getSeconds() : r.robj_date.getSeconds();    
            }
        }
    }

    rili_rfill_ryears(r.id);
    rili_rfill_rdates(r.id); //输出日期表

    
}
function rili_rfill_ryears(id,year) {
    year = year ? year : rhash[id].robj_date.getFullYear();
    str = '';
    for (var y = year - 2; y <= year + 2; y++) {
        str += '<div class="ryear" onclick="rili_rset_year(\''+id+'\',this.innerHTML)">' + y + '</div>';
    }
    str += '<div id="ryear_add">';
    str += '<span style="width:25px;height:16px" onclick="rili_rfill_ryears(\''+id+'\',' + (year - 5) + ')">&nbsp;&nbsp;-&nbsp;&nbsp;</span>';
    str += '<span style="width:25px;height:16px" onclick="rili_rfill_ryears(\''+id+'\',' + (year + 5) + ')">&nbsp;&nbsp;+&nbsp;&nbsp;</span>';
    str += '</div>';
    rhash[id].ryears.innerHTML = str;
}
function rili_rfill_rmonths(id) {
    str = '';
    for (var m = 1; m <= 12; m++) {
        str += '<div class="rmonth" onclick="rili_rset_month(\''+id+'\',this.innerHTML)">' + (m < 10 ? '0' + m : m) + '</div>';
    }
    rhash[id].rmonths.innerHTML = str;
}
function rili_rfill_rdates(id) {
    var y = parseInt(rhash[id].ryear.innerHTML);
    var m = parseInt(rhash[id].rmonth.innerHTML.replace(/^0(\d)/, '$1'));
    var first_day_of_month = new Date(y, m - 1, 1); //当月第一天
    var date_b = new Date(y, m - 1, 1);
    var w = date_b.getDay(); //第一天是星期几
    if(w ==0){
        w = 7;
    }
    date_b.setDate(1 + 1 - w ); //计算应该开始的日期    

    var last_day_of_month = new Date(y, m, 0); //当月最后一天
    var date_e = new Date(y, m, 0);
    w = date_e.getDay(); //最后一天是星期几，
    if(w == 0){
        w = 7;
    }
    date_e.setDate(date_e.getDate() + 7 - w);
    
    var str = "";
    var nums = 0;
    for (var d = date_b; d.getTime() <= date_e.getTime(); d.setDate(d.getDate() + 1)) {
        var color, m_add;
        if (d.getTime() < first_day_of_month.getTime()) {
            //上月
            color = '#999999';
            m_add = '-1';
        }
        else if (d.getTime() > last_day_of_month.getTime()) {
            //下月
            color = '#999999';
            m_add = '1';
        }
        else {
            color = '#2b2b2b';
            m_add = '0';
        }
        if (d.getDate() == rhash[id].rnow.getDate() && d.getMonth() == rhash[id].rnow.getMonth() && d.getFullYear() == rhash[id].rnow.getFullYear()) {
            //今天颜色
            color = '#0398db';
        }

        var font_weight = '';
        if (d.getDate() == rhash[id].robj_date.getDate() && m_add == '0') {
            font_weight = ' font-weight:bold;background:#0398db';
            //选中当天的颜色
            color = "#fff";
        }
        str += '<div class="rdate" style="color:' + color + ';' + font_weight + '" onclick="rili_rset_date(\''+id+'\',this.innerHTML, ' + m_add + ',this)" m_add="'+m_add+'">' + d.getDate() + '</div>';
        nums ++;
    }
    if(nums <= 35){
        for(var i=0;i<7;i++){
            str += '<div class="rdate" style="color:#999999;" onclick="rili_rset_date(\''+id+'\',this.innerHTML,1,this)" m_add="1">' + d.getDate() + '</div>';
            d.setDate(d.getDate() + 1)
        }
    }
    rhash[id].rdates.innerHTML = str;
}
function rili_rfill_rhours(id) {
    var str = '';
    for (var h = 0; h < 24; h++) {
        d.setDate(d.getDate() + 1);
        str += '<div class="rhour" onclick="rili_rset_hour(\''+id+'\',this.innerHTML)">' + (h < 10 ? '0' + h : h) + '</div>';
    }
    rhash[id].rhours.innerHTML = str;
}
function rili_rfill_rminutes(id) {
    var str = '';
    for (var m = 0; m < 60; m += 5) {
        str += '<div class="rminute" onclick="rili_rset_minute(\''+id+'\',this.innerHTML)">' + (m < 10 ? '0' + m : m) + '</div>';
    }
    rhash[id].rminutes.innerHTML = str;
}
function rili_rfill_rseconds(id) {
    var str = '';
    for (var s = 0; s < 60; s += 5) {
        str += '<div class="rsecond" onclick="rili_rset_second(\''+id+'\',this.innerHTML)">' + (s < 10 ? '0' + s : s) + '</div>';
    }
    rhash[id].rseconds.innerHTML = str;
}
function rili_rselect_years(id,span_year) {
    if (rhash[id].ryears.style.display == "none") {
        var left_top = rget_offset_left_top(span_year);
        // rhash[id].ryears.style.left = (left_top[0] - parseInt(rhash[id].div_rcalendar.style.left)) + "px";
        // rhash[id].ryears.style.top = (left_top[1] - parseInt(rhash[id].div_rcalendar.style.top) + span_year.offsetHeight) + "px";
        rhash[id].ryears.style.left = (left_top[0] - 230 ) +  "px";
        rhash[id].ryears.style.top = (left_top[1] + span_year.offsetHeight - 110 ) + "px";
        if (rhash[id].rc_browser.name == "Opera") {
            rhash[id].ryears.style.left = (parseInt(rhash[id].ryears.style.left) - 10) + "px";
            rhash[id].ryears.style.top = (parseInt(rhash[id].ryears.style.top) - 1) + "px";
        }
        rhash[id].ryears.style.display = "";

        if(rhash[id].rmode !='Ymd'){
            rhash[id].rhours.style.display = "none";
            if(rhash[id].rmode == 'YmdHi' || rhash[id].rmode == 'YmdHis'){
                rhash[id].rmonths.style.display = "none";
                if(rhash[id].rmode == 'YmdHis'){
                    rhash[id].rseconds.style.display = "none";
                }
            }
        }
    }
    else {
        rhash[id].ryears.style.display = "none";
    }
}
function rili_rselect_months(id,span_month) {
    if (rhash[id].rmonths.style.display == "none") {
        var left_top = rget_offset_left_top(span_month);
        // rhash[id].rmonths.style.left = (left_top[0] - parseInt(rhash[id].div_rcalendar.style.left) - 6) + "px";
        // rhash[id].rmonths.style.top = (left_top[1] - parseInt(rhash[id].div_rcalendar.style.top) + span_month.offsetHeight) + "px";
        rhash[id].rmonths.style.left = (left_top[0]  - 236)  + "px";
        rhash[id].rmonths.style.top = (left_top[1]  + span_month.offsetHeight - 110) + "px";
        if (rhash[id].rc_browser.name == "Opera") {
            rhash[id].rmonths.style.left = (parseInt(rhash[id].rmonths.style.left) - 1) + "px";
            rhash[id].rmonths.style.top = (parseInt(rhash[id].rmonths.style.top) - 1) + "px";
        }
        rhash[id].rmonths.style.display = "";

        if(rhash[id].rmode !='Ymd'){
            rhash[id].rhours.style.display = "none";
            if(rhash[id].rmode == 'YmdHi' || rhash[id].rmode == 'YmdHis'){
                rhash[id].rmonths.style.display = "none";
                if(rhash[id].rmode == 'YmdHis'){
                    rhash[id].rseconds.style.display = "none";
                }
            }
        }
    }
    else {
        rhash[id].rmonths.style.display = "none";
    }
}
function rili_rselect_hours(id,span_hour) {
    if (rhash[id].rhours.style.display == "none") {
        var left_top = rget_offset_left_top(span_hour);
        // rhash[id].rhours.style.left = (left_top[0] - parseInt(rhash[id].div_rcalendar.style.left)) + "px";
       	// rhash[id].rhours.style.top = (left_top[1] - parseInt(rhash[id].div_rcalendar.style.top) - 150) + "px";
        rhash[id].rhours.style.left = (left_top[0] ) + "px";
        rhash[id].rhours.style.top = (left_top[1]  - 115) + "px";
        if (rhash[id].rc_browser.name == "Opera") {
            rhash[id].rhours.style.left = (parseInt(rhash[id].rhours.style.left) - 1) + "px";
            rhash[id].rhours.style.top = (parseInt(rhash[id].rhours.style.top) - 1) + "px";
        }
        rhash[id].ryears.style.display = "none";
        rhash[id].rminutes.style.display = "none";
        rhash[id].rhours.style.display = "";
        if(rhash[id].rmode == 'YmdHi' || rhash[id].rmode == 'YmdHis'){
            rhash[id].rmonths.style.display = "none";
            if(rhash[id].rmode == 'YmdHis'){
                rhash[id].rseconds.style.display = "none";
            }
        }
    }
    else {
        rhash[id].rhours.style.display = "none";
    }
}
function rili_rselect_minutes(id,span_minute) {
    if (rhash[id].rminutes.style.display == "none") {
        var left_top = rget_offset_left_top(span_minute);
        // rhash[id].rminutes.style.left = (left_top[0] - parseInt(rhash[id].div_rcalendar.style.left)) + "px";
        // rhash[id].rminutes.style.top = (left_top[1] - parseInt(rhash[id].div_rcalendar.style.top) - 80) + "px";
        rhash[id].rminutes.style.left = (left_top[0] ) + "px";
        rhash[id].rminutes.style.top = (left_top[1] - 90) + "px";
        if (rhash[id].rc_browser.name == "Opera") {
            rhash[id].rminutes.style.left = (parseInt(rhash[id].rminutes.style.left) - 1) + "px";
           	rhash[id].rminutes.style.top = (parseInt(rhash[id].rminutes.style.top) - 1) + "px";
        }
        rhash[id].rminutes.style.display = "";

        rhash[id].ryears.style.display = "none";
        rhash[id].rmonths.style.display = "none";
        rhash[id].rhours.style.display = "none";
        if(rhash[id].rmode == 'YmdHis'){
            rhash[id].rseconds.style.display = "none";
        }
    }
    else {
        rhash[id].rminutes.style.display = "none";
    }
}
function rili_rselect_seconds(id,span_second) {
    if (rhash[id].rseconds.style.display == "none") {
        var left_top = rget_offset_left_top(span_second);
        // rhash[id].rseconds.style.left = (left_top[0] - parseInt(rhash[id].div_rcalendar.style.left)) + "px";
        // rhash[id].rseconds.style.top = (left_top[1] - parseInt(rhash[id].div_rcalendar.style.top) - 55) + "px";
        rhash[id].rseconds.style.left = (left_top[0] ) + "px";
        rhash[id].rseconds.style.top = (left_top[1] - 90) + "px";
        if (rhash[id].rc_browser.name == "Opera") {
            rhash[id].rseconds.style.left = (parseInt(rhash[id].rseconds.style.left) - 1) + "px";
            rhash[id].rseconds.style.top = (parseInt(rhash[id].rseconds.style.top) - 1) + "px";
        }
        rhash[id].rseconds.style.display = "";

        rhash[id].ryears.style.display = "none";
        rhash[id].rmonths.style.display = "none";
        rhash[id].rhours.style.display = "none";
        rhash[id].rminutes.style.display = "none";
    }
    else {
        rhash[id].rseconds.style.display = "none";
    }
}

function rili_rset_year(id,y) {
    rhash[id].ryear.innerHTML = y;
    rili_rfill_rdates(id);
    rhash[id].ryears.style.display = "none";
}

function rili_change_month(id,obj,flag){
    var year  = $(obj).parent().find('.r_year').html();
    var month = $(obj).parent().find('.r_month').html();
    var to_month = 0;
    var to_year = 0;
    if( flag>0 ){
        to_month = parseInt(month)+1;
        if(to_month>12){
            to_month = 1;
            to_year = parseInt(year)+1;
        }
    }else{
        to_month = parseInt(month)-1;
        if(to_month < 1){
            to_month = 12;
            to_year  = parseInt(year)-1;
        }
    }
    if(to_year != 0){
        rili_rset_year(id,to_year);
    }
    rili_rset_month(id,to_month);
}

function rili_rset_month(id,m) {
    rhash[id].rmonth.innerHTML = m;
    rili_rfill_rdates(id);
    rhash[id].rmonths.style.display = "none";
}
function rili_rset_hour(id,h) {
    rhash[id].rhour.innerHTML = h;
    rhash[id].rhours.style.display = "none";
}
function rili_rset_minute(id,m) {
    rhash[id].rminute.innerHTML = m;
    rhash[id].rminutes.style.display = "none";
}
function rili_rset_second(id,s) {
    rhash[id].rsecond.innerHTML = s;
    rhash[id].rseconds.style.display = "none";
}
function rili_rset_date(id,d, m_add,obj) {
    $('#'+id+'_rdates').find('.rdate').each(function(){
        if(this.style.fontWeight == "bold" || this.style.fontWeight == 700){
            var color = $(this).attr('m_add') == '0'?'#2b2b2b':'#999999';
            $(this).css({'fontWeight':'normal','background':'','color':color});
        }
    });

    $(obj).css({'fontWeight':'bold','background':'#0398db','color':'#fff'});
    if($('#rtime').length > 0 && $('#rtime').is(':visible')){
        rili_rset_datetime(id,d, m_add,0);    
    }else{
        rili_rset_datetime(id,d, m_add,1);
    }
    
}

function rili_rset_datetime(id,d, m_add,close) {
    console.log(d);
    var y = parseInt(rhash[id].ryear.innerHTML);
    var m = parseInt(rhash[id].rmonth.innerHTML.replace(/^0(\d)/, '$1')) - 1 + m_add;
    var h = parseInt(rhash[id].rhour.innerHTML.replace(/^0(\d)/, '$1'));
    var i = parseInt(rhash[id].rminute.innerHTML.replace(/^0(\d)/, '$1'));
    var s = parseInt(rhash[id].rsecond.innerHTML.replace(/^0(\d)/, '$1'));   

    if(rhash[id].rmode == 'Ymd'){
        var date = new Date(y, m, d);    
    // }else if(rmode == 'YmdH'){
    //     var date = new Date(y, m, d, h);
    }else if(rhash[id].rmode == 'YmdHi'){
        var date = new Date(y, m, d, h,i);
    }else{
        var date = new Date(y, m, d, h,i,s);
    }
    
    m = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
    d = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    h = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
    i = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();    
    
    if (rhash[id].rmode == "YmdHis" ){
       rhash[id]. rtext_date.value = date.getFullYear() + "-" + m + "-" + d + " " + h + ":" + i + ":" + s;
    }else if( rhash[id].rmode == 'YmdHi'){
        rhash[id].rtext_date.value = date.getFullYear() + "-" + m + "-" + d + " " + h + ":" + i ;
    // }else if(rmode == 'YmdH'){
    //     rtext_date.value = date.getFullYear() + "-" + m + "-" + d + " " + h;
    }else{
        rhash[id].rtext_date.value = date.getFullYear() + "-" + m + "-" + d;    
    }
    
    
    if (rhash[id].rcalendar_function != null && rhash[id].rcalendar_function != "") {
        rhash[id].rcalendar_function(date);
    }
}
