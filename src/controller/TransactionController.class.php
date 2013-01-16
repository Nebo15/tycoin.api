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
			return $this->_answerWithError($this->error_list);

		$uid = $this->request->get('uid');
		if (!$recipient = User::findByFacebookUid($uid))
		{
			$recipient = new User();
			$recipient->facebook_uid = $uid;
			$recipient->import($this->toolkit->getFacebookProfile()->getFriendInfo($uid));
			$recipient->save();
		}

		$coins_type = $this->request->get('type') == 'big' ? COIN_BIG : COIN_USUAL;
		$message = $this->request->get('message');
		$transaction = $this->toolkit->getMoneyService()->transfer($this->_getUser(), $recipient, $coins_type, 1, $message);

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
		list($from, $to, $limit) = $this->_getFromToLimitations();

		$answer = [];
		foreach ((new MoneyService())->historyWithLimitation($this->_getUser(), $from, $to, $limit) as $transaction)
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

	function doGuestFallbackFromPosting()
	{
		$this->response->redirect('tycoin://index.html#give');
		return $this->_answerOk();
	}
}