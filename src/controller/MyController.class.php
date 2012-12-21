<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/News.class.php');

class MyController extends BaseJsonController
{
  function doBalance()
  {
    $balance = new stdClass();
    $balance->coins = new stdClass();
    $balance->coins->usual_count = 3;
    $balance->coins->big_count = 2;
    $balance->received_coins = new stdClass();
    $balance->received_coins->usual_count = 4;
    $balance->received_coins->big_count = 1;
    $balance->free_coin = new stdClass();
    $balance->free_coin->available = false;
    $balance->free_coin->available_time = time()+60*60*8;
    return $this->_answerOk($balance);
  }
}
