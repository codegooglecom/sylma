<?php

namespace sylma\storage\sql\pager;
use sylma\core;

class Dummy extends core\module\Exceptionable {

  protected $iCount;
  protected $iPage;
  protected $iSize;

  public function setCount($iCount) {

    $this->iCount = intval($iCount);
  }

  protected function getCount() {

    return $this->iCount;
  }

  public function setPage($iPage) {

    $iValue = intval($iPage);

    $this->iPage = $iValue < 1 ? 1 : $iValue;
  }

  public function getPage() {

    return $this->iPage;
  }

  public function setSize($iSize) {

    $this->iSize = intval($iSize);
  }

  protected function getSize() {

    return $this->iSize;
  }

  public function getOffset() {

    $iResult = ($this->getPage() - 1) * $this->getSize();

    return $iResult < 0 ? 0 : $iResult;
  }

  public function isMultiple() {

    $this->getCount() > $this->getSize();
  }

  public function isFirst() {

    return $this->getPage() == 1;
  }

  public function isLast() {

    return $this->getLast() == $this->getPage();
  }

  public function getNext() {

    return $this->getPage() + 1;
  }

  public function getLast() {

    return $this->getSize() ? ceil($this->getCount() / $this->getSize()) : 0;
  }

  public function getPrevious() {

    return $this->getPage() - 1;
  }
}

