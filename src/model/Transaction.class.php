<?php
lmb_require('src/model/base/BaseModel.class.php');
lmb_require('src/model/User.class.php');

class Transaction extends BaseModel
{
  const TRANSFER = 1;
  const PAYMENT = 2;
  const PURCHASE = 3;
  const RESTORATION = 4;

  protected $_default_sort_params = array('id' => 'desc');
  protected $_db_table_name = 'transaction';

  public $type;
  public $sender_id;
  public $recipient_id;
  public $coins_count;
  public $coins_type;
  public $ctime;
  public $message;

  protected function _createValidator()
  {
    $validator = new lmbValidator();
    $validator->addRequiredRule('type');
    $validator->addRequiredRule('coins_type');
    $validator->addRequiredRule('coins_count');
    return $validator;
  }

  function setSender($user)
  {
    lmb_assert_type($user, 'User');
    $this->sender_id = $user->id;
  }

  function setRecipient($user)
  {
    lmb_assert_type($user, 'User');
    $this->recipient_id = $user->id;
  }

  function getRecipient()
  {
    return User::findByIds($this->recipient_id);
  }

  static function findByUser(User $user)
  {
    $criteria = lmbSQLCriteria::equal('sender_id', $user->id);
    $criteria->addOr(lmbSQLCriteria::equal('recipient_id', $user->id));
    return Transaction::find($criteria, ['id' => 'DESC']);
  }

  function exportForApi(array $properties = null)
  {
    $exported = parent::exportForApi(['id', 'sender_id', 'recipient_id', 'type', 'coins_type', 'coins_count',
      'message']);
    $exported->time = $this->ctime;
    return $exported;
  }
}
