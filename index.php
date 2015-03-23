<?php
$config = require 'app/config.php';
require 'app/services/net-usage-monitor.php';

$pdo = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'], $config['db']['username'], $config['db']['password']);
$netUsageMonitor = new NetUsageMonitor($pdo);

foreach ($config['routers'] AS $routerConfig) {
  require_once('app/router-interfaces/' . $routerConfig['routerInterface']);
  $router = new $routerConfig['class']($routerConfig);
  $usage = $router->getUsageSinceLastQuery();
  $netUsageMonitor->save($usage['dataReceived'], $usage['dataSent']);
}

require_once 'app/services/speedtest.php';
$speedTest = new SpeedTest('http://jts.kapsi.fi/speedtest/1m.zip');
$info = $speedTest->run();
print_r($info);
