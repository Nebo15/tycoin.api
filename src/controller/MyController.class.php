<?php
lmb_require('src/controller/BaseJsonController.class.php');

class MyController extends BaseJsonController
{
  function doBalance()
  {
    return $this->_answerOk((new MoneyService())->balance($this->_getUser()));
  }
}
