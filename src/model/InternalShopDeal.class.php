<?php

class InternalShopDeal
{
  public $id;
  public $title;
  public $coins_count;
  public $coins_type;
  public $price;

  static function find()
  {
    $deals = [];

    $deal = new InternalShopDeal();
    $deal->id = 1;
    $deal->coins_count = 1;
    $deal->coins_type = COIN_USUAL;
    $deal->title = 'Usual coin';
    $deal->price = 1;
    $deals[] = $deal;

    $deal = new InternalShopDeal();
    $deal->id = 2;
    $deal->coins_count = 1;
    $deal->coins_type = COIN_BIG;
    $deal->title = 'Big coins';
    $deal->price = 3;
    $deals[] = $deal;

    return $deals;
  }
}