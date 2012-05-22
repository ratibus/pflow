<?php

/**
 * @throws RuntimeException
 * 
 */
class Input_Readline extends Input
{
  /**
   * @param OutputInterface $output
   * @throws RuntimeException
   */
  public function __construct(OutputInterface $output)
  {
    if (!function_exists('readline'))
    {
      throw new RuntimeException('You must activate the readline extension');
    }

    parent::__construct($output);
  }

  /**
   * @param string $prompt
   * @return string
   */
  public function readLine($prompt)
  {
    return readline($prompt);
  }
}