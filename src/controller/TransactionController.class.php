<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/Transaction.class.php');

class TransactionController extends BaseJsonController
{
  function doTransfer()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    $this->toolkit->getFacebookProfile($this->_getUser())->shareTransaction((new odObjectMother())->transaction());

    return $this->toolkit->getMoneyService()->balance($this->_getUser());
  }

  function doPay()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    return $this->toolkit->getMoneyService()->balance($this->_getUser());
  }

  function doClaim()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    return $this->toolkit->getMoneyService()->balance($this->_getUser());
  }

  function doGuestHistory()
  {
    $answer = [];
    foreach((new MoneyService())->history($this->_getUser()) as $transaction)
    {
      $transaction = $transaction->exportForApi();
      if($transaction->sender_id)
        $transaction->sender = User::findById($transaction->sender_id);
      if($transaction->recipient_id)
        $transaction->recipient = User::findById($transaction->recipient_id);
      $answer[] = $transaction;
    }
    return $this->_answerOk($answer);
  }
}
