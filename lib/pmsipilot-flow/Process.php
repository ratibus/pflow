<?php

class Process
{
  /**
   * @var string
   */
  private $command;

  /**
   * @var resource
   */
  private $pid;

  /**
   * @var array[]ressource
   */
  private $pipes = array();

  /**
   * @var int
   */
  private $exitStatus = null;

  /*
   * @var array[]array
   */
  private $descriptors = array(
    0 => array('pipe', 'r'), // stdin
    1 => array('pipe', 'w'), // stdout
    2 => array('pipe', 'w'), // stderr
  );

   /**
   * @var array
   */
  public $options = array();

  /**
   * @var array
   */
  private $output = null;

  /**
   * @var array
   */
  private $error  = null;

  /**
   * @param $command
   * @param array $options
   */
  public function __construct($command, $options = array())
  {
    $this->command = $command;
    $this->options = $options;
  }

  /**
   * @return Process
   */
  public function execute()
  {
    $this->pid = proc_open($this->command, $this->descriptors, $this->pipes);

    return $this;
  }

  /**
   * @return Process
   */
  public function close()
  {
    $this->exitStatus = proc_close($this->pid);

    return $this;
  }

  /**
   * @return array
   */
  public function getOutput()
  {
    if (is_null($this->output))
    {
      $this->output = $this->streamToArray($this->getOutputStream());
    }

    return $this->output;
  }

  /**
   * @return array
   */
  public function getError()
  {
    if (is_null($this->error))
    {
      $this->error = $this->streamToArray($this->getErrorStream());
    }

    return $this->error;
  }

  /**
   * @return stream
   */
  public function getInputStream()
  {
    return $this->pipes[0];
  }

  /**
   * @return stream
   */
  public function getOutputStream()
  {
    return $this->pipes[1];
  }

  /**
   * @return stream
   */
  public function getErrorStream()
  {
    return $this->pipes[2];
  }

  /**
   * @return int
   */
  public function getExitCode()
  {
    if (!$this->isFinished())
    {
      $this->close();
    }

    return $this->exitStatus;
  }
  
  /**
   * @return bool
   */
  public function isFinished()
  {
    return $this->exitStatus !== null;
  }

  /**
   * @return bool
   */
  public function isSuccess()
  {
    return $this->getExitCode() == 0;
  }

  /**
   * @param ressource $stream
   * @return array
   */
  private function streamToArray($stream)
  {
    $result = array();

    while (!feof($stream))
    {
      $result[] = stream_get_line($stream, 1024, PHP_EOL);
    }

    if (count($result) == 1 && $result[0] == '')
    {
      $result = array();
    }

    return $result;
  }
  
  /**
   * 
   */
  public function __destruct()
  {
    if (!$this->isFinished())
    {
      $this->close();
    }
  }
}