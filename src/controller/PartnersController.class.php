<?php
lmb_require('src/controller/BaseJsonController.class.php');

class PartnersController extends BaseJsonController
{
  function doGuestDeals()
  {
    $offer = new stdClass();
    $offer->id = 1;
    $offer->description = 'По киллограму свинного фарша, всем добрым людям!';
    $offer->image = 'http://deficit.in.ua/assets/images/farsh1.jpg';
    $offer->coins = '5';
    $offer->coins_type = '';
    $offer->shop_id = 1;
    return $this->_answerOk($offer);
  }

  function doGuestItem()
  {
    $shop = new stdClass();
    $shop->id = 1;
    $shop->title = 'Дом мясорубок на Кутузовском';
    $shop->location = "На Кутузовском. Узнать легко, по царь-котлете на крыше.";
    return $this->_answerOk($shop);
  }

  function doGuestBuy()
  {
    (new MyController())->doBalance();
  }
}
