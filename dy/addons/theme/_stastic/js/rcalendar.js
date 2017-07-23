/*
瑞意日期选择框 rcalendar 2.0
create by rain, Nov 7, 2008
update by rain, Nov 14, 2008
copyright @ rainic.com
example:
<input type="text" onclick="rcalendar(this)">
<input type="text" onclick="rcalendar(this, 'YmdHis', alert)">
*/
var div_rcalendar;
var ryears;
var rmonths;
var rdates;
var rhours;
var rminutes;
var rseconds;
var ryear;
var rmonth;
var rhour;
var rminute;
var rsecond;
var robj_date; //根据文本域的值建立的Date对象
var rnow;
var rc_browser;
var rtext_date;
var rmode;
var rcalendar_function;
function rcalendar(text, mode, retfunction) { //文本域对象, 模式(dateonly,YmdHis), 选择日期后的事件函数(函数是新时间的Date对象)
    rc_browser = new function () {
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
    };

    rnow = new Date();
    rtext_date = typeof(text)=='object' ? text :document.getElementById(text);
    rmode = mode;
    if(rmode == '1'){
        rmode = 'Ymd';
    }else if(rmode == 'full'){
        rmode = 'YmdHis';
    }
    rcalendar_function = retfunction;
    console.log(rcalendar_function);
    try { //获取文本域中的日期
        if(rtext_date.value == ''){
            robj_date = new Date(rnow.getTime());
        }else{
            var ymdhis = rtext_date.value.split(/[^\d]+/);
            ymdhis[0] = parseInt(ymdhis[0]);
            ymdhis[1] = parseInt(ymdhis[1].replace(/^0(\d)/, '$1'));
            ymdhis[2] = parseInt(ymdhis[2].replace(/^0(\d)/, '$1'));
            ymdhis[3] = (ymdhis[3] == null || ymdhis[3] == "") ? 0 : parseInt(ymdhis[3].replace(/^0(\d)/, '$1'));
            ymdhis[4] = (ymdhis[4] == null || ymdhis[4] == "") ? 0 : parseInt(ymdhis[4].replace(/^0(\d)/, '$1'));
            ymdhis[5] = (ymdhis[5] == null || ymdhis[5] == "") ? 0 : parseInt(ymdhis[5].replace(/^0(\d)/, '$1'));
            robj_date = new Date(ymdhis[0], ymdhis[1] - 1, ymdhis[2], ymdhis[3], ymdhis[4], ymdhis[5]);
            if (isNaN(robj_date.getTime())) {
                robj_date = new Date(rnow.getTime());
            }
        }
    }
    catch (e) {
        robj_date = new Date(rnow.getTime());
    }

    if (!div_rcalendar) { //如果不存在，则初始化创建它
        //设置颜色选择框的样式 BEGIN
        var css = "";
        if (document.compatMode == "BackCompat" && navigator.userAgent.indexOf("MSIE") != -1) {
            css += "#rcalendar{overflow:hidden;padding:4px;width:200px;height:200px;background:#fff;font-size:12px;}";
            css += "#rcalendar_ym{overflow:hidden;margin-bottom:4px;width:216px;height:14px;}";
            css += "#rcalendar_y{float:left;padding-left:2px;width:70px;color:#777;font-weight:bold;}";
            css += "#rcalendar_m{float:left;width:60px;color:#777;}";
            css += ".r_arrow_down{height:0;width:0;overflow: hidden;font-size: 0;line-height: 0;border-color:transparent #FF9600 transparent transparent;border-style:dashed solid dashed dashed;border-width:20px;}";
            css += "#rweeks{overflow:hidden;width:217px;height:20px;}";
            css += "#rdates{overflow:hiddenborder:#bbb solid 1px;width:210px;border-radius:0 0 2px 2px;}";
            css += ".rweek{float:left;overflow:hidden;padding:4px 0 4px 0;width:31px;height:20px;border-top:# C54333 solid 1px;background:#dd4b39;color:#fff;text-align:center;font-size:10px;}";
            css += ".rdate{float:left;overflow:hidden;padding:4px 0 4px 0;width:29px;height:20px;text-align:center;cursor:pointer;}";
            css += "#ryears{border:1px solid #ccc;border-top:0;background:#fff;color:#fff;text-align:center;}";
            css += ".ryear{overflow:hidden;padding:4px 4px 4px 4px;width:36px;height:20px;font-weight:bold;cursor:pointer;}";
            css += "#ryear_add{overflow:hidden;padding:0;width:36px;height:12px;cursor:pointer;}";
            css += "#rmonths{overflow:hidden;width:80px;height:81px;border:1px solid #ccc;border-top:0;background:#fff;color:#fff;text-align:center;font-weight:bold;}";
            css += ".rmonth{float:left;overflow:hidden;padding:4px 4px 4px 4px;width:26px;height:20px;cursor:pointer;}";
            css += "#rtime{float:left;overflow:hidden;width:90px;height:13px;}";
            css += "#rhour{padding:0 7px 0 7px;background:#e9e9e9;cursor:pointer;}";
            css += "#rminute{padding:0 7px 0 7px;background:#e9e9e9;cursor:pointer;}";
            css += "#rsecond{padding:0 7px 0 7px;background:#e9e9e9;cursor:pointer;}";
            css += "#rbtns{float:right;overflow:hidden;margin-left:10px;width:90px;height:13px;text-align:right;}";
            css += "#rhours{overflow:hidden;width:104px;height:145px;}";
            css += ".rhour{float:left;overflow:hidden;padding:3px 7px 3px 7px;height:18px;background:#fff;color:#2b2b2b;cursor:pointer;}";
            css += "#rminutes{overflow:hidden;width:104px;height:75px;}";
            css += ".rminute{float:left;overflow:hidden;padding:3px 7px 3px 7px;height:18px;background:#fff;color:#2b2b2b;cursor:pointer;}";
            css += "#rseconds{overflow:hidden;width:104px;height:54px;}";
            css += ".rsecond{float:left;overflow:hidden;padding:3px 7px 3px 7px;height:18px;background:#fff;color:#2b2b2b;cursor:pointer;}";
        }
        else {//173  17  190
            css += "#rcalendar{overflow:hidden;width:265px;background:#fff;box-shadow:0 7px 21px rgba(0,0,0,0.1);font-size:12px;border:#ccc solid 1px;border-radius:3px;}";
            css += "#rcalendar_ym{position:relative;overflow:hidden;width:265px;height:45px;text-align:center;font-family:arial;}";
            css += "#rcalendar_y{display:inline-block;margin-top:7px;color:#333;*display:inline;width:70px}";
            css += ".r_arrow_down{height:0;width:0;overflow: hidden;font-size: 0;line-height: 0;border-color:transparent transparent #FF9600 transparent;border-style:dashed dashed solid dashed;border-width:20px;}";
            css += "#ryear{display:inline-block;width:55px;border-radius:2px;font-size:18px;line-height:25px;}";
            css += "#rcalendar_y:hover{line-height:25px;background:#eee;}";
            css += "#rcalendar_m{display:inline-block;width:60px;color:#333;*display:inline}";
            css += "#rmonth{display:inline-block;width:32px;font-size:18px;line-height:25px;}";
            css += "#rcalendar_m:hover{border-radius:2px;background:#eee;}";
            css += "#rweeks{overflow:hidden;padding:0 10px;width:245px;color:#333;}";
            css += "#rdates{overflow:hidden;padding:0 10px 10px;width:245px;}";
            css += ".rweek{float:left;overflow:hidden;padding:4px 0 4px 0;width:35px;height:16px;border-top:# C54333 solid 1px;color:#999;text-align:center;font-size:10px;line-height:16px;}";
            css += ".rdate{float:left;overflow:hidden;padding:6px 0 6px 0;width:35px;height:17px;border-radius:2px;text-align:center;line-height:17px;cursor:pointer;}";
            css += ".rdate:hover{border-radius:2px;background:#eee;color:#333 !important;}";
            css += "#ryears{border-radius:2px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);color:#2b2b2b;text-align:center;}";
            css += ".ryear{overflow:hidden;padding:6px 0 6px 0;width:52px;height:16px;border-bottom:#e6e6e6 solid 1px;line-height:16px;cursor:pointer;}";
            css += ".ryear:hover{background:#0398db;color:#fff;}";
            css += "#ryear_add{overflow:hidden;padding:5px 0;height:16px;background:#eee;line-height:16px;cursor:pointer;}";
            css += "#rmonths{overflow:hidden;width:60px;border-radius:2px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);text-align:center;}";
            css += ".rmonth{float:left;overflow:hidden;padding:6px 0 6px 0;width:29px;height:17px;border-right:#e6e6e6 solid 1px;border-bottom:#e6e6e6 solid 1px;line-height:17px;cursor:pointer;}";
            css += ".rmonth:hover{background:#0398db;color:#fff !important;}";
            css += "#rtime{float:left;overflow:hidden;margin:0 10px;}";
            css += "#rhour{display:inline-block;padding:0 5px;margin:0 5px 0 0;height:20px;border-radius:2px;background:#e9e9e9;line-height:20px;cursor:pointer;}";
            css += "#rminute{display:inline-block;padding:0 5px;margin:0 0 0 5px;height:20px;border-radius:2px;background:#e9e9e9;line-height:20px;cursor:pointer;}";
            css += "#rsecond{display:inline-block;padding:0 5px;height:20px;border-radius:2px;background:#e9e9e9;line-height:20px;cursor:pointer;}";
            css += "#rbtns{float:right;overflow:hidden;margin:0 10px;width:90px;text-align:right;}";
            css += "#rhours{overflow:hidden;padding:10px;width:245px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);}";
            css += ".rhour{float:left;overflow:hidden;width:30px;height:30px;border-radius:2px;color:#333;text-align:center;line-height:30px;cursor:pointer;}";
            css += ".rhour:hover{background:#eee;}";
            css += "#rminutes{overflow:hidden;padding:10px;width:245px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);}";
            css += ".rminute{float:left;overflow:hidden;width:30px;height:30px;border-radius:2px;color:#333;text-align:center;line-height:30px;cursor:pointer;}";
            css += ".rminute:hover{background:#eee;}";
            css += "#rseconds{overflow:hidden;padding:10px;width:245px;background:#fff;box-shadow:0 0 21px rgba(0,0,0,0.2);}";
            css += ".rsecond{float:left;overflow:hidden;width:30px;height:30px;border-radius:2px;color:#333;text-align:center;line-height:30px;cursor:pointer;}";
            css += ".rsecond:hover{background:#eee;}";
            css += ".day_cur{width:29px;height:29px;background:url("+THEME_URL+"/image/calendar.png) no-repeat 0 -61px;}";
            css += "#prev_month{position:absolute;top:7px;left:10px;display:inline-block;width:25px;height:25px;border-radius:2px;vertical-align:-7px;line-height:25px;cursor:pointer;}";
            css += "#prev_month span{line-height:25px;}";
            css += "#next_month span{line-height:25px;}";
            css += "#prev_month:hover{position:absolute;top:7px;left:10px;display:inline-block;width:25px;height:25px;background:#eee;color:#0398db;vertical-align:-7px;line-height:25px;cursor:pointer;}";
            css += "#next_month{position:absolute;top:7px;right:10px;display:inline-block;width:25px;height:25px;border-radius:2px;vertical-align:-7px;line-height:25px;cursor:pointer;}";
            css += "#next_month:hover{position:absolute;top:7px;right:10px;display:inline-block;width:25px;height:25px;background:#eee;color:#0398db;vertical-align:-7px;line-height:25px;cursor:pointer;}";
        }

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

        div_rcalendar = document.createElement("div");
        div_rcalendar.setAttribute("id", "rcalendar");
        div_rcalendar.setAttribute('class','rcalendar');
        div_rcalendar.style.position = "absolute";
        div_rcalendar.style.zIndex = 999999;
        div_rcalendar.style.background = "#FFFFFF";
        div_rcalendar.style.display = "none";

        var str = "";
        str += '<div id="rcalendar_ym">';
        str += '  <div id="prev_month" onclick="change_month(this,-1)"><span class="ico-chevron-left"></span>';
        str += '  </div>';
        str += '  <div id="rcalendar_y">';
        str += '    <span id="ryear" style="cursor:pointer" onclick="rselect_years(this)" class="r_year"></span><span class="ico-caret-down"></span>';
        str += '  </div>';
        str += '  <div id="rcalendar_m">';
        str += '    <span id="rmonth" style="cursor:pointer" onclick="rselect_months(this)" class="r_month"></span><span class="ico-caret-down"></span>';
        str += '  </div>';
        str += '  <div id="next_month" onclick="change_month(this,1)"><span class="ico-chevron-right"></span>';
        str += '  </div>';

        str += '  <div style="float:right; text-align:right;padding-right:10px;display:none">';
        str += '    <span style="cursor:pointer;" onclick="rcalendar_close()">×</span>';
        str += '  </div>';
        str += '</div>';
        str += '<div id="rweeks">';
        str += '  <div class="rweek">一</div>';
        str += '  <div class="rweek">二</div>';
        str += '  <div class="rweek">三</div>';
        str += '  <div class="rweek">四</div>';
        str += '  <div class="rweek">五</div>';
        str += '  <div class="rweek">六</div>';
        str += '  <div class="rweek">日</div>';
        str += '</div>';
        str += '<div id="rdates"></div>';

        //if(rmode == 'Ymd'){
            //str += '<div style="width:265px;height:40px;line-height:40px;overflow:hidden;border-top:#ccc solid 1px;display:none">';
        //}else{
            str += '<div style="width:265px;height:40px;line-height:40px;overflow:hidden;border-top:#ccc solid 1px;">';    
        //}
        str += '  <div id="rtime">';
        if(rmode == "YmdHis"){
            str += '    <span id="rhour" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rselect_hours(this)"></span>:<span id="rminute" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rselect_minutes(this)"></span>:<span id="rsecond" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rselect_seconds(this)"></span>';
        }else if(rmode == "YmdHi"){
            str += '    <span id="rhour" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rselect_hours(this)"></span>:<span id="rminute" onmouseover="this.style.background=\'#eee\';" onmouseout="this.style.background=\'#eee\';" onclick="rselect_minutes(this)"></span><span id="rsecond" style="display:none"></span>';
        }else{
            str += '    <span id="rhour"  style="display:none"></span><span id="rminute"  style="display:none"></span><span id="rsecond"  style="display:none"></span>';
        }
        str += '  </div>';
        str += '  <div id="rbtns">';
        str += '     <span style="padding:0px 6px 0px 6px; color:#0398db; cursor:pointer;" onmouseover="this.style.background=\'\';" onmouseout="this.style.background=\'\';" onclick="rokclick()">确定</span><span style="padding:0px 6px 0px 6px; color:#0398db; cursor:pointer;" onmouseover="this.style.background=\'\';" onmouseout="this.style.background=\'\';" onclick="rtext_date.value=\'\';rcalendar_close();">清空</span>';
        str += '  </div>';
        str += '</div>';
        div_rcalendar.innerHTML = str;

        ryears = document.createElement("div");
        ryears.setAttribute("id", "ryears");
        ryears.style.position = "absolute";
        ryears.style.display = "none";

        rmonths = document.createElement("div");
        rmonths.setAttribute("id", "rmonths");
        rmonths.style.position = "absolute";
        rmonths.style.display = "none";
        rfill_rmonths();

        if( rmode != 'Ymd'){
            rhours = document.createElement("div");
            rhours.setAttribute("id", "rhours");
            rhours.style.position = "absolute";
            rhours.style.display = "none";
            rfill_rhours();    
        }

        if(rmode == 'YmdHi' || rmode =='YmdHis'){
            rminutes = document.createElement("div");
            rminutes.setAttribute("id", "rminutes");
            rminutes.style.position = "absolute";
            rminutes.style.display = "none";
            rfill_rminutes();
        }

        if(rmode =='YmdHis'){
            rseconds = document.createElement("div");
            rseconds.setAttribute("id", "rseconds");
            rseconds.style.position = "absolute";
            rseconds.style.display = "none";
            rfill_rseconds();
        }

        document.body.appendChild(div_rcalendar);
        div_rcalendar.appendChild(ryears);
        div_rcalendar.appendChild(rmonths);
        rdates = document.getElementById("rdates");
        if( rmode != 'Ymd'){
            div_rcalendar.appendChild(rhours);
            if(rmode == 'YmdHis' || rmode == 'YmdHi'){
                div_rcalendar.appendChild(rminutes);
                if(rmode == 'YmdHis'){
                    div_rcalendar.appendChild(rseconds);
                }
            }
        }
        ryear = document.getElementById("ryear");
        rmonth = document.getElementById("rmonth");
        rhour = document.getElementById("rhour");
        rminute = document.getElementById("rminute");
        rsecond = document.getElementById("rsecond");
    }

    if (div_rcalendar.style.display == "")
        rcalendar_close();


    if (rmode != "Ymd"){
        document.getElementById("rtime").style.visibility = "visible";
    }else{
        document.getElementById("rtime").style.visibility = "hidden";    
    }
        

    //填写年和月
    ryear.innerHTML = robj_date.getFullYear();
    rmonth.innerHTML = robj_date.getMonth() + 1 < 10 ? '0' + (robj_date.getMonth() + 1) : robj_date.getMonth() + 1;
    if(rmode != 'Ymd'){
        rhour.innerHTML = robj_date.getHours() < 10 ? '0' + robj_date.getHours() : robj_date.getHours();
        if(rmode == 'YmdHis' || rmode == 'YmdHi'){
            rminute.innerHTML = robj_date.getMinutes() < 10 ? '0' + robj_date.getMinutes() : robj_date.getMinutes();
            if(rmode == "YmdHis"){
                rsecond.innerHTML = robj_date.getSeconds() < 10 ? '0' + robj_date.getSeconds() : robj_date.getSeconds();    
            }
        }
    }

    rfill_ryears();
    rfill_rdates(); //输出日期表

    //定位并显示rcalendar
    var left_top = rget_offset_left_top(rtext_date);
    div_rcalendar.style.left = left_top[0] + "px";
    div_rcalendar.style.top = (left_top[1] + rtext_date.offsetHeight + 1) + "px";
    div_rcalendar.style.display = "";

    bindBodyClickEvent(function(){
        div_rcalendar.style.display = 'none';
    }, [div_rcalendar, text]);
}
function rfill_ryears(year) {
    year = year ? year : robj_date.getFullYear();
    str = '';
    for (var y = year - 2; y <= year + 2; y++) {
        str += '<div class="ryear" onclick="rset_year(this.innerHTML)">' + y + '</div>';
    }
    str += '<div id="ryear_add">';
    str += '<span style="width:25px;height:16px" onclick="rfill_ryears(' + (year - 5) + ')">&nbsp;&nbsp;-&nbsp;&nbsp;</span>';
    str += '<span style="width:25px;height:16px" onclick="rfill_ryears(' + (year + 5) + ')">&nbsp;&nbsp;+&nbsp;&nbsp;</span>';
    str += '</div>';
    ryears.innerHTML = str;
}
function rfill_rmonths() {
    str = '';
    for (var m = 1; m <= 12; m++) {
        str += '<div class="rmonth" onclick="rset_month(this.innerHTML)">' + (m < 10 ? '0' + m : m) + '</div>';
    }
    rmonths.innerHTML = str;
}
function rfill_rdates() {
    var y = parseInt(ryear.innerHTML);
    var m = parseInt(rmonth.innerHTML.replace(/^0(\d)/, '$1'));
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


    str = "";
    for (var d = date_b; d.getTime() <= date_e.getTime(); d.setDate(d.getDate() + 1)) {
        var color, m_add;
        if (d.getTime() < first_day_of_month.getTime()) {
            color = '#999999';
            m_add = '-1';
        }
        else if (d.getTime() > last_day_of_month.getTime()) {
            color = '#999999';
            m_add = '1';
        }
        else {
            color = '#2b2b2b';
            m_add = '0';
        }
        if (d.getDate() == rnow.getDate() && d.getMonth() == rnow.getMonth() && d.getFullYear() == rnow.getFullYear()) {
            //今天颜色
            color = '#0398db';
        }

        var font_weight = '';
        if (d.getDate() == robj_date.getDate() && m_add == '0') {
            font_weight = ' font-weight:bold;background:#0398db';
            //选中当天的颜色
            color = "#fff";
        }
        str += '<div class="rdate" style="color:' + color + ';' + font_weight + '" onclick="rset_date(this.innerHTML, ' + m_add + ',this)" m_add="'+m_add+'">' + d.getDate() + '</div>';
       
    }
    rdates.innerHTML = str;
}
function rfill_rhours() {
    str = '';
    for (var h = 0; h < 24; h++) {
        str += '<div class="rhour" onclick="rset_hour(this.innerHTML)">' + (h < 10 ? '0' + h : h) + '</div>';
    }
    rhours.innerHTML = str;
}
function rfill_rminutes() {
    str = '';
    for (var m = 0; m < 60; m += 5) {
        str += '<div class="rminute" onclick="rset_minute(this.innerHTML)">' + (m < 10 ? '0' + m : m) + '</div>';
    }
    rminutes.innerHTML = str;
}
function rfill_rseconds() {
    str = '';
    for (var s = 0; s < 60; s += 5) {
        str += '<div class="rsecond" onclick="rset_second(this.innerHTML)">' + (s < 10 ? '0' + s : s) + '</div>';
    }
    rseconds.innerHTML = str;
}
function rselect_years(span_year) {
    if (ryears.style.display == "none") {
        var left_top = rget_offset_left_top(span_year);
        ryears.style.left = (left_top[0] - parseInt(div_rcalendar.style.left)) + "px";
        ryears.style.top = (left_top[1] - parseInt(div_rcalendar.style.top) + span_year.offsetHeight) + "px";
        if (rc_browser.name == "Opera") {
            ryears.style.left = (parseInt(ryears.style.left) - 10) + "px";
            ryears.style.top = (parseInt(ryears.style.top) - 1) + "px";
        }
        ryears.style.display = "";

        if(rmode !='Ymd'){
            rhours.style.display = "none";
            if(rmode == 'YmdHi' || rmode == 'YmdHis'){
                rmonths.style.display = "none";
                if(rmode == 'YmdHis'){
                    rseconds.style.display = "none";
                }
            }
        }
    }
    else {
        ryears.style.display = "none";
    }
}
function rselect_months(span_month) {
    if (rmonths.style.display == "none") {
        var left_top = rget_offset_left_top(span_month);
        rmonths.style.left = (left_top[0] - parseInt(div_rcalendar.style.left) - 6) + "px";
        rmonths.style.top = (left_top[1] - parseInt(div_rcalendar.style.top) + span_month.offsetHeight) + "px";
        if (rc_browser.name == "Opera") {
            rmonths.style.left = (parseInt(rmonths.style.left) - 1) + "px";
            rmonths.style.top = (parseInt(rmonths.style.top) - 1) + "px";
        }
        rmonths.style.display = "";

        if(rmode !='Ymd'){
            rhours.style.display = "none";
            if(rmode == 'YmdHi' || rmode == 'YmdHis'){
                rmonths.style.display = "none";
                if(rmode == 'YmdHis'){
                    rseconds.style.display = "none";
                }
            }
        }
    }
    else {
        rmonths.style.display = "none";
    }
}
function rselect_hours(span_hour) {
    if (rhours.style.display == "none") {
        var left_top = rget_offset_left_top(span_hour);
        rhours.style.left = (left_top[0] - parseInt(div_rcalendar.style.left)) + "px";
        rhours.style.top = (left_top[1] - parseInt(div_rcalendar.style.top) - 150) + "px";
        if (rc_browser.name == "Opera") {
            rhours.style.left = (parseInt(rhours.style.left) - 1) + "px";
            rhours.style.top = (parseInt(rhours.style.top) - 1) + "px";
        }
        ryears.style.display = "none";
        rminutes.style.display = "none";
        rhours.style.display = "";
        if(rmode == 'YmdHi' || rmode == 'YmdHis'){
            rmonths.style.display = "none";
            if(rmode == 'YmdHis'){
                rseconds.style.display = "none";
            }
        }
    }
    else {
        rhours.style.display = "none";
    }
}
function rselect_minutes(span_minute) {
    if (rminutes.style.display == "none") {
        var left_top = rget_offset_left_top(span_minute);
        rminutes.style.left = (left_top[0] - parseInt(div_rcalendar.style.left)) + "px";
        rminutes.style.top = (left_top[1] - parseInt(div_rcalendar.style.top) - 80) + "px";
        if (rc_browser.name == "Opera") {
            rminutes.style.left = (parseInt(rminutes.style.left) - 1) + "px";
            rminutes.style.top = (parseInt(rminutes.style.top) - 1) + "px";
        }
        rminutes.style.display = "";

        ryears.style.display = "none";
        rmonths.style.display = "none";
        rhours.style.display = "none";
        if(rmode == 'YmdHis'){
            rseconds.style.display = "none";
        }
    }
    else {
        rminutes.style.display = "none";
    }
}
function rselect_seconds(span_second) {
    if (rseconds.style.display == "none") {
        var left_top = rget_offset_left_top(span_second);
        rseconds.style.left = (left_top[0] - parseInt(div_rcalendar.style.left)) + "px";
        rseconds.style.top = (left_top[1] - parseInt(div_rcalendar.style.top) - 55) + "px";
        if (rc_browser.name == "Opera") {
            rseconds.style.left = (parseInt(rseconds.style.left) - 1) + "px";
            rseconds.style.top = (parseInt(rseconds.style.top) - 1) + "px";
        }
        rseconds.style.display = "";

        ryears.style.display = "none";
        rmonths.style.display = "none";
        rhours.style.display = "none";
        rminutes.style.display = "none";
    }
    else {
        rseconds.style.display = "none";
    }
}
function rget_offset_left_top(obj) {
    var offset = $(obj).offset();
    return new Array(offset.left,offset.top);
}
function rcalendar_close() {
    ryears.style.display = "none";
    rmonths.style.display = "none";
    if(rmode != 'Ymd'){
        rhours.style.display = "none";
        if(rmode == 'YmdHis' || rmode=='YmdHi'){
            rminutes.style.display = "none";
            if(rmode == 'YmdHis'){
                rseconds.style.display = "none";
            }    
        }
    }
    div_rcalendar.style.display = "none";
}
function rset_year(y) {
    ryear.innerHTML = y;
    rfill_rdates();
    ryears.style.display = "none";
}

function change_month(obj,flag){
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
        rset_year(to_year);
    }
    rset_month(to_month);
}

function rset_month(m) {
    rmonth.innerHTML = m;
    rfill_rdates();
    rmonths.style.display = "none";
}
function rset_hour(h) {
    rhour.innerHTML = h;
    rhours.style.display = "none";
}
function rset_minute(m) {
    rminute.innerHTML = m;
    rminutes.style.display = "none";
}
function rset_second(s) {
    rsecond.innerHTML = s;
    rseconds.style.display = "none";
}
function rset_date(d, m_add,obj) {
    $('#rdates').find('.rdate').each(function(){
        if(this.style.fontWeight == "bold" || this.style.fontWeight == 700){
            var color = $(this).attr('m_add') == '0'?'#2b2b2b':'#999999';
            $(this).css({'fontWeight':'normal','background':'','color':color});
        }
    });

    $(obj).css({'fontWeight':'bold','background':'#0398db','color':'#fff'});
    if($('#rtime').length > 0 && $('#rtime').is(':visible')){
        rset_datetime(d, m_add,0);    
    }else{
        rset_datetime(d, m_add,1);
    }
}
function rokclick() {
    var d = 1;
    var m_add = 0;
    for (var k = 0; k < rdates.childNodes.length; k++) {
        if (rdates.childNodes[k].style.fontWeight == "bold" || rdates.childNodes[k].style.fontWeight == 700) {
            d = parseInt(rdates.childNodes[k].innerHTML.replace(/^0(\d)/, '$1'));
            m_add = parseInt(rdates.childNodes[k].getAttribute('m_add'));
            break;
        }
    }
    rset_datetime(d, m_add,1);
}
function rset_datetime(d, m_add,close) {

    var y = parseInt(ryear.innerHTML);
    var m = parseInt(rmonth.innerHTML.replace(/^0(\d)/, '$1')) - 1 + m_add;
    var h = parseInt(rhour.innerHTML.replace(/^0(\d)/, '$1'));
    var i = parseInt(rminute.innerHTML.replace(/^0(\d)/, '$1'));
    var s = parseInt(rsecond.innerHTML.replace(/^0(\d)/, '$1'));   

    if(rmode == 'Ymd'){
        var date = new Date(y, m, d);    
    // }else if(rmode == 'YmdH'){
    //     var date = new Date(y, m, d, h);
    }else if(rmode == 'YmdHi'){
        var date = new Date(y, m, d, h,i);
    }else{
        var date = new Date(y, m, d, h,i,s);
    }
    
    m = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
    d = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    h = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
    i = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();    
    
    if (rmode == "YmdHis" ){
        rtext_date.value = date.getFullYear() + "-" + m + "-" + d + " " + h + ":" + i + ":" + s;
    }else if( rmode == 'YmdHi'){
        rtext_date.value = date.getFullYear() + "-" + m + "-" + d + " " + h + ":" + i ;
    // }else if(rmode == 'YmdH'){
    //     rtext_date.value = date.getFullYear() + "-" + m + "-" + d + " " + h;
    }else{
        rtext_date.value = date.getFullYear() + "-" + m + "-" + d;    
    }
    if(close == 1){
        rcalendar_close();
        var func = "undefined" == typeof(rcalendar_function) ?  '':rcalendar_function;
        if('function'==typeof(func)){
            func(date);
        }else{
            eval(func+'(date)');
        }
    }    
}
