<?php

interface Pflow_HistoryInterface
{
  /**
   * @abstract
   * @param string $line
   * @return integer
   */
  function add($line);

  /**
   * @abstract
   * @return string
   */
  function getLast();

  /**
   * @abstract
   * @return void
   */
  function purge();
}