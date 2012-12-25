<?php
lmb_require('src/model/Transaction.class.php');
lmb_require('src/model/Balance.class.php');

class MoneyService
{
	function history(User $user)
	{
		return Transaction::findByUser($user);
	}

	function transfer(User $sender, User $recipient, $coins_type, $coins_count, $message)
	{
		$transaction               = new Transaction();
		$transaction->sender_id    = $sender->id;
		$transaction->recipient_id = $recipient->id;
		$transaction->type         = Transaction::TRANSFER;
		$transaction->coins_type   = $coins_type;
		$transaction->coins_count  = $coins_count;
		$transaction->message      = $message;

		return $transaction->save();
	}

	function payment(User $sender, PartnerDeal $deal)
	{
		$transaction               = new Transaction();
		$transaction->sender_id    = $sender->id;
		$transaction->recipient_id = null;
		$transaction->type         = Transaction::PAYMENT;
		$transaction->coins_type   = $deal->coins_type;
		$transaction->coins_count  = $deal->coins_count;
		$transaction->message      = '';

		return $transaction->save();
	}

	/**
	 * @param User $recipient
	 * @param InternalShopDeal $deal
	 * @return Transaction
	 */
	function purchase(User $recipient, InternalShopDeal $deal)
	{
		$transaction               = new Transaction();
		$transaction->sender_id    = null;
		$transaction->recipient_id = $recipient->id;
		$transaction->type         = Transaction::PURCHASE;
		$transaction->coins_type   = $deal->coins_type;
		$transaction->coins_count  = $deal->coins_count;

		return $transaction->save();
	}

	function tryRestore(User $recipient, InternalShopDeal $deal)
	{
		$transaction               = new Transaction();
		$transaction->sender_id    = null;
		$transaction->recipient_id = $recipient->id;
		$transaction->type         = Transaction::RESTORE;
		$transaction->coins_type   = $deal->coins_type;
		$transaction->coins_count  = $deal->coins_count;

		return $transaction->save();
	}

	function balance(User $user)
	{
		$balance = new Balance();
		foreach ($this->history($user) as $transaction)
		{
			if ($transaction->coins_type != COIN_USUAL && $transaction->coins_type != COIN_BIG)
				throw new lmbException("Unknown coin type '{$transaction->coins_type}'");

			if (Transaction::TRANSFER == $transaction->type)
			{
				if ($user->id == $transaction->recipient_id)
				{
					if (COIN_USUAL == $transaction->coins_type)
						$balance->received_coins_count += $transaction->coins_count;
					else
						$balance->received_big_coins_count += $transaction->coins_count;
				}
				elseif ($user->id == $transaction->sender_id)
				{
					if (COIN_USUAL == $transaction->coins_type)
						$balance->purchased_coins_count -= $transaction->coins_count;
					else
						$balance->purchased_big_coins_count -= $transaction->coins_count;
				}
			}
			elseif (Transaction::PAYMENT == $transaction->type)
			{
				if (COIN_USUAL == $transaction->coins_type)
					$balance->purchased_coins_count -= $transaction->coins_count;
				else
					$balance->purchased_big_coins_count -= $transaction->coins_count;
			}
			elseif (Transaction::PURCHASE == $transaction->type)
			{
				if (COIN_USUAL == $transaction->coins_type)
					$balance->purchased_coins_count += $transaction->coins_count;
				else
					$balance->purchased_big_coins_count += $transaction->coins_count;
			}
		}

		return $balance;
	}


	function claim(User $recipient, $code)
	{

	}
}
