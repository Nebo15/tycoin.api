<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/InternalShopDeal.class.php');

class ShopController extends BaseJsonController
{
  function doGuestDeals()
  {
    return $this->_answerOk(InternalShopDeal::find());
  }
}
