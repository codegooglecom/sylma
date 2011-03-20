<?php

class XML_Resource {
  
  protected $aRights = array();
  
  protected $sPath = '';
  protected $sName = '';
  protected $sFullPath = '';
  protected $oParent = null;
  
  private $bExist = false;
  private $bSecured = false;
  
  public function doExist($bExist = null) {
    
    if ($bExist !== null) $this->bExist = $bExist;
    return $this->bExist;
  }
  
  public function getOwner() {
    
    return $this->aRights['owner'];
  }
  
  public function getGroup() {
    
    return $this->aRights['group'];
  }
  
  public function getMode() {
    
    return $this->aRights['mode'];
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  public function isOwner() {
    
    return Controler::getUser()->isName($this->getOwner());
  }
  
  public function getFullPath() {
    
    return $this->sFullPath;
  }
  
  public function getParents($oTarget = null) {
    
    $oParent = $this;
    $aResult = array();
    
    while (($oParent = $oParent->getParent()) && (!$oTarget || ($oParent != $oTarget))) {
      
      array_unshift($aResult, $oParent);
    }
    
    if ($oTarget && !$oParent) return null;
    else return $aResult;
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  protected function getUserMode() {
    
    // if (!array_key_exists('user-mode', $this->aRights)) Controler::addMessage($this, 'success');
    return $this->aRights['user-mode'];
  }
  
  protected function isSecured($bSecured = null) {
    
    if ($bSecured === null) return $this->bSecured;
    else $this->bSecured = $bSecured;
  }
  
  protected function getRights() {
    
    return $this->aRights;
  }
  
  /*
   * Extract and check validity of parameter from an XML_Element
   * @return an array of validated security parameters, with the user-mode for the result of
   * rights of the user on rights on the file
   **/
  
  protected function extractRights($oElement = null) {
    
    if ($oElement && ($oSecurity = $oElement->getByName('security', SYLMA_NS_SECURITY))) {
      
      if (Controler::useStatut('file/report')) Controler::addMessage(xt('Ressource "%s" sécurisée ', new HTML_Strong($this->getFullPath())), 'file/report');
      
      $sOwner = $oSecurity->readByName('owner', SYLMA_NS_SECURITY);
      $sGroup = $oSecurity->readByName('group', SYLMA_NS_SECURITY);
      $sMode = $oSecurity->readByName('mode', SYLMA_NS_SECURITY);
      
      $iMode = Controler::getUser()->getMode($sOwner, $sGroup, $sMode, new HTML_Strong((string) $this));
      
      if ($iMode !== null) return array('owner' => $sOwner, 'group' => $sGroup, 'mode' => $sMode, 'user-mode' => $iMode);
    }
    
    return array();
  }
  
  /**
   * Put all rights into object
   * @param array|XML_Element|null $mRights Rights to use
   * @return array Rights used
   */
  protected function setRights($mRights = null) {
    
    if (is_array($mRights)) $aRights = $mRights;
    else {
      
      $aDefaultRights = array(
        'owner' => $this->getOwner(),
        'group' => $this->getGroup(),
        'mode' => $this->getMode(),
        'user-mode' => $this->getUserMode());
      
      if (Controler::getUser())
        $aDefaultRights['user-mode'] = Controler::getUser()->getMode(
          $aDefaultRights['owner'], $aDefaultRights['group'], $aDefaultRights['mode']);
      
      if (is_object($mRights)) {
        
        if (!$aRights = $this->extractRights($mRights)) $aRights = $aDefaultRights;
        
      } else $aRights = $aDefaultRights;
    }
    
    $this->aRights = $aRights;
    $this->isSecured(true);
    
    return $aRights;
  }
  
  /**
   * Check rights arguments for update in updateRights
   */
  protected function checkRightsArguments($sOwner, $sGroup, $sMode) {
    
    if ($this->isOwner()) {
      
      $bOwner = $sOwner !== $this->getOwner();
      $bGroup = $sGroup !== $this->getGroup();
      $bMode  = $sMode !== $this->getMode();
      
      if ($bOwner || $bGroup || $bMode) {
        
        $bResult = true;
        
        // Check validity
        
        if ($bOwner) {
          
          $bOwner = false;
          dspm(t('Changement d\'utilisateur impossible pour le moment'), 'file/warning');
        }
        
        if ($bGroup && !Controler::getUser()->isMember($sGroup)) {
          
          $bResult = false;
          dspm(t('Vous n\'avez pas les droits sur ce groupe ou il n\'existe pas !'), 'file/warning');
        }
        
        $iMode = Controler::getUser()->getMode($sOwner, $sGroup, $sMode);
        
        if ($bMode && $iMode === null) {
          
          $bResult = false;
          dspm(t('Les arguments pour la mise-à-jour ne sont pas valides'), 'file/warning');
        }
        
        if ($bMode && !($iMode & MODE_READ)) {
          
          $bResult = false;
          dspm(t('Vous ne pouvez pas retirer tous les droits de lecture'), 'file/warning');
        }
        
        // all datas are ok, or not modified
        
        if ($bResult && ($bOwner || $bGroup || $bMode)) return true;
      }
      
    } else dspm('Vous n\'avez pas les droits pour faire des modifications !', 'file/warning');
    
    return false;
  }
  
  public function __toString() {
    
    return $this->getFullPath();
  }
}

class XML_Directory extends XML_Resource {
  
  private $aDirectories = array();
  private $aFiles = array();
  private $aFreeFiles = array();
  private $oSettings = null;
  
  private $aChildrenRights = null;
  
  public function __construct($sPath, $sName, $aRights = array(), $oParent = null) {
    
    $this->sFullPath = $sName ? $sPath.'/'.$sName : $sPath;
    
    if (is_dir(MAIN_DIRECTORY.$this->getFullPath())) {
      
      $this->aRights = $this->aChildrenRights = $aRights;
      $this->sName = $sName;
      $this->sPath = $sPath;
      $this->oParent = $oParent;
      
      $this->doExist(true);
      $this->loadRights();
    }
  }
  
  private function getChildrenRights() {
    
    return $this->aChildrenRights;
  }
  
  /**
   * Get security XML_Document (eg: directory.sml)
   * @param boolean $bRecursive Get last setting file from parents
   * @return XML_SFile|null
   */
  public function getSettings($bRecursive = false) {
    
    if ($bRecursive && !$this->oSettings) {
      
      if ($this->getParent()) return $this->getParent()->getSettings(true);
      else dspm(t('Aucun fichier de sécurité dans le répertoire parent'), 'file/error');
    }
    
    return $this->oSettings;
  }
  
  private function loadRights() {
    
    if (!$this->isSecured()) {
      
      if (Controler::getUser()) {
        
        $this->oSettings = new XML_SFile($this);
        
        if ($this->getSettings()->getDocument()) { // read security file
          
          // self rights
          
          if ($aRights = $this->setRights($this->getSettings()->getDirectory())) $this->aChildrenRights = $aRights;
          
          // children rights
          
          if (($oChildrenRights = $this->getSettings()->getPropagation()) &&
            ($aChildrenRights = $this->extractRights($oChildrenRights))) $this->aChildrenRights = $aChildrenRights;
          
        } else $this->setRights();
        
      } else if (Controler::useStatut('file/report')) Controler::addMessage(xt('Sécurisation suspendue dans "%s"', new HTML_Strong($this->getFullPath())), 'file/report');
    }
  }
  
  public function unbrowse() {
    
    $oResult = $this->parseXML();
    $oParent = $this;
    
    while ($oParent = $oParent->getParent()) {
      
      $oResult->shift($oParent->parseXML());
    }
    
    return $oResult;
  }
  
  public function browse($aExtensions, $aPaths = array(), $iDepth = null) {
    
    $aFiles = scandir(MAIN_DIRECTORY.$this->getFullPath(), 0);
    $oElement = $this->parseXML();
    
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      foreach ($aFiles as $sFile) {
        
        if ($sFile != '.' && $sFile != '..') {
          
          if (($oFile = $this->getFile($sFile)) && $oFile->getUserMode() != 0) {
            
            if ($aExtensions && !in_array(strtolower($oFile->getExtension()), $aExtensions)) $oFile = null;
            else $oElement->add($oFile->parseXML());
            
          } else if ($oDirectory = $this->getDirectory($sFile)) {
            
            $bValid = true;
            
            foreach ($aPaths as $sPath) {
              
              switch ($sPath{0}) {
                
                case '/' : if ($sPath == $oDirectory->getFullPath()) $bValid = false; break;
                default : if ($sPath == $oDirectory->getName()) $bValid = false; break;
              }
            }
            
            if ($bValid) {
              
              if ($iDepth === null || $iDepth) $oElement->add($oDirectory->browse($aExtensions, $aPaths, $iDepth));
              else $oElement->add($oDirectory->parseXML());
            }
          }
        }
      }
    }
    
    //if ($oElement->isEmpty() && $this->getUserMode() != 0) return null;
    //else 
    return $oElement;
  }
  
  /**
   * Add a file into this directory via XML_Document->freeSave()
   */
  
  public function addFreeDocument($sName, XML_Document $oDocument) {
    
    $oDocument->saveFree($this, $sName);
  }
  
  /*
   * Browse then return list of files inside the directory and sub-directories if iDepth != null
   */
  public function getFiles(array $aExtensions = array(), $sPreg = null, $iDepth = 0) {
    
    $this->browse(array(), array(), 1);
    $aResult = array();
    
    // Files of current directory
    
    if ($aExtensions) {
      
      foreach ($this->aFiles as $sFile => $oFile) {
        
        if ($oFile &&
          (!$aExtensions || in_array(strtolower($oFile->getExtension()), $aExtensions)) &&
          (!$sPreg || preg_match($sPreg, $sFile))) $aResult[] = $oFile;
      }
      
    } else $aResult = array_values($this->aFiles);
    
    // Recursion in sub-directory
    
    if ($iDepth === null || $iDepth > 0) {
      
      if ($iDepth) $iDepth--;
      
      foreach ($this->aDirectories as $oDirectory) {
        
        if ($oDirectory) $aResult = array_merge($aResult, $oDirectory->getFiles($aExtensions, $sPreg, $iDepth));
      }
    }
    
    return $aResult;
  }
  
  /**
   * Unload then reload file/document, maybe TODO to optimize by keeping the doc
   */
  public function updateFile($sName) {
    
    if (array_key_exists($sName, $this->aFiles)) unset($this->aFiles[$sName]);
    return $this->getFile($sName);
  }
  
  /**
   * Unload then reload directory
   */
  public function updateDirectory($sName) {
    
    if (array_key_exists($sName, $this->aDirectories)) unset($this->aDirectories[$sName]);
    return $this->getDirectory($sName);
  }
  
  /**
   * Build an XML_File, check existenz and right access
   * @param $sName The name + extension of the file
   * @param $bDebug If true, send an error message if no access is found
   * @return XML_File the file requested
   */
  public function getFile($sName, $bDebug = false) {
    
    $this->loadRights();
    
    if ($sName && is_string($sName)) {
      
      if (!array_key_exists($sName, $this->aFiles)) {
        
        $oFile = new XML_File($this->getFullPath(), $sName, $this->getRights(), $this, $bDebug);
        
        if ($oFile->doExist()) {
          
          if (!$mRights = $this->getSettings()->getFile($sName)) $mRights = $this->getChildrenRights();
          
          //if (((string) $sName) == 'root.xml') print_r($mRights);
          $oFile->setRights($mRights);
          
          if (Controler::getUser()) $this->aFiles[$sName] = $oFile;
          else return $oFile;
          
        } else $this->aFiles[$sName] = null;
      }
      
      return $this->aFiles[$sName];
    }
    
    return null;
  }
  
  public function getDirectory($sName) {
    
    $this->loadRights();
    
    if ($sName == '.') return $this;
    else if ($sName == '..') return $this->getParent();
    else if ($sName) {
      
      if (!array_key_exists($sName, $this->aDirectories)) {
        
        $oDirectory = new XML_Directory($this->getFullPath(), $sName, $this->getChildrenRights(), $this);
        
        if ($oDirectory->doExist()) $this->aDirectories[$sName] = $oDirectory;
        else $this->aDirectories[$sName] = null;
      }
      
      return $this->aDirectories[$sName];
    }
    
    return null;
  }
  
  public function getDistantFile($aPath, $bDebug = false) {
    
    if ($aPath) {
      
      if (count($aPath) == 1) {
        
        return $this->getFile($aPath[0], $bDebug);
        
      } else {
        
        $sName = array_shift($aPath);
        
        $oSubDirectory = $this->getDirectory($sName);
        
        if ($oSubDirectory) return $oSubDirectory->getDistantFile($aPath, $bDebug);
      }
    }
    
    return null;
  }
  
  public function getDistantDirectory($mPath) {
    
    if (is_string($mPath)) $mPath = explode('/', $mPath);
    
    if ($mPath) {
      
      $sName = array_shift($mPath);
      
      if ($oSubDirectory = $this->getDirectory($sName)) return $oSubDirectory->getDistantDirectory($mPath);
      
    } else return $this;
    
    return null;
  }
  
  public function addDirectory($sName) {
    
    $oDirectory = null;
    
    if (!$oDirectory = $this->getDirectory($sName)) {
      
      if ($sName && $this->checkRights(MODE_WRITE)) {
        
        $sPath = MAIN_DIRECTORY.$this.'/'.$sName;
        
        mkdir($sPath, SYLMA_DEFAULT_MODE);
        
        unset($this->aDirectories[$sName]);
        $oDirectory = $this->getDirectory($sName);
        
        //dspm(xt('Création du répertoire %s', new HTML_Strong($oDirectory)), 'file/notice');
        
        //} else dspm(xt('Création du répertoire %s impossible', new HTML_Stong($this.$sName)), 'file/error');
      }
    }
    
    return $oDirectory;
  }
  
  public function checkRights($iMode) {
    
    $this->loadRights();
    
    if (!$this->isSecured() || ($iMode & $this->getUserMode())) return true;
    
    return false;
  }
  
  /**
   * Change rights in corresponding SECURITY_FILE
   */
  public function updateRights($sOwner, $sGroup, $sMode) {
    
    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {
      
      return $this->getSettings()->updateDirectory($sOwner, $sGroup, $sMode);
    }
    
    return false;
  }
  
  public function rename($sNewName) {
    
    $oResult = null;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      if (rename($this->getRealPath(), $this->getParent()->getRealPath().'/'.$sNewName)) {
        
        $oResult = $this->getParent()->updateDirectory($sNewName);
        Controler::addMessage(t('Répertoire renommé !'), 'success');
        
      } else Controler::addMessage(t('Impossible de renommer le répertoire !'), 'warning');
    }
    
    return $oResult;
  }
  
  public function getSystemPath() {
    
    return Controler::getSystemPath().'/'.$this->getRealPath();
  }
  
  public function getRealPath() {
    
    return $this->getParent() ? MAIN_DIRECTORY.$this : MAIN_DIRECTORY;
  }
  
  public function delete($bDeleteChildren = false) {
    
    $bResult = false;
    
    if ($this->checkRights(MODE_WRITE)) {
      
      if ($bDeleteChildren) {
        
        if ($this === Controler::getDirectory()) dspm('Impossible de supprimer le répertoire principal !', 'file/error');
        else {
          
          $this->browse(array(), array(), 1);
          
          foreach ($this->aFiles as $oFile) if ($oFile) $oFile->delete();
          foreach ($this->aDirectories as $oDirectory) $oDirectory->delete(true);
        }
      }
      
      $bResult = rmdir(MAIN_DIRECTORY.$this);
      
      $this->getParent()->updateDirectory($this->getName());
    }
    
    return $bResult;
  }
    
  public function parseXML() {
    
    if (!$sName = xmlize($this->getName())) {
      
      $sName = t('<racine>');
      $sPath = ''; //'/';
      
    } else $sPath = $this->getFullPath();
    
    return new XML_Element('directory', null, array(
      'full-path' => xmlize($sPath),
      'owner' => $this->getOwner(),
      'group' => $this->getGroup(),
      'mode' => $this->getMode(),
      'read' => booltostr($this->checkRights(MODE_READ)),
      'write' => booltostr($this->checkRights(MODE_WRITE)),
      'execution' => booltostr($this->checkRights(MODE_EXECUTION)),
      'name' => $sName));
  }
  
  public function parse() {
    
    return new HTML_A(Controler::getSettings('module[@name="explorer"]/path').'?path='.$this->getFullPath(), $this->getFullPath());
  }
  
  public function __toString() {
    
    if ($this->getFullPath()) return $this->getFullPath();
    else return '';//'/';
  }
}

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
    
    return new HTML_A(SYLMA_PATH_EDITOR.'?path='.$this->getFullPath(), $this->getFullPath());
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

class XML_SFile {
  
  private $oDocument;
  private $oDirectory;
  
  public function __construct($oDirectory) {
    
    $this->oDirectory = $oDirectory;
    $sPath = $oDirectory->getFullPath().'/'.SYLMA_SECURITY_FILE;
    
    if (file_exists(MAIN_DIRECTORY.$sPath)) {
      
      $this->oDocument = new XML_Document();
      $this->getDocument()->loadFreeFile($sPath);
    }
  }
  
  public function getDocument() {
    
    return $this->oDocument;
  }
  
  public function getParent() {
    
    return $this->oDirectory;
  }
  
  public function build() {
    
    if ($this->getDocument()) dspm(xt('Le fichier de sécurité dans %s existe déjà', $this->getParent()), 'file/error');
    else {
      
      $oDocument = new XML_Document;
      $oDocument->addNode('directory', null, null, SYLMA_NS_DIRECTORY);
      
      $this->getParent()->addFreeDocument(SYLMA_SECURITY_FILE, $oDocument);
      
      $this->oDocument = $oDocument;
    }
  }
  
  public function getDirectory() {
    
    if ($this->getDocument()) return $this->getDocument()->getByName('self', SYLMA_NS_DIRECTORY);
    else return null;
  }
  
  public function getPropagation() {
    
    if ($this->getDocument()) return $this->getDocument()->getByName('propagate', SYLMA_NS_DIRECTORY);
    else return null;
  }
  
  public function getFile($sName) {
    
    if ($this->getDocument()) return $this->getDocument()->get('ld:file[@name="'.xmlize($sName).'"]', 'ld', SYLMA_NS_DIRECTORY);
    else return null;
  }
  
  public function updateFileName($sName, $sNewName) {
    
    $bResult = null;
    
    if ($nFile = $this->getFile($sName)) {
      
      $nFile->setAttribute('name', $sNewName);
      $bResult = $this->save();
    }
    
    return $bResult;
  }
  
  public function updateFile($sName, $sOwner, $sGroup, $sMode) {
    
    if ($nFile = $this->getFile($sName)) $nFile->remove();
    else if (!$this->getDocument()) $this->build();
    
    $nFile = new XML_Element('file', 
      new XML_Element('ls:security', array(
          new XML_Element('ls:owner', $sOwner, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:group', $sGroup, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:mode', $sMode, null, SYLMA_NS_SECURITY)),
        null, SYLMA_NS_SECURITY),
      array('name' => $sName), SYLMA_NS_DIRECTORY);
    
    $this->getDocument()->add($nFile);
    
    return $this->save();
  }
  
  public function deleteFile($sName) {
    
    $bResult = null;
    
    if ($nFile = $this->getFile($sName)) {
      
      $nFile->remove(); // TODO check if empty
      $bResult = $this->save();
    }
    
    return $bResult;
  }
  
  public function updateDirectory($sOwner, $sGroup, $sMode) {
    
    if ($nDirectory = $this->getDirectory()) $nDirectory->remove();
    else if (!$this->getDocument()) $this->build();
    
    $nDirectory = new XML_Element('self', 
      new XML_Element('ls:security', array(
          new XML_Element('ls:owner', $sOwner, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:group', $sGroup, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:mode', $sMode, null, SYLMA_NS_SECURITY)),
        null, SYLMA_NS_SECURITY), SYLMA_NS_DIRECTORY);
    
    $this->getDocument()->add($nDirectory);
    
    return $this->save();
  }
  
  private function save() {
    
    if ($this->getDocument()) {
      
      if ($this->getDocument()->getRoot()->hasChildren()) return $this->getDocument()->saveFree($this->getParent(), SYLMA_SECURITY_FILE);
      else unlink(MAIN_DIRECTORY.$this->getParent()->getFullPath().'/'.SYLMA_SECURITY_FILE);
      
    } else return null;
  }
}
