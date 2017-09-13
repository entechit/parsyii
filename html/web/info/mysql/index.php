<?php 

/* Set your host and login parameters */
 
$user="sava";
$password="simsim";

$host="localhost";

/***************************************************************************
 *
 *   Created by Steffen <steffen@land10web.com> http://www.apachelounge.com
 *
 *   This program is free software; you can redistribute it and/or modify it.
 *   note: not working with the php mysqli extension
 *
 *    $Id: mysqlinfo.php,v 1.0.0 2006/08/27 steffen Exp $
 *
 ***************************************************************************/
 
?>
<html>
<head>
<title>MySQLinfo</title>
</head>
<body>
<a name="top"><br>
<?php
if(!extension_loaded("mysqli")){ 
   echo ("<br><font color=red><b>php MySQL extension not loaded !!</font><br><br>"); 
    phpinfo();
    die;
} 
$link = mysql_connect($host, $user, $password);
if (!$link) {
   echo ('<br><font color=red><b>Could not connect to the Mysql server !!</font><br><br>' . mysql_error() . '<br><br><b>Did you set the correct host and login parameters in mysqlinfo.php ? <br><br><br>');
   phpinfo();
   die;
}
else
{
if( $user == 'root' ) {
  if( $password == '') {
     echo "<font color=red><b>Your user and password are the install default (user:root and password is blank), change it !!</b></font><br><br>";
}
}
?>
<a style="text-decoration: underline" href="#var"><b>MySQL Server variables and settings</b></a> &nbsp; &nbsp;<br /><br /> 
<?php printf("<font color=green>Mysql version:</b><b> %s\n", mysql_get_server_info()); ?>
</b></font><br><br><center><font color=green><b>MySQL Runtime Information</b></font><br>
<TABLE border=0  bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0">
<TD VALIGN=TOP border="0"  bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0">
<br><br>
<?php
$result = mysql_query('SHOW GLOBAL STATUS', $link);
$p = 0;
while ($row = mysql_fetch_assoc($result)) {
      $p ++;
      if ($p==123){
          echo '<br><br></td><TD VALIGN=TOP><br><br>';
}
      echo ' &nbsp;  &nbsp;  ' . $row['Variable_name'] . ' = ' . $row['Value'] . " &nbsp; &nbsp; <br>";
}
?>
</td></tr></table><br><a name="var"><a href="#top"><br><b>Back to top</b></a><br><br><br><font color=green><b>MySQL Server variables and settings</b></font><br>
<table border=0  bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0">
<td valign=top>
<br><br>
<?php
$p = 0;
$result = mysql_query('SHOW VARIABLES', $link);
while ($row = mysql_fetch_assoc($result)) {
      $p ++;
      if ($p==111){
         echo '<br><br></td><td valign=top><br><br>';
}
      echo ' &nbsp;  &nbsp;  ' . $row['Variable_name'] . ' = ' . $row['Value'] . " &nbsp;  &nbsp; <br>";
}
}

?><br />
</font><a href="#top"><b>Back to top</b></a>
</center>
</body>
</html>
