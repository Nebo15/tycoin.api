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
    $this->assertEqual(0, $balance->free_coins_count);
    $this->assertEqual(null, $balance->free_coins_available_time);
  }

  function testPurchase()
  {
    $service = new MoneyService();
    $recipient = $this->generator->user();

    $deal = new InternalShopDeal();
    $deal->coins_count = 2;
    $deal->coins_type = COIN_BIG;

	  $this->assertEqual(0, count($service->history($recipient)));
	  $this->assertEqual(0, $service->balance($recipient)->purchased_big_coins_count);

    $transaction = $service->purchase($recipient, $deal);

	  //transaction
    $this->assertTrue($transaction->id);
    $this->assertEqual(null, $transaction->sender_id);
    $this->assertEqual($recipient->id, $transaction->recipient_id);
    $this->assertEqual(Transaction::PURCHASE, $transaction->type);
    $this->assertEqual($deal->coins_type, $transaction->coins_type);
    $this->assertEqual($deal->coins_count, $transaction->coins_count);
    $this->assertEqual(null, $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $this->assertEqual(1, count($service->history($recipient)));
	  //balance
	  $this->assertEqual(2, $service->balance($recipient)->purchased_big_coins_count);
  }

  function testTransfer()
  {
	  $service = new MoneyService();
    $sender = $this->generator->user('sender');
	  $service->purchase($sender, $this->_internalDeal(COIN_USUAL, 3));
    $recipient = $this->generator->user('recipient');

	  $this->assertEqual(3, $service->balance($sender)->purchased_coins_count);
	  $this->assertEqual(0, $service->balance($recipient)->received_coins_count);

    $transaction = $service->transfer($sender, $recipient, COIN_USUAL, 3, 'foo');
		//transaction
    $this->assertTrue($transaction->id);
    $this->assertEqual($sender->id, $transaction->sender_id);
    $this->assertEqual($recipient->id, $transaction->recipient_id);
    $this->assertEqual(Transaction::TRANSFER, $transaction->type);
    $this->assertEqual(COIN_USUAL, $transaction->coins_type);
    $this->assertEqual(3, $transaction->coins_count);
    $this->assertEqual('foo', $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //balance
	  $this->assertEqual(1, count($service->history($recipient)));
	  $this->assertEqual(0, $service->balance($sender)->purchased_coins_count);
	  $this->assertEqual(3, $service->balance($recipient)->received_coins_count);
  }

	function testTransfer_notEnoughMoney()
	{
		$service = new MoneyService();
		$recipient = $this->generator->user('recipient');

		$sender = $this->generator->user('sender');
		$service->purchase($sender, $this->_internalDeal(COIN_USUAL, 2));
		$this->assertFalse($service->transfer($sender, $recipient, COIN_USUAL, 3, 'foo'));

		$sender = $this->generator->user('sender');
		$service->purchase($sender, $this->_internalDeal(COIN_USUAL, 2));
		$service->tryRestore($sender, $this->_internalDeal(COIN_USUAL, 1));
		$this->assertTrue($service->transfer($sender, $recipient, COIN_USUAL, 3, 'foo'));
	}

	function testTransferToCode()
	{
		$service = new MoneyService();
		$sender = $this->generator->user('sender');
		$service->purchase($sender, $this->_internalDeal(COIN_USUAL, 3));
		$code = 42;

		$this->assertEqual(3, $service->balance($sender)->purchased_coins_count);

		$transaction = $service->transferToCode($sender, $code, COIN_USUAL, 3, 'foo');
		//transaction
		$this->assertTrue($transaction->id);
		$this->assertEqual($sender->id, $transaction->sender_id);
		$this->assertEqual($code, $transaction->recipient_id);
		$this->assertEqual(Transaction::TRANSFER, $transaction->type);
		$this->assertEqual(COIN_USUAL, $transaction->coins_type);
		$this->assertEqual(3, $transaction->coins_count);
		$this->assertEqual('foo', $transaction->message);
		$this->assertTrue($transaction->ctime);
		//balance
		$this->assertEqual(1, count($service->historyByCode($code)));
		$this->assertEqual(0, $service->balance($sender)->purchased_coins_count);
		$this->assertEqual(3, $service->balanceOfCode($code)->received_coins_count);
	}

	function testClaim()
	{
		$service = new MoneyService();
		$sender = $this->generator->user('sender');
		$recipient = $this->generator->user('recipient');
		$service->purchase($sender, $this->_internalDeal(COIN_USUAL, 5));

		$transaction = $service->transferToCode($sender, $code = 4242, COIN_USUAL, 5, 'claim');
		$service->claimCode($recipient, $code);
		//balance
		$this->assertEqual(1, count($service->history($recipient)));
		$this->assertEqual(5, $service->balance($recipient)->received_coins_count);
	}

  function testPayment()
  {
	  $service = new MoneyService();
	  $rich_guy = $this->generator->user();
	  $service->purchase($rich_guy, $this->_internalDeal(COIN_BIG, 10));

    $sender = $this->generator->user();
	  $service->transfer($rich_guy, $sender, COIN_BIG, 5, '');

    $deal = new PartnerDeal();
    $deal->coins_count = 5;
    $deal->coins_type = COIN_BIG;

    $transaction = $service->payment($sender, $deal);
		//transaction
    $this->assertTrue($transaction->id);
    $this->assertEqual($sender->id, $transaction->sender_id);
    $this->assertEqual(null, $transaction->recipient_id);
    $this->assertEqual(Transaction::PAYMENT, $transaction->type);
    $this->assertEqual($deal->coins_type, $transaction->coins_type);
    $this->assertEqual($deal->coins_count, $transaction->coins_count);
    $this->assertEqual(null, $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $this->assertEqual(2, count($service->history($sender)));
	  //sender balance
	  $this->assertEqual(0, $service->balance($sender)->received_big_coins_count);
  }

	function testPayment_notEnoughMoney()
	{
		$service = new MoneyService();
		$sender = $this->generator->user();

		$deal = new PartnerDeal();
		$deal->coins_count = 1;
		$deal->coins_type = COIN_BIG;

		$this->assertFalse($service->payment($sender, $deal));
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
    $this->assertTrue($transaction->id);
    $this->assertEqual(null, $transaction->sender_id);
    $this->assertEqual($recipient->id, $transaction->recipient_id);
    $this->assertEqual(Transaction::RESTORE, $transaction->type);
    $this->assertEqual($deal->coins_type, $transaction->coins_type);
    $this->assertEqual($deal->coins_count, $transaction->coins_count);
    $this->assertEqual(null, $transaction->message);
    $this->assertTrue($transaction->ctime);
	  //history
	  $this->assertEqual(1, count($service->history($recipient)));
	  //balance
	  $balance = $service->balance($recipient);
	  $this->assertEqual(1, $balance->free_coins_count);
	  $this->assertEqual(true, (bool) $balance->free_coins_available_time);
  }

	function testRestore_repeat()
	{
		$service = new MoneyService();
		$recipient = $this->generator->user();

		$deal = new InternalShopDeal();
		$deal->coins_type = COIN_USUAL;
		$deal->coins_count = 1;

		$service->tryRestore($recipient, $deal);
		$service->tryRestore($recipient, $deal);

		$balance = $service->balance($recipient);
		$this->assertEqual(1, $balance->free_coins_count);
	}

	/**
	 * @param $coins_type
	 * @param $coins_count
	 * @return InternalShopDeal
	 */
	protected function _internalDeal($coins_type, $coins_count)
	{
		$deal = new InternalShopDeal();
		$deal->coins_type = $coins_type;
		$deal->coins_count = $coins_count;
		return $deal;
	}
}