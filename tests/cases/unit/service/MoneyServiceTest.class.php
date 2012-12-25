<?php
lmb_require('tests/cases/unit/odUnitTestCase.class.php');
lmb_require('src/service/MoneyService.class.php');
lmb_require('src/model/InternalShopDeal.class.php');
lmb_require('src/model/PartnerDeal.class.php');

class MoneyServiceTest extends odUnitTestCase
{
	function testHistory_empty()
	{
		$user = $this->generator->user();
		$service = new MoneyService();
		$this->assertEqual(0, count($service->history($user)));
	}

  function testBalance_empty()
  {
    $balance = (new MoneyService())->balance($this->generator->user());
    $this->assertEqual(0, $balance->purchased_coins_count);
    $this->assertEqual(0, $balance->purchased_big_coins_count);
    $this->assertEqual(0, $balance->received_coins_count);
    $this->assertEqual(0, $balance->received_big_coins_count);
    $this->assertEqual(false, $balance->free_coins_available);
    $this->assertEqual(null, $balance->free_coins_available_time);
  }

  function testPurchase()
  {
    $service = new MoneyService();
    $recipient = $this->generator->user();

    $deal = new InternalShopDeal();
    $deal->coins_count = 2;
    $deal->coins_type = COIN_BIG;

    $transaction = $service->purchase($recipient, $deal);

	  //transaction
    $this->assertEqual(1, count($service->history($recipient)));
    $this->assertTrue($transaction->id);
    $this->assertEqual(null, $transaction->sender_id);
    $this->assertEqual($recipient->id, $transaction->recipient_id);
    $this->assertEqual(Transaction::PURCHASE, $transaction->type);
    $this->assertEqual($deal->coins_type, $transaction->coins_type);
    $this->assertEqual($deal->coins_count, $transaction->coins_count);
    $this->assertEqual(null, $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $history = $service->history($recipient);
	  $this->assertEqual(1, count($history));
	  //balance
	  $balance = $service->balance($recipient);
	  $this->assertEqual(0, $balance->purchased_coins_count);
	  $this->assertEqual(2, $balance->purchased_big_coins_count);
	  $this->assertEqual(0, $balance->received_coins_count);
	  $this->assertEqual(0, $balance->received_big_coins_count);
	  $this->assertEqual(false, $balance->free_coins_available);
	  $this->assertEqual(null, $balance->free_coins_available_time);
  }

  function testTransfer()
  {
    $sender = $this->generator->user('sender');
    $recipient = $this->generator->user('recipient');
    $service = new MoneyService();

    $transaction = $service->transfer($sender, $recipient, COIN_USUAL, 3, 'foo');
		//transaction
    $this->assertEqual(1, count($service->history($recipient)));
    $this->assertTrue($transaction->id);
    $this->assertEqual($sender->id, $transaction->sender_id);
    $this->assertEqual($recipient->id, $transaction->recipient_id);
    $this->assertEqual(Transaction::TRANSFER, $transaction->type);
    $this->assertEqual(COIN_USUAL, $transaction->coins_type);
    $this->assertEqual(3, $transaction->coins_count);
    $this->assertEqual('foo', $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $history = $service->history($recipient);
	  $this->assertEqual(1, count($history));
	  //sender balance
	  $balance = $service->balance($sender);
	  $this->assertEqual(-3, $balance->purchased_coins_count);
	  $this->assertEqual(0, $balance->purchased_big_coins_count);
	  $this->assertEqual(0, $balance->received_coins_count);
	  $this->assertEqual(0, $balance->received_big_coins_count);
	  $this->assertEqual(false, $balance->free_coins_available);
	  $this->assertEqual(null, $balance->free_coins_available_time);
	  //recipient balance
	  $balance = $service->balance($recipient);
	  $this->assertEqual(0, $balance->purchased_coins_count);
	  $this->assertEqual(0, $balance->purchased_big_coins_count);
	  $this->assertEqual(3, $balance->received_coins_count);
	  $this->assertEqual(0, $balance->received_big_coins_count);
	  $this->assertEqual(false, $balance->free_coins_available);
	  $this->assertEqual(null, $balance->free_coins_available_time);
  }

  function testPayment()
  {
    $service = new MoneyService();
    $sender = $this->generator->user();

    $deal = new PartnerDeal();
    $deal->coins_count = 5;
    $deal->coins_type = COIN_BIG;

    $transaction = $service->payment($sender, $deal);
		//transaction
    $this->assertEqual(1, count($service->history($sender)));
    $this->assertTrue($transaction->id);
    $this->assertEqual($sender->id, $transaction->sender_id);
    $this->assertEqual(null, $transaction->recipient_id);
    $this->assertEqual(Transaction::PAYMENT, $transaction->type);
    $this->assertEqual($deal->coins_type, $transaction->coins_type);
    $this->assertEqual($deal->coins_count, $transaction->coins_count);
    $this->assertEqual(null, $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $history = $service->history($sender);
	  $this->assertEqual(1, count($history));
	  //sender balance
	  $balance = $service->balance($sender);
	  $this->assertEqual(0, $balance->purchased_coins_count);
	  $this->assertEqual(-5, $balance->purchased_big_coins_count);
	  $this->assertEqual(0, $balance->received_coins_count);
	  $this->assertEqual(0, $balance->received_big_coins_count);
	  $this->assertEqual(false, $balance->free_coins_available);
	  $this->assertEqual(null, $balance->free_coins_available_time);
  }

  function testRestore()
  {
    $service = new MoneyService();
    $recipient = $this->generator->user();

    $deal = new InternalShopDeal();
    $deal->coins_type = COIN_USUAL;
    $deal->coins_count = 1;

    $transaction = $service->tryRestore($recipient, $deal);
		//transaction
    $this->assertEqual(1, count($service->history($recipient)));
    $this->assertTrue($transaction->id);
    $this->assertEqual(null, $transaction->sender_id);
    $this->assertEqual($recipient->id, $transaction->recipient_id);
    $this->assertEqual(Transaction::RESTORE, $transaction->type);
    $this->assertEqual($deal->coins_type, $transaction->coins_type);
    $this->assertEqual($deal->coins_count, $transaction->coins_count);
    $this->assertEqual(null, $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $history = $service->history($recipient);
	  $this->assertEqual(1, count($history));
	  //balance
	  $balance = $service->balance($recipient);
	  $this->assertEqual(0, $balance->purchased_coins_count);
	  $this->assertEqual(0, $balance->purchased_big_coins_count);
	  $this->assertEqual(0, $balance->received_coins_count);
	  $this->assertEqual(0, $balance->received_big_coins_count);
	  $this->assertEqual(true, $balance->free_coins_available);
	  $this->assertEqual(true, (bool) $balance->free_coins_available_time);
  }
}