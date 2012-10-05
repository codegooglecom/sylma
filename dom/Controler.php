<?php

namespace sylma\dom;
use sylma\core, sylma\core\functions\path;

require_once('core/module/Filed.php');
require_once('core/functions/Path.php');

class Controler extends core\module\Domed {

  const NS = 'http://www.sylma.org/dom';
  const SETTINGS = 'settings.yml';

  protected $aDefaultClasses = array();

  protected $aClasses = array(
    'document' => 'DOMDocument',
    'element' => 'DOMElement',
    'fragment' => 'DOMDocumentFragment',
    'text' => 'DOMText',
    'attribute' => 'DOMAttr',
    'comment' => 'DOMComment',
    'instruction' => 'DOMProcessingInstruction',
    //'collection' => 'DOMNodeList', // See https://bugs.php.net/bug.php?id=48352
  );

  protected $directory;

  protected $aStats = array();

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setArguments(self::SETTINGS);
    $this->setNamespace(self::NS);

    foreach ($this->getArgument('namespaces')->query() as $sPrefix => $sNamespace) {

      $this->setNamespace($sNamespace, $sPrefix, false);
    }
  }

  public function createCollection(\DOMNodeList $list) {

    require_once('basic/Collection.php');
    return new basic\Collection($list);
  }

  public function createDocument($mContent = '') {

    return parent::createDocument($mContent);
  }

  public function getClasses(core\argument $settings = null) {

    $aClasses = array();

    if (!$this->aDefaultClasses || $settings) {

      $factory = \Sylma::getControler('factory');

      $this->getArguments()->registerToken(core\factory::CLASSBASE_TOKEN);
      $this->getArguments()->registerToken(core\factory::DIRECTORY_TOKEN);

      $classes = $this->getArguments()->get('classes');
      if ($settings) $classes->merge($settings);

      foreach ($this->aClasses as $sKey => $sClass) {

        if ($class = $classes->get($sKey)) {

          if ($sClassBase = $classes->getToken(core\factory::CLASSBASE_TOKEN)) {

            $class->set('name', path\toAbsolute($class->read('name'), $sClassBase, '\\'));
          }

          if (!class_exists($class->read('name'), false)) {

            if ($sFile = $class->read('file', false)) $class->set('file', path\toAbsolute($sFile, $class->getLastDirectory()));
            $factory->includeClass($class->read('name'), $class->read('file', false));
          }

          $aClasses[$sClass] = $class->read('name');
        }
      }

      $this->getArguments()->unRegisterToken(core\factory::CLASSBASE_TOKEN);
      $this->getArguments()->unRegisterToken(core\factory::DIRECTORY_TOKEN);

      if (!$settings) $this->aDefaultClasses = $aClasses;
    }
    else {

      $aClasses = $this->aDefaultClasses;
    }

    return $aClasses;
  }

  public function readArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::readArgument($sPath, $mDefault, $bDebug);
  }

  public function addStat($sName, array $aArguments) {

    if ($this->readArgument('stats/enable')) $this->aStats[$sName][] = $aArguments;
  }

  public function stringToBool($sValue, $bDefaut = false) {

    $sValue = strtolower($sValue);

    if (strtolower($sValue) == 'true') return true;
    else if (strtolower($sValue) == 'false') return false;
    else return $bDefaut;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }
}
