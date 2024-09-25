<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 21/jul/2012 by cassio@ime.usp.br
ob_start();
header ("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");
session_start();
ob_end_flush();
require_once('../version.php');

require_once("../globals.php");
require_once("../db.php");
$runteam='run.php';

echo "<html><head><title>Team's Page</title>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
echo "<link rel=stylesheet href=\"../Css.php\" type=\"text/css\">\n";

//echo "<meta http-equiv=\"refresh\" content=\"60\" />"; 

if(!ValidSession()) {
	InvalidSession("team/index.php");
        ForceLoad("../index.php");
}
if($_SESSION["usertable"]["usertype"] != "team") {
	IntrusionNotify("team/index.php");
        ForceLoad("../index.php");
}

// Container principal para o cabeçalho e informações do usuário
echo "<div style='display: flex; align-items: center; justify-content: space-between; background-color: #B7D8BC; padding: 1px; border: 2px solid #0E1E0F;'>\n"; // Adiciona a borda de divisão
echo "<div style='display: flex; align-items: center;'>\n"; // Adiciona um container flex para alinhar BOCA e o Username à esquerda
echo "<div style='margin-right: 10px;'><img src=\"../images/smallballoontransp.png\" alt=\"\"></div>\n"; // Ajusta a margem para aproximar a imagem de BOCA
echo "<div style='font-weight: bold; margin-right: 20px; border-right:2px solid #0E1E0F;padding-right: 20px'>\n"; // Margem direita para separar BOCA do Username
echo "<font color=\"#000000\">BOCA</font>\n";
echo "</div>\n";
echo "<div style='flex-grow: 2; text-align: center;'>\n";
echo "Username: " . $_SESSION["usertable"]["userfullname"] . " (site=" . $_SESSION["usertable"]["usersitenumber"] . ")\n";
echo "</div>\n";

$ds = DIRECTORY_SEPARATOR;
if($ds=="") $ds = "/";

$runtmp = $_SESSION["locr"] . $ds . "private" . $ds . "runtmp" . $ds . "run-contest" . $_SESSION["usertable"]["contestnumber"] . 
	"-site". $_SESSION["usertable"]["usersitenumber"] . "-user" . $_SESSION["usertable"]["usernumber"] . ".php";
$doslow=true;
if(file_exists($runtmp)) {
	if(($strtmp = file_get_contents($runtmp,FALSE,NULL,0,1000000)) !== FALSE) {
		$postab=strpos($strtmp,"\t");
		$conf=globalconf();
		if(isset($conf['doenc']) && $conf['doenc'])
		  $strcolors = decryptData(substr($strtmp,$postab+1,strpos($strtmp,"\n")-$postab-1),$conf['key'],'');
		else
		  $strcolors = substr($strtmp,$postab+1,strpos($strtmp,"\n")-$postab-1);
		$doslow=false;
		$rn=explode("\t",$strcolors);
		$n=count($rn);
		for($i=1; $i<$n-1;$i++) {
			echo "<img alt=\"".$rn[$i]."\" width=\"10\" ".
				"src=\"" . balloonurl($rn[$i+1]) . "\" />\n";
			$i++;
		}
	} else unset($strtmp);
}
if($doslow) {
	$run = DBUserRunsYES($_SESSION["usertable"]["contestnumber"],
						 $_SESSION["usertable"]["usersitenumber"],
						 $_SESSION["usertable"]["usernumber"]);
	$n=count($run);
	for($i=0; $i<$n;$i++) {
		echo "<img alt=\"".$run[$i]["colorname"]."\" width=\"10\" ".
			"src=\"" . balloonurl($run[$i]["color"]) . "\" />\n";
	}
}

if(!isset($_SESSION["popuptime"]) || $_SESSION["popuptime"] < time()-120) {
	$_SESSION["popuptime"] = time();

	if(($st = DBSiteInfo($_SESSION["usertable"]["contestnumber"],$_SESSION["usertable"]["usersitenumber"])) != null) {
		$clar = DBUserClars($_SESSION["usertable"]["contestnumber"],
							$_SESSION["usertable"]["usersitenumber"],
							$_SESSION["usertable"]["usernumber"]);
		for ($i=0; $i<count($clar); $i++) {
			if ($clar[$i]["anstime"]>$_SESSION["usertable"]["userlastlogin"]-$st["sitestartdate"] && 
				$clar[$i]["anstime"] < $st['siteduration'] &&
				trim($clar[$i]["answer"])!='' && !isset($_SESSION["popups"]['clar' . $i . '-' . $clar[$i]["anstime"]])) {
				$_SESSION["popups"]['clar' . $i . '-' . $clar[$i]["anstime"]] = "(Clar for problem ".$clar[$i]["problem"]." answered)\n";
			}
		}
		$run = DBUserRuns($_SESSION["usertable"]["contestnumber"],
						  $_SESSION["usertable"]["usersitenumber"],
						  $_SESSION["usertable"]["usernumber"]);
		for ($i=0; $i<count($run); $i++) {
			if ($run[$i]["anstime"]>$_SESSION["usertable"]["userlastlogin"]-$st["sitestartdate"] && 
				$run[$i]["anstime"] < $st['sitelastmileanswer'] &&
				$run[$i]["ansfake"]!="t" && !isset($_SESSION["popups"]['run' . $i . '-' . $run[$i]["anstime"]])) {
				$_SESSION["popups"]['run' . $i . '-' . $run[$i]["anstime"]] = "(Run ".$run[$i]["number"]." result: ".$run[$i]["answer"] . ')\n';
			}
		}
	}

	$str = '';
	if(isset($_SESSION["popups"])) {
		foreach($_SESSION["popups"] as $key => $value) {
			if($value != '') {
				$str .= $value;
				$_SESSION["popups"][$key] = '';
			}
		}
		if($str != '') {
			MSGError('YOU GOT NEWS:\n' . $str . '\n');
		}
	}
}
echo "</div>\n";

// Relógio do site
list($clockstr, $clocktype) = siteclock();
echo "<div style='flex-shrink: 0; text-align: center; background-color: #B7D8BC; padding: 5px;'>" . $clockstr . "</div>\n";
echo "</div>\n";
// Barra de navegação
echo "<div style='display: flex; justify-content: space-around; margin-top: 10px;'>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='problem.php'>Problems</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='run.php'>Runs</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='score.php'>Score</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='clar.php'>Clarifications</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='task.php'>Tasks</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='files.php'>Backups</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='option.php'>Options</a>\n";
echo "  <a class='menu' style='font-weight: bold; text-align: center;' href='../index.php'>Logout</a>\n";
echo "</div>\n";

echo "</body></html>";
?>
