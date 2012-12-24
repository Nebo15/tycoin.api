<?php
lmb_require('src/controller/BaseJsonController.class.php');

class PartnersController extends BaseJsonController
{
  function doGuestDeals()
  {
    $offer = new stdClass();
    $offer->id = 1;
    $offer->description = 'Бесплатный кофе за каждое хорошее дело!';
    $offer->image = 'http://upload.wikimedia.org/wikipedia/commons/thumb/4/45/A_small_cup_of_coffee.JPG/800px-A_small_cup_of_coffee.JPG';
    $offer->coins = '1';
    $offer->coins_type = 1;
    $offer->shop_id = 1;
    return $this->_answerOk($offer);
  }

  function doGuestItem()
  {
    $shop = new stdClass();
    $shop->id = 1;
    $shop->title = 'Кофейня "Венеция"';
    $shop->location = "ул. Ленина, 4а";
    return $this->_answerOk($shop);
  }

  function doGuestBuy()
  {
    return (new MyController())->doBalance();
  }
}
