<?php

class Pflow_History_File implements Pflow_HistoryInterface
{
  /**
   * @var SplFileObject
   */
  public $file;

  /**
   * @param SplFileObject $file
   */
  public function __construct(SplFileObject $file)
  {
    $this->file = $file;
  }

  /**
   * @param string $line
   * @return void
   */
  public function add($line)
  {
    file_put_contents($this->file->getRealPath(), sprintf('[%s] %s'.PHP_EOL, date('Y-m-d H:i:s'), $line), FILE_APPEND);
  }

  /**
   * 
   */
  public function getLast()
  {
    // @todo
  }

  /**
   * @return bool
   */
  public function purge()
  {
    // no unlink because there's an add after that requires the file to exist
    return file_put_contents($this->file->getRealPath(), '');
  }
}