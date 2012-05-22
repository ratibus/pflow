<?php

/**
 * 
 */
interface InputInterface
{
  /**
   * @abstract
   * @param OutputInterface $output
   */
  function __construct(OutputInterface $output);

  /**
   * @abstract
   * @param string $message
   * @return void
   */
  function output($message);

  /**
   * @abstract
   * @param string $prompt
   * @param string $default
   * @return string
   */
  function readValue($prompt, $default = null);

  /**
   * @abstract
   * @param string $prompt
   * @return string
   */
  function readLine($prompt);
}