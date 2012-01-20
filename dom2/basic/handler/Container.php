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

    //$result = $this->parseNamespaces($this->getContent());
    $result = $this->getContent();
    $this->setContent();

    return parent::loadText($result);
  }

  protected function getContent() {

    return $this->sContent;
  }

  protected function setContent($sContent = '') {

    $this->sContent = $sContent;
  }
}