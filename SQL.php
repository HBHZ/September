<?php
//SQL 注入是PHP应用中最常见的漏洞之一。事实上令人惊奇的是，开发者要同时犯两个错误才会引发一个SQL注入漏洞

/***
 * 
 * 
 * 
 * 一个是没有对输入的数据进行过滤（过滤输入），还有一个是没有对发送到数据库的数据进行转义（转义输出）。这两个重要的步骤缺一不可，需要同时加以特别关注以减少程序错误。
对于攻击者来说，进行SQL注入攻击需要思考和试验，对数据库方案进行有根有据的推理非常有必要（当然假设攻击者看不到你的源程序和数据库方案），考虑以下简单的登录表单：
 */

print <<<EOT
<form action="/login.php" method="POST">
<p>Username: <input type="text" name="username" /></p>
<p>Password: <input type="password" name="password" /></p>
<p><input type="submit" value="Log In" /></p>
</form>
EOT;


/**

 * 
 * 作为一个攻击者，他会从推测验证用户名和密码的查询语句开始。通过查看源文件，他就能开始猜测你的习惯。
比如命名习惯。通常会假设你表单中的字段名为与数据表中的字段名相同。当然，确保它们不同未必是一个可靠的安全措施。
第一次猜测，一般会使用下面例子中的查询：
 */


$password_hash = md5($_POST['password']);

$sql = "SELECT count(*)
FROM   users
WHERE  username = '{$_POST['username']}'
AND    password = '$password_hash'";

/**

 * 
 * 
 * 使用用户密码的MD5值原来是一个通行的做法，但现在并不是特别安全了。最近的研究表明MD5算法有缺陷，而且大量MD5数据库降低了MD5反向破解的难度。请访问http://md5.rednoize.com/ 查看演示（原文如此，山东大学教授王小云的研究表明可以很快的找到MD5的“碰撞”，就是可以产生相同的MD5值的不同两个文件和字串。MD5是信息摘要算法，而不是加密算法，反向破解也就无从谈起了。不过根据这个成果，在上面的特例中，直接使用md5是危险的。）。
最好的保护方法是在密码上附加一个你自己定义的字符串，例如：
 */



$salt = 'SHIFLETT';
$password_hash = md5($salt . md5($_POST['password'] . $salt));

//当然，攻击者未必在第一次就能猜中，他们常常还需要做一些试验。有一个比较好的试验方式是把单引号作为用户名录入，原因是这样可能会暴露一些重要信息。有很多开发人员在Mysql语句执行出错时会调用函数mysql_error()来报告错误。见下面的例子：


mysql_query($sql) or exit(mysql_error());

//虽然该方法在开发中十分有用，但它能向攻击者暴露重要信息。如果攻击者把单引号做为用户名，mypass做为密码，查询语句就会变成：

$sql = "SELECT *
      FROM   users
      WHERE  username = '''
      AND    password = 'a029d0df84eb5549c641e04a9ef389e5'";

//当该语句发送到MySQL后，系统就会显示如下错误信息：

/***
 * 
 * 
 * 
 * 
 * You have an error in your SQL syntax. Check the manual that corresponds to your
MySQL server version for the right syntax to use near 'WHERE username = ''' AND
password = 'a029d0df84eb55



 */


/**
 
 
 
 不费吹灰之力，攻击者已经知道了两个字段名(username和password)以及他们出现在查询中的顺序。除此以外，攻击者还知道了数据没有正确进行过滤（程序没有提示非法用户名）和转义（出现了数据库错误），同时整个WHERE条件的格式也暴露了，这样，攻击者就可以尝试操纵符合查询的记录了。
在这一点上，攻击者有很多选择。一是尝试填入一个特殊的用户名，以使查询无论用户名密码是否符合，都能得到匹配：

*/
 

$sql = "SELECT *
      FROM   users
      WHERE  username = 'myuser' or 'foo' = 'foo' --
      AND    password = 'a029d0df84eb5549c641e04a9ef389e5'";



/***
 * 
 * 
 * 
 * 
 * 
 * 幸运的是，SQL注入是很容易避免的。正如前面所提及的，你必须坚持过滤输入和转义输出。
虽然两个步骤都不能省略，但只要实现其中的一个就能消除大多数的SQL注入风险。如果你只是过滤输入而没有转义输出，你很可能会遇到数据库错误（合法的数据也可能影响SQL查询的正确格式），但这也不可靠，合法的数据还可能改变SQL语句的行为。另一方面，如果你转义了输出，而没有过滤输入，就能保证数据不会影响SQL语句的格式，同时也防止了多种常见SQL注入攻击的方法。
当然，还是要坚持同时使用这两个步骤。过滤输入的方式完全取决于输入数据的类型（见第一章的示例），但转义用于向数据库发送的输出数据只要使用同一个函数即可。对于MySQL用户，可以使用函数mysql_real_escape_string( )：
 */
      
$clean = array();
$mysql = array();

$clean['last_name'] = "O'Reilly";
$mysql['last_name'] = mysql_real_escape_string($clean['last_name']);

$sql = "INSERT
INTO   user (last_name)
VALUES ('{$mysql['last_name']}')";


/**
尽量使用为你的数据库设计的转义函数。如果没有，使用函数addslashes()是最终的比较好的方法。
当所有用于建立一个SQL语句的数据被正确过滤和转义时，实际上也就避免了SQL注入的风险。如果你正在使用支持参数化查询语句和占位符的数据库操作类（如PEAR::DB, PDO等），你就会多得到一层保护。见下面的使用PEAR::DB的例子：
*/

$sql = 'INSERT
      INTO   user (last_name)
      VALUES (?)';
$dbh->query($sql, array($clean['last_name']));
/*

 * 
 * 
 * 由于在上例中数据不能直接影响查询语句的格式，SQL注入的风险就降低了。PEAR::DB会自动根据你的数据库的要求进行转义，所以你只需要过滤输出即可。
如果你正在使用参数化查询语句，输入的内容就只会作为数据来处理。这样就没有必要进行转义了，尽管你可能认为这是必要的一步（如果你希望坚持转义输出习惯的话）。实际上，这时是否转义基本上不会产生影响，因为这时没有特殊字符需要转换。在防止SQL注入这一点上，参数化查询语句为你的程序提供了强大的保护。
注：关于SQL注入，不得不说的是现在大多虚拟主机都会把magic_quotes_gpc选项打开，在这种情况下所有的客户端GET和POST的数据都会自动进行addslashes处理，所以此时对字符串值的SQL注入是不可行的，但要防止对数字值的SQL注入，如用intval()等函数进行处理。但如果你编写的是通用软件，则需要读取服务器的magic_quotes_gpc后进行相应处理。
 */