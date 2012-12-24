<?php

class MoneyService
{
  function history()
  {
    return [];
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

  }

  function claim(User $recipient, $code)
  {

  }
}
