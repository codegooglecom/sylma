<?php

namespace sylma\dom\basic\handler;
use \sylma\dom, \sylma\core;

require_once('Basic.php');

/**
 *
 */
abstract class Container extends Basic {

  private $fragment;

  private $sContent = '';

  private $bFragment;

  /**
   * @var dom\document
   */
  private $document;

  protected function setFragment(dom\fragment $fragment) {

    $this->fragment = $fragment;
  }

  public function getContainer() {

    $result = null;

    if ($this->bFragment) $result = $this->getFragment();
    else $result = $this->getDocument();

    if (!$result) $this->throwException(t('No valid container defined'));

    return $result;
  }

  protected function loadContent() {

    $bResult = false;
    //$result = $this->parseNamespaces($this->getContent());

    if ($sResult = $this->getContent()) {
      
      $this->setContent();
      parent::loadText($sResult);
    }

    return $bResult;
  }

  protected function getContent() {

    return $this->sContent;
  }

  public function setContent($sContent = '') {

    if (!is_string($sContent)) {

      $formater = $this->getControler('formater');
      $this->throwException(sprintf('Cannot insert %s as document content, string expected', $formater->asToken($sContent)));
    }
    
    $this->sContent = $sContent;
  }

  public function asString($iMode = 0) {

    if (!$sResult = $this->getContent()) {

      $sResult = parent::asString($iMode);
    }

    return $sResult;
  }
}