<?php
//ini_set('display_errors', 1); error_reporting(E_ALL);

include_once './common.php';

// Ponto de Entrada da Página.
// Define o termo a partir da QueryString.
$term = $_GET["term"];

// Se não há parametro, desiste.
if (!(strlen($term) > 0) || empty($_GET)) {
  header("HTTP/1.0 500 Internal Server Error");
  die('');
}

// Gera a URL para o endpoint e carrega um array com o retorno (json).
$srcUrl = getDBPediaUrl($term);
$response = request_cache($srcUrl, $term.'.dbpedia');
if ($response === false) {
  header("HTTP/1.0 500 Internal Server Error");
  die('');
}

// Cria o array com os dados vindos via JSON
$responseArray = json_decode($response,true);
unset($response);

// Se não há conteúdo, desiste.
if ($responseArray['results']['bindings']['0'] == false) {
  header("HTTP/1.0 500 Internal Server Error");
  die('');
}

// Declaração de variáveis para construção da Tabela.
$tblRowValue = $tblRowType = $tblRowDataType = $mainInfo = "";
$tblHeader = "<thead><tr>";
$tblRow = "<tbody><tr>";

// Caminha pelas colunas gerando a linha de cabeçalho e de conteúdo.
foreach ($responseArray['head']['vars'] as $value) {
  // Carrega valor.
  $tblRowValue = htmlentities(trim($responseArray['results']['bindings']['0'][$value]['value']), ENT_QUOTES, 'UTF-8');

  // Se tem valor, procede.
  if (strlen($tblRowValue) > 0) {
    if ($value == "Info") {
      $mainInfo = $tblRowValue.' <a href="'.substr(preg_replace('/sparql/', 'snorql', $srcUrl, 1), 0, strrpos($srcUrl, "&", -0)).'">Fonte</a>';
      continue;
    }
    $tblRowType = $responseArray['results']['bindings']['0'][$value]['type'];
    
    // Verifica se há tipo de dado conhecido para realizar o tratamento apropriado.
    switch ($tblRowType) {
      case "uri":
        $tblRowValue = '<a href="'.$tblRowValue.'">'.$tblRowValue.'</a>';
        break;
      case "typed-literal":
        $tblRowDataType = $responseArray['results']['bindings']['0'][$value]['datatype'];
        switch ($tblRowDataType) {
          case "http://www.w3.org/2001/XMLSchema#integer":
            $tblRowValue = number_format($tblRowValue, 0, ',', '.');
            break;
        }
        break;
    }
    // Gera a célula.
    $tblHeader .= "<th>".htmlentities($value, ENT_QUOTES, 'UTF-8')."</th>";
    $tblRow .= "<td>".$tblRowValue."</td>";
  }
}
unset($responseArray, $tblRowValue, $tblRowType, $tblRowDataType);

// Fecha os blocos.
$tblHeader .= "</tr></thead>";
$tblRow .= "</tr></tbody>";

// Renderiza a Tabela.
echo "<table>$tblHeader$tblRow</table>$mainInfo";
?>
