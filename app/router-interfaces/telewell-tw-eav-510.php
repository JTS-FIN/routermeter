<?php

class TelewellTwEav510 {
  private $ip;
  private $interface;
  private $username;
  private $password;

  public function __construct($config) {
    $this->ip = $config['ip'];
    $this->username = $config['auth']['username'];
    $this->password = $config['auth']['password'];
    $this->interface = $config['internetInterface'];
  }

  public function getPage($path) {
    if (substr($path, 0, 1) !== '/') {
      $path = '/' . $path;
    }
    $curlConnection = curl_init($this->ip . $path);
    curl_setopt($curlConnection, CURLOPT_USERPWD, $this->username . ":" . $this->password);
    curl_setopt($curlConnection, CURLOPT_TIMEOUT, 1);
    curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($curlConnection);

    curl_close($curlConnection);

    return $return;
  }

  public function getUsageSinceLastQuery() {
    $html = $this->getPage('statswan.cmd');
    $lines = explode("\n",$html);
    $linesCount = count($lines);

    for ($i = 0; $i < $linesCount; $i++) {
      if ($lines[$i] === "      <td class='hd'>{$this->interface}</td>") {
        list($dataReceived) = sscanf($lines[$i + 2], '      <td>%i</td>');
        list($dataSent) = sscanf($lines[$i + 10], '      <td>%i</td>');
        break;
      }
    }
    if ($dataReceived > 0 || $dataSent > 0) {
      $this->getPage('statswanreset.html');
    }
    return array('dataReceived' => $dataReceived, 'dataSent' => $dataSent);
  }
}
