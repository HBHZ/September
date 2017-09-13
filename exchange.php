<?php
/*session_start();
 if(!isset($_SESSION['zaszh_user_id'])){
 echo json_encode(array('status'=>'error','msg'=>'连接超时，请重新打开页面。'));
 exit;
 }
 $user_id = $_SESSION['zaszh_user_id'];*/

$user_id = 1; // 测试用
$exchange_points = intval($_GET['exchange_points']);

require('connect_database.php');
// 扣除答题积分
$mysqli->query("update zaszh_user set answer_points=answer_points-{$exchange_points} where id='{$user_id}' and answer_points>={$exchange_points}");
if($mysqli->affected_rows){
    // 有积分
    switch($exchange_points){
        // 5元话费
        case 200:
            $mysqli->query("update zaszh_telephone_charge_surplus set charge_surplus=charge_surplus-5 where date=substring(now(),1,10) and charge_surplus>=5");
            if($mysqli->affected_rows){
                // 有剩余
                $mysqli->query("insert into zaszh_award(user_id,prize,create_date) values('{$user_id}','5元话费',unix_timestamp(now()))");
                if($mysqli->affected_rows){
                    echo json_encode(array('status'=>'success','msg'=>'5元话费'));
                }else{
                    // 获奖失败
                }
            }else{
                // 无剩余
                // 恢复答题积分
                $mysqli->query("update zaszh_user set answer_points=answer_points+{$exchange_points} where id='{$user_id}'");
            }
            break;
            // 10元话费
        case 400:
            $mysqli->query("update zaszh_telephone_charge_surplus set charge_surplus=charge_surplus-10 where date=substring(now(),1,10) and charge_surplus>=10");
            if($mysqli->affected_rows){
                // 有剩余
                $mysqli->query("insert into zaszh_award(user_id,prize,create_date) values('{$user_id}','10元话费',unix_timestamp(now()))");
                if($mysqli->affected_rows){
                    echo json_encode(array('status'=>'success','msg'=>'10元话费'));
                }else{
                    // 获奖失败
                }
            }else{
                // 无剩余
                // 恢复答题积分
                $mysqli->query("update zaszh_user set answer_points=answer_points+{$exchange_points} where id='{$user_id}'");
            }
            break;
    }
    
    // 记录积分消耗
    $mysqli->query("insert into zaszh_answer_points_consume(user_id,points_consume,consume_for,create_date) values('{$user_id}','{$exchange_points}','exchange',unix_timestamp(now()))");
}else{
    // 无积分
    echo json_encode(array('status'=>'error','msg'=>'您的积分不足。'));
}
$mysqli->close();