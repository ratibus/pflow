<?php

abstract class Input implements InputInterface
{
  /**
   * @param OutputInterface $output
   */
  public function __construct(OutputInterface $output)
  {
    $this->output = $output;
  }

  /**
   * @param string $message
   * @return void
   */
  public function output($message)
  {
    $this->output->write($message);
    return true;
  }
  
  /**
   * @param $prompt
   * @param null $default
   * @param null $allowedValues
   * @return string
   */
  public function formatPrompt($prompt, $default = null, $allowedValues = null)
  {
    if (!is_null($allowedValues))
    {
      $prompt .= sprintf(' (%s)', implode('|', $allowedValues));
    }
    
    if (!is_null($default))
    {
      $prompt .= sprintf(' [%s]', $default);
    }

    $prompt .= ' : ';

    return $prompt;
  }

  /**
   * @param string $prompt
   * @param null|string $default
   * @param null|array $allowedValues
   * @return string
   */
  public function readValue($prompt, $default = null, $allowedValues = null)
  {
    do
    {
      $line  = trim($this->readLine($this->formatPrompt($prompt, $default, $allowedValues)));
      $value = $line ? $line : $default;
    } while (is_array($allowedValues) && !in_array($value, $allowedValues) && $this->output('Unauthorized value'));

    return $value;
  }
}