<?php
$config = require 'app/config.php';
require 'app/models/net-usage.php';
require 'app/services/net-usage-monitor.php';
require_once 'app/services/speedtest.php';
require_once('app/router-interfaces/' . $config['router']['routerInterface']);

$pdo = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'], $config['db']['username'], $config['db']['password']);

$netUsageMonitor = new NetUsageMonitor($pdo, $config);

try {
  $latestUsage = NetUsage::loadLatest($pdo, 1)[0];
  if ($latestUsage->ageInSeconds > $config['trackingInterval']) {
    $router = new $config['router']['class']($config['router']);
    $usage = $router->getUsageSinceLastQuery();

    NetUsage::save($pdo, $usage['dataReceived'], $usage['dataSent']);
  }

  $netUsageMonitor->checkIfSpeedtestSafeToRun();
}
catch (Exception $e) {
  exit("Speed test can't be run: " . $e->getMessage());
}

$speedTest = new SpeedTest('http://jts.kapsi.fi/speedtest/1m.zip');
$info = $speedTest->run();


print_r($info);
