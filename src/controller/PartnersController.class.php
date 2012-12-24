<?php
lmb_require('src/controller/BaseJsonController.class.php');

class PartnersController extends BaseJsonController
{
  function doGuestDeals()
  {
    $offer = new stdClass();
    $offer1 = new stdClass();
    $offer1->id = 1;
    $offer1->description = 'Бесплатный кофе за каждое хорошее дело!';
    $offer1->image = 'http://upload.wikimedia.org/wikipedia/commons/thumb/4/45/A_small_cup_of_coffee.JPG/800px-A_small_cup_of_coffee.JPG';
    $offer1->coins = 1;
    $offer1->coin_type = 1;
    $offer1->shop = new stdClass();
    $offer1->shop->title = 'Кофейня "Венеция"';
    $offer1->shop->location = "ул. Ленина, 4а";
    $offer[] = $offer1;
    return $this->_answerOk($offer);
  }

  function doGuestItem()
  {
    $offer = new stdClass();
    $offer->id = 1;
    $offer->description = 'Бесплатный кофе за каждое хорошее дело!';
    $offer->image = 'http://upload.wikimedia.org/wikipedia/commons/thumb/4/45/A_small_cup_of_coffee.JPG/800px-A_small_cup_of_coffee.JPG';
    $offer->coins = 1;
    $offer->coin_type = 1;
    $offer->shop = new stdClass();
    $offer->shop->title = 'Кофейня "Венеция"';
    $offer->shop->location = "ул. Ленина, 4а";
    return $this->_answerOk($offer);
  }

  function doGuestBuy()
  {
    return (new MyController())->doBalance();
  }
}
