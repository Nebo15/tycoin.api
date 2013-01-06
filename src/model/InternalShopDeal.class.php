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
		$deal->price = 0;
		$deals[] = $deal;

		$deal = new InternalShopDeal();
		$deal->id = 2;
		$deal->coins_count = 10;
		$deal->coins_type = COIN_USUAL;
		$deal->title = 'Usual coin';
		$deal->price = 0;
		$deals[] = $deal;

		$deal = new InternalShopDeal();
		$deal->id = 3;
		$deal->coins_count = 1;
		$deal->coins_type = COIN_BIG;
		$deal->title = 'Big coins';
		$deal->price = 0;
		$deals[] = $deal;

		return $deals;
	}

	static function findById($id)
	{
		foreach (InternalShopDeal::find() as $deal)
		{
			if ($deal->id == $id)
				return $deal;
		}
	}
}