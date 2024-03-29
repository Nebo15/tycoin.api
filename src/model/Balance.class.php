<?php

class Balance
{
  public $purchased_coins_count = 0;
  public $purchased_big_coins_count = 0;
  public $received_coins_count = 0;
  public $received_big_coins_count = 0;
  public $free_coins_count = 0;
  public $free_coins_available_time;

  function exportForApi()
  {
    $balance = new stdClass();
    $balance->purchased_coins = new stdClass();
    $balance->purchased_coins->usual_count = $this->purchased_coins_count;
    $balance->purchased_coins->big_count = $this->purchased_big_coins_count;
    $balance->received_coins = new stdClass();
    $balance->received_coins->usual_count = $this->received_coins_count;
    $balance->received_coins->big_count = $this->received_big_coins_count;
    $balance->free_coin = new stdClass();
    $balance->free_coin->count = $this->free_coins_count;
    $balance->free_coin->available_time = $this->free_coins_available_time;
    return $balance;
  }
}