<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/Transaction.class.php');

class TransactionController extends BaseJsonController
{
  function doTransaction()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();

    
  }

  function odPayment()
  {
    if(!$this->request->isPost())
      return $this->_answerNotPost();
  }

  function doGuestHistory()
  {
    return $this->_answerOk([
      (new odObjectMother())->transaction(),
      (new odObjectMother())->transaction()
    ]);
  }
}
