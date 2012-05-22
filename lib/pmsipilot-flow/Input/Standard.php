<?php

/**
 *
 */
class Input_Standard extends Input
{
  /**
   * @param string $prompt
   * @return string
   */
  public function readLine($prompt)
  {
    $this->output($prompt);
    return fgets(STDIN);
  }
}