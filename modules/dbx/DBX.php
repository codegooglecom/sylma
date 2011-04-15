<?php

class DBX_Module extends XDB_Module {
  
  private $oModel = null;
  private $oHeaders = null;
  
  public function __construct(XML_Directory $oDirectory, XML_Document $oSchema, XML_Document $oOptions) {
    
    $this->setName('dbx');
    
    $this->addDirectory(__file__, __class__);
    $this->addDirectory($oDirectory);
    
    $this->setNamespace('http://www.sylma.org/modules/dbx', 'dbx', false);
    $this->setNamespace(SYLMA_NS_XHTML, 'html', false);
    $this->setNamespace(SYLMA_NS_SCHEMAS, 'lc', false);
    
    $this->setOptions($oOptions);
    
    if (!$oSchema) $this->dspm(xt('Aucun schéma défini'), 'action/warning');
    else $this->setSchema($oSchema, true, $this->readOption('database/prefix', false));
    
    $this->oHeaders = new XML_Document($this->getOption('headers', false));
    
    $this->setDocument($this->readOption('database/document', false));
  }
  
  /*** Module Extension ***/
  
  private function getExtendDirectory() {
    
    return $this->getDirectory();
  }
  
  private function getAdminPath() {
    
    return $this->readOption('path');
  }
  
  /**
   * Build a document with the first node indicate in dbx options
   * @*param string $sName Customized name for root node
   * @return XML_Document The resulting "only rooted" document
   */
  protected function getEmpty($sName = '') {
    
    if (!$sName) $sName = $this->readOption('database/name');
    
    $oResult = new XML_Document();
    $oResult->addNode($this->getFullPrefix().$sName, null, null, $this->getNamespace());
    
    return $oResult;
  }
  
  /*** Various ***/
  
  public function validateElement(XML_Element $oElement, $bID = false) {
    
    foreach ($oElement->getChildren() as $oChild) if ($oChild->isElement()) $this->validateElement($oChild);
    
    $iFile = 0;
    
    foreach ($oElement->getAttributes() as $oAttribute) {
      
      $sValue = $oAttribute->getValue();
      
      if ($oAttribute->useNamespace($this->getNamespace('lc'))) {
        
        switch ($oAttribute->getName()) {
          
          case 'duplicate' :
            
            // load function and targeted element
            
            $aValue = explode(' ', $sValue);
            
            if (!count($aValue) == 2) dspm(xt('Arguments %s insuffisant pour la duplication dans %s',
              new HTML_Strong($sValue), view($oElement)), 'xml/warning');
            else {
              
              list($sFunction, $sTarget) = $aValue;
              
              if ($sTarget{0} == '@') $oNode = $oElement->setAttribute(substr($sTarget, 1), 'null');
              else $oNode = $oElement->getParent()->insertNode($sTarget, null, null, $this->getNamespace(), $oElement, true);
              
              switch ($sFunction) {
                
                case 'urlize' : $oNode->set(urlize($oElement->getValue())); break;
                case 'wikize' : 
                  
                  $sValue = preg_replace('/\\n/', '<html:br/>', $oElement->read());
                  //dspf($sValue);
                  if ($sValue) $oNode->set(strtoxml($sValue, $this->getNS()));
                  
                break;
                
                default :
                  
                  dspm(xt('Fonction %s inconnu sur le champ %s', new HTML_Strong($sValue), view($oElement)), 'xml/warning');
              }
            }
            
          break;
          
          case 'gen-id' :
            
            if ($bID) $oElement->setAttribute('xml:id', uniqid('x')); //$sValue
            
          break;
          
          case 'export' : 
            
            // $sPath = $this->readOption("file[@name='$sValue']");
            
          break;
          
          case 'file' :
            
            if ($sID = $oElement->getAttribute('temp-file', $this->getNamespace('lc'))) {
              
              // move file
              
              if ((!$aFiles = array_val('files', $this->getSessionForm())) ||
                (!$aFile = array_val($sID, $aFiles))) {
                
                $this->dspm(xt('Fichier perdu. Veuillez nous en excuser'), 'error');
              }
              else {
                
                if (!$oDirectory = Controler::getDirectory($sValue)) {
                  
                  $this->dspm(xt('Impossible d\'ouvrir le répertoire de destination des fichiers %s',
                  new HTML_Strong($sValue)), 'action/error');
                }
                else {
                  
                  if (!$oFile = Controler::getFile($aFile['path'])) {
                    
                    $this->dspm(xt('Fichier source perdu. Veuillez nous en excuser'), 'error');
                  }
                  else {
                    
                    if (!$sFile = $oFile->moveFree((string) $oDirectory)) {
                      
                      $oElement->remove();
                    }
                    else {
                      
                      $oElement->set($sFile);
                      $iFile++;
                    }
                  }
                }
              }
            }
            
          break;
          
          case 'model' :
          default :
            
            // dspm(xt('Attribut %s inconnu dans l\'élément %s',
              // new HTML_Strong($oAttribute->getName()), view($oAttribute->getParent())), 'xml/warning');
        }
      }
    }
    
    if ($iFile) $this->dspm(xt('%s fichier téléchargé', new HTML_Strong($iFile)), 'success');
    
    foreach ($oElement->getAttributes() as $oAttribute) { // remove all attr after to allow multiple/connected attr use
      
      if ($oAttribute->useNamespace($this->getNamespace('lc'))) $oAttribute->remove();
    }
  }
  
  protected function getTemplateExtension() {
    
    $oTemplate = null;
    
    if ($oFormExtension = $this->getOption('template-form')) {
      
      $oTemplate = $oFormExtension->getFirst();
    }
    
    return $oTemplate;
  }
  
  protected function setFormID() {
    
    $sID = uniqid('form-');
    
    if (!array_key_exists('forms', $_SESSION)) $_SESSION['forms'] = array();
    $_SESSION['forms'][$sID] = array();
    
    return $sID;
  }
  
  protected function getFormID() {
    
    return array_val('sylma_form_id', $_POST);
  }
  
  private function killSessionForm() {
    
    if ($this->getSessionForm()) unset($_SESSION['forms'][$this->getFormID()]);
  }
  
  private function &getSessionForm() {
    
    if ($this->checkForm()) $aResult =& $_SESSION['forms'][$this->getFormID()];
    else $aResult = array();
    
    return $aResult;
  }
  
  private function checkForm() {
    
    $bResult = (array_key_exists('forms', $_SESSION) &&
      ($sForm = $this->getFormID()) &&
      array_key_exists($sForm, $_SESSION['forms']));
      
    if (!$bResult) $this->dspm(xt('Formulaire invalide, la session a été perdu. Veuillez nous en excuser.'), 'error');
    
    return $bResult;
  }
  
  /*** Headers ***/
  
  private function getAdmin() {
    
    if (!$sPath = $this->readOption('path', false)) $sPath = $this->getExtendDirectory().'/admin';
    return $sPath;
  }
  
  private function getHeaders() {
    
    return $this->oHeaders;
  }
  
  private function setHeader($sName, $sValue, $bReplace = false) {
    
    if ($sValue) {
      
      if ($oElement = $this->getHeaders()->getByName($sName, $this->getNamespace('dbx'))) {
        
        if ($bReplace) {
          
          $oElement->set($sValue);
          return $oElement;
        }
        
      } else {
        
        return $this->getHeaders()->addNode($sName, $sValue, null, $this->getNamespace('dbx'));
      }
    }
  }
  
  /*** Values ***/
  
  /*
   * Evaluate $oValues and prepare special fields beginning with 'sylma-' for schema validation
   * Return value will be the new document ready for validation
   * $oValues can also be altered to resend to form if validation fail
   * 
   * @param TODO $oValues The document to read and transform
   * @param XML_Document|null $oParent The parent document to insert children to,
   *    if null, a new document will be builded then returned
   * @return XML_Document The document sent as @param or a new document with result nodes ready for validation
   **/
  
  protected function buildValues($oValues, XML_Element $oParent = null) {
    
    if (!$oParent) $oParent = new XML_Document($this->getEmpty());
    $sDirectory = nonull_val($this->readOption('upload-path', false), 'uploads');
    
    if ($oFormID = $oValues->getByName('sylma_form_id')) $oFormID->remove();
    
    foreach ($oValues->getChildren() as $oValue) {
      
      if ($oValue->isElement()) {
        
        $sPrefix = substr($oValue->getName(), 0, 10);
        $sName = substr($oValue->getName(), 11);
        
        if ($sPrefix == 'sylma-attr') {
          
          if ($sName == 'id') $oParent->setAttribute('xml:'.$sName, $oValue->read());
          else $oParent->setAttribute($sName, $oValue->read());
        }
        // else if (substr($oValue->getName(), 0, 11) == 'sylma-empty') {
          
          // $sName = substr($oValue->getName(), 12);
        // }
        else if ($sPrefix == 'sylma-file') {
          
          $oElement = new XML_Element($sName, $oValue->readByName('path'), array(
            'name' => $oValue->readByName('name')), $oParent->getNamespace());
          
          $oElement->setAttribute('lc:temp-file', $oValue->readByName('id'), $this->getNamespace('lc'));
          
          $oParent->add($oElement);
        }
        else {
          
          $oChild = $oParent->addNode($oValue->getName());
          
          if ($oValue->isComplex()) $this->buildValues($oValue, $oChild);
          else $oChild->add($oValue->read());
          
          if (!trim($oChild->read())) $oChild->remove();
        }
      }// else dspf($oValue);
    }
    
    return $oParent;
  }
  
  protected function getPost(Redirect $oRedirect, $bMessage = true, XML_Element $oParent = null) {
    
    $oResult = null;
    
    if (!$oPost = $oRedirect->getDocument('post')) {
      
      if ($bMessage) {
        
        dspm(t('Une erreur s\'est produite. Impossible de continuer. Modifications perdues'), 'error');
        dspm(t('Aucune données dans $_POST'), 'action/warning');
      }
    }
    else {
      
      if (!$oValues = $this->buildValues($oPost, $oParent)) {
        
        if ($bMessage) {
          
          dspm(t('Impossible de lire les valeurs envoyés par le formulaire'), 'error');
          dspm(xt('Erreur dans la conversion des valeurs %s dans $_POST', view($oPost)), 'action/error');
        }
      }
      else $oResult = $oValues->getDocument();
    }
    
    return $oResult;
  }
  
  /*** Actions ***/
  
  public function run(Redirect $oRedirect, $sAction, $aOptions = array(), XML_Element $oOptions = null) {
    
    $mResult = null;
    $sList = $this->getAdmin().'/list';
    
    if ($oOptions) $this->getHeaders()->shift($oOptions->getChildren());
    
    $sID = array_val(0, $aOptions, '');
    $sFormID = $this->setFormID();
    
    if ($aForm = $oRedirect->getArgument('post-form')) $_SESSION['forms'][$sFormID] = $aForm; // copy previous form arguments
    
    switch ($sAction) {
      
      case 'view' : $mResult = $this->view($sID); break;
      
      case 'edit' : $mResult = $this->edit($oRedirect, $sID, $sFormID); break;
      
      case 'edit-do' :
        
        if ($this->checkForm()) {
          
          if (!$this->editDo($oRedirect, $sID)) {
            
            $oRedirect->setPath($this->getAdminPath().'/edit/'.$sID);
            $oRedirect->setArgument('post-form', $this->getSessionForm());
          }
          else $oRedirect->setPath($sList);
          
          $this->killSessionForm();
          $mResult = $oRedirect;
        }
        
      break;
      
      case 'add' :
        
        if ($oTemplate = $this->getTemplate('form/index.xsl')) {
          
          $mResult = $this->add(
            $oRedirect,
            $sFormID,
            $oTemplate,
            $this->readOption('add-do-path', false),
            $this->getTemplateExtension());
        }
        
      break;
      
      case 'add-do' :
        
        if ($this->checkForm()) {
          
          if (!$this->addDo($oRedirect)) {
            
            $sPath = $this->readOption('add-path', false);
            $sPath = $sPath ? $sPath : $this->getAdminPath().'/add';
            
            $oRedirect->setPath($sPath);
            $oRedirect->setArgument('post-form', $this->getSessionForm());
            
          } else {
            
            if (!$sPath = $this->readOption('redirect', false)) $sPath = $sList;
            $oRedirect->setPath($sPath);
          }
          
          $mResult = $oRedirect;
          $this->killSessionForm();
        }
        
      break;
      
      case 'simple-list' :
        
        $this->loadView($oRedirect, $aOptions);
        $mResult = $this->getList($this->getAdmin().'/list/simple', 'simple-list');
        
      break;
      
      case 'list' :
        
        $this->loadView($oRedirect, $aOptions);
        $mResult = $this->getList($this->getAdmin().'/list/simple');
        
      break;
      
      case 'delete' :
        
        $oPath = new XML_Path($this->getDirectory().'/delete.eml', array(
          'form-id' => $sFormID,
          'id' => $sID,
          'action' => $this->getAdminPath()."/delete-do/$sID.redirect"), true, false); //.redirect
        
        $mResult = new XML_Action($oPath);
        
      break;
      
      case 'delete-do' :
        
        if ($this->checkForm()) {
          
          if (!$sID) {
            
            dspm(t('ID manquant'), 'action/error');
            
          } else {
            
            $this->delete($sID);
            dspm(xt('Elément %s supprimé', $sID), 'success');
            
            $oRedirect->setPath($sList);
            $mResult = $oRedirect;
          }
          
          $this->killSessionForm();
        }
        
      break;
      
      case 'add-field' : $this->addField(array_val('path', $aOptions));
        
      break;
      
      case 'upload' :
        
        if ($this->checkForm()) $mResult = $this->upload();
        
      break;
      
      case 'upload-view' : $mResult = $this->uploadView($oRedirect);
        
      break;
      
      case 'import' :
        
        $this->import();
        
      break;
      
      case 'archive' :
        
        $this->archive();
        
      break;
      
      default : 
        
        $this->dspm(xt('Commande %s inconnue', new HTML_Strong($sAction)), 'error');
        
      break;
    }
    
    return $mResult;
  }
  
  /**
   * Build the form to add an element
   * @param Redirect $oRedirect The redirect object containing POST request
   * @param string $sFormID The token generated for the form
   * @param XML_Document $oTemplate The main template that will be used for parsing
   * @param string $sAddDo The action path of the form
   * @param XML_Element $oExtension The extensions to add at the end of the main template
   * @return XML_Document The resulting form document
   */
  protected function add(Redirect $oRedirect, $sFormID, XML_Document $oTemplate, $sAddDo = '', $oExtension = null) {
    
    $oResult = null;
    
    if (!$oValues = $this->getPost($oRedirect, false)) $oValues = $this->getEmpty();
    
    if (!$oModel = $oValues->getModel($this->getSchema(), array('messages' => (bool) $oRedirect->getDocument('post')))) {
      
      dspm(xt('Impossible de charger l\'élément'), 'error');
      $this->dspm(xt('Aucun modèle chargé pour %s', view($oValues)), 'action/error');
    }
    else {
      
      $sPath = $sAddDo ? $sAddDo : $this->getAdminPath().'/add-do';
      
      $sForm = $this->readOption('ajax', false) == 'true' ? 'form/ajax' : 'form';
      
      $oTemplate->setParameters(array(
        'action' => $sPath.Sylma::get('form/redirect'),
        'form-id' => $sFormID,
      ));
      
      if ($oExtension) $oTemplate->includeElement($oExtension);
      
      // first, look for corresponding file in targetDirectory
      
      $oResult = $this->runAction($sForm, array(
        'model' => $oModel,
        'module' => $this->getAdmin(),
        'template' => $oTemplate,
      ));
    }
    
    return $oResult;
  }
  
  /**
   * Generate a view from an element with the given ID
   * @param string $sID The ID of the item to retrieve
   * @return XML_Document Resulting document view
   */
  protected function view($sID) {
    
    $aOptions = array('messages' => false, 'load-refs' => false);
    $oResult = null;
    
    if (!$oModel = $this->getEmpty()->getModel($this->getSchema(), $aOptions)) {
      
      $this->dspm(xt('Impossible de charger l\'élément'), 'error');
      $this->dspm(xt('Modèle de base invalide pour %s', view($oValues)), 'action/error');
      
    } else {
      
      // load extended datas
      
      $oTemplate = $this->getTemplate('/view/xquery.xsl');
      
      $oTemplate->setParameters(array(
        'path' => $this->getPath("//id('$sID')"),
        'prefix' => $this->getFullPrefix()));
      
      $oModel->add($oModel->parseXSL($this->getTemplate('build-headers.xsl')));
      
      $oQuery = new XML_XQuery($oModel->parseXSL($oTemplate, false), $this->getNS());
      $oItem = new XML_Document($oQuery);
      
      if ($oItem->isEmpty()) {
        
        $this->dspm(xt('Impossible de charger l\'élément'), 'error');
        $this->dspm(xt('Document vide'), 'action/error');
        
      } else {
        
        if (!$oModel = $oItem->getModel($this->getSchema(), $aOptions)) {
          
          $this->dspm(xt('Impossible de charger l\'élément'), 'error');
          $this->dspm(xt('Aucun modèle chargé pour %s', view($oItem)), 'action/error');
          
        } else {
          
          // run action
          
          $aArguments = array('model' => $oModel);
          
          if ($oFormExtension = $this->getOption('template-view')) {
            
            $aArguments['template-extension'] = $oFormExtension->getFirst();
          }
          
          $oResult = $this->runAction('view', $aArguments);
        }
      }
    }
    
    return $oResult;
  }
  
  /**
   * Generate a form to edit an element
   * @param Redirect $oRedirect The redirect object containing POST values
   * @param string $sID The ID of the element to edit
   * @param string $sFormID The token ID of the form
   * @return XML_Document The resulting form element
   */
  protected function edit(Redirect $oRedirect, $sID, $sFormID) {
    
    $sPath = $this->readOption('use-child', false);
    
    if (!$oValues = $this->getPost($oRedirect, false)) {
      
      if ($sPath) $oValues = $this->get($this->getPath("//id('$sID')/".$this->parsePath($sPath)), true);
      else $oValues = $this->load($sID);
    }
    
    if (!$oValues) {
      
      dspm(xt('L\'élément identifié par %s n\'existe pas', new HTML_Strong($sID)), 'warning');
    }
    else {
      
      $aOptions = array();
      if ($sPath) $aOptions['path'] = $this->readOption('database/name').'/'.$sPath;
      
      if (!$oModel = $oValues->getModel($this->getSchema(), $aOptions)) {
        
        dspm(xt('Impossible de charger l\'élément'), 'error');
        dspm(xt('Aucun modèle chargé pour %s', view($oValues)), 'action/error');
        
      }
      else {
        
        $sForm = $this->readOption('ajax', false) == 'true' ? 'form/ajax' : 'form';
        
        // first, look for corresponding file in targetDirectory
        // if (!$oForm = Controler::getFile($sForm.'.eml', $this->getDirectory())) $this->switchDirectory();
        
        $mResult = $this->runAction($sForm, array(
          'form-id' => $sFormID,
          'model' => $oModel,
          'module' => $this->getAdmin(),
          'action' => $this->getAdminPath()."/edit-do/$sID".SYLMA_FORM_REDIRECT_EXTENSION,
          'template-extension' => $this->getTemplateExtension()));
        
        // $mResult = $mResult->getDocument()->updateNamespaces(SYLMA_NS_XHTML, SYLMA_NS_XHTML);
      }
    }
  }
  
  /**
   * Extract and generate a single field from a schema
   * @param array $sPath The path that will indicate the element to load. ie: /items/item/name
   * @return XML_Document The resulting field element
   */
  protected function addField($sPath) {
    
    if (!$sPath) {
      
      $this->dspm(xt('Impossible d\'ajouter de champs pour le moment, chemin non spécifié'), 'error');
    }
    else {
      
      $aPath = explode('/', $sPath);
      $sName = array_last($aPath);
      
      $aTempPath = $aPath;
      
      // $sCSSName = array_unshift($aTempPath);
      // dspf(array($aPath, $aTempPath));
      // foreach ($aTempPath as $sElement) $sCSSName .= "[$sElement]";
      
      $mResult = $this->runAction('form/add', array(
        'element' => $this->getEmpty()->getRoot(),
        'name' => $sName,
        'css-name' => '',//$sCSSName,
        'path' => $sPath,
        'schema' => $this->getSchema()));
    }
  }
  
  protected function uploadView($oRedirect) {
    
    if ($this->checkForm()) {
      
      $sID = $oRedirect->getDocument('post')->readByName('id');
      $sName = $oRedirect->getDocument('post')->readByName('name');
      
      if (!$sID || !$sName) {
        
        dspm(xt('ID ou chemin non spécifié pour le fichier temporaire'), 'error');
      }
      else {
        
        if ((!$aFiles = array_val('files', $this->getSessionForm())) ||
          (!$aFile = array_val($sID, $aFiles))) {
          
          $this->dspm(xt('Fichier perdu. Veuillez nous en excuser'), 'error');
        }
        else {
          
          if (!$oFile = Controler::getFile($aFile['path'])) {
            
            dspm(xt('Le fichier a été perdu, veuillez nous en excuser'), 'error');
          }
          else {
            
            $oFile = $oFile->parseXML();
            $oFile->setAttribute('lc:temp-file', $sID, $this->getNamespace('lc'));
            
            $mResult = $this->runAction('form/upload', array(
              'title' => $aFile['title'],
              'name' => $sName,
              'file' => $oFile));
            }
        }
      }
    }
  }
  
  protected function sendMail($sID, $oRedirect) {
    
    $sFrom = nonull_val($this->readOption('mailer/from', false), $this->getSettings('mailer/from'));
    $sTo = nonull_val($this->readOption('mailer/to', false), $this->getSettings('mailer/to'));
    $sType = nonull_val($this->readOption('mailer/type', false), $this->getSettings('mailer/type'), 'html');
    
    $sSubject = $this->getSettings('mailer/prefix');
    $sSubject .= nonull_val($this->readOption('mailer/subject', false), t('Nouvelle entrée sur le site'));
    
    $oView = new HTML_Div($this->run($oRedirect, 'view', array($sID)));
    
    $sHref = 'http://'.$_SERVER['HTTP_HOST'].$this->readOption('mailer/view').'/'.$sID;
    
    $oView->shift(
      new HTML_Style(null, $this->getFile('view.css')->read()),
      new HTML_A($sHref, t('Voir sur le site')),
      new HTML_Br);
    
    $sHeaders = "From: $sFrom\r\n";
    
    //$sHeaders .= 'Mime-Version: 1.0'."\r\n";
    if ($sType == 'html') $sHeaders .= 'Content-type: text/html; charset= utf-8\n';
    else $sHeaders .= 'Content-type: text/plain; charset=utf-8'; // text
    
    mail($sTo, $sSubject, (string) $oView, $sHeaders);
  }
  
  private function loadView(Redirect $oRedirect, array $aOptions) {
    
    $sDocument = 'dbx-list-headers'.$this->getOption('database/document');
    
    if ($sHeaders = array_val($sDocument, $_SESSION)) $this->oHeaders = new XML_Document($sHeaders);
    
    $iPage = array_val('page', $aOptions);
    $iPageSize = array_val('size', $aOptions, 15);
    $sOrder = array_val('order', $aOptions); //, $this->getFullPrefix().'date-publish'
    $sOrderDir = array_val('order-dir', $aOptions, 'a');
    
    $this->setHeader('page', ($iPage ? $iPage : 1), (bool) $iPage);
    $this->setHeader('page-size', $iPageSize);
    if ($oOrder = $this->setHeader('order', $sOrder, true)) $oOrder->setAttribute('dir', $sOrderDir);
    // $this->setHeader('where', $sWhere);
    
    $_SESSION[$sDocument] = (string) $this->getHeaders();
  }
  
  private function upload() {
    
    $oResult = null;
    
    $sFile = 'file-uploader';
    
    if (!isset($_FILES[$sFile]) || !$_FILES[$sFile]['name']) {
      
      $this->dspm(xt('Aucun fichier envoyé'), 'warning');
    }
    else {
      
      if ($_FILES[$sFile]['size'] > SYLMA_UPLOAD_MAX_SIZE) {
        
        $this->dspm(t('Le fichier lié est trop grand'), 'warning');
      }
      else {
        
        if (!$oDirectory = Controler::getUser()->getDirectory('#tmp')) {
          
          $this->dspm(t('Impossible de créer ou lire le répertoire de destination des fichiers'), 'error');
        }
        else {
          
          $sExtension = '';
          $sName = $_FILES[$sFile]['name'];
          
          if ($iExtension = strrpos($sName, '.')) $sExtension = strtolower(substr($sName, $iExtension));
          
          if ($sExtension == '.php') {
            
            $this->dspm(xt('L\'extension "%s" de ce fichier est interdite !', new HTML_Strong($sExtension)), 'warning');
          }
          else { // valid file
            
            $sID = uniqid('file-');
            
            $sPath = $oDirectory.'/'.$sID.$sExtension;
            $sRealPath = $oDirectory->getRealPath().'/'.$sID.$sExtension;
            
            if(!move_uploaded_file($_FILES[$sFile]['tmp_name'], $sRealPath)) {
              
              $this->dspm(t('Problème lors du chargement du fichier'), 'warning');
            }
            else { // success
              
              $aForm =& $this->getSessionForm();
              
              if (!array_val('files', $aForm)) $aForm['files'] = array();
              
              $aForm['files'][$sID] = array(
                'path' => $sPath,
                'title' => $sName);
              
              $oResult = $sID;
              $this->dspm(xt('Fichier %s ajouté dans %s', new HTML_Strong($sName), (string) $oDirectory));
            }
          }
        }
      }
    }
    
    return $oResult;
    
  }
  
  public function getList($sPath, $sAction = 'list') {
    
    $mResult = null;
    $aOptions = array('messages' => false, 'load-refs' => false);
    
    $oModel = $this->getEmpty()->getModel($this->getSchema(), $aOptions);
    
    if (!$oModel || $oModel->isEmpty()) dspm(xt('Fichier modèle %s invalide', view($oModel)), 'action/error');
    else {
      
      $oModel->add($this->getHeaders());
      
      // dspf($oModel);
      
      $oTemplate = $this->getTemplate('list/xquery.xsl');
      
      $sChildren = $this->readOption('database/list-path', false);
      $sName = $this->getFullPrefix().$this->readOption('database/name');
      $sChildren = $sChildren ? $sChildren : $this->readOption('database/parent').'/'.$sName;
      
      $oTemplate->setParameters(array(
        'parent-name' => $this->readOption('database/parent'),
        'parent-path' => $this->getPath('/'.$sChildren),
        'build-empty' => 'true',
        'prefix' => $this->getFullPrefix()));
      
      $sQuery = $oModel->parseXSL($oTemplate, false);
      
      $oTemplateExt = $this->getOption('template-list', false);
      if ($oTemplateExt) $oTemplateExt = $oTemplateExt->getFirst();
      
      $mResult = $this->runAction($sAction, array(
        'o-model' => $oModel,
        'datas' => $this->get($sQuery, true),
        'path-add' => $this->getAdmin().'/add',
        'path-list' => $sPath,
        'module' => (string) $this->getAdmin(),
        'template-extension' => $oTemplateExt));
    }
    
    return $mResult;
  }
  
  public function addDo(Redirect $oRedirect) {
    
    $bResult = false;
    
    if ($oValues = $this->getPost($oRedirect)) {
      
      if (!$oValues->validate($this->getSchema())) {
        
        dspm(t('Un ou plusieurs champs ne sont pas corrects, ceux-ci sont indiqués en rouge'), 'warning');
        
      } else {
        
        $this->validateElement($oValues->getRoot(), true);
        
        $oValues = $oValues->updateNamespaces($this->getNamespace(), $this->getNamespace(), $this->getPrefix());
        
        $sParent = nonull_val($this->readOption('database/insert-path', false), $this->readOption('database/parent'));
        
        // if ($oFile = $oValues->saveTemp()) $this->getDB()->run("add to {$this->getParent()} {$oFile->getSystemPath()}");
        $sPath = $this->getPath('/'.$sParent);
        
        if (!$this->query($sPath, array(), false)) {
          
          dspm(t('Une erreur est survenue, impossible de sauvegarder, réessayer plus tard'), 'error');
          $this->dspm(xt('Elément parent %s introuvable', new HTML_Strong($sPath)), 'action/error');
        }
        else {
          
          $this->insert($oValues, $sPath);
          
          dspm(t('Elément ajouté'), 'success');
          
          $sID = $oValues->getAttribute('xml:id');
          if ($this->getOption('mailer', false)) $this->sendMail($sID, $oRedirect);
          
          $bResult = true;
        }
      }
    }
    
    return $bResult;
  }
  
  public function editDo(Redirect $oRedirect, $sID) {
    
    $bResult = false;
    $oParent = null;
    $aOptions = array();
    
    if ($sPath = $this->readOption('use-child', false)) {
      
      $aPath = explode('/', $sPath);
      $oParent = new XML_Element(array_last($aPath), null, null, $this->getNamespace());
      $aOptions['path'] = $this->readOption('database/name').'/'.$sPath;
    }
    
    if ($oValues = $this->getPost($oRedirect, true, $oParent)) {
      
      if (!$oValues->validate($this->getSchema(), $aOptions)) {
        
        dspm(t('Un ou plusieurs champs ne sont pas corrects, ceux-ci sont indiqués en rouge'), 'warning');
        
      } else {
        
        $this->validateElement($oValues->getRoot(), false);
        
        $oValues = $oValues->updateNamespaces($this->getNamespace(), $this->getNamespace(), $this->getPrefix());
        
        if (!$this->load($sID)) {
          
          dspm(xt('L\'élément %s n\'existe pas. Modifications perdues', new HTML_Strong($sID)), 'warning');
          
        } else {
          
          if ($sPath) $this->replace($this->getPath("//id('$sID')/".$this->parsePath($sPath)), $oValues);
          else $this->replaceID($sID, $oValues);
          
          $bResult = true;
          dspm(t('Elément mis-à-jour'), 'success');
        }
      }
    }
    
    return $bResult;
  }
  
  public function import() {
    
    if (!$sFile = $this->getOptions()->read('dbx:file', $this->getNS())) {
      
      dspm(xt('Aucun fichier d\'importation défini dans les options'), 'warning');
      
    } else {
      
      if ($oFile = Controler::getFile($sFile)) {
        
        $oDoc = $oFile->getDocument();
        
        if (!$oDoc || $oDoc->isEmpty() || !$oDoc->getRoot()->hasChildren()) {
          
          dspm(xt('Document %s invalide', $oFile->parse()), 'warning');
          
        } else {
          
          //foreach ($oDoc->getChildren() as $oItem) $this->insert($oItem, $this->getParent());
          dspm('Refaire la fonction avec les doc de type database/datas');
          //if ($this->getDB()->run("add to {$this->getParent()} {$oFile->getSystemPath()}"))
          
          dspm(xt('Document %s importé dans %s', view($oFile->getDocument()), new HTML_Strong($this->getParent())), 'success');
        }
      }
    }
  }
  
  public function archive() {
    
    $sDuration = 'P15D';
    $sPrefix = $this->getPrefix();
    
    $sDocumentName = $this->readOption('database/document');
    $sArchive = parent::getPath('/*', $sDocumentName.'-archives');
    $sDocument = parent::getPath('', $sDocumentName);
    
    $sName = $this->readOption('database/name');
    $sAll = $sDocument."//$sPrefix:$sName";
    
    $sItem = $sDocument.'//id($item/@xml:id)';
    
    $sEnd = "xs:date(\$item/$sPrefix:dbx-insert)";
    $sCurrent = 'fn:current-date()';
    $sWhere = "$sEnd < $sCurrent - xs:dayTimeDuration('$sDuration')";
    
    if ($sCondition = $this->readOption('archive/condition')) $sWhere = "($sWhere) and ($sCondition)";
    
    $sQuery = "for \$item in $sAll
      return if ($sWhere)
        then
          (update insert \$item into $sArchive,
          update delete $sItem)
        else ()";
    
    $this->query($sQuery);
  }
  
}



