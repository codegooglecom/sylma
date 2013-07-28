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
  public function send($sFrom, $sEmail, $sSubject, $sMessage, $bHTML = false) {

    $sHeaders = "From: $sFrom\n";

    if ($bHTML) $sHeaders .= "Content-type: text/html; charset= utf-8\n";
    else $sHeaders .= 'Content-type: text/plain; charset=utf-8'; // text

    if (\Sylma::read('debug/email/enable')) {

      $sEmail = \Sylma::read('debug/email/default');
    }

    $this->getManager(self::PARSER_MANAGER)->getContext('messages')->add(array('content' => 'Mail sent to ' . $sEmail));

    return mail($sEmail, $sSubject, $sMessage, $sHeaders);
  }
}
