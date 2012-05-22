<?php

interface Pflow_CommandInterface
{
  /**
   * @param Git $git
   * @param OutputInterface $output
   * @param InputInterface $input
   * @param Pflow_HistoryInterface $history
   */
  function __construct(Git $git, OutputInterface $output, InputInterface $input, Pflow_HistoryInterface $history);

  /**
   * @abstract
   * @param array $argv
   * @return void
   */
  function execute($argv);

  /**
   * @var string $message
   * @var string $scope
   */
  function output($message, $scope = Output::SCOPE_PRIVATE);

  /**
   * @var string $prompt
   * @var string $default
   */
  function input($prompt, $default = null);

  /**
   * @abstract
   * @param string $name
   * @return string
   */
  function getConfig($name);
  
  /**
   * @abstract
   * @return array
   */
  static function getHelp();
}