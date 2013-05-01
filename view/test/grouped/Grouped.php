<?php

namespace sylma\view\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  const DB_MANAGER = 'mysql';

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    \Sylma::setControler(self::DB_MANAGER, new sql\Manager($this->createArgument('../database.xml')));

    $this->runQuery("DELETE FROM `user`");
    $this->runQuery("INSERT INTO `user` (`id`, `name`, `email`, `group_id`) VALUES
      (1, 'root', 'root@sylma.org', 2),
      (2, 'admin', 'admin@sylma.org', 1),
      (3, 'webmaster', 'webmaster@sylma.org', 0);");

    parent::__construct();
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function runQuery($sValue, $bMultiple = true) {

    $db = $this->getManager(self::DB_MANAGER);
    return $bMultiple ? $db->query($sValue) : $db->get($sValue);
  }
}

