<?php

class NetUsageMonitor {
  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  public function save($dataReceived, $dataSent) {
    $sql = "
      INSERT INTO
        net_usage
      SET
        dataReceived = :dataReceived,
        dataSent = :dataSent,
        created = NOW()
    ";
    $sth = $this->db->prepare($sql);
    if ($sth->execute(array(':dataReceived' => $dataReceived, ':dataSent' => $dataSent))) {
      return true;
    }
    else {
      return false;
    }
  }
}
