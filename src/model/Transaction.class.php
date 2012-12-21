<?php
lmb_require('src/model/base/BaseModel.class.php');
lmb_require('src/model/User.class.php');

class Transaction extends BaseModel
{
  protected $_default_sort_params = array('id' => 'desc');
  protected $_db_table_name = 'transaction';

  public $sender_id;
  public $recipient_id;
  public $coins_count;
  public $coins_type;
  public $ctime;
  public $text;

  protected function _createValidator()
  {
    $validator = new lmbValidator();
    $validator->addRequiredRule('sender_id');
    $validator->addRequiredRule('recipient_id');
    $validator->addRequiredRule('type');
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

  function exportForApi(array $properties = null)
  {
    $exported = parent::exportForApi(['id', 'sender_id', 'recipient_id', 'type', 'text']);
    $exported->time = $this->ctime;
    return $exported;
  }
}
