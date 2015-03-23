<?php

class SpeedTest {
  private $fileUrl;

  public function __construct($url) {
    $this->fileUrl = $url;
  }

  public function run() {
    $curlConnection = curl_init($this->fileUrl);
    curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curlConnection);
    if(!curl_errno($curlConnection)) {
      $info = curl_getinfo($curlConnection);
      return $info;
    }
    else {
      throw new Exception('Failed to download test-file.');
    }
  }
}
