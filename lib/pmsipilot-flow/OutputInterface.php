<?php

/**
 *
 */
interface OutputInterface
{
  /**
   * @abstract
   * @param $message
   * @param string $scope
   */
  function write($message, $scope = Output::SCOPE_PRIVATE);
}
