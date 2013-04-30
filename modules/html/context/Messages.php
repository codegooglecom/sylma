<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom;

class Messages extends context\Basic implements dom\domable {

  public function asDOM() {

    $doc = $this->createDocument();
    $doc->addElement('div', null, array(), \Sylma::read('namespaces/html'));

    foreach ($this->asArray() as $sMessage) {

      $doc->add($this->createDocument($sMessage));
    }

    return $doc->getChildren();
  }

  public function asString() {

    if ($aContent = $this->asArray()) {

      $sContent = '<div xmlns="' . \Sylma::read('namespaces/html') . '">';

      foreach ($aContent as $sMessage) {

        $sContent .= $sMessage;
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
}
