<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template\parser;

abstract class Templated extends reflector\handler\Elemented {

  protected $aTemplates = array();
  protected $aCheckTemplates = array();

  public function lookupTemplate(dom\element $el, $sMode, $bRoot = false) {

    $iLast = 0;
    $result = null;

    if ($bRoot) {

      $this->launchException('Not yet implemented');
    }

    foreach ($this->getTemplates() as $template) {

      if ($this->checkTemplate($template, null, false)) continue;

      $iWeight = $template->getWeight($el->getNamespace(), $el->getName(), $sMode);
      if ($iWeight && $iWeight >= $iLast) {

        $result = $template;
        $iLast = $iWeight;
      }
    }

    if ($result && $result->getMatch()) {

      $result = clone $result;
    }

    return $result;
  }

  public function getCurrentTemplate() {

    if (!$this->aTemplates) {

      $this->launchException('No template defined');
    }

    return end($this->aTemplates);
  }

  public function startTemplate(parser\template $tpl) {

    $this->aTemplates[] = $tpl;
    $this->aCheckTemplates[$this->getTreeID($tpl, $tpl->getTree(false))] = true;
//echo 'start ' . $tpl->getID().'<br/>';
  }

  public function stopTemplate() {

    $tpl = array_pop($this->aTemplates);
    unset($this->aCheckTemplates[$this->getTreeID($tpl, $tpl->getTree(false))]);
//echo 'stop ' . $tpl->getID().'<br/>';
  }

  protected function getTreeID(parser\template $tpl, parser\tree $tree = null) {

    return $tpl->getID() . ($tree ? $tree->asToken() : '');
  }

  public function checkTemplate(parser\template $tpl, parser\tree $tree = null, $bDebug = true) {
//echo 'check ' . $tpl->getID() . '<br/>';
    $bResult = isset($this->aCheckTemplates[$this->getTreeID($tpl, $tree)]);

    if ($bResult) {

      if ($bDebug) $this->launchException('Recursive template call');
    }

    return $bResult;
  }

  protected function loadTemplate(dom\element $el) {

    $template = $this->createComponent('component/template', $this);
    $template->parseRoot($el);

    $this->addTemplate($template);
  }
/*
  protected function resetTemplates() {

    $this->aTemplates = array();
  }
*/
  protected function getTemplates() {

    return $this->aTemplates;
  }

  protected function addTemplate(parser\component\Template $template) {

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
