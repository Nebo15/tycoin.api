<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/Transaction.class.php');

class TransactionController extends BaseJsonController
{
	function doTransfer()
	{
		if (!$this->request->isPost())
			return $this->_answerNotPost();

		$this->_checkPropertiesInRequest(array('uid', 'type', 'message'));
		if (!$this->error_list->isEmpty())
			return $this->_answerWithError($this->error_list->export());

		$uid = $this->request->get('uid');
		if (!$recipient = User::findByFacebookUid($uid))
		{
			$recipient = new User();
			$recipient->facebook_uid = $uid;
			$recipient->import($this->toolkit->getFacebookProfile()->getFriendInfo($uid));
			$recipient->save();
		}

		$transaction = new Transaction();
		$transaction->sender_id = $this->_getUser()->id;
		$transaction->recipient_id = $recipient->id;
		$transaction->type = Transaction::TRANSFER;
		$transaction->coins_type = $this->request->get('type') == 'big' ? COIN_BIG : COIN_USUAL;
		$transaction->coins_count = 1;
		$transaction->message = $this->request->get('message');
		$transaction->save();

		$this->toolkit->getFacebookProfile($this->_getUser())->shareTransaction($transaction, $recipient);

		return $this->_answerOk($this->toolkit->getMoneyService()->balance($this->_getUser()));
	}

	function doPay()
	{
		if (!$this->request->isPost())
			return $this->_answerNotPost();

		return $this->toolkit->getMoneyService()->balance($this->_getUser());
	}

	function doClaim()
	{
		if (!$this->request->isPost())
			return $this->_answerNotPost();

		return $this->toolkit->getMoneyService()->balance($this->_getUser());
	}

	function doHistory()
	{
		$answer = [];
		foreach ((new MoneyService())->history($this->_getUser()) as $transaction)
		{
			$transaction = $transaction->exportForApi();
			if ($transaction->sender_id)
				$transaction->sender = User::findById($transaction->sender_id)->exportForApi();
			if ($transaction->recipient_id)
				$transaction->recipient = User::findById($transaction->recipient_id)->exportForApi();
			$answer[] = $transaction;
		}
		return $this->_answerOk($answer);
	}
}
