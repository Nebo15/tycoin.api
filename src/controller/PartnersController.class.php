<?php
lmb_require('src/controller/BaseJsonController.class.php');

class PartnersController extends BaseJsonController
{
  function doGuestDeals()
  {
    return '['.$this->doGuestItem().']';
  }

  function doGuestItem()
  {
		$offer = $this->_getPartnerDeal()->exportForApi();
    $offer->shop = new stdClass();
    $offer->shop->title = 'Coffee House "Kiev"';
    $offer->shop->location = "Lenina street, 4a";
    return $this->_answerOk($offer);
  }

  function doBuy()
  {
    $this->toolkit->getMoneyService()->payment($this->_getUser(), $this->_getPartnerDeal());
	  return $this->_answerOk($this->toolkit->getMoneyService()->balance($this->_getUser()));
  }

	function _getPartnerDeal()
	{
		$offer = new PartnerDeal();
		$offer->id = 1;
		$offer->good = "Cappuccino";
		$offer->description = 'Free coffee for all nice people!';
		$offer->image = 'http://files.softicons.com/download/food-drinks-icons/cappuccino-icons-by-soundforge/png/256x256/Cappuccino_Illy.png';
		$offer->coins_count = 1;
		$offer->coins_type = 1;
		return $offer;
	}
}
