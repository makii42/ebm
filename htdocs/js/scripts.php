<?php
/**
 * @todo This hacked together -> needs proper implementation
 */
require_once __DIR__ . '/../../library/library.php';

// fetching script contents
chdir(DIR_JS_SCRIPTS);
$scriptContent = array();
$scriptFiles   = array_merge(glob('lib/*.js'), glob('*.js'));
foreach ($scriptFiles as $file) {
    $scriptContent[] = file_get_contents(DIR_JS_SCRIPTS . '/' . $file);
}

// adding the configuration
$config          = new Config(__DIR__ . '/../../config/config.json');
$scriptContent[] = "var configData = $.parseJSON('" . $config->toJSON() . "');";

header('Content-Type: application/javascript');
echo implode(PHP_EOL . PHP_EOL . PHP_EOL, $scriptContent);
