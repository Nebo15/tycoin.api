<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/InternalShopDeal.class.php');

class ShopController extends BaseJsonController
{
	function doDeals()
	{
		return $this->_answerOk(InternalShopDeal::find());
	}

	function doGuestPurchase()
	{
		if (!$this->request->isPost())
			return $this->_answerNotPost();

		$id = $this->request->get('id');
		if (!$deal = InternalShopDeal::findById($id))
			return $this->_answerNotFound("Deal with id '{$id}' not found");

		$this->toolkit->getMoneyService()->purchase($this->_getUser(), $deal);
		return $this->_answerOk($this->toolkit->getMoneyService()->balance($this->_getUser()));
	}
}
