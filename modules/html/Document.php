<?php

namespace sylma\modules\html;
use sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

require_once('parser/action/handler/Action.php');
require_once('core/window.php');

class Document extends parser\action\handler\Action {

  private $head = null;
  protected $result = null;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->setContexts(array('css', 'js', 'title'));
    $this->setNamespaces(array(
      'html' => \Sylma::read('namespaces/html'),
    ));

    parent::__construct($file, $aArguments, $base);
  }

  protected function addJS($sHref, $mContent = null) {

    if ($oHead = $this->getHead()) {

      if ($mContent) ($oHead->addElement('script', array('src' => $sLink), ''));
      else if (!$oHead->get("self:script[@src='$sHref']")) $oHead->add(new HTML_Script($sHref));

    }// else dspm(xt('Impossible d\'ajouter le fichier script %s', new HTML_Strong($sHref)), 'warning');
  }

  protected function addCSS($aContext) {

    if ($aContext && ($head = $this->getHead())) {

      foreach ($aContext as $file) {

        $head->addElement('link', null, array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'media' => 'all',
          'href' => (string) $file,
        ));
      }
    }
  }

  protected function getHead() {

    if (!$this->head) {

      if ($this->result) {

        $this->head = $this->result->getx('html:head');
      }
    }

    return $this->head;
  }

  protected function loadHeaders($sMime) {

    $sResult = '';
    $sCharset = 'utf-8';

    if($sMime == "application/xhtml+xml") {

      $sResult = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    }
    else {

      $sResult = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
    }

    header("Content-Type: $sMime;charset=$sCharset");
    header("Vary: Accept");

    return $sResult;
  }

  protected function loadContexts() {

    $this->addCSS($this->aArguments['content']->getContext('css'));
  }

  protected function loadSystemInfos(dom\handler $doc) {

    $body = $doc->getx('//html:body');

    $aContent = array(
      'user : ' . $this->getControler('user')->getName(),
    );

    $system = $body->addElement('div', $content, array('id' => 'sylma-system'));
    $system->addElement('div', $aContent);
  }

  protected function cleanResult(dom\handler $doc) {

    require_once('dom/handler.php');

    $this->setDirectory(__FILE__);
    $cleaner = $this->getTemplate('cleaner.xsl');

    $cleaned = $cleaner->parseDocument($doc);

    return $cleaned->asString(dom\handler::STRING_INDENT); // | dom\handler::STRING_HEAD
  }

  public function asString() {

    $sProlog = $this->loadHeaders('text/html'); // 'application/xhtml+xml'

    $doc = parent::asDOM();
    $this->result = $doc;

    $doc->registerNamespaces($this->getNS());

    $this->loadSystemInfos($doc);
    $this->loadContexts();

    return $sProlog . "\n" . $this->cleanResult($doc);;
  }
}

