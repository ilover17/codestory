<?php
	error_reporting(0);
    //演示数据
    $rows=array(
      0=>array(
        'type'      =>  'tel',
        'address'   =>  '0574-88888888',
        'note'      =>  '办公室'
      ),
      1=>array(
        'type'      =>  'qq',
        'address'   =>  '57409999',
        'note'      =>  '在线咨询'
      ),
      2=>array(
        'type'      =>  'url',
        'address'   =>  'http://www.cn09.com',
        'note'      =>  '网址'
      )
    );
    if($_POST['formId']=='form-addressList'){
        $post=$_POST;
        $rows=array();
        foreach($post['type'] as $key=>$type){
            if(!$post['delete'][$key]){
                $rows[]=array('type'=>$type,'address'=>$post['address'][$key],'note'=>$post['note'][$key]);
            }
        }
    }elseif($_POST['formId']=='form-addAddress'){
        $post=$_POST;
        array_unshift($rows,array('type'=>$post['type'],'address'=>$post['address'],'note'=>$post['note']));
    }
?>
<fieldset>
    <legend>联系方式</legend>
    <form id="form-addressList" name="form-addressList" action="address.php" method="POST">
        <input name="formId" type="hidden" value="form-addressList"/>
<ul id="sl-address" class="sl-table">
    <li><span class="action">删除</span> <span class="type">联系方式</span> <span class="value">内容</span> <span class="note">说明</span></li>
    <? foreach($rows as $key=>$row){?>
    <li>
            <span class="action">
                <input name="delete[<?=$key?>]" type="checkbox" value="1"/>
                
            </span>
            <span class="type"><select name="type[<?=$key?>]">
                    <option value="tel" <? if("tel"==$row['type']){?>SELECTED<?}?>>电话</option>
                <option value="mobile" <? if("mobile"==$row['type']){?>SELECTED<?}?>>手机</option>
                <option value="email" <? if("email"==$row['type']){?>SELECTED<?}?>>电邮</option>
                <option value="address" <? if("address"==$row['type']){?>SELECTED<?}?>>地址</option>
                <option value="qq" <? if("qq"==$row['type']){?>SELECTED<?}?>>QQ</option>
                <option value="msn" <? if("msn"==$row['type']){?>SELECTED<?}?>>MSN</option>
                <option value="url" <? if("url"==$row['type']){?>SELECTED<?}?>>网址</option>
            </select></span>
            <span class="value"><input type="text" class="required" name="address[<?=$key?>]" value="<?=$row['address']?>"/></span>
            <span class="note"><input type="text" name="note[<?=$key?>]" value="<?=$row['note']?>"/></span>
    </li>
    <? }?>
    <li><span class="action">&nbsp;</span><input id="addressList-submit" name="addressList-submit" type="submit" value="变更"/></li>
</ul>
  </form>
</fieldset>
<br/>

<fieldset>
    <legend>添加联系方式</legend>
    <form id="form-addAddress" name="form-addAddress" method="POST" action="address.php">
        <input name="formId" type="hidden" value="form-addAddress"/>
<ul id="sl-addAddress" class="sl-table">
    <li><label>联系方式</label>
        <select name="type">
                <option value="tel">电话</option>
                <option value="mobile">手机</option>
                <option value="address">地址</option>
                <option value="qq">QQ</option>
                <option value="msn">MSN</option>
            </select>
    </li>
    <li><label>内容</label><input type="text" name="address" class="required"/></li>
    <li><label>说明</label><input type="text" name="note"/></li>
    <li><label>&nbsp;</label><input type="submit" id="addAddress-submit" name="addAddress-subimt" value="添加"/></li>
</ul>
        </form>
</fieldset>