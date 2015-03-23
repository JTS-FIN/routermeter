<?php

class NetUsage {
  private $pdo;
  private $properties;

  public function __construct($pdo) {
    $this->pdo = $pdo;
    $this->properties = array();
  }

  /**
   * Loads latest NetUsage from the pdo, and populates the current model with
   * it's data
   */
  public static function loadLatest($pdo, $count = 1) {
    $sql = "
      SELECT
        id,
        dataReceived,
        dataSent,
        created,
        (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created)) AS ageInSeconds
      FROM
        net_usage
      ORDER BY
        id DESC
      LIMIT
        :count
    ";
    $sth = $pdo->prepare($sql);
    $sth->bindValue(':count', (int)trim($count), PDO::PARAM_INT);
    if ($sth->execute()) {
      $netUsageArray = array();
      while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        $netUsage = new NetUsage($pdo);
        foreach ($row AS $column => $value) {
          $netUsage->$column = $value;
        }
        $netUsageArray[] = $netUsage;
      }

      return $netUsageArray;
    }
    return false;
  }

  public static function save($pdo, $dataReceived, $dataSent) {
    $sql = "
      INSERT INTO
        net_usage
      SET
        dataReceived = :dataReceived,
        dataSent = :dataSent,
        created = NOW()
    ";
    $sth = $pdo->prepare($sql);
    if ($sth->execute(array(':dataReceived' => $dataReceived, ':dataSent' => $dataSent))) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * C#-style property getter
   * https://wiki.php.net/rfc/propertygetsetsyntax
   */
  public function __get($name) {
    return $this->properties[$name];
  }

  /**
   * C#-style property setter
   * https://wiki.php.net/rfc/propertygetsetsyntax
   */
  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }
}
