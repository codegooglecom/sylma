<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Foreign extends sql\template\component\Foreign {

  const JUNCTION_MODE = 'insert';

  protected function reflectFunctionAll(array $aPath, $sMode, array $aArguments = array()) {

    return null;
  }

  /**
   * @uses Table::getDummy()
   * @uses Table::setDummy()
   */
  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array(), $bRead = false) {

    $table = $this->getElementRef();
    //$val = $table->getElementArgument($table->getElement('id')->getName());

    $result = $this->getParent()->loadSingleReference($this->getName(), $table, $aPath, $sMode, $aArguments);

    return $result;
  }

  protected function buildSingle($sMode, $content = null) {

    $this->getParent()->addElement($this, $content, array(
      'default' => $this->getDefault(),
      'optional' => $this->isOptional(),
      'mode' => $sMode,
      'multiple' => $this->getMaxOccurs(true),
    ));
  }

  protected function reflectKey() {

    return $this->getParent()->getQuery()->getVar();
  }

  /**
   * @uses Table::getDummy()
   * @return array
   */
  public function buildMultiple(sql\schema\table $junction, sql\schema\foreign $source, sql\schema\foreign $target) {

    if ($this->getParent()->isSub()) {

      $dummy = $this->getParent()->getDummy();
      $collection = $dummy->call('getElement', array($this->getName()))->call('getValue');
    }
    else {

      $collection = $this->getParent()->getElementArgument($this->getName(), 'get');
    }

    $window = $this->getWindow();
    $val = $window->createVariable('', 'php-null');
    $key = $window->createVariable('', 'php-integer');
    $loop = $window->createLoop($collection, $val, $key);

    $junction->init($key, $this->getParent()->getDummy(false));
    $junction->addElement($source, $this->reflectKey());
    $junction->addElement($target, $val);

    $loop->addContent($junction);

    $result = $loop;
    return $result;
  }

  public function reflectRegister($content = null, $sReflector = '', $sMode = '') {

    if ($this->getMaxOccurs(true)) {

      list($junction, $source, $target) = $this->loadJunction();

      if ($this->getParent()->isSub()) {

        $self = $this;
        $table = $this->getParent();
        $table->addElement($this, $table->getElementArgument($this->getName(), 'query'), array(
          'multiple' => true,
        ));

        $caller = $this->getWindow()->createCaller(function() use ($junction, $source, $target, $self) {

          return $self->buildMultiple($junction, $source, $target);
        });

        $this->getParent()->addTrigger(array($caller));
      }
      else {

        list($junction, $source, $target) = $this->loadJunction();
        $this->getParent()->addTrigger(array($this->buildMultiple($junction, $source, $target)));
      }
    }
    else {

      $this->buildSingle($sMode, $content);
    }
  }
}

