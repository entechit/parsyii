<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body><table border="0"><?php
  echo '<tr><td>Engine</td><td>MySrv '.getenv('MYSRV_ENGINE_VERSION').'</td></tr>';
  echo '<tr><td>OS</td><td>';   $output = strstr(php_uname(), '('); echo substr($output, 1, strpos($output, ')')-1).'</td></tr>';
  $nginx_ver=getenv('MYSRV_NGINX_VERSION'); if ($nginx_ver != '') { echo '<tr><td>nginx</td><td>'.$nginx_ver.'</td></tr>'; }
  echo '<tr><td>Apache</td><td>'.getenv('MYSRV_APACHE_VERSION').'</td></tr>';
  echo '<tr><td>PHP</td><td>'.getenv('MYSRV_PHP_VERSION').'</td></tr>';
  echo '<tr><td>MySQL</td><td>'.getenv('MYSRV_MYSQL_VERSION').'</td></tr>';
  echo '<tr><td>Perl</td><td>'.getenv('MYSRV_PERL_VERSION').'</td></tr>';
  echo '<tr><td>phpMyAdmin</td><td>'.getenv('MYSRV_PMA_VERSION').'</td></tr>';
  echo '<tr><td>Uptime</td><td>'.exec('uptime | gawk --field-separator=: "{ print $2 }"').'</td></tr>';
?></table></body></html>