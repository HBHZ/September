<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
<style type="text/css">
*
{
    margin:0px auto; 
    padding:0px; 
    font-family:"微软雅黑";    
}
#list
{
    width:350px;
    height:400px;
    background-color:#999;
        
}
.py
{
    width:350px; height:40px;    
    margin:8px 0px 0px 0px;
}
.py:hover
{
    background-color:#3F0;
    color:#FFF;
    cursor:pointer;
}
.img
{
    width:35px; height:40px; float:left;
}
.nc
{
    float:left; height:40px;
    margin:0px 0px 0px 20px;
    line-height:40px;
    vertical-align:middle;
}
</style>

</head>

<body>
<?php
    $uid = "15053397521";  //因为没有从登陆页面传过来,所以先给uid传一个值
?>

<div id="list">
    <?php
    //造链接对象
    $db =new MySQLi("localhost","root","517","weixin");
    //判断是否出错
    !mysqli_connect_error() or die("连接失败");
    //写sql语句
    $sql = "select Friends from friends where Uid='{$uid}'";
    //执行sql语句
    $result = $db->query($sql);
    
    $attr = $result->fetch_all();
    
    for($i=0;$i<count($attr);$i++)
    {
        //查出朋友的用户名，因为二维数组的结果就一个值,所以索引取0.
        $fuid = $attr[$i][0];
        //查users表,根据朋友的UID查出朋友的昵称和头像
        $sqlf = "select Pic,NickName from Users where Uid='{$fuid}'";
        $resultf = $db->query($sqlf);
        
        $attrf = $resultf->fetch_row();//因为是根据朋友的uid查询的，所以查出的只能是一条数据,所以最好用fetch_row()方便些
        
        //在外层div里加一个bs自定义属性,方便后期加功能,想选中某一个DIV的时候,存上他的用户名,以后方便以后取出
        echo"<div onclick='ShowCode(this)' class='py' bs='{fuid}'>
        <img class='img' src='{$attrf[0]}' />
        <div class='nc'>{$attrf[1]}</div>
        </div>";
        
        
    }
    
?>
</div>
<!--用js的方式更改样式-->
<script type="text/javascript">
 function ShowCode(div)
 {
     //让div在被选中之前,先把之前所有div的样式先清除掉
         var a = document.getElementsByClassName("py"); //查出来的a是一个集合,js里的集合和PHP里的数组是一样的道理
        //利用for循环把所有div的样式清除
        for(var i=0;i<d.length;i++)
        {
            //alert(d[i]);         //可以alert试试,看下输出的是什么.如果输出的是divelement,就说明找到了每一个DIV了.d[i]就可以代表div了.
            d[i].style.backgroundcolor = "#FFF";
            d[i].style.color = "#000";
        }
        //清除之后再给选中的div加上指定的样式
       div.style.backgroundcolor = "#3F0";   //修改样式的时候,背景色backgroundcolor中间没有横杠链接的
       div.style.Color = "#FFF";                //修改字体颜色
       
       //每点击一个div让它显示输出谁的用户名
       alert(div.getAttribute("bs"));   //把用户名存在自定义属性bs里了,所以可以通过这个方法获取属性
     
 }
</script>
</body>
</html>