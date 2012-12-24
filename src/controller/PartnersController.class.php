<?php
lmb_require('src/controller/BaseJsonController.class.php');

class PartnersController extends BaseJsonController
{
  function doGuestDeals()
  {
    $offers = [];
    $offer1 = new stdClass();
    $offer1->id = 1;
    $offer1->good = "Cappuccino";
    $offer1->description = 'Free coffee for all nice people!';
    $offer1->image = 'http://files.softicons.com/download/food-drinks-icons/cappuccino-icons-by-soundforge/png/256x256/Cappuccino_Illy.png';
    $offer1->coins = 1;
    $offer1->coin_type = 1;
    $offer1->shop = new stdClass();
    $offer1->shop->title = 'Coffee House "Kiev"';
    $offer1->shop->location = "Lenina street, 4a";
    $offers[] = $offer1;
    return $this->_answerOk($offers);
  }

  function doGuestItem()
  {
    $offer = new stdClass();
    $offer->id = 1;
    $offer->good = "Cappuccino";
    $offer->description = 'Free coffee for all nice people!';
    $offer->image = 'http://files.softicons.com/download/food-drinks-icons/cappuccino-icons-by-soundforge/png/256x256/Cappuccino_Illy.png';
    $offer->coins = 1;
    $offer->coin_type = 1;
    $offer->shop = new stdClass();
    $offer->shop->title = 'Coffee House "Kiev"';
    $offer->shop->location = "Lenina street, 4a";
    return $this->_answerOk($offer);
  }

  function doGuestBuy()
  {
    (new MyController())->doBalance();
  }
}
