<?php
lmb_require('src/controller/BaseJsonController.class.php');

class ShopController extends BaseJsonController
{
  function doGuestDisplay()
  {
    $shop = new stdClass();
    $shop->id = 1;
    $shop->title = 'Дом мясорубок на Кутузовском';
    $offer = new stdClass();
    $offer->id = 1;
    $offer->description = 'По киллограму свинного фарша, всем добрым людям!';
    $offer->image = 'http://deficit.in.ua/assets/images/farsh1.jpg';
    $offer->coins = '5';
    $offer->coins_type = '';
    $shop->offers = [$offer];
    return $this->_answerOk([$shop]);
  }

  function doGuestItem()
  {
    $shop = new stdClass();
    $shop->id = 1;
    $shop->title = 'Дом мясорубок на Кутузовском';
    $shop->location = "На Кутузовском. Узнать легко, по царь-котлете на крыше.";
    return $this->_answerOk($shop);
  }
}
