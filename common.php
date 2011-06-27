<?php
//ini_set('display_errors', 1); error_reporting(E_ALL);

/* Baseado no código do Yahoo { http://developer.yahoo.com/php/howto-cacheRestPhp.html }
** Função utilizada como Cache para evitar atingir o servidor remoto o tempo todo.
*/
function request_cache($url, $dest_file, $timeout=21600) { // 6h default
  $dest_file = './cache/'.$dest_file;
///*
//** Comentado porque o servidor da unisinos não deixa fazer requests externos, então tudo é cacheado.
//**
  if(!file_exists($dest_file) || filemtime($dest_file) < (time()-$timeout)) {
    $data = file_get_contents($url);
    if ($data === false) return false;
    $tmpf = tempnam('/tmp','YWS');
    $fp = fopen($tmpf,"w");
    fwrite($fp, (string)$data);
    fclose($fp);
    rename($tmpf, $dest_file);
  } else {
//*/
    return file_get_contents($dest_file);
///*
  }
  return($data);
//*/
}

/* Baseado no código de John Wright { http://johnwright.me/blog }
** Gera a url de um request à DBPedia procurando pelo termo solicitado.
** A busca limita-se a locais (a intenção é buscar cidades) cujo nome, em inglês, é igual ao do termo especificado.
*/
function getDBPediaUrl($term) {
  $format = 'json';
  // A query foi escrita por mim. Traz uma linha com as informações solicitadas.
  /*
  ** Explicação da Query:
  **  o Busca um objeto da classe Place que tenha Label (em ingles) igual ao termo de busca.
  **  o A busca é limitada a 1 resultado.
  **  o Todos campos, menos o nome do local, são opcionais pois muitas vezes não estão *todos* disponíveis.
  **  o Os campos de informações gerais (?Info) e nome do prefeito (?Prefeito) são buscados em Português e,
  **  caso não disponívei, há um fallback para inglês.
  */
  $query = '
PREFIX o: <http://dbpedia.org/ontology/>
SELECT DISTINCT
 ?Info
 ?InfoPage
 ?Local
 ?Populacao
 ?Prefeito
 ?URL
{
 ?p a o:Place;
    rdfs:label "'.$term.'"@en,
               ?Local.
 FILTER langMatches(lang(?Local),"en").
OPTIONAL{?p foaf:homepage ?URL.}
OPTIONAL{?p foaf:page ?InfoPage.}
OPTIONAL{?p o:populationTotal ?Populacao.}
OPTIONAL{?p o:foundingDate ?fundacao.}
OPTIONAL{?p o:leaderName ?pref.}
OPTIONAL{{?p o:abstract ?Info. FILTER langMatches(lang(?Info),"en")} UNION {?p o:abstract ?Info. FILTER langMatches(lang(?Info),"pt")}}
OPTIONAL{{?pref rdfs:label ?Prefeito. FILTER langMatches(lang(?Prefeito),"en")} UNION {?pref rdfs:label ?Prefeito. FILTER langMatches(lang(?Prefeito),"pt")}}
}
LIMIT 1
';
 
  $searchUrl = 'http://dbpedia.org/sparql?'
    .'query='.urlencode($query)
    .'&format='.$format
  ;
  unset($query, $format);

  return $searchUrl;
}

// Função pra retornar a própria URL, usada pelo Facebook.
function curPageURL() {
  $pageURL = 'http';
  if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
  $pageURL .= "://";
  if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["SCRIPT_NAME"];
  } else {
     $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
  }
  return $pageURL;
}

?>
