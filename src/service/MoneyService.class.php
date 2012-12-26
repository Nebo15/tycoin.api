<?php
lmb_require('src/model/Transaction.class.php');
lmb_require('src/model/Balance.class.php');

class MoneyService
{
	const FREE_COINS_RESTORE_PERIOD = 60;

	function history(User $user)
	{
		return Transaction::findByUser($user);
	}

	function transfer(User $sender, User $recipient, $coins_type, $coins_count, $message)
	{
		return $this->_transfer($sender, $recipient->id, $coins_type, $coins_count, $message, false);
	}

	function payment(User $sender, PartnerDeal $deal)
	{
		$balance               = $this->balance($sender);
		$available_coins_count = (COIN_USUAL == $deal->coins_type) ? $balance->received_coins_count :
				$balance->received_big_coins_count;
		if ($available_coins_count < $deal->coins_count)
			return false;

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
		return $this->_balance($this->history($user), $user->id);
	}

	function historyByCode($code)
	{
		return Transaction::findByCode($code);
	}

	function balanceOfCode($code)
	{
		return $this->_balance($this->historyByCode($code), $code);
	}

	function transferToCode(User $sender, $code, $coins_type, $coins_count, $message)
	{
		return $this->_transfer($sender, $code, $coins_type, $coins_count, $message, true);
	}

	function claimCode(User $recipient, $code)
	{

	}

	protected function _balance($transactions, $user_id)
	{
		$balance = new Balance();
		foreach ($transactions as $transaction)
		{
			if ($transaction->coins_type != COIN_USUAL && $transaction->coins_type != COIN_BIG)
				throw new lmbException("Unknown coin type '{$transaction->coins_type}'");

			if (Transaction::TRANSFER == $transaction->type)
			{
				if ($user_id == $transaction->recipient_id)
				{
					if (COIN_USUAL == $transaction->coins_type)
						$balance->received_coins_count += $transaction->coins_count;
					else
						$balance->received_big_coins_count += $transaction->coins_count;
				}
				elseif ($user_id == $transaction->sender_id)
				{
					if (COIN_USUAL == $transaction->coins_type)
					{
						$balance->purchased_coins_count -= $transaction->coins_count + $balance->free_coins_count;
						$balance->free_coins_count = 0;
					}
					else
						$balance->purchased_big_coins_count -= $transaction->coins_count;
				}
			}
			elseif (Transaction::PAYMENT == $transaction->type)
			{
				if (COIN_USUAL == $transaction->coins_type)
					$balance->received_coins_count -= $transaction->coins_count;
				else
					$balance->received_big_coins_count -= $transaction->coins_count;
			}
			elseif (Transaction::PURCHASE == $transaction->type)
			{
				if (COIN_USUAL == $transaction->coins_type)
					$balance->purchased_coins_count += $transaction->coins_count;
				else
					$balance->purchased_big_coins_count += $transaction->coins_count;
			}
			elseif (Transaction::RESTORE == $transaction->type)
			{
				$balance->free_coins_count          = $transaction->coins_count;
				$balance->free_coins_available_time = time() + self::FREE_COINS_RESTORE_PERIOD;
			}
		}

		return $balance;
	}

	protected function _transfer(User $sender, $recipient_id, $coins_type, $coins_count, $message, $to_code)
	{
		$balance               = $this->balance($sender);
		$available_coins_count = (COIN_USUAL == $coins_type) ?
				$balance->free_coins_count + $balance->purchased_coins_count : $balance->purchased_big_coins_count;
		if ($available_coins_count < $coins_count)
			return false;

		$transaction               = new Transaction();
		$transaction->sender_id    = $sender->id;
		$transaction->recipient_id = $recipient_id;
		$transaction->type         = Transaction::TRANSFER;
		$transaction->to_code      = (int)$to_code;
		$transaction->coins_type   = $coins_type;
		$transaction->coins_count  = $coins_count;
		$transaction->message      = $message;

		return $transaction->save();
	}
}
