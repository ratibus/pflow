<?php

class Process_Runtime_Git extends Process_Runtime
{
  /**
   * @param OutputInterface $output
   * @param array $options
   * @param array $processOptions
   */
  public function __construct(OutputInterface $output, array $options = array(), array $processOptions = array())
  {
    $options['format'] = sprintf('git --git-dir=%s --work-tree=%s %%s', $options['git_dir'].'/.git', $options['git_dir']);
    unset($options['git_dir']);

    parent::__construct($output, $options, $processOptions);
  }
}