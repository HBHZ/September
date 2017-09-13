
<?php
error_reporting(7);
class msn
{
    private $startcomm = 0;
    private $username = '';
    private $password = '';
    private $commend = '';
    private $domain = '';
    private $socket = '';
    private $challenge = '';
    private $status = array();
    private $data = array();
    function set_account($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    function getData(){
        $buffer="";
        while (!feof($this->socket)) {
            $buffer .= fread($this->socket,1024);
            if (preg_match("//r/",$buffer)) {
                break;
            }
        }
        $this->checkData($buffer);
    }

    function getData2() {
        $buffer="";
        while (!feof($this->socket)) {
            $buffer .= fread($this->socket,1024);
            if (preg_match("//r/n/r/n/",$buffer)) {
                break;
            }
        }
        $this->checkData($buffer);
    }
    function checkData($buffer) {
        if (preg_match("/lc/=(.+?)/Ui",$buffer,$matches)) {    
            $this->challenge = "lc=" . $matches[1];
        }
        if (preg_match("/(XFR 3 NS )([0-9/./:]+?) (.*) ([0-9/./:]+?)/is",$buffer,$matches)) {
            $split = explode(":",$matches[2]);
            $this->startcomm = 1;
            $this->msn_connect($split[0],$split[1]);
        }
        if (preg_match("/tpf/=([a-zA-Z0-9]+?)/Ui",$buffer,$matches)) {
            $this->nexus_connect($matches[1]);
        }
        $split = explode("/n",$buffer);
        for ($i=0;$i<count($split);$i++) {  
            $detail = explode(" ",$split[$i]);
            if ($detail[0] == "LST") {
                if(isset($detail[2])) $this->data[] = array($detail[1], urldecode($detail[2]));
            }
        }
        $this->status = array(200, $this->data);
        //echo $buffer;
    }
    function msn_connect($server,$port) {
        if ($this->socket) {
            fclose($this->socket);
        }
        $this->socket = @fsockopen($server,$port, $errno, $errstr, 20);
        if (!$this->socket) {
            $this->status = array(500,'MSN验证服务器无法连接');
            return false;

        } else {

            $this->startcomm++;
            $this->send_command("VER " . $this->startcomm . " MSNP8 CVR0",1);
            $this->send_command("CVR " . $this->startcomm . " 0x0409 win 4.10 i386 MSNMSGR 6.2 MSMSGS " . $this->username,1);
            $this->send_command("USR " . $this->startcomm . " TWN I " . $this->username,1);
        }
    }
    function send_command($command) {
        $this->commend = $command;
        $this->startcomm++;       
        fwrite($this->socket,$command . "/r/n");
        $this->getData();
    }
    function nexus_connect($tpf) {
        $arr[] = "GET /rdr/pprdr.asp HTTP/1.0/r/n/r/n";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://nexus.passport.com:443/rdr/pprdr.asp");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER,1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $arr);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $data = curl_exec($curl);
        curl_close($curl);
        preg_match("/DALogin=(.+?),/",$data,$matches);
        if(!isset($matches[1])) return false;
        $split = explode("/",$matches[1]);
        $headers[0] = "GET /$split[1] HTTP/1.1/r/n";
        $headers[1] = "Authorization: Passport1.4 OrgVerb=GET,OrgURL=http%3A%2F%2Fmessenger%2Emsn%2Ecom,sign-in=" . $this->username . ",pwd=" . $this->password . ", " . trim($this->challenge) . "/r/n";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://" . $split[0] . ":443/". $split[1]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $data = curl_exec($curl);
        curl_close($curl);
        preg_match("/t=(.+?)'/",$data,$matches);
        if(!isset($matches[1])){

            $this->status = array(404, '你输入的MSN帐号或者密码错误');
            return false;
        }
        $this->send_command("USR " . $this->startcomm . " TWN S t=" . trim($matches[1]) . "",2);
        $this->send_command("CHG " . $this->startcomm . " HDN",2);
        $this->send_command("SYN " . $this->startcomm . " 0",2);
        $this->getData2();
        $this->send_command("SYN " . $this->startcomm . " 1 46 2",2);
        $this->getData2();
        $this->send_command("CHG ". $this->startcomm . " BSY");
        $this->getData();     
    }
    public function getStatus()
    {
        return $this->status;
    }
}

$msn = new MSN;
$msn->set_account('xx@hotmail.com', 'xxxxx');
$msn->msn_connect("messenger.hotmail.com",1863);
$data = $msn->getStatus();
print_r($data);

/***
 * 
 * 
 * 
 * PHP导出QQ好友列表搞定，兴奋中。。。

2009年08月23日 星期日 07:29

从昨天晚上开始搞，搞了一个通宵，终于搞定。

昨晚，想了一下我们游戏如果能够通过QQ好友进行推广，是不是可以省去不少广告费那？

先不管这么多，自己到网上搜索了一下腾讯的协议，基本上都是1年前的老帖子了

大部分都已经失效，不能用了，腾讯的协议修改太快了。

但是网上我发现有人用QQ邮箱导出联系人的方法，这个方法不错，但是有个小弊端，如果我没有开通QQ邮箱的话，就不能导出好友列表了。

所以，我想到了用web.qq.com登录导出好友，参考了网上那位导出QQ邮箱里面联系人的方法，搞定了web.qq.com的登录。

后面遇到了不少坎，web.qq.com上面获取好友列表还是比较麻烦的，还要先登录web-proxy.qq.com服务器，然后再发送那些特殊的指令来获取好友列表，但是不知道为什么PHP的curl，只能执行到登陆web-proxy.qq.com，再发送一个获取好友列表的命令就一直卡住不动。

眼看马上就要成功了，竟然被这个玩意卡住了，郁闷死我了，我认为web.qq.com是使用的长连接，可能会对我的结果造成阻塞，我就搞QQ空间去了，找到了比较完美的方法获取好友列表，但是，麻烦又出现了。

又是卡住的问题，登录完之后，直接执行获取列表，就会卡死，郁闷的已经不行了，都快凌晨4点了，实在郁闷。

老子就不信这个邪，我又换了一种方式，不用curl了，拿fsockopen测试了一下，发现没有卡住的现象，curl这个破东西不管怎么设置都不行，我决定用fsockopen这个函数了，终于把函数封装好了，问题又来了。

登录之后取不到Cookie，腾讯的服务器提示我服务器繁忙，郁闷郁闷实在郁闷，又复习了一遍HTTP协议，然后下载了个http的抓包工具，仔细看了看浏览器到底怎么提交的，我缺少了什么，终于有收获了，原来我把Cookie的位置弄错了，Cookie必须写在下面。

所有的问题都一一解决，把程序修改好，试了一下，哇哈哈，搞定，很爽！

这个程序比起原先用邮箱联系人的方法要好一些，只要这个人开通了QQ空间，就可以导出好友。

也不会提示您的QQ已在其他地方登陆，web.qq.com登录的话会提示您在其他地方登陆，这个比较恶心。
 */
?>