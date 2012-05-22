<?php

/**
 * 
 */
class Output_Xmpp extends Output
{
  /**
   * @var XMPPHP_XMPP
   */
  public $xmpp;

  /**
   * @var string
   */
  public $chatroom;

  /**
   * @var string
   */
  public $user;

  /**
   * @param XMPPHP_XMPP $xmpp
   * @param string $chatroom
   * @param string $user
   */
  public function __construct(XMPPHP_XMPP $xmpp, $chatroom, $user)
  {
    $this->xmpp     = $xmpp;
    $this->chatroom = $chatroom;
    $this->user     = $user;
  }

  /**
   *
   */
  public function __destruct()
  {
    $this->xmpp->disconnect();
  }

  /**
   * @return string
   */
  public function getChatroom()
  {
    return $this->chatroom.'@conference.jabber.abc-objectif.local';
  }

  /**
   * @return string
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param string $message
   * @param string $scope
   * @return void
   */
  public function write($message, $scope = Output::SCOPE_PRIVATE)
  {
    if ($scope == Output::SCOPE_PUBLIC)
    {
      $this->xmpp->presence(null, 'available', $this->getChatroom().'/Git');
      $this->xmpp->message($this->getChatroom(), sprintf('[%s] %s', $this->getUser(), $message), 'groupchat');
      $this->xmpp->presence(null, 'unavailable', $this->getChatroom().'/Git');
    }
  }
}