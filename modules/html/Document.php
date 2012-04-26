<?php

namespace sylma\modules\html;
use sylma\core, sylma\parser;

require_once('parser/action/handler/Action.php');
require_once('core/window.php');

class Document extends parser\action\handler\Action {

  private $oHead = null;

  protected function addJS($sHref, $mContent = null) {

    if ($oHead = $this->getHead()) {

      if ($mContent) ($oHead->addElement('script', array('src' => $sLink), ''));
      else if (!$oHead->get("self:script[@src='$sHref']")) $oHead->add(new HTML_Script($sHref));

    }// else dspm(xt('Impossible d\'ajouter le fichier script %s', new HTML_Strong($sHref)), 'warning');
  }

  protected function addCSS($sHref = '') {

    if (($oHead = $this->getHead()) && !$oHead->get("ns:link[@href='$sHref']")) {

      $oHead->add(new HTML_Style($sHref));

    }// else dspm(xt('Impossible d\'ajouter la feuille de style %s', new HTML_Strong($sHref)), 'warning');
  }

  protected function getHead() {

    if (!$this->oHead) $this->oHead = new XML_Element('head', null, null, SYLMA_NS_XHTML);

    return $this->oHead;
  }

  public function asString() {

    // @copyright following code to keystonewebsites.com - http://keystonewebsites.com/articles/mime_type.php
    // @updated by Rodolphe Gerber

    $sResult = '';

    $sCharset = 'utf-8';
    $sMime = 'text/html';

    if($sMime == "application/xhtml+xml") {

      $sProlog = '<?xml version="1.0" encoding="' . $sCharset . '" ?>' . "\n" .
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    }
    else {

      $sProlog = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
    }

    header("Content-Type: $sMime;charset=$sCharset");
    header("Vary: Accept");

    try {

      $doc = parent::asDOM();

    } catch(Exception $e) {

      $sResult = (string) 'Problème lors du chargement du site. Nous nous excusons pour ce désagrément. <a href="/">Cliquez-ici</a> pour revenir à la page d\'accueil';

      if (\Sylma::read('debug/enable')) {

        echo('<table>' . $e->xdebug_message . '</table>');
        //echo '<div style="background-color: #ddd; padding: 10px; border: 1px solid black;">' . $e->getTrace() . '</div>';
      }

      exit;
    }

    // $this->loadContexts();
    $sProlog . "\n" . $doc->asString();

    return $sResult;
  }
}

