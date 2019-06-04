<?php

$URLS = [
  'manche' => 'https://www.premar-manche.gouv.fr',
  'atlantique' => 'https://www.premar-atlantique.gouv.fr',
  'méditerranée' => 'https://www.premar-mediterranee.gouv.fr'
];

$region =  $_GET['region'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$URLS[$region]/api/avis-urgents-aux-navigateurs.html");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

$data = json_decode($output, true);

$res = [];

foreach ($data as $avurnav) {
  $current = [];
  $current['valid_from'] = substr($avurnav['dateDebut'], 0, 10);
  $current['valid_until'] = substr($avurnav['dateFin'], 0, 10);
  $current['url'] = $URLS[$region]."/avis-urgents-aux-navigateurs/".$avurnav['slug'];
  $current['title'] = $avurnav['title'];
  $current['number'] = $avurnav['numero'];
  $current['latitude'] = floatval($avurnav['latitude']);
  $current['longitude'] = floatval($avurnav['longitude']);

  $res[] = $current;
}

header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
echo json_encode($res, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
