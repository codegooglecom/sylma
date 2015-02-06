<?php

namespace sylma\template\parser;
use sylma\core, sylma\template, sylma\storage\xml;

class Document  extends xml\tree\Argument implements template\parser\tree
{
  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    $result = null;

    switch ($sName) {

      case 'url' : $result = $this->reflectFunctionURL(); break;
      case 'root' : $result = $this->reflectFunctionRoot($aPath, $sMode, $bRead, $aArguments); break;
      case 'sylma' : $result = $this->reflectFunctionSylma($aPath); break;
      case 'argument' : $result = $this->reflectFunctionArgument($aPath); break;

      default :

        $this->launchException('Unknown function name');
    }

    return $result;
  }

  /**
   * Read YML config file. @warning allow credentials access
   */
  protected function reflectFunctionSylma(array $aPath) {

    return $this->getWindow()->getSylma()->call('read', array(implode('/', $aPath), true));
  }

  protected function reflectFunctionRoot(array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    $tree = $this->getParser()->getResource()->getTree();
    $pather = $this->getParser()->getCurrentTemplate()->getPather();
    $pather->setSource($tree);

    return $pather->parsePathToken($aPath, $sMode, $bRead, $aArguments);
  }

  protected function reflectFunctionURL() {

    $window = $this->getWindow();

    $init = $window->addControler('init');

    return $init->call('getURL');
  }

  protected function reflectFunctionArgument(array $aPath) {

    $arg = $this->getWindow()->getVariable('arguments');

    return $arg->call('read', $aPath);
  }
}
