<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\parser\languages\common, sylma\storage\sql;

class Pager extends reflector\component\Foreigner implements reflector\component, template\parser\tree {

  protected $var;
  protected $collection;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
    $this->allowText(true);

    $collection = $this->getParser()->getTree();
    $collection->setPager($this);
    $this->setCollection($collection);

    $this->build();
  }

  public function setCollection(sql\template\component\Collection $collection) {

    $this->collection = $collection;
  }

  protected function setParser(reflector\domed $parent) {

    return parent::setParser($parent);
  }

  protected function getCollection() {

    return $this->collection;
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  protected function getVar() {

    return $this->var;
  }

  protected function build() {

    $collection = $this->getCollection();

    $var = $this->createObject();
    $this->setVar($var);

    $offset = $this->parseComponentRoot($this->getx('self:current'));
    $count = $this->parseComponentRoot($this->getx('self:count'));

    $collection->setLimit($var->call('getOffset'), $count);

    $var->call('setCount', array($this->getCollection()->getCounter()))->insert();
    $var->call('setPage', array($offset))->insert();
    $var->call('setSize', array($count))->insert();
  }

  public function reflectApply($sMode) {

    if (!$result = $this->getParser()->lookupTemplate($this->getNode(), $sMode)) {

      $this->launchException('Cannot render pager without template');
    }

    $result->setTree($this);

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode) {

    $var = $this->getVar();

    switch ($sName) {

      case 'is-multiple' : $result = $var->call('isMultiple'); break;
      case 'is-first' : $result = $var->call('isFirst');; break;
      case 'is-last' : $result = $var->call('isLast'); break;

      default :

        $this->launchException("Unknow function : '$sName'");
    }

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode) {

    $var = $this->getVar();

    switch ($sPath) {

      case 'current' : $result = $var->call('getPage'); break;
      case 'next' : $result = $var->call('getNext'); break;
      case 'last' : $result = $var->call('getLast'); break;
      case 'prev' :
      case 'previous' : $result = $var->call('getPrevious'); break;

      default :

        $this->launchException("Unknown path : '$sPath'");
    }

    return $result;
  }

  public function asToken() {

    return '[obj]' . get_class($this);
  }
}

