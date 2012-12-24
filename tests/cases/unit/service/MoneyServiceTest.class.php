<?php
lmb_require('tests/cases/unit/odUnitTestCase.class.php');
lmb_require('src/service/MoneyService.class.php');

class MoneyServiceTest extends odUnitTestCase
{
  function testHistory()
  {
    $user = $this->generator->user();
    $service = new MoneyService();
    $this->assertEqual([], $service->history($user));
  }

  function testTransfer()
  {
    $sender = $this->generator->user('sender', 1);
    $recipient = $this->generator->user('recipient');
    $service = new MoneyService();
    $service->transfer($sender, $recipient, COIN_USUAL, 1);
    $this->assertEqual(1, count($service->history($sender)));
    $this->assertEqual(1, count($service->history($recipient)));
  }
}