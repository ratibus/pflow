<?php

class Pflow_History_Git implements Pflow_HistoryInterface
{
  /**
   * @var Git
   */
  public $git;

  /**
   * @param Git $git
   */
  public function __construct(Git $git)
  {
    $this->git = $git;
  }

  /**
   * @param string $string
   * @return void
   */
  public function add($string)
  {
    $this->git->setConfig('pflow.history.last', $string);
  }

  /**
   * @return string
   */
  public function getLast()
  {
    return $this->git->getConfig('pflow.history.last');
  }

  /**
   * @return bool
   */
  public function purge()
  {
    return $this->git->removeConfigSection('pflow.history');
  }
}