<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom;

class Messages extends core\module\Domed implements core\argumentable, dom\domable {

  public function __construct() {

    $this->setArguments(array());
  }

  public function isEmpty() {

    return !$this->getArguments()->query();
  }

  public function add($mVal) {
//if (!is_array($mVal)) $this->launchException ('Bad message');
    $this->getArguments()->add($mVal);
  }

  public function asDOM() {

    $doc = $this->createDocument();
    $doc->addElement('div', null, array(), \Sylma::read('namespaces/html'));

    foreach ($this->getArguments() as $message) {

      $sMessage = $message->read('content');

      if ($sMessage && is_string($sMessage) && $sMessage{0} === '<') $doc->add($this->createDocument($sMessage));
      else $doc->add($sMessage);
    }

    return $doc->getChildren();
  }

  public function asString() {

    if ($this->getArguments()->query()) {

      $sContent = '<div xmlns="' . \Sylma::read('namespaces/html') . '">';

      foreach ($this->getArguments() as $message) {

        if (is_array($message)) $sContent .= $this->show($message);
        else $sContent .= $message->read('content');
      }

      $sContent .= '</div>';

      $doc = $this->createDocument($sContent);

      $this->loadDefaultArguments();
      $this->setDirectory(__FILE__);
      $tpl = $this->getTemplate('/#sylma/modules/html/cleaner.xsl');

      $sResult = $tpl->parseDocument($doc)->asString();
    }
    else {

      $sResult = '';
    }

    return $sResult;
  }

  public function asArgument() {

    return $this->getArguments();
    $json = $this->getArguments()->asJSON();

    return $json ? $json : '';
  }
}
