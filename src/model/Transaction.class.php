<?php
lmb_require('src/model/base/BaseModel.class.php');
lmb_require('src/model/User.class.php');

class Transaction extends BaseModel
{
	const TRANSFER = 'transfer';
	const PAYMENT = 'payment';
	const PURCHASE = 'purchase';
	const RESTORE = 'restore';

	protected $_default_sort_params = array('id' => 'desc');
	protected $_db_table_name = 'transaction';

	public $type;
	public $sender_id;
	public $recipient_id;
	public $to_code;
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
		return User::findById($this->recipient_id);
	}

	function getHash()
	{
		return substr(md5($this->sender_id . $this->id), 6);
	}

	static function findByUser(User $user)
	{
		$criteria = lmbSQLCriteria::equal('sender_id', $user->id)->addOr(lmbSQLCriteria::equal('recipient_id', $user->id));

		return Transaction::find($criteria, ['id' => 'ASC']);
	}

	static function findByUserWithLimitation(User $user, $from_id, $to_id, $limit)
	{
		$criteria = new lmbSQLCriteria();
		$criteria->add(
			lmbSQLCriteria::equal('sender_id', $user->id)
					->addOr(lmbSQLCriteria::equal('recipient_id', $user->id))
		);

		if ($from_id)
			$criteria->add(lmbSQLCriteria::less('id', $from_id));
		if ($to_id)
			$criteria->add(lmbSQLCriteria::greater('id', $to_id));

		return Transaction::find($criteria, ['id' => 'DESC'])
				->paginate(0, (!$limit || $limit > 100) ? 100 : $limit);
	}

	static function findByCode($code)
	{
		$criteria = lmbSQLCriteria::equal('to_code', 1)->addAnd(lmbSQLCriteria::equal('recipient_id', $code));

		return Transaction::find($criteria, ['id' => 'DESC']);
	}

	function exportForApi(array $properties = null)
	{
		$exported = parent::exportForApi(['id', 'sender_id', 'recipient_id', 'type', 'coins_type', 'coins_count', 'message']);
		$exported->time = $this->ctime;

		return $exported;
	}
}
