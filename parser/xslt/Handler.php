<?php

namespace sylma\parser\xslt;
use sylma\core, sylma\dom;

class Handler extends dom\basic\handler\Rooted {

  const NS = 'http://www.w3.org/1999/XSL/Transform';

  public function __construct($mChildren = '', $iMode = \Sylma::MODE_EXECUTE, array $aNamespaces = array()) {

    $this->setProcessor(new \XSLTProcessor);

    parent::__construct($mChildren, $iMode);
  }

  public function setParameter($sName, $sValue, $sUri = '') {

    $bResult = $this->getProcessor()->setParameter($sUri, $sName, (string) $sValue);

    if (!$bResult) {

      $this->throwException(sprintf('Cannot create parameter %s', $sName));
    }

    return $bResult;
  }

  public function getParameter($sLocalName, $sUri = '') {

    $mResult = $this->getProcessor()->getParameter($sUri, $sLocalName);

    if (!$mResult) {

      $this->throwException(sprintf('Cannot retrieve parameter %s', $sName));
    }

    return $mResult;
  }

  /**
   *
   * @param \sylma\dom\handler $doc
   * @param type $bXML
   * @return \sylma\dom\handler
   */
  public function parseDocument(dom\handler $doc, $bXML = true) { // WARNING, XML_Document typed can cause crashes

    $mResult = null;
    $dom = $this->getControler();

    if ($doc->isEmpty()) {

      $doc->throwException('Cannot parse empty document');
    }

    if ($this->isEmpty()) {

      $this->throwException(t('Cannot parse empty template'));
    }

    $this->includeExternals();

    libxml_use_internal_errors(true);

    $this->getProcessor()->importStylesheet($this->getDocument());

    $this->retrieveErrors();
    libxml_clear_errors();

    if ($bXML) {

      $doc->getRoot();
      $mResult = $this->getProcessor()->transformToDoc($doc->getDocument());

      if ($mResult && $mResult->documentElement) {

        $mResult = $dom->create('handler', array($mResult));
      }
      else {

        $this->throwException('No result on parsing');
      }
    }
    else {

      $mResult = $this->getProcessor()->transformToXML($doc->getDocument());
    }

    $this->retrieveErrors();

    libxml_clear_errors();
    libxml_use_internal_errors(false);

    $dom->addStat('parse', array($this, $doc));

    return $mResult;
  }
}