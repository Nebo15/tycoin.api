<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/Transaction.class.php');

class TransactionController extends BaseJsonController
{
  function doTransfer()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    return (new MyController())->doBalance();
  }

  function doPay()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    return (new MyController())->doBalance();
  }

  function doClaim()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    return (new MyController())->doBalance();
  }

  function doGuestHistory()
  {
    return $this->_answerOk([
      (new odObjectMother())->transaction(),
      (new odObjectMother())->transaction()
    ]);
  }
}
