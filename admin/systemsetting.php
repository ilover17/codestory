<?php
	error_reporting(0);
    //测试数据
    $testRow=array();
    $row = !empty($_POST) ? $_POST : $testRow;
    $state=!empty($_POST);
?>
<?php if($state){?>
<div class="ui-state-highlight">
<p><span class="ui-icon ui-icon-info"></span>
发布成功时显示！</p>
</div>
<div class="ui-state-error">
<p class="ui-state-error-text"><span class="ui-icon ui-icon-alert"></span>
发布失败时显示！</p>
</div>
<?php }?>
<fieldset id="sl-systemsetting">
    <legend>基本设置</legend>
    <form id="form-systemsetting" name="form-systemsetting" action="systemsetting.php" method="POST">
<ul class="sl-table">
    <li><label>是否缓存</label><input type="radio" name="useCache" value="0" <? if($row['useCache']==0){?>checked<?}?>>否 <input type="radio" name="useCache" value="1" <? if($row['useCache']==1){?>checked<?}?>>是</li>
    <li><label>缓存有效期(分钟,1天=1440分钟，1周=10080分钟，1年=525600分钟)</label><input type="text" name="cacheLimit" class="required digits" value="<?=$row['cacheLimit']?>"/></li>
    <li><label>时区</label> <select size="1" id="offset" name="offset"><option value="-12" <? if($row['offset']==-12){?>selected<?}?>>(UTC -12:00) 西部国际日期变更线、艾尼威多克、夸贾林环礁</option><option value="-11" <? if($row['offset']==-11){?>selected<?}?>>(UTC -11:00) 中途岛、萨摩亚群岛</option><option value="-10" <? if($row['offset']==-10){?>selected<?}?>>(UTC -10:00) 夏威夷</option><option value="-9.5" <? if($row['offset']==-9.5){?>selected<?}?>>(UTC -09:30) 泰奥海伊、马克萨斯群岛</option><option value="-9" <? if($row['offset']==-9){?>selected<?}?>>(UTC -09:00) 阿拉斯加</option><option value="-8" <? if($row['offset']==-8){?>selected<?}?>>(UTC -08:00) 美国西部标准时间（美国及加拿大）</option><option value="-7" <? if($row['offset']==-7){?>selected<?}?>>(UTC -07:00) 山地时间（美国及加拿大）</option><option value="-6" <? if($row['offset']==-6){?>selected<?}?>>(UTC -06:00) 中部时间（美国及加拿大）、墨西哥城</option><option value="-5" <? if($row['offset']==-5){?>selected<?}?>>(UTC -05:00) 东部时间（美国及加拿大）、波哥大、利马</option><option value="-4.5" <? if($row['offset']==-4.5){?>selected<?}?>>(UTC -04:30) Venezuela</option><option value="-4">(UTC -04:00) 大西洋时间（加拿大）、加拉加斯、拉巴斯</option><option value="-3.5" <? if($row['offset']==-3.5){?>selected<?}?>>(UTC -03:30) 圣约翰、纽芬兰和拉布拉多</option><option value="-3" <? if($row['offset']==-3){?>selected<?}?>>(UTC -03:00) 巴西、布宜诺斯艾利斯、乔治敦</option><option value="-2" <? if($row['offset']==-2){?>selected<?}?>>(UTC -02:00) 中大西洋</option><option value="-1" <? if($row['offset']==-1){?>selected<?}?>>(UTC -01:00) 亚速尔群岛、佛得角群岛</option><option selected="selected" value="0" <? if($row['offset']==0){?>selected<?}?>>(UTC 00:00) 西欧时间、伦敦、里斯本、卡萨布兰卡、雷克雅未克</option><option value="1" <? if($row['offset']==1){?>selected<?}?>>(UTC +01:00) 中欧时间(布鲁塞斯、哥本哈根、马德里、巴黎)</option><option value="2" <? if($row['offset']==2){?>selected<?}?>>(UTC +02:00) 东欧时间(伊士坦布尔、耶路撒冷、加里宁格勒、南非)</option><option value="3" <? if($row['offset']==3){?>selected<?}?>>(UTC +03:00) 莫斯科、巴格达、奈洛比、圣彼德斯堡</option><option value="3.5">(UTC +03:30) 德黑兰</option><option value="4" <? if($row['offset']==4){?>selected<?}?>>(UTC +04:00) 阿布扎比、马斯喀特、巴库、第比利斯</option><option value="4.5" <? if($row['offset']==4.5){?>selected<?}?>>(UTC +04:30) 喀布尔</option><option value="5" <? if($row['offset']==5){?>selected<?}?>>(UTC +05:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇、塔什干</option><option value="5.5">(UTC +05:30) Bombay, Calcutta, Madras, New Delhi, Colombo</option><option value="5.75" <? if($row['offset']==5.75){?>selected<?}?>>(UTC +05:45) 加德满都</option><option value="6" <? if($row['offset']==6){?>selected<?}?>>(UTC +06:00) Almaty, Dhaka</option><option value="6.5" <? if($row['offset']==6.5){?>selected<?}?>>(UTC +06:30) 亚贡(缅甸)</option><option value="7" <? if($row['offset']==7){?>selected<?}?>>(UTC +07:00) 曼谷、河内、雅加达、金边</option><option value="8" <? if($row['offset']==8){?>selected<?}?>>(UTC +08:00) 北京时间、佩思、新加坡、香港、台北</option><option value="8.75" <? if($row['offset']==8.75){?>selected<?}?>>(UTC +08:00) Ulaanbaatar, Western Australia</option><option value="9" <? if($row['offset']==9){?>selected<?}?>>(UTC +09:00) 东京、汉城、大阪、札幌</option><option value="9.5" <? if($row['offset']==9.5){?>selected<?}?>>(UTC +09:30) 阿德莱德、达尔文、Yakutsk</option><option value="10" <? if($row['offset']==10){?>selected<?}?>>(UTC +10:00) 澳大利亚东部标准时间、关岛、海参崴</option><option value="10.5" <? if($row['offset']==10.5){?>selected<?}?>>(UTC +10:30) 豪勋爵岛 (澳大利亚)</option><option value="11" <? if($row['offset']==11){?>selected<?}?>>(UTC +11:00) 马加丹、索罗门群岛、新卡伦多尼亚</option><option value="11.5" <? if($row['offset']==11.5){?>selected<?}?>>(UTC +11:30) 诺福克岛</option><option value="12" <? if($row['offset']==12){?>selected<?}?>>(UTC +12:00) 奥克兰、惠灵顿、斐济</option><option value="12.75" <? if($row['offset']==12.75){?>selected<?}?>>(UTC +12:45) 查塔姆岛</option><option value="13" <? if($row['offset']==13){?>selected<?}?>>(UTC +13:00) 汤加</option><option value="14" <? if($row['offset']==14){?>selected<?}?>>(UTC +14:00) 基里巴斯</option></select></li>
    <li><label>session时长(分钟)</label> <input type="text" name="sessionLimit" value="<?$row['sessionLimit']?>" class="required digits"/> </li>
    <li><label>后台默认语言</label> <select name="adminLanguage"><option value="zh-CN" SELECTED>中文</option><option value="en-US">english</option></select> </li>
    <li><label>前台默认语言</label> <select name="defaultLanguage"><option value="zh-CN" SELECTED>中文</option><option value="en-US">english</option></select> </li>
    <li><label>后台配色方案</label> <select name="adminTheme"><option value="ui-lightness" SELECTED>ui-lightness</option><option value="base">base</option></select> </li>
    <li><label>暂时关闭网站</label> <input type="radio" name="offline" value="0" checked>否 <input type="radio" name="offline" value="1">是</li>
    <li><label>网站关闭原因</label> <textarea name="offlineMessage" cols="50" rows="5"><?$row['offlineMessage']?></textarea></li>
    <li><label>网站标题</label><input type="text" name="title" class="required"/></li>
    <li><label>网站简介（Meta 描述）</label> <textarea name="metaDesc" cols="50" rows="5"><?$row['metaDesc']?></textarea> </li>
    <li><label>网站关键字（全站 Meta 关键字）</label> <textarea name="metaKeys" cols="50" rows="5"><?$row['metaKeys']?></textarea></li>
    <li><label>&nbsp;</label><input id="submit" type="submit" name="submit" value="保存修改"/></li>
</ul>
    </form>
</fieldset>