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

      foreach ($aContext as $sLink) {

        $head->addElement('link', null, array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'media' => 'all',
          'href' => $sLink,
        ));
      }
    }
  }

  protected function getHead() {

    if (!$this->head) {

      if ($this->result) {

        $this->head = $this->result->getx('html:head', array('html' => \Sylma::read('namespaces/html')));
      }
    }

    return $this->head;
  }

  public function asString() {

    // @copyright following code to keystonewebsites.com - http://keystonewebsites.com/articles/mime_type.php
    // @updated by Rodolphe Gerber

    $sResult = '';

    $sCharset = 'utf-8';
    //$sMime = 'application/xhtml+xml';
    $sMime = 'text/html';

    if($sMime == "application/xhtml+xml") {

      $sProlog = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    }
    else {

      $sProlog = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
    }

    header("Content-Type: $sMime;charset=$sCharset");
    header("Vary: Accept");

    try {

      $doc = parent::asDOM();

    } catch(Exception $e) {

      /*
      $sResult = (string) 'Problème lors du chargement du site. Nous nous excusons pour ce désagrément. <a href="/">Cliquez-ici</a> pour revenir à la page d\'accueil';

      if (\Sylma::read('debug/enable')) {

        echo('<table>' . $e->xdebug_message . '</table>');
        //echo '<div style="background-color: #ddd; padding: 10px; border: 1px solid black;">' . $e->getTrace() . '</div>';
      }
      */

      throw $e;
    }

    //$this->setContexts();
    $this->result = $doc;

    $this->addCSS($this->aArguments['content']->getContext('css'));

    require_once('dom/handler.php');

    $this->setDirectory(__FILE__);
    $cleaner = $this->getTemplate('cleaner.xsl');

    $cleaned = $cleaner->parseDocument($doc);
    // $this->loadContexts();
    $sResult = $sProlog . "\n" . $cleaned->asString(dom\handler::STRING_INDENT); // | dom\handler::STRING_HEAD

    return $sResult;
  }
}

