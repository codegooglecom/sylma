<?php
/*
 * Classes des type de sorties
 **/

interface Main {
  
  public function loadAction($oAction);
}

class Any implements Main {
  
  private $oFile = null;
  
  public function loadAction($oFile) {
    
    $this->oFile = $oFile;
  }
  
  public function __toString() {
    
    if ($this->oFile) {
      
      $sPath = MAIN_DIRECTORY.'/'.$this->oFile;
      
      Controler::setContentType($this->oFile->getExtension());
      header('Content-Length: ' . $this->oFile->getSize());
      header('Content-Disposition: attachment; filename=' . basename($sPath));
      // header('Content-Description: File Transfer');
      readfile($sPath);
    }
    
    return '';
  }
}

class Img implements Main {
  
  private $oFile = null;
  
  public function loadAction($oFile) {
    
    if ($oFile instanceof XML_File) $this->oFile = $oFile;
  }
  
  public function resize($sExtension, $iMaxWidth, $iMaxHeight, $bCrop = false) {
    
    // Calcul des nouvelles dimensions
    
    list($iWidth, $iHeight) = getimagesize(MAIN_DIRECTORY.$this->oFile);
    
    $iWidthRatio = $iHeightRatio = 1;
    $iXSource = $iYSource = 0;
    
    $iSourceHeight = $iHeight;
    $iSourceWidth = $iWidth;
    
    // look up for ratios
    
    if ($iWidth > $iMaxWidth) {
      
      $iWidthRatio = $iWidth / $iMaxWidth;
      $iPreviewWidth = $iMaxWidth;
      
    } else $iPreviewWidth = $iWidth;
    
    if ($iHeight > $iMaxHeight) {
      
      $iHeightRatio = $iHeight / $iMaxHeight;
      $iPreviewHeight = $iMaxHeight;
      
    } else $iPreviewHeight = $iHeight;
    
    // set croping
    if ($iWidthRatio > $iHeightRatio) {
      
      if ($bCrop) {
        
        $iSourceWidth = $iPreviewWidth * $iHeightRatio;
        $iXSource = ($iWidth - $iSourceWidth) / 2;
        
      } else $iPreviewWidth = $iSourceWidth;
      
    } else if ($iWidthRatio < $iHeightRatio) {
      
      if ($bCrop) {
        
        $iSourceHeight = $iPreviewHeight * $iWidthRatio;
        $iYSource = ($iHeight - $iSourceHeight) / 2;
        
      } else $iPreviewHeight = $iSourceHeight;
      
    }
    
    $oImagePreview = imagecreatetruecolor($iPreviewWidth, $iPreviewHeight);
    
    if ($sExtension == 'png' || $sExtension == 'gif') {
      
      imagealphablending($oImagePreview, false);
      $iTransparent = imagecolortransparent($oImagePreview, imagecolorallocatealpha($oImagePreview, 0, 0, 0, 127));
      imagefill($oImagePreview, 0, 0, $iTransparent);
      imagesavealpha($oImagePreview, true);
    }
    
    $sExtension = strtolower($this->oFile->getExtension());
    if ($sExtension == 'jpg') $sExtension = 'jpeg';
    
    $sFunction = 'imagecreatefrom'.$sExtension;
    $oImageSource = @$sFunction(MAIN_DIRECTORY.$this->oFile) or die("Cannot Initialize new GD image stream");
    
    // Redimensionnement
    
    imagecopyresampled($oImagePreview, $oImageSource, 0, 0, $iXSource, $iYSource, $iPreviewWidth, $iPreviewHeight, $iSourceWidth, $iSourceHeight);
    
    return $oImagePreview;
  }
  
  public function __toString() {
    
    if ($this->oFile) {
      
      $sFilePath = (string) $this->oFile;
      
      $sExtension = strtolower($this->oFile->getExtension());
      if ($sExtension == 'jpg') $sExtension = 'jpeg';
      
      $aExtensions = array('jpeg', 'png', 'gif');
      
      if ($sExtension == 'gif') { // TODO: tmp, seems to work :/
        
        Controler::setContentType($sExtension);
        
        return $this->oFile->read();
        
      } else if (in_array($sExtension, $aExtensions)) {
        
        Controler::setContentType($sExtension);
        
        $iWidth = Controler::getPath()->getAssoc('width');
        $iHeight = Controler::getPath()->getAssoc('height');
        
        if ($iWidth || $iHeight) {
          
          if (!$iWidth) $iWidth = 200;
          if (!$iHeight) $iHeight = 200;
          
          $img = $this->resize($sExtension, $iWidth, $iHeight, true);
          
        } else {
          
          $sFunction = 'imagecreatefrom'.strtolower($sExtension);
          $img = @$sFunction(MAIN_DIRECTORY.$sFilePath) or die("Cannot Initialize new GD image stream");
          
          if ($sExtension == 'png' || $sExtension == 'gif') {
            
            imagealphablending($img, false);
            imagesavealpha($img, true);
          }
        }
        //echo Controler::getMessages();
        // imagefilter($img, IMG_FILTER_GRAYSCALE);
        // imagestring($img, 2, 5, 15, date('H:i:s'), imagecolorallocate($img, 255, 216, 147));
        
        $sFunction = 'image'.$sExtension;
        
        $sFunction($img);
        imagedestroy($img);
        
        exit;
      }
      
    } else Controler::error404();
  }
}

class Redirection implements Main {
  
  public function addOnLoad($sValue) {
    
    return null;
  }
  
  public function addJS($sHref, $mContent = null) {
    
    return null; // TODO
  }
  
  public function addCSS($sHref = '') {
    
    return null; // TODO
  }
  
  public function loadAction($oAction) {
    
    $mResult = $oAction->parse();
    
    if (!is_object($mResult) || !$mResult instanceof Redirect) {
      
      $mResult = new Redirect('/');
      dspm(xt('Aucune redirection dans l\'action (%s), redirection par défaut effectuée', view($mResult)), 'action/warning');
    }
    
    return $mResult;
  }
  
  public function __toString() {
    
    return t('Erreur : Problème dans la redirection');
    //return xt('Erreur : Problème dans la redirection %s', new HTML_A('/hello', 'Cliquez ici pour revenir à la page d\'accueil'));
  }
}

class Form extends XML_Helper implements Main {
  
  private $oRedirect = null;
  
  public function __construct() {
    
    parent::__construct();
    $this->setBloc('content-title', new HTML_Tag('h4', '', array('class' => 'ajax-title'), true));
  }
  
  public function loadAction($oAction) {
    
    $this->setBloc('content', $oAction);
  }
  
  public function addJS($sHref) {
    
    $this->getBloc('header')->add(new HTML_Script($sHref));
  }
  
  public function addCSS($sHref) {
    
    $this->getBloc('header')->add(new HTML_Style($sHref));
  }
  
  public function isRedirect() {
    
    return $this->getRedirect();
  }
  
  public function setRedirect($oRedirect = null) {
    
    $this->oRedirect = $oRedirect;
  }
  
  public function getRedirect() {
    
    return $this->oRedirect;
  }
  
  public function __toString() {
    
    if ($this->isRedirect()) {
      
      $sAction = $this->getRedirect()->getArgument('action');
      
      $this->add($sAction.'<>');
      if ($sAction == 'script') $this->add($this->getRedirect()->getArgument('script'));
      else if ($sAction == 'redirect') $this->add($this->getRedirect());
      
    } else {
      
      Controler::getMessages()->setMessages('system');
      
      $this->add('display<>');
      $this->add($this->getBloc('content')->getAttribute('action')->getValue().'<>');
      
      // Suppression du nom du form pour empêcher l'affichage
      $this->getBloc('content')->setName();
      
      $this->addBloc('header');
      $this->addBloc('content-title');
      $this->add(new HTML_Div($this->getBloc('content'), array('class' => 'ajax-content')));
      $this->add(new HTML_Div('', array('class' => 'ajax-shadow')));
      $this->add(new HTML_Div('', array('class' => 'ajax-bulle')));
      $this->add(Controler::getMessages());
      
      // $this->addBloc('content');
    }
    
    return parent::__toString;
  }
}

class Txt implements Main {
  
  private $sContent = '';
  
  public function addJS($sHref, $mContent = null) {
    
  }
  
  public function addCSS($sHref = '') {
    
  }
  
  public function addOnLoad($sContent) {
    
  }
  
  public function loadAction($oAction) {
    
    if ($oAction) {
      
      if ($oAction instanceof XML_Action) {
        
        $this->sContent = (string) $oAction->parse();
        
      } else if ($oAction instanceof XML_File) {
        
        /*
        $oFinfo = new finfo(FILEINFO_MIME, ini_get('mime_magic.magicfile')); // Retourne le type mime
        
        if (!$oFinfo) {
          
          $this->sContent = "Échec de l'ouverture de la base de données fileinfo";
          
        } else {
          
          if($sMime = $oFinfo->file(MAIN_DIRECTORY.$sPath)) {
            
            $this->sContent = $sMime.'hello';
            $oFinfo->close();
            
          } else $this->sContent = 'Mime introuvable !';
          
        }
        */
        
        Controler::setContentType(Controler::getPath()->getExtension());
        
        $this->sContent = file_get_contents(MAIN_DIRECTORY.$oAction);
        
      } else $this->sContent = (string) $oAction;
      
    } else Controler::error404();
  }
  
  public function __toString() {
    
    return $this->sContent;
  }
}

class WindowAction extends XML_Document implements Main {
  
  private $sOnLoad = '';
  
  public function addOnLoad($sValue) {
    
    return null;
  }
  
  public function addJS($sHref, $mContent = null) {
    
    return null; // TODO
  }
  
  public function addCSS($sHref = '') {
    
    return null; // TODO
  }
  
  public function loadAction($oAction) {
    
    Controler::setContentType('xml');
    
    // $oRoot = $this->set(new XML_Element('action', null, null, 'salut'));
    // $oContent = $oRoot->addNode('content', null, null, 'salut');
    
    $oRoot = $this->set(new XML_Element('action', null, null, SYLMA_NS_XHTML));
    $oContent = $oRoot->addNode('content', null, null, SYLMA_NS_XHTML);
    
    if ($oAction instanceof XML_Action) { // action
      
      $oResult = $oAction->parse();
      $oContent->add($oResult);
      
    } else if ($oAction instanceof XML_File) { // file
      
      $oContent->add(new XML_Document((string) $oAction));
      
    } else {
      
      $oContent->add($oAction);
    }
    
    $oRoot->addNode('messages', Controler::getMessages());
    
    $aKeys = array_reverse(array_keys(Controler::getResults()));
    
    if (Controler::countResults() >= 1) $oContent->setAttribute('recall', $aKeys[1]); // TODO
    if (Controler::countResults() == 2) $oContent->setAttribute('methods', $aKeys[0]); // TODO
    
    $oInfos = $oRoot->addNode('infos', Controler::getInfos());
    $oInfos->getFirst()->addClass('msg-infos-sub');
  }
  
  public function __toString() {
    
    $oView = new XML_Document($this);
    $oView->formatOutput();
    
    return $oView->display();
  }
}

class HTML_Action extends XML_Action {
  
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
      
    } else {
      
      // Add js onload
      //$this->dsp();
      //exit;
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
      // dspf($_SESSION['test']);

      if ($oContainer = $oView->get($sMessage)) $oContainer->shift(Controler::getMessages());
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
        return $sDocType."\n".$oView->display(false, true);
      }
    }
  }
  
  public function __toString() {
    
    try {
      
      $sResult = $this->printXML();
      
    } catch(Exception $e) {
      
      if (DEBUG && Controler::isAdmin()) {
        
        dsp($e->getMessage());
        dsp($e->getFile().' : ligne '.$e->getLine());
        foreach ($e->getTrace() as $mVal) {
          
          echo $mVal['file'].' ['.$mVal['line'].'] '.$mVal['function'].'(';
          //foreach ($mVal as $sKey => $mPeuimpor) echo $sKey.', ';
          foreach ($mVal['args'] as $sKey => $mArgument) echo $sKey.' => '.view($mArgument).',<br/>';
          echo ')<br/>';
        }
        /*
        foreach (debug_backtrace() as $aLine1)
          foreach ($aLine1 as $aLine2) echo($aLine2);*/
      }
      
      $sResult = (string) xt('Problème lors du chargement du site. Nous nous excusons pour ce désagrément. %s pour revenir à la page d\'accueil', new HTML_Br.new HTML_A('/', t('Cliquez-ici')));

    }
    
    return $sResult;
  }
}


class Xml extends XML_Document implements Main {
  
  private $sMode = '';
  
  public function loadAction($oAction) {
    
    Controler::setContentType('xml');
    $this->sMode = Controler::getPath()->getAssoc('xml-mode');
    
    if ($oAction instanceof XML_Action) {
      
      $oResult = $oAction->parse();
      
      if (is_string($oResult)) $this->add('root', $oResult);
      else $this->set($oResult);
      
    } else if ($oAction instanceof XML_File) {
      
      $this->set(new XML_Document((string) $oAction));
      
    } else {
      
      $this->set(new XML_Element('root', (string) $oAction));
    }
  }
  
  public function __toString() {
    
    if ($this->sMode == 'html' || (Controler::getPath()->getExtension() == 'rss')) {
      
      $oView = new XML_Document($this);
      $oView->formatOutput();
      
      return $oView->display(true);
      
    } else if ($this->sMode == 'htm') {
      
      return $this->display(true, false);
      
    } else {
      
      return parent::__toString();
    }
  }
}


