<?php
lmb_require('src/model/Transaction.class.php');
lmb_require('src/model/Balance.class.php');

class MoneyService
{
  function history(User $user)
  {
    $answer = [];
    foreach(Transaction::findByUser($user) as $transaction)
      $answer[] = $transaction->exportForApi();
    return $answer;
  }

  function transfer(User $sender, User $recipient, $coins_type, $coins_count)
  {
    $transaction = new Transaction();
    $transaction->sender_id = $sender->id;
    $transaction->recipient_id = $recipient->id;
    $transaction->type = Transaction::TRANSFER;
    $transaction->coins_type = $coins_type;
    $transaction->coins_count = $coins_count;
    $transaction->save();
  }

  function payment(User $sender, RevenueDeal $deal)
  {

  }

  function purchase(User $recipient, InternalShopDeal $deal)
  {

  }

  function balance(User $user)
  {
    $history = $this->history($user);
    foreach($history as $transaction)
    {
      if($transaction->type != Transaction::TRANSFER)
        continue;
      //if($transaction->coins_type == COIN_USUAL)
    }

    $balance = new Balance();
    $balance->purchased_coins_count = 3;
    $balance->purchased_big_coins_count = 2;
    $balance->received_coins_count = 1;
    $balance->received_big_coins_count = 4;
    $balance->free_coins_available = true;
    return $balance;
  }



  function claim(User $recipient, $code)
  {

  }
}
