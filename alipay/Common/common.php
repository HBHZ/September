<?php

//////////////////////////////////////////////////////
//Orderlist数据表，用于保存用户的购买订单记录；
/* Orderlist数据表结构；
 CREATE TABLE `tb_orderlist` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userid` int(11) DEFAULT NULL,购买者userid
 `username` varchar(255) DEFAULT NULL,购买者姓名
 `ordid` varchar(255) DEFAULT NULL,订单号
 `ordtime` int(11) DEFAULT NULL,订单时间
 `productid` int(11) DEFAULT NULL,产品ID
 `ordtitle` varchar(255) DEFAULT NULL,订单标题
 `ordbuynum` int(11) DEFAULT '0',购买数量
 `ordprice` float(10,2) DEFAULT '0.00',产品单价
 `ordfee` float(10,2) DEFAULT '0.00',订单总金额
 `ordstatus` int(11) DEFAULT '0',订单状态
 `payment_type` varchar(255) DEFAULT NULL,支付类型
 `payment_trade_no` varchar(255) DEFAULT NULL,支付接口交易号
 `payment_trade_status` varchar(255) DEFAULT NULL,支付接口返回的交易状态
 `payment_notify_id` varchar(255) DEFAULT NULL,
 `payment_notify_time` varchar(255) DEFAULT NULL,
 `payment_buyer_email` varchar(255) DEFAULT NULL,
 `ordcode` varchar(255) DEFAULT NULL,       //这个字段不需要的，大家看我西面的修正补充部分的说明！
 `isused` int(11) DEFAULT '0',
 `usetime` int(11) DEFAULT NULL,
 `checkuser` int(11) DEFAULT NULL,
 PRIMARY KEY (`id`)
 ) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
 */
//在线交易订单支付处理函数
//函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
//返回值：如果订单已经成功支付，返回true，否则返回false；
function checkorderstatus($ordid){
    $Ord=M('Orderlist');
    $ordstatus=$Ord->where('ordid='.$ordid)->getField('ordstatus');
    if($ordstatus==1){
        return true;
    }else{
        return false;
    }
}
//处理订单函数
//更新订单状态，写入订单支付后返回的数据
function orderhandle($parameter){
    $ordid=$parameter['out_trade_no'];
    $data['payment_trade_no']      =$parameter['trade_no'];
    $data['payment_trade_status']  =$parameter['trade_status'];
    $data['payment_notify_id']     =$parameter['notify_id'];
    $data['payment_notify_time']   =$parameter['notify_time'];
    $data['payment_buyer_email']   =$parameter['buyer_email'];
    $data['ordstatus']             =1;
    $Ord=M('Orderlist');
    $Ord->where('ordid='.$ordid)->save($data);
}
/*-----------------------------------
 2013.8.13更正
 下面这个函数，其实不需要，大家可以把他删掉，
 具体看我下面的修正补充部分的说明
 ------------------------------------*/
//获取一个随机且唯一的订单号；
function getordcode(){
    $Ord=M('Orderlist');
    $numbers = range (10,99);
    shuffle ($numbers);
    $code=array_slice($numbers,0,4);
    $ordcode=$code[0].$code[1].$code[2].$code[3];
    $oldcode=$Ord->where("ordcode='".$ordcode."'")->getField('ordcode');
    if($oldcode){
        getordcode();
    }else{
        return $ordcode;
    }
}

