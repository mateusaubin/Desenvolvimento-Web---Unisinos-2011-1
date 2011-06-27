<?php

$executionTime = microtime(true);
include_once './common.php';

// Busca o arquivo
$response = request_cache('http://gomashup.com/json.php?fds=geo/timezone/locations', 'timezones.json');
if (!$response === false) {
  // Corta o inicio e o fim pra retirar os ();
  $response = substr($response,1,strlen($response)-2);
  // Cria o array com os dados vindos via JSON
  $timezones = json_decode($response,true);
  $timezoneSize = count($timezones["result"]);
}
unset($response);

// Seta os defaults
$selectedLocationIndex = (is_numeric($_GET["local"]) ? (int)$_GET["local"] : mt_rand(0,$timezoneSize));

?>
<!DOCTYPE html xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml" >
<html>
  <head>
    <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Relógio Semântico Mundial - Mateus Rauback Aubin</title>
    <link rel="stylesheet" href="clockstyles.css">
    <script type="text/javascript" src="jquery-1.5.2.min.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&region=BR"></script>
    <meta name="description" content="Atividade da disciplina Desenvolvimento Web 2011/1. Relógio com tecnologia CSS3 e JavaScript, integrado com a API do Google Maps e extraindo informações de fontes Semânticas sobre determinadas localidades."/>
    <meta name="keywords" content="CSS3, JavaScript, Relogio, Web Semantica, Google Maps"/>
    <meta property="og:title" content="Relógio Semântico Mundial"/>
    <meta property="og:type" content="website"/>
    <meta property="og:description" content="Atividade da disciplina Desenvolvimento Web 2011/1. Relógio com tecnologia CSS3 e JavaScript, integrado com a API do Google Maps e extraindo informações de fontes Semânticas sobre determinadas localidades."/>
  </head>
  <body>
    <form id="formHeader" class="dest">
      <p>Selecione sua Localização</p>
      <select id="local" name="local" onchange="Mateus.submitForm();" autocomplete="off">
<?php
  $selectedLocationOffset = 0;
  $selectedLocationName = '';

  if ($timezoneSize == 0){
    // Não veio nada
    echo "<option value=\"-1\">Não há dados</option>\n";
  } else {
    // Toca ficha!
    $tzIdx = 0;
    $isSelected = false;
    foreach ($timezones["result"] as $value) {
      if ($tzIdx == $selectedLocationIndex){
        $isSelected = true;
        $selectedLocationName = $value["TimeZoneId"];
        $selectedLocationOffset = $value["DST"];
      } else {
        $isSelected = false;
      }
/*
** Usei pra fazer o cache local de todas as querys e poder colocar no servidor da unisinos;

$term = str_replace('_', ' ', substr($value["TimeZoneId"], strrpos($value["TimeZoneId"], '-', -0) + 1));
$srcUrl = getDBPediaUrl($term);
$response = request_cache($srcUrl, $term.'.dbpedia');
*/
      echo "<option " . ($isSelected ? 'selected ' : '') . "value=\"" . $tzIdx ."\">" . $value["TimeZoneId"] . "</option>\n";
      $tzIdx++;
    }
    unset($selectedLocationIndex, $timezoneSize, $tzIdx, $isSelected, $value);
  }
  unset($timezones);
?>
      </select>
      <div id="social">
      <a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="mateusaubin">Tweet</a>
      &nbsp;&nbsp;&nbsp;
      <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo curPageURL(); ?>&amp;send=false&amp;layout=box_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=90" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:60px;" allowTransparency="true"></iframe>
      </div>
    </form>
    <div id="map_canvas"></div>
    <div id="city_info" class="dest">Não foram encontradas informações.</div>
    <div id="relogio_wrapper">
      <ul id="relogio">  
        <li id="seg"></li>
        <li id="hor"></li>
        <li id="min"></li>
      </ul>
    </div>
    <script type="text/javascript">
      // Variáveis que são buscadas pelo maps.js;
      var queryAddress = '<?php echo $selectedLocationName; ?>';
      var queryOffset  = <?php echo $selectedLocationOffset; ?>;
      var cityInfoDiv = null;
    </script>
<?php
  unset($selectedLocationName, $selectedLocationOffset);
?>
    <script type="text/javascript" src="maps.js"></script>
    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
  </body>
  <!-- Executed in: <?php echo microtime(true) - $executionTime ?>s -->
</html>
