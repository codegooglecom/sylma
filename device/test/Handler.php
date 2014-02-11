<?php

namespace sylma\device\test;
use sylma\core, sylma\modules\tester;

class Handler extends tester\Initializer implements core\argumentable {

  public static $aUbuntu = array(
    'HTTP_HOST' => 'sylma',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:27.0) Gecko/20100101 Firefox/27.0',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'HTTP_ACCEPT_LANGUAGE' => 'fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
    'HTTP_CONNECTION' => 'keep-alive',
    'HTTP_CACHE_CONTROL' => 'max-age=0',
  );

  public static $aIphone = array(
    'SERVER_SOFTWARE'       => 'Apache/2.2.15 (Linux) Whatever/4.0 PHP/5.2.13',
    'REQUEST_METHOD'        => 'POST',
    'HTTP_HOST'             => 'home.ghita.org',
    'HTTP_X_REAL_IP'        => '1.2.3.4',
    'HTTP_X_FORWARDED_FOR'  => '1.2.3.5',
    'HTTP_CONNECTION'       => 'close',
    'HTTP_USER_AGENT'       => 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0_1 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A523 Safari/8536.25',
    'HTTP_ACCEPT'           => 'text/vnd.wap.wml, application/json, text/javascript, */*; q=0.01',
    'HTTP_ACCEPT_LANGUAGE'  => 'en-us,en;q=0.5',
    'HTTP_ACCEPT_ENCODING'  => 'gzip, deflate',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_REFERER'          => 'http://mobiledetect.net',
    'HTTP_PRAGMA'           => 'no-cache',
    'HTTP_CACHE_CONTROL'    => 'no-cache',
    'REMOTE_ADDR'           => '11.22.33.44',
    'REQUEST_TIME'          => '01-10-2012 07:57'
  );

  protected $sTitle = 'Device';

  protected $aUsers = array(
    'tester01' => array(
      'test01',
      'test00',
    ),
    'tester02' => array(
      'test02',
      'test00',
    ),
    'tester03' => array(
      'test03',
      'test00',
    ),
  );

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setSettings('settings.xml');

    parent::__construct();
  }

  public static function setDevice($sValue) {

    $aHeader = array();

    switch($sValue) {

      case 'mobile' : $aHeader = self::$aIphone; break;
      case 'desktop' : $aHeader = self::$aUbuntu; break;
    }

    if ($aHeader) {

      \Sylma::getManager('device')->setHttpHeaders($aHeader);
    }
  }
}

