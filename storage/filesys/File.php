<?php

class XML_File extends XML_Resource {
  
  private $oDocument = null;
  private $sExtension = '';
  private $iSize = 0;
  private $iChanged = 0;
  private $oSettings = null;
  
  private $bFileSecured = false;
  
  public function __construct($sPath, $sName, array $aRights, XML_Directory $oParent, $bDebug) {
    
    $this->sFullPath = $sName ? $sPath.'/'.$sName : $sPath;
    $sPath = MAIN_DIRECTORY.$this->getFullPath();
    
    if (is_file($sPath)) {
      
      $this->aRights = $aRights;
      $this->sName = $sName;
      $this->sPath = $sPath;
      $this->oParent = $oParent;
      
      $this->iSize = filesize($sPath);
      $this->iChanged = filemtime($sPath);
      
      if ($iExtension = strrpos($sName, '.')) $this->sExtension = substr($sName, $iExtension + 1);
      else $this->sExtension = '';
      
      $this->doExist(true);
      
    } else if ($bDebug) dspm(xt('Fichier %s introuvable dans %s', view($sName), $oParent->parse()), 'file/notice');
  }
  
  public function getLastChange() {
    
    return $this->iChanged;
  }
  
  public function getActionPath() {
    
    $sPath = substr($this->getFullPath(), 0, strlen($this->getFullPath()) - strlen($this->getExtension()) - 1);
    return $this->getName() == 'index.eml' ? substr($sPath, 0, -6) : $sPath;
  }
  
  public function getSimpleName() {
    
    return substr($this->getName(), 0, strlen($this->getName()) - strlen($this->getExtension()) - 1);
  }
  
  public function getDisplayName() {
    
    return str_replace('_', ' ', substr($this->getName(), 0, strlen($this->getName()) - strlen($this->getExtension()) - 1));
  }
  
  public function getExtension() {
    
    return $this->sExtension;
  }
  
  public function getSize() {
    
    return $this->iSize;
  }
  
  public function getSystemPath() {
    
    return $this->getParent()->getSystemPath().'/'.$this->getName();
  }
  
  public function getRealPath() {
    
    return $this->getParent()->getRealPath().'/'.$this->getName();
  }
  
  public function isLoaded() {
    
    return (bool) $this->oDocument;
  }
  
  /**
   * Get the real corresponding XML_Document without appending rights control
   */
  public function getFreeDocument() {
    
    if (!$this->oDocument) $this->getDocument(); // will load XML_Document in the XML_Document->loadFile() with setDocument(), maybe TODO
    
    return $this->oDocument;
  }
  
  /**
   * Get the copy of the corresponding document
   * Call XML_Document->loadFile() via new instance, that will put a copy here of the document with setDocument()
   */
  public function getDocument() {
    
    return new XML_Document((string) $this);
  }
  
  /**
   * Each XML_Document loads will register in the corresponding XML_File
   * This function will clone the document to avoid secured elements deleting
   * @param XML_Document $oDocument The XML_Document caller
   */
  public function setDocument(XML_Document $oDocument) {
    
    if ($oDocument->isEmpty()) $oDocument = new XML_Document;
    else $oDocument = new XML_Document($oDocument->getRoot()); // getRoot avoid parsing of specials classes like actions
    
    $oDocument->setFile($this);
    
    $this->oDocument = $oDocument;
  }
  
  public function checkRights($iMode) {
    
    if (!$this->isSecured() || ($iMode & $this->getUserMode())) return true;
    
    return false;
  }
  
  public function isFileSecured($mSecured = null) {
    
    if ($mSecured === null) return $this->bFileSecured;
    else $this->bFileSecured = $mSecured;
  }
  
  /**
   * Get security XML_SFile
   * @param boolean $bRecursive Get last setting file from parents
   * @return XML_Document|null
   */
  public function getSettings($bRecursive = false) {
    
    return $this->getParent()->getSettings($bRecursive);
  }
  
  /**
   * Change rights in corresponding SECURITY_FILE
   */
  public function updateRights($sOwner, $sGroup, $sMode) {
    
    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {
      
      return $this->getSettings()->updateFile($this->getName(), $sOwner, $sGroup, $sMode);
    }
    
    return false;
  }
  
  public function readArray() {
    
    return file($this->getRealPath(), FILE_SKIP_EMPTY_LINES);
  }
  
  public function read() {
    
    return file_get_contents($this->getRealPath());
  }
  
  /**
   * Alias of moveSecured() with $bSecured set to true. Move a file WITH security rights
   * 
   * @param string $sDirectory Targeted directory
   * @param string $sName Optional new name
   * @return null|XML_File The result from moveSecured() with $bSecured set to true
   */
  public function move($sDirectory, $sName = '') {
    
    return $this->moveSecured($sDirectory, $sName);
  }
  
  /**
   * Alias of moveSecured() with $bSecured set to false. Move a file WITHOUT security rights
   * 
   * @param string $sDirectory Targeted directory
   * @param string $sName Optional new name
   * @return null|string The result from moveSecured() with $bSecured set to false
   */
  public function moveFree($sDirectory, $sName = '') {
    
    return $this->moveSecured($sDirectory, $sName, false);
  }
  
  /**
   * Move a file with or without security rights, depends on @param $bSecured
   * - This file must be writable
   * - The target file shouldn't exist
   * - The target directory must be writable
   * 
   * If @param $bSecured is set to TRUE :
   * - Rights will be kept
   * 
   * If @param $bSecured is set to FALSE :
   * - Rights will not be kept and new rights will depends on new parent directory
   * - The target directory must be readable, but not necessary writable
   * 
   * @param string $sDirectory Targeted directory
   * @param string $sName Optional new name
   * @return null|string|XML_File If $bSecured is set to true, the resulting new XML_file if move success or null if not
   *    If $bSecured is set to false, then it will return (string) path if move success or null if not.
   */
  protected function moveSecured($sDirectory, $sNewName = '', $bSecured = true) {
    
    $oResult = null;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      $sName = $this->getName();
      if (!$sNewName) $sNewName = $sName;
      
      if ((!$oDirectory = Controler::getDirectory($sDirectory)) ||
        ($bSecured && !$oDirectory->checkRights(MODE_WRITE))) {
        
        dspm(xt('Impossible de déplacer %s dans %s, le répertoire est introuvable ou privé',
          $this->parse(), new HTML_Strong($sDirectory)), 'warning');
      }
      else if (rename($this->getRealPath(), $oDirectory->getRealPath().'/'.$sNewName)) {
        
        $this->update();
        
        if ($oDirectory != $this->getParent()) {
          
          if ($bSecured) $oDirectory->getSettings()->updateFile($sNewName,
            $this->getOwner(), $this->getGroup(), $this->getMode()); // copy security attributes
          
          $this->getSettings()->deleteFile($sName);
        }
        
        if ($bSecured) $oResult = $oDirectory->updateFile($sNewName);
        else $oResult = $oDirectory.'/'.$sNewName; // if not secured, target file may be not readable
        
        // Controler::addMessage(t('Fichier déplacé !'), 'success');
        
        // update directory settings
        $this->getSettings()->updateFileName($this->getName(), $sName);
        
      } else dspm(t('Impossible de déplacer le fichier !'), 'warning');
    }
    
    return $oResult;
  }
  
  public function rename($sNewName) {
    
    $oResult = null;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      if (rename($this->getRealPath(), $this->getParent()->getRealPath().'/'.$sNewName)) {
        
        $this->update();
        $oResult = $this->getParent()->updateFile($sNewName);
        
        Controler::addMessage(t('Fichier renommé !'), 'success');
        
        // update directory settings
        $this->getSettings()->updateFileName($this->getName(), $sNewName);
        
      } else Controler::addMessage(t('Impossible de renommer le fichier !'), 'warning');
    }
    
    return $oResult;
  }
  
  public function delete($bMessage = true, $bUpdateDirectory = true) {
    
    $bResult = null;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      if ($bResult = unlink($this->getSystemPath())) {
        
        if ($bUpdateDirectory) $this->update();
        
        // update directory settings
        $this->getSettings()->deleteFile($this->getName());
        
        if ($bMessage) dspm(xt('Suppression du fichier %s', $this->parse()), 'file/notice');
      }
    }
    
    return $bResult;
  }
  
  public function saveText($sContent) {
    
    return file_put_contents($this->getRealPath(), $sContent);
  }
  /*
   * Call parent directory to reload (re-create) an XML_File reference, this one will be destroy
   */
  
  public function update() {
    
    $this->getParent()->updateFile($this->getName());
  }
  
  public function parse() {
    
    return new HTML_A(Sylma::get('modules/editor/path').'?path='.$this->getFullPath(), $this->getFullPath());
    //$oLink->add($this->getParent().'/', new HTML_Span($this->getName(), array('class' => 'file-name')));
    
    //return $oLink;
  }
  
  public function parseXML() {
    
    $iSize = ($this->getSize() / 1000);
    
    if ($iSize < 1) $iSize = 1;
    
    return new XML_Element('file', null, array(
      'full-path' => $this->getFullPath(),
      'name' => $this->getName(),
      'simple-name' => $this->getSimpleName(),
      'display-name' => $this->getDisplayName(),
      'owner' => $this->getOwner(),
      'group' => $this->getGroup(),
      'mode' => $this->getMode(),
      'read' => booltostr($this->checkRights(MODE_READ)),
      'write' => booltostr($this->checkRights(MODE_WRITE)),
      'execution' => booltostr($this->checkRights(MODE_EXECUTION)),
      'size' => $iSize,
      'extension' => $this->getExtension()), SYLMA_NS_DIRECTORY);
  }
}

