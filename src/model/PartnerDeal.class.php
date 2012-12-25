<?php
lmb_require('src/model/base/BaseModel.class.php');

class PartnerDeal extends BaseModel
{
  protected $_db_table_name = 'partner_deal';

  public $id;
  public $shop_id;
  public $title;
  public $coins_count;
  public $coins_type;
  public $description;
}