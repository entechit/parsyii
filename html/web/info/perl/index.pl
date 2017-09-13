#!/usr/bin/perl

use ExtUtils::Installed;

my ($inst) = ExtUtils::Installed->new();
my (@modules) = $inst->modules();

print << "[END]";
Content-type: text/html

<html>
<head>
<meta charset="UTF-8" />
<title>infoperl</title>
<style type="text/css">
BODY {
 background-color: #FFFFFF;
 color: #000000;
}
.normal {
 font-family: Arial Cyr,Arial;
 font-size: 12px;
}
.forms {
 font-family: MS Sans Serif;
 font-size: 10px;
}
.new {
 text-align: justify;
 text-indent: 0.8em;
 font-family: Arial Cyr,Arial;
 font-size: 12px;
}
.txthead {
 text-align: center;
 font-weight: bold;
 text-decoration: underline;
 font-family: Arial Cyr,Arial;
 font-size: 13px;
}
A {
 color: #000080;
}
A:hover {
 text-decoration: none;
}
</style>
</head>
<body>
<br><p align="center"><strong><font size="4">Детальная информация по модулям Perl</font></strong></p>
<hr width="85%">
[END]

print "<ul><li><p>Версия Perl: $]</p></li>";

for($i=0;$i<scalar(@INC);$i++) {
 $outinc.="\"$INC[$i]\"<br>";
}
print "<li><p>Каталоги модулей:<br>$outinc</p></li>";

print "<li><p>Установленные модули:</p>";
print "<table border=1 width=100%>";
print "<tr><td align=center bgcolor=#F0F0F0><font size=2>Название</font></td>";
print "<td align=center bgcolor=#F0F0F0><font size=2>Версия</font></td>";
print "<td align=center bgcolor=#F0F0F0><font size=2>Используемые файлы</font></td></tr>";

for($i=0;$i<scalar(@modules);$i++) {
 my $version = $inst->version($modules[$i]) || "???";
 my @all_files = $inst->files($modules[$i]);
 for($a=0,$outfiles='';$a<scalar(@all_files);$a++) {
  $outfiles.="$all_files[$a]<br>";
 }
 print "<tr><td valign=top><font size=2>$modules[$i]</font></td>";
 print "<td valign=top><font size=2>$version</font></td>";
 print "<td><font size=2>$outfiles</font></td></tr>";
}

print "</table></li></ul>";

print << "[END]";
<hr width="85%">
</body>
</html>
[END]

