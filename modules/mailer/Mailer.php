<?php

namespace sylma\modules\mailer;
use sylma\core;

class Mailer extends core\module\Domed {

  /**
   *
   * @param string $sEmail
   * @param type $sSubject
   * @param type $sMessage
   * @return string
   */
  public function send($sEmail, $sSubject, $sMessage) {

    $sHeaders = "From: Website <$sEmail>\r\n";
    // $sHeaders .= 'Mime-Version: 1.0'."\r\n";
    $sHeaders .= 'Content-type: text/plain; charset=utf-8';
    // $sHeaders .= "\r\n";."\r\n"

    if (\Sylma::read('debug/email/enable')) {

      $sEmail = \Sylma::read('debug/email/default');
    }

    $this->getManager(self::PARSER_MANAGER)->getContext('messages')->add(array('content' => 'Mail sent to ' . $sEmail));

    mail($sEmail, $sSubject, $sMessage, $sHeaders);
  }
}
