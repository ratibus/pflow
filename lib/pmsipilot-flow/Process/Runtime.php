<?php
 
class Process_Runtime
{
  /**
   * @var string
   */
  public $format;

  /**
   * @var array
   */
  public $options = array(
    'format' => '%s',
  );

  /**
   * @var array
   */
  public $processOptions = array();

  /**
   * @var OutputInterface
   */
  private $output;
  
  /**
   * @param OutputInterface $output
   * @param array $options
   * @param array $processOptions
   */
  public function __construct(OutputInterface $output, array $options = array(), array $processOptions = array())
  {
    $this->output         = $output;
    $this->options        = array_merge($this->options, $options);
    $this->processOptions = array_merge($this->options, $options);
  }

  /**
   * @param string $string
   * @param string $scope
   * @return void
   */
  public function output($string, $scope = Output::SCOPE_PRIVATE)
  {
    $this->output->write($string, $scope);
  }
  
  /**
   * @param $cmd
   * @param array $processOptions
   * @return Process
   */
  public function execute($cmd, $processOptions = array())
  {
    // @todo
    // this thing is only there to avoid outputing config commands
    // will have to go once #12539 is fixed
    if (!(isset($this->options['skip_output']) && preg_match($this->options['skip_output'], $cmd)))
    {
      $this->output(sprintf("\033[33m-!- %s\033[0m", $cmd));
    }

    $full_cmd = sprintf($this->options['format'], $cmd);

    $this->output(sprintf("\033[33m-!- (DEBUG) %s\033[0m", $full_cmd), Output::SCOPE_DEBUG);

    $proc = new Process($full_cmd, array_merge($this->processOptions, $processOptions));
    $proc->execute();

    return $proc;
  }
}
