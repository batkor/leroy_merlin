<?php

namespace LeroyMerlin\Exception;

class LeroyMerlinException extends \Exception {

  /**
   * LeroyMerlinException constructor.
   *
   * @param string $messages
   *   The exception message.
   * @param array $data
   *   The exception data.
   */
  public function __construct(string $messages, array $data = []) {
    $messages .= ' ' . json_encode($data);
    parent::__construct($messages);
  }

}
