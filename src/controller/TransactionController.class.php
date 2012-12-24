<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/Transaction.class.php');

class TransactionController extends BaseJsonController
{
  function doTransfer()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    $transaction = (new odObjectMother())->transaction();
    $transaction->sender_id = $this->_getUser()->id;
    $transaction->save();

    $this->toolkit->getFacebookProfile($this->_getUser())->shareTransaction($transaction);

    return $this->_answerOk($this->toolkit->getMoneyService()->balance($this->_getUser()));
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

  function doHistory()
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
