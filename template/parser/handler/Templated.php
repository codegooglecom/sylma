<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template\parser;

abstract class Templated extends reflector\handler\Elemented {

  protected $aCurrentTemplates = array();
  protected $aCheckTemplates = array();

  protected $aTemplates = array();
  protected $aXModes = array();

  public function lookupTemplate($sName, $sNamespace, $sMode, $bRoot = false) {

    $iLast = 0;
    $result = null;

    foreach ($this->getTemplates() as $template) {

      $sToken = $sNamespace . ':' . $sName;
      if ($this->checkTemplate($template, $sToken, false)) continue;

      $iWeight = $template->getWeight($sNamespace, $sName, $sMode, $this->getXMode(), $bRoot);
      if ($iWeight && $iWeight >= $iLast) {

        $result = $template;
        $iLast = $iWeight;
      }
    }

    if ($result) {

      $result->build();
      $result = clone $result;
    }

    return $result;
  }

  /**
   * @return parser\template
   */
  public function getCurrentTemplate($bDebug = true) {

    if (!$this->aCurrentTemplates) {

      if ($bDebug) $this->launchException('No template defined');
      $result = null;
    }
    else {

      $result = end($this->aCurrentTemplates);
    }

    return $result;
  }

  public function startTemplate(parser\template $tpl) {

    $this->aCurrentTemplates[] = $tpl;
    $this->aCheckTemplates[$this->getTreeID($tpl, $this->getTreeToken($tpl))] = true;
//echo 'start ' . $tpl->getID().'<br/>';
  }

  public function stopTemplate() {

    $tpl = array_pop($this->aCurrentTemplates);
    unset($this->aCheckTemplates[$this->getTreeID($tpl, $this->getTreeToken($tpl))]);
//echo 'stop ' . $tpl->getID().'<br/>';
  }

  protected function getTreeToken(parser\template $tpl) {

    if ($tree = $tpl->getTree(false)) {

      $sResult = $tree->asToken();
    }
    else {

      $sResult = '';
    }

    return  $sResult;
  }

  protected function getTreeID(parser\template $tpl, $sToken) {

    return $tpl->getID() . $sToken;
  }

  /**
   * @usedby \sylma\storage\sql\template\handler\Basic::lookupTemplate()
   */
  public function checkTemplate(parser\template $tpl, $sToken, $bDebug = true) {

    $bResult = isset($this->aCheckTemplates[$this->getTreeID($tpl, $sToken)]);

    if ($bResult) {

      if ($bDebug) $this->launchException('Recursive template call');
    }

    return $bResult;
  }

  protected function loadTemplate(dom\element $el) {

    $template = $this->createComponent('component/template', $this);
    $aResult = $template->parseRoot($el);

    $this->addTemplate($template);

    //return $aResult;
  }
/*
  protected function resetTemplates() {

    $this->aTemplates = array();
  }
*/

  /**
   * @usedby \sylma\view\parser\component\Apply::build()
   */
  public function startXMode($sMode) {

    $this->aXModes[] = $sMode;
  }

  /**
   * @usedby \sylma\view\parser\component\Apply::build()
   */
  public function stopXMode() {

    array_pop($this->aXModes);
  }

  /**
   * @usedby \sylma\storage\sql\template\handler\Basic::lookupTemplate()
   */
  public function getXMode() {

    return end($this->aXModes);
  }

  protected function getTemplates() {

    return $this->aTemplates;
  }

  protected function addTemplate(parser\template $template) {

    $this->aTemplates[] = $template;
  }

  protected function getTemplate($sPath = '') {

    if ($sPath) {

      $this->throwException('Feature not available');
    }

    //if (!$sMatch) $sMatch = parser_ns\component\Template::MATCH_DEFAULT;

    $result = $this->getDefaultTemplate();

    if (!$result) {

      $this->launchException('No root template found', get_defined_vars());
    }

    return $result;
  }

  protected function getDefaultTemplate() {

    $result = null;

    foreach ($this->aTemplates as $template) {

      if (!$template->getMatch() && !$template->getMode()) {

        $result = $template;
        break;
      }
    }

    return $result;
  }
}
