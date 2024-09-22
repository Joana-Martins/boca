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
// Last modified 05/aug/2012 by cassio@ime.usp.br
require('header.php');

if(($ct = DBContestInfo($_SESSION["usertable"]["contestnumber"])) == null)
    ForceLoad("../index.php");

// Função para obter o número de problemas com resposta "Yes"
function countCompletedProblems($contestnumber) {
    $runs = DBUserRuns($contestnumber, $_SESSION["usertable"]["usersitenumber"], $_SESSION["usertable"]["usernumber"]);
    $completedProblems = [];
    
    // Percorre todas as submissões (runs)
    foreach ($runs as $run) {
        if (strtolower($run['answer']) === 'yes') {
            // Adiciona o problema à lista se tiver sido resolvido com sucesso
            $completedProblems[$run['problem']] = true;
        }
    }
    
    // Retorna o número de problemas únicos resolvidos
    return count($completedProblems);
}

$completedProblemsCount = countCompletedProblems($_SESSION["usertable"]["contestnumber"]);
?>
<br><b>Information:</b>
<?php
/*
<br>General information: <a href="https://global.naquadah.com.br/boca/info_sheet.pdf">info_sheet.pdf</a>

<br>Timelimits:
<a href="https://global.naquadah.com.br/boca/contest_times.pdf">contest_times.pdf</a> 
 */

if(is_readable('/var/www/boca/src/sample/secretcontest/maratona.pdf')) {
?>
<b>PLAIN FILES:</b>  <b>CONTEST</b> (
<a href='https://global.naquadah.com.br/boca/secretcontest/maratona.pdf'>PT</a> |
<a href='https://global.naquadah.com.br/boca/secretcontest/maratona_es.pdf'>ES</a> |
<a href='https://global.naquadah.com.br/boca/secretcontest/maratona_en.pdf'>EN</a>
)
&nbsp;&nbsp;&nbsp; 
<b>Info Sheet</b> (
<a href='https://global.naquadah.com.br/boca/secretcontest/info_maratona.pdf'>PT</a> |
<a href='https://global.naquadah.com.br/boca/secretcontest/info_maratona_es.pdf'>ES</a> |
<a href='https://global.naquadah.com.br/boca/secretcontest/info_maratona_en.pdf'>EN</a>
)

<?php
}
?>

<!-- Summary Section -->
<div>
  <p>Total Problems: <span id="total-problems"></span></p>
  <p>Completed Exercises: <span id="completed-exercises"></span></p>
  <p>Remaining Exercises: <span id="remaining-exercises"></span></p>
</div>

<br>
<!-- Adicionando o ID problem-table para a tabela correta -->
<table width="100%" border=1 id="problem-table">
 <thead>
   <tr>
    <td><b>Name</b></td>
    <td><b>Basename</b></td>
    <td><b>Fullname</b></td>
    <td><b>Descfile</b></td>
   </tr>
 </thead>
 <tbody>
<?php
$prob = DBGetProblems($_SESSION["usertable"]["contestnumber"]);
for ($i=0; $i<count($prob); $i++) {
  echo " <tr>\n";
//  echo "  <td nowrap>" . $prob[$i]["number"] . "</td>\n";
  echo "  <td nowrap>" . $prob[$i]["problem"];
  if($prob[$i]["color"] != "")
          echo " <img alt=\"".$prob[$i]["colorname"]."\" width=\"20\" ".
              "src=\"" . balloonurl($prob[$i]["color"]) ."\" />\n";
  echo "</td>\n";
  echo "  <td nowrap>" . $prob[$i]["basefilename"] . "&nbsp;</td>\n";
  echo "  <td nowrap>" . $prob[$i]["fullname"] . "&nbsp;</td>\n";
  if (isset($prob[$i]["descoid"]) && $prob[$i]["descoid"] != null && isset($prob[$i]["descfilename"])) {
    echo "  <td nowrap><a href=\"../filedownload.php?" . filedownload($prob[$i]["descoid"], $prob[$i]["descfilename"]) .
        "\">" . basename($prob[$i]["descfilename"]) . "</a></td>\n";
  }
  else
    echo "  <td nowrap>no description file available</td>\n";
  echo " </tr>\n";
}
echo "</tbody></table>";
if (count($prob) == 0) echo "<br><center><b><font color=\"#ff0000\">NO PROBLEMS AVAILABLE YET</font></b></center>";
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  updateSummary();  // Chama a função após carregar o DOM completamente
});

function updateSummary() {
  // Agora selecionamos as linhas da tabela de problemas
  const rows = document.querySelectorAll('#problem-table tbody tr');
  const total = rows.length;  // Total de problemas é o número de linhas da tabela
  
  // O valor de completed é passado do PHP para o JavaScript
  let completed = <?php echo $completedProblemsCount; ?>;  // Problemas completados
  
  const remaining = total - completed;

  // Atualiza os elementos do resumo na página
  document.getElementById('total-problems').innerText = total;
  document.getElementById('completed-exercises').innerText = completed;
  document.getElementById('remaining-exercises').innerText = remaining;
}
</script>
</body>
</html>
