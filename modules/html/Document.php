<?php

class WindowHTML extends XML_Action {

  private $oHead = null;
  private $sOnLoad = '';

  public function addOnLoad($sContent) {

    $this->sOnLoad .= "\n".$sContent;
  }

  public function addJS($sHref, $mContent = null) {

    if ($oHead = $this->getHead()) {

      if ($mContent) $oHead->add(new HTML_Script('', (string) $mContent));
      else if (!$oHead->get("ns:script[@src='$sHref']")) $oHead->add(new HTML_Script($sHref));

    }// else dspm(xt('Impossible d\'ajouter le fichier script %s', new HTML_Strong($sHref)), 'warning');
  }

  public function addCSS($sHref = '') {

    if (($oHead = $this->getHead()) && !$oHead->get("ns:link[@href='$sHref']")) {

      $oHead->add(new HTML_Style($sHref));

    }// else dspm(xt('Impossible d\'ajouter la feuille de style %s', new HTML_Strong($sHref)), 'warning');
  }

  public function getHead() {

    if (!$this->oHead) $this->oHead = new XML_Element('head', null, null, SYLMA_NS_XHTML);

    return $this->oHead;
  }

  public function printXML() {

    $sDocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

    // Action parsing

    $oView = new XML_Document($this);

    if ($oView->isEmpty()) {

      throw new Exception('Frontend ne retourne aucun résultat.');

    }

    // Add js onload

    if ($this->sOnLoad) $this->addJS(null, "window.addEvent('domready', function() {\n".$this->sOnLoad."\n});");

    if ($oHead = $oView->get('//ns:head')) $oHead->add($this->getHead()->getChildren());
    else dspm(xt('Impossible de trouver l\'en tête de la fenêtre dans %s', view($oView)), 'action/error');

    // Put messages and infos

    $sBody = '//ns:body';

    // infos

    if (Controler::isAdmin()) {

      $oInfos = new XML_Element('div', Controler::getInfos(), array('id' => 'msg-admin'));

      if ($oContainer = $oView->get($sBody)) $oContainer->add($oInfos);
      else $oView->add($oInfos);
    }

    // messages

    if (!$sMessage = Controler::getWindowSettings()->read('messages')) $sMessage = $sBody;

    if (Sylma::read('dom/debug/show-queries')) {

      $args = new XArguments(XML_Controler::$aQueries);
      dspm(XArguments::renderTree($args->parseTree()));
    }

    $iCount = \sylma\core\exception\Basic::getCount();

    if ($iCount > 8) {

      dspm(xt('%s exception(s) has been thrown', new HTML_Strong($iCount - 8)));
    }

    if ($oContainer = $oView->get($sMessage)) {

      $oContainer->shift(Controler::getMessages());
    }
    else {

      dspm(xt('Containeur %s introuvable', new HTML_Strong($sMessage)), 'action/warning');
      $oView->add(Controler::getMessages());
    }

    Controler::useMessages(false);

    // Fill empty html tags
    // TODO check not to heavy (metal)
    if ($oElements = $oView->query(SYLMA_HTML_TAGS, 'html', SYLMA_NS_XHTML)) {

      foreach ($oElements as $oElement) {

        if (!$oElement->hasChildren()) $oElement->set(' ');
      }
    }

    // Remove security elements

    if ($oElements = $oView->query('//@ls:owner | //@ls:mode | //@ls:group', 'ls', SYLMA_NS_SECURITY)) $oElements->remove();

    if ($oView->isEmpty()) {

      return (string) xt('Problème lors du chargement du site. Nous nous excusons pour ce désagrément. %s pour revenir à la page d\'accueil', new HTML_Br.new HTML_A('/', t('Cliquez-ici')));

    } else {

      $oView->formatOutput();
      // return $sDocType."\n".$oView->display(false, true);
      return $oView->display(true, false);
    }
  }

  protected function useApplication() {

    $bResult = false;

    if(stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {

      if(preg_match("/application\/xhtml\+xml;q=([01]|0\.\d{1,3}|1\.0)/i", $_SERVER["HTTP_ACCEPT"], $aMatches)) {

        $sXHtml = $aMatches[1];

        if(preg_match("/text\/html;q=([01]|0\.\d{1,3}|1\.0)/i", $_SERVER["HTTP_ACCEPT"], $aMatches)) {

          $sHtml = $aMatches[1];

          if((float)$sXHtml >= (float)$sHtml) {

            $bResult = true;
          }
        }
      }
      else {

        $bResult = true;
      }
    }

    return $bResult;
  }

  public function __toString() {

    try {

      // @copyright following code to keystonewebsites.com - http://keystonewebsites.com/articles/mime_type.php
      // @updated by Rodolphe Gerber

      $sCharset = 'utf-8';
      $sMime = 'text/html';

//      if ($this->useApplication()) $sMime = "application/xhtml+xml";

      if($sMime == "application/xhtml+xml") {

        $sProlog = '<?xml version="1.0" encoding="' . $sCharset . '" ?>' . "\n" .
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
      }
      else {

        $sProlog = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
      }

      header("Content-Type: $sMime;charset=$sCharset");
      header("Vary: Accept");

      $sResult = $sProlog . "\n" . $this->printXML();

    } catch(Exception $e) {

      $sResult = (string) xt('Problème lors du chargement du site. Nous nous excusons pour ce désagrément. %s pour revenir à la page d\'accueil', new HTML_Br.new HTML_A('/', t('Cliquez-ici')));

      if (Controler::isAdmin()) {

        echo('<table>' . $e->xdebug_message . '</table>');
        echo '<div style="background-color: #ddd; padding: 10px; border: 1px solid black;">' . Controler::getBacktrace($e->getTrace()) . '</div>';
      }
    }

    return $sResult;
  }
}

