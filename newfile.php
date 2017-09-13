<?php

require_once ('Class_Core.php');

$value = new Core();

$value = serialize($value);

echo $value;

echo "</br>";

echo "</br>";

$value = json_encode(serialize($value),JSON_OBJECT_AS_ARRAY);

echo $value;

$value = serialize(123);

echo "</br>";

echo "</br>";

echo $value;

$value = json_encode(serialize(123),JSON_FORCE_OBJECT);


echo "</br>";

echo "</br>";

echo $value;


echo "</br>";

echo "</br>";

$array = ['€', 'http://example.com/some/cool/page', '337'];
$bad   = json_encode($array);


echo $bad;
echo "</br>";

echo "</br>";

$good  = json_encode($array,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

echo $good;




$name = "123";
echo <<<EOT
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
            <title>Untitled Document</title>
            </head>
            <body>
            <!--12321-->
            <a href="baidu.com">Hello,$name!</a>
            </body>
            </html>
EOT;

$file = file('./newfile.php');


$count = array_sum($file);
echo "</br>";

echo "</br>";
$contents = file_get_contents('./newfile.php');


echo $contents;


echo "</br>";

echo "</br>";
echo $count;
 list($filename) = $file;
 
 echo $filename;

