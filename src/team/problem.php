<?php
session_start(); // Inicia a sessão

require('header.php');

// Inicializa o array de links clicados na sessão, se não estiver definido
if (!isset($_SESSION['clicked_links'])) {
    $_SESSION['clicked_links'] = [];
}

// Atualiza os links clicados se recebido via GET
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['clicked_id'])) {
    $clicked_id = $_GET['clicked_id'];
    // Adiciona o link clicado à sessão se ainda não estiver presente
    if (!in_array($clicked_id, $_SESSION['clicked_links'])) {
        $_SESSION['clicked_links'][] = $clicked_id;
    }
}

if (($ct = DBContestInfo($_SESSION["usertable"]["contestnumber"])) == null) {
    ForceLoad("../index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
/* Define styles for unvisited links */
a:link {
    color: blue;
    text-decoration: none;
}

/* Define styles for visited links */
a:visited {
    color: purple;
    text-decoration: none;
}

/* Define styles for mouse-over links */
a:hover {
    color: red;
    text-decoration: underline;
}

/* Define styles for active links */
a:active {
    color: orange;
    text-decoration: underline;
}

/* Define styles for links that should appear red due to being clicked */
.red-link {
    color: red !important;
}
</style>
</head>
<body>
<br><b>Information:</b>
<?php

if (is_readable('/var/www/boca/src/sample/secretcontest/maratona.pdf')) {
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

<br><br><br>
<table width="100%" border=1>
 <tr>
  <td><b>Name</b></td>
  <td><b>Basename</b></td>
  <td><b>Fullname</b></td>
  <td><b>Descfile</b></td>
 </tr>
<?php
$prob = DBGetProblems($_SESSION["usertable"]["contestnumber"]);
for ($i = 0; $i < count($prob); $i++) {
    echo " <tr>\n";
    echo "  <td nowrap>" . $prob[$i]["problem"];
    if ($prob[$i]["color"] != "")
        echo " <img alt=\"" . $prob[$i]["colorname"] . "\" width=\"20\" src=\"" . balloonurl($prob[$i]["color"]) . "\" />\n";
    echo "</td>\n";
    echo "  <td nowrap>" . $prob[$i]["basefilename"] . "&nbsp;</td>\n";
    echo "  <td nowrap>" . $prob[$i]["fullname"] . "&nbsp;</td>\n";

    $descfile_id = $prob[$i]["descoid"];
    $descfile_name = $prob[$i]["descfilename"];
    
    // Verifica se o link foi clicado e deve ser vermelho
    $link_class = in_array($descfile_id, $_SESSION['clicked_links']) ? 'red-link' : '';
    
    if (isset($descfile_id) && $descfile_id != null && isset($descfile_name)) {
        echo "  <td nowrap><a href=\"../filedownload.php?" . filedownload($descfile_id, $descfile_name) .
        "\" class=\"descfile-link $link_class\" data-id=\"$descfile_id\">" . basename($descfile_name) . "</a></td>\n";
    } else {
        echo "  <td nowrap>no description file available</td>\n";
    }
    echo " </tr>\n";
}
echo "</table>";
if (count($prob) == 0) echo "<br><center><b><font color=\"#ff0000\">NO PROBLEMS AVAILABLE YET</font></b></center>";

?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona os links de descrição de arquivos
    const descfileLinks = document.querySelectorAll('.descfile-link');
    descfileLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            const linkId = link.getAttribute('data-id');
            // Envia uma requisição GET para atualizar a sessão com o ID do link clicado
            const linkUrl = new URL(window.location.href);
            linkUrl.searchParams.set('clicked_id', linkId);
            fetch(linkUrl) // Faz a requisição para atualizar a sessão
                .then(response => {
                    if (response.ok) {
                        link.classList.add('red-link'); // Adiciona a classe vermelha para indicar o link clicado
                        window.location.href = link.href; // Continua com o download após atualizar a sessão
                    }
                });
            event.preventDefault(); // Previne o comportamento padrão para realizar a atualização corretamente
        });
    });
});
</script>
</body>
</html>
