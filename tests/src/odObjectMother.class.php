<?php

class odObjectMother
{
  protected $generate_random = true;

  /**
   * @return User
   */
  function user($name = null, $purchased_coins_count = 1)
  {
    $user = new User();
    $user->facebook_uid = $this->string(5);
    $user->facebook_access_token = $this->string(50);
    $user->email = $this->email();
    $user->facebook_profile_utime = $this->integer(11);
    $user->name = $name ?: $this->string(100);
    $user->timezone = $this->integer(1);
    $user->sex = 'female';
    $user->occupation = $this->string(50);
    $user->birthday = $this->date_sql();
    $user->save();
    return $user;
  }

  function complaint($day = null)
  {
    $complaint = new Complaint();
    $complaint->setDay($day ?: $this->day());
    $complaint->text = $this->string(522);
    return $complaint;
  }

  function news(User $creator = null, User $recipient = null) {
    $creator   = $creator   ?: $this->user();
    $recipient = $recipient ?: $this->user();

    $news = new News();
    $news->setSender($creator);
    $news->text = $creator->name . ' likes ' . $recipient->name;
    $news->link = $this->string();
    $news->save();

    $reception = new NewsRecipient();
    $reception->setNews($news);
    $reception->setUser($recipient);
    $reception->save();

    return $news;
  }

  function transaction($type = null, $coins_type = null, $coins_count = null, $sender = null, $recipient = null)
  {
    if(!$sender)
      $sender = $this->user();
    if(!$recipient)
      $recipient = $this->user();
    if(!$type)
      $type = Transaction::TRANSFER;
    if(!$coins_type)
      $coins_type = COIN_USUAL;
    if(!$coins_count)
      $coins_count = 3;

    $item = new Transaction();
    $item->type = $type;
    $item->coins_type = $coins_type;
    $item->coins_count = $coins_count;
    $item->sender_id = $sender->id;
    $item->recipient_id = $recipient->id;
    //$item->save();

    return $item;
  }

  function deviceToken(User $user = null)
  {
    $device_token = new DeviceToken();
    $device_token->token = $this->string(64);
    $device_token->setUser($user ?: $this->user());
    return $device_token->save();
  }

  function deviceNotification(DeviceToken $token = null)
  {
    $notification = new DeviceNotification();
    $notification->setDeviceToken($token ?: $this->deviceToken());
    $notification->text = $this->string(32);
    $notification->icon = $this->integer(1);
    $notification->sound = $this->string();
    $notification->is_sended = 0;
    return $notification->save();
  }

  function shopDealWithUsualCoin()
  {
    return ShopDeal::find()[0];
  }

  function shopDealWithBigCoin()
  {
    return ShopDeal::find()[1];
  }

  function string($length = 6)
  {
    $conso = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");
    $vocal = array("a", "e", "i", "o", "u");
    $password = "";
    srand((double) microtime() * 1000000);
    $max = $length / 2;
    for ($i = 1; $i <= $max; $i++) {
      $password .= $conso[rand(0, 19)];
      $password .= $vocal[rand(0, 4)];
    }
    return $password;
  }

  function integer($length = 4)
  {
    if(!$this->generate_random)
      return (int) substr(str_repeat("1337", ceil($length/4)+1), 0, $length);

    return rand(1, 10^($length+1) - 1);
  }

  function image()
  {
    static $contents;
    if(!$contents)
      $contents = file_get_contents(__DIR__.'/../init/image_128x128.jpg');
    return $contents;
  }

  function image_name()
  {
    return $this->string().'.jpg';
  }

  function email() {
    return $this->string(20).'@odm.com';
  }

  function date_sql()
  {
    if(!$this->generate_random)
      return sprintf("%1d-%2$02d-%3$02d", 1990, 1, 2);

    return sprintf("%1d-%2$02d-%3$02d", rand(1900, 1990), rand(0, 1), rand(1, 29));
  }

  function twitter_credentials()
  {
    return array(
      array(
        'uid'                 => '637083468',
        'access_token'        => '637083468-nBzWGwpdfgTqrg2H3DZwnSgBWwMkbNmxVrwCVepx',
        'access_token_secret' => '4jWX2ozuXHcY4yRwqjFBUfV08t7kFjfxBR1OCV7Y0'
      ),
      array(
        'uid'                 => '718050210',
        'access_token'        => '718050210-SVERCoH3Zrxiw1KiBqjN3khC6tb6Rfwzkpx4D2kt',
        'access_token_secret' => 'KoZL6VY45Wfp0laFXhETkEdSKFdIY92YpRfCkzZu4'
      )
    );
  }

  function facebookInfo($uid = null)
  {
    return array(
      'facebook_uid'      => $uid ?: $this->integer(20),
      'email'            => $this->email(),
      'name'             => $this->userName(),
      'sex'              => User::SEX_MALE,
      'timezone'         => $this->integer(1),
      'facebook_profile_utime' => $this->integer(11),
      'pic'              => 'http://fbcdn.com/'.$this->image_name(),
      'pic_big'          => 'http://fbcdn.com/'.$this->image_name(),
      'occupation'       => $this->string(),
      'current_location' => $this->string(),
      'birthday'         => $this->date_sql()
    );
  }

  function userName()
  {
    $names = [
      'Matt', 'Stew', 'Andrew', 'Mike', 'Josh', 'Joe', 'Drew'
    ];
    $surnames = [
      'Romanova', 'Steinheart', 'Johnson', 'Williams', 'Smith', 'Brown', 'Davis', 'Moore'
    ];

    if($this->generate_random)
      return $names[array_rand($names)] . ' ' . $surnames[array_rand($surnames)];
    else
      return $names[0] . ' ' . $surnames[0];
  }
}
