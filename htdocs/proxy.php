<?php

require_once __DIR__ . '/../library/library.php';

$config    = new Config(DIR_CONFIG . '/config.json');
$host = $config->get('hosts.' . $_GET['hostLabel']);

$client    = new CurlHttpClient(
    new Curl(),
    $host->url . $_GET['url'],
    $host->port,
    $host->user,
    $host->password);

echo $client->get(array());
