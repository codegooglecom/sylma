<?php

namespace sylma\modules\cron;
use sylma\core;

class Task extends core\module\Domed {

  const ALL = '*';
  const ROUND_MIN = 10;

  public function __construct(core\argument $args, core\argument $settings) {

    $this->setArguments($args);
    $this->setSettings($settings);

    $this->build();
  }

  protected function roundMin($iMin) {

    return round($iMin / self::ROUND_MIN) * self::ROUND_MIN;
  }

  protected function build() {

    $now = new \DateTime;

    $minute = $this->roundMin($now->format('m'));
    $cronMinute = $this->roundMin($this->read('minute'));

    if (
            $this->compare($minute, $cronMinute) &&
            $this->compare($now->format('h'), $this->read('hour')) &&
            $this->compare($now->format('j'), $this->read('day')) &&
            $this->compare($now->format('N'), $this->read('weekday')) &&
            $this->compare($now->format('m'), $this->read('month'))

       ) {

      $this->getScript($this->read('command'));
    }
  }

  protected function compare($sCurrent, $sVal) {

    if (strpos($sVal, '/') !== false) {


    }
    else {


    }

    return $sVal == self::ALL || $sVal == $sCurrent;
  }

}

