<?php

namespace sylma\core\user;
use sylma\core, sylma\schema;

class Form extends schema\cached\form\Form {

  public function validate() {

    $bResult = false;
    $sConfirmAlias = 'password_confirm';

    $sConfirm = $this->read($sConfirmAlias);
    $password = $this->getElement('password');

    if (!$sConfirm) {

      if ($this->getMode() == 'insert') {

        $this->addMessage('You must confirm the password', $password->asAlias());
      }
      else {

        if (!$iID = inval($this->readArgument('id'))) {

          $this->launchException('ID not valid');
        }

        $sPassword = $this->getManager('mysql')->read("SELECT password FROM user WHERE id = $iID");
        $password->setValue($sPassword);

        $bResult = parent::validate();
      }
    }
    else {

      if (!$password->validate() || $sConfirm !== $password->getValue()) {

        $this->addMessage('Passwords do not match', $password->asAlias());
      }
      else {

        
        $password->setValue();

        $bResult = parent::validate();
      }
    }

    return $bResult;
  }
}

