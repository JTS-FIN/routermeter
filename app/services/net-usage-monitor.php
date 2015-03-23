<?php

class NetUsageMonitor {
  private $pdo;
  private $config;

  public function __construct($pdo, $config) {
    $this->pdo = $pdo;
    $this->config = $config;
  }

  public function checkIfSpeedtestSafeToRun() {
    $netUsages = NetUsage::loadLatest($this->pdo, 5);

    $totalNetUsage = 0;
    $totalTime = 0;

    foreach ($netUsages AS $netUsage) {
      $totalNetUsage += $netUsage->dataReceived + $netUsage->dataSent;
      $totalTime += $netUsage->ageInSeconds;
      if ($this->config['speedTest']['safeToRun']['minTrackingTime'] < $totalTime &&
          $totalTime < $this->config['speedTest']['safeToRun']['maxTrackingTime']) {
        break;
      }
    }

    $avgNetSpeed = round($totalNetUsage / $totalTime);

    if ($totalTime < $this->config['speedTest']['safeToRun']['minTrackingTime'] ||
        $totalTime > $this->config['speedTest']['safeToRun']['maxTrackingTime'] ) {
      throw new Exception('Not enough net usage data to tell if running speed test is safe');
      return false;
    }
    elseif ($avgNetSpeed > $this->config['speedTest']['safeToRun']['maxAverageSpeed']) {
      throw new Exception('Too much net traffic to run speed test, was ' .
      $avgNetSpeed . 'kB/s, limit is ' .
      $this->config['speedTest']['safeToRun']['maxAverageSpeed'] . "kB/s in last " . round($totalTime / 60) . " minutes");
      return false;
    }

    return true;
  }

}
