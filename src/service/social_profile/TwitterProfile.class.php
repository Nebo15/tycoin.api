<?php
lmb_require('src/service/social_provider/odTwitter.class.php');
lmb_require('src/service/social_profile/SocialServicesProfileInterface.class.php');
lmb_require('src/service/social_profile/SharesInterface.class.php');

class TwitterProfile implements SocialServicesProfileInterface, SharesInterface
{
  const ID = 'Twitter';

  /**
   * @var User
   */
  protected $user;
  /**
   * @var odTwitter
   */
  protected $provider;

  /**
   * @param User $user
   */
  public function __construct(User $user)
  {
    $user_token  = $user->twitter_access_token;
    $user_secret = $user->twitter_access_token_secret;

    lmb_assert_true($user, 'Twitter profile user not specified.');
    lmb_assert_true($user_token, 'Twitter access token not specified.');
    lmb_assert_true($user_secret, 'Twitter access token secret not specified.');

    $this->provider = lmbToolkit::instance()->getTwitter($user_token, $user_secret);
    $this->user     = $user;
 }

  /**
   * @return odTwitter
   */
  public function getProvider()
  {
    return $this->provider;
  }

  /**
   * Returns user profile information that social_provider allow to recieve.
   *
   * @return array
   */
  public function getInfo_Raw()
  {
    return $this->provider->api('1/account/verify_credentials');
  }

  /**
   * Returns accessible user profile information that corresponds database fields.
   *
   * @return array
   */
  function getInfo()
  {
    return self::_mapUserInfo($this->getInfo_Raw());
  }

  /**
   * Returns twitter ID of followed users.
   *
   * @return array
   */
  function getFriendsIds()
  {
    $cursor = '-1';
    $ids = array();
    while (true) {
      if ($cursor == '0')
        break;

      $response = $this->provider->api('1/friends/ids', odTwitter::METHOD_GET, array(
        'cursor' => $cursor
      ));

      if($response) {
        $ids = array_merge($ids, $response['ids']);
        $cursor = $response['next_cursor_str'];
      }
    }
    return $ids;
  }

  /**
   * Returns profile information of given twitter users.
   *
   * @return array
   */
  protected function getUsersByIds(array $ids)
  {
    $lookup = 100;
    $paging = ceil(count($ids) / $lookup);
    $users = array();
    for ($i=0; $i < $paging ; $i++) {
      $set = array_slice($ids, $i*$lookup, $lookup);

      $response = $this->provider->api('1/users/lookup', odTwitter::METHOD_GET, array(
        'user_id' => implode(',', $set)
      ));

      if ($response) {
        $users = array_merge($users, $response);
      }
    }
    return $users;
  }

  /**
   * Returns profile information of followed in twitter users.
   *
   * @return array
   */
  public function getFriends()
  {
    return $this->getUsersByIds($this->getFriendsIds());
  }

  /**
   * Returns users that registered in application and followed by user in twitter.
   *
   * @return array
   */
  public function getRegisteredFriends()
  {
    $results = array();
    foreach($this->getFriends() as $friend)
    {
      $info = $this->_mapUserInfo($friend);
      $user = User::findByTwitterUid($friend['id']);
      if(!$user)
        continue;
      $user->import($info);
      $results[] = $user;
    }
    return $results;
  }

  /**
   * Returns user avatars.
   *
   * @return array
   */
  public function getPictures()
  {
    if($this->getInfo_Raw()['default_profile_image'])
      return array();

    $uid = $this->user->twitter_uid;
    return array(
      '73x73' => 'http://api.twitter.com/1/users/profile_image?user_id='.$uid.'&size=bigger',
      '?x?'   => 'http://api.twitter.com/1/users/profile_image?user_id='.$uid.'&size=original'
    );
  }

  /**
   * Returns contents of picture.
   *
   * @param  string $url
   * @return string Binary string contents
   */
  public function getPictureContents($url)
  {
    return $this->getProvider()->downloadImage($url);
  }

  /**
   * Update twitter status.
   *
   * @param  string $string
   * @return mixed
   */
  protected function tweet($string)
  {
    return $this->provider->api('1/statuses/update', odTwitter::METHOD_POST, array(
      'status' => $string
    ));
  }

  public function shareInvitation($twitter_user_id) {}

  protected function _mapUserInfo($user_info)
  {
    return array(
        'twitter_uid'      => $user_info['id'],
        'name'             => $user_info['screen_name'],
        'timezone'         => $user_info['utc_offset'],
        'location'         => isset($user_info['location'])
                                  ? $user_info['location']
                                  : ''
    );
  }
}
