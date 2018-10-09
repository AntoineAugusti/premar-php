<?php

function formatDate($date) {
  if (is_null($date)) {
    return null;
  }
  $parts = array_map(trim, explode('/', $date));
  return "$parts[2]-$parts[1]-$parts[0]";
}

$URLS = [
  'manche' => 'https://www.premar-manche.gouv.fr',
  'atlantique' => 'https://www.premar-atlantique.gouv.fr',
  'méditerranée' => 'https://www.premar-mediterranee.gouv.fr'
];

$region =  $_GET['region'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$URLS[$region]/avis-urgents-aux-navigateurs.html");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

preg_match_all('/pushElem\("(?<title>.*?)", "En vigueur du : (?<valid_from>.*?)", "(?<valid_until>.*?)", (?<latitude>[-+]?[0-9]+\.[0-9]+), (?<longitude>[-+]?[0-9]+\.[0-9]+), "(?<url>.*?)"/', $output, $matches);

$res = [];
$nbResults = count($matches[0]);

for ($i=0; $i < $nbResults; $i++) {
  $current = [];
  foreach (['title', 'valid_from', 'valid_until', 'latitude', 'longitude', 'url'] as $key) {
    $value = $matches[$key][$i];

    if ($key === 'title') {
      $current['title'] = $value;
    }
    if (in_array($key, ['latitude', 'longitude'], true)) {
      $current[$key] = floatval($value);
    }
    if ($key === 'valid_from') {
      $value = str_replace('.', '', $value);
      $current['valid_from'] = formatDate($value);
    }
    if ($key === 'valid_until') {
      $value = str_replace('au ', '', $value);
      $value = $value === '' ? null : $value;
      $current['valid_until'] = formatDate($value);
    }
    if ($key === 'url') {
      $current['url'] = "$URLS[$region]/$value";
    }
  }
  $res[] = $current;
}

header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
echo json_encode($res, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
