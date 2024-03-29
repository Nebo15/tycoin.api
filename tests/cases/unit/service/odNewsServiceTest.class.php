<?php
lmb_require('tests/cases/unit/odUnitTestCase.class.php');
lmb_require('src/service/odNewsService.class.php');
lmb_require('src/model/Day.class.php');

Mock::generate('FacebookProfile', 'FacebookProfileMock');

class odNewsServiceTest extends odUnitTestCase
{
  /**
   * @var odNewsService
   */
  protected $sender_service;

  /**
   * @var UserForSettingsTests
   */
  protected $sender;
  /**
   * @var UserForSettingsTests
   */
  protected $follower;

  function setUp()
  {
    parent::setUp();
    $this->sender = $this->generator->user('sender');
    $this->sender_service = new odNewsService($this->sender);
    $this->follower = $this->generator->user('follower');
    $this->generator->follow($this->sender, $this->follower);
  }

  function testGetMessage()
  {
    $sender = (object) ['id' => 1, 'name' => 'foo'];
    $user = (object) ['id' => 2, 'name' => 'bar'];
    $day = (object) ['id' => 3, 'title' => 'baz'];
    $params = ['sender' => $sender, 'user' => $user, 'day' => $day];
    $expected = <<<EOD
<a href="odom://users/1">foo</a>-<a href="odom://users/2">bar</a>-<a href="odom://days/3">baz</a>
EOD;
    $this->assertEqual(odNewsService::getMessage('{sender}-{user}-{day}', $params), $expected);
  }

  function testOnDay()
  {
    $day = $this->generator->day($this->sender, 'testOnDay');

    $this->sender_service->onDay($day);

    $news = $this->follower->getNews()->at(0);

    $this->assertNewsUsers($news, $this->follower, $this->sender);
    $this->assertNewsText($news, odNewsService::MSG_DAY_CREATED, ['day' => $day]);
    $this->assertEqual("odom://day/{$day->id}", $news->link);
    $this->assertEqual($day->id, $news->day_id);
  }

  function testOnDay_DisabledInSettings()
  {
    $settings = $this->follower->getSettings();
    $settings->notifications_new_days = 0;
    $settings->save();

    $day = $this->generator->day($this->sender, false, 'testOnDay');
    $day->save();

    $this->sender_service->onDay($day);

    $this->assertNoNews($this->follower);
  }

  function testOnDayDelete()
  {
    $day = $this->generator->day($this->sender, false, 'testOnDay');
    $day->save();
    $day_to_delete = $this->generator->day($this->sender);
    $day_to_delete->save();
    $this->sender_service->onDay($day);
    $this->sender_service->onDay($day_to_delete);
    $this->sender_service->onDayDelete($day_to_delete);

    $this->assertEqual(1, $this->follower->getNews()->count());
  }

  function testOnDayComment_Owner()
  {
    $day_owner = $this->generator->user('onwer');
    $day_owner->save();
    $commentator = $this->generator->user('cmmentator');
    $commentator->save();

    $day = $this->generator->day($day_owner, false, 'some_day');

    $comment = $this->generator->dayComment($day, $commentator);
    $comment->save();

    (new odNewsService($commentator))->onDayComment($comment);

    $news = $day_owner->getNews()->at(0);
    $this->assertNewsUsers($news, $day_owner, $commentator);
    $this->assertNewsText($news, odNewsService::MSG_DAY_COMMENT, ['day' => $day], $commentator);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual($comment->id, $news->day_comment_id);
    $this->assertEqual("odom://day/{$day->id}/comment/{$comment->id}", $news->link);
  }

  function testOnDayCommentDelete()
  {
    $day_owner = $this->generator->user('onwer');
    $day_owner->save();
    $commentator = $this->generator->user('commentator');
    $commentator->save();

    $day = $this->generator->day($day_owner, false, 'some_day');

    $comment = $this->generator->dayComment($day, $commentator);
    $comment->save();

    (new odNewsService($commentator))->onDayComment($comment);
    $this->assertEqual(News::find()->count(), 1);
    (new odNewsService($commentator))->onDayCommentDelete($comment);
    $this->assertEqual(News::find()->count(), 0);
  }

  function testOnDayComment_Owner_DisabledInSettings()
  {
    $day_owner = $this->_createUserWithDisabledNotification('new_comments');

    $commentator = $this->generator->user('commentator');
    $commentator->save();

    $day = $this->generator->day($day_owner, false, 'some_day');
    $comment = $this->generator->dayComment($day, $commentator);
    $comment->save();

    (new odNewsService($commentator))->onDayComment($comment);

    $this->assertNoNews($day_owner);
  }

  function testOnDayComment_OldCommentator()
  {
    $old_commentator = $this->generator->user('old_commentator');
    $old_commentator->save();
    $new_commentator = $this->generator->user('new_commentator');
    $new_commentator->save();

    $day = $this->generator->day();
    $day->save();
    $old_comment = $this->generator->dayComment($day, $old_commentator);
    $old_comment->save();

    $comment = $this->generator->dayComment($day, $new_commentator);
    $comment->save();

    (new odNewsService($new_commentator))->onDayComment($comment);

    $news = $old_commentator->getNews()->at(0);
    $this->assertNewsUsers($news, $old_commentator, $new_commentator);
    $this->assertNewsText($news, odNewsService::MSG_DAY_COMMENT,  ['day' => $day], $new_commentator);
    $this->assertEqual("odom://day/{$day->id}/comment/{$comment->id}", $news->link);
  }

  function testOnDayComment_OldCommentator_DisabledInSettings()
  {
    $old_commentator = $this->_createUserWithDisabledNotification('new_replays');
    $old_commentator->save();
    $new_commentator = $this->generator->user();
    $new_commentator->save();

    $day = $this->generator->day();
    $day->save();
    $old_comment = $this->generator->dayComment($day, $old_commentator);
    $old_comment->save();

    $comment = $this->generator->dayComment($day, $new_commentator);
    $comment->save();

    (new odNewsService($new_commentator))->onDayComment($comment);

    $this->assertNoNews($old_commentator);
  }

  function testOnMoment()
  {
    $day = $this->generator->day();
    $moment = $this->generator->moment($day);
    $moment->save();
    $this->sender_service->onMoment($moment);

    $news = News::findFirst();
    $this->assertNewsUsers($news, $this->follower,$this->sender);
    $this->assertNewsText($news, odNewsService::MSG_MOMENT_CREATED, ['day' => $day]);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual($moment->id, $news->moment_id);
  }

  function testOnMoment_DisabledInSettings()
  {
    $settings = $this->follower->getSettings();
    $settings->notifications_new_days = 0;
    $settings->save();

    $day = $this->generator->day();
    $moment = $this->generator->moment($day);
    $moment->save();
    $this->sender_service->onMoment($moment);

    $this->assertNoNews($this->follower);
  }

  function testOnMomentDelete()
  {
    $moment = $this->generator->moment();
    $moment->save();
    $this->sender_service->onMoment($moment);
    $this->sender_service->onMomentDelete($moment);

    $this->assertNoNews($this->follower);
  }

  function testOnMomentComment_DayOwner()
  {
    $commentator = $this->sender;
    $commentator->save();

    $day = $this->generator->day();
    $moment = $this->generator->moment($day);
    $comment = $this->generator->momentComment($moment, $commentator);
    $comment->save();

    (new odNewsService($commentator))->onMomentComment($comment);

    $user = User::findById($day->user_id);
    $news = $user->getNews()->at(0);
    $this->assertNewsUsers($news, $user, $commentator);
    $this->assertNewsText($news, odNewsService::MSG_MOMENT_COMMENT, ['day' => $day]);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual($moment->id, $news->moment_id);
    $this->assertEqual($comment->id, $news->moment_comment_id);
    $this->assertEqual("odom://moment/{$moment->id}/comment/{$comment->id}", $news->link);
  }

  function testOnMomentCommentDelete()
  {
    $commentator = $this->sender;
    $commentator->save();

    $day = $this->generator->day();
    $moment = $this->generator->moment($day);
    $comment = $this->generator->momentComment($moment, $commentator);
    $comment->save();

    (new odNewsService($commentator))->onMomentComment($comment);
    $this->assertEqual(News::find()->count(), 1);
    (new odNewsService($commentator))->onMomentCommentDelete($comment);
    $this->assertEqual(News::find()->count(), 0);
  }

  function testOnMomentComment_DayOwner_DisabledInSettings()
  {
    $commentator = $this->sender;
    $commentator->save();

    $day_owner = $this->_createUserWithDisabledNotification('new_comments');
    $day_owner->save();

    $day = $this->generator->day($day_owner);
    $moment = $this->generator->moment($day);
    $comment = $this->generator->momentComment($moment, $commentator);
    $comment->save();

    (new odNewsService($commentator))->onMomentComment($comment);

    $this->assertNoNews($day->getUser());
  }

  function testOnMomentComment_OldCommentator()
  {
    $old_commentator = $this->generator->user();
    $new_commentator = $this->sender;

    $day = $this->generator->day();
    $moment = $this->generator->moment($day);
    $old_comment = $this->generator->momentComment($moment, $old_commentator);
    $old_comment->save();

    $comment = $this->generator->momentComment($moment, $new_commentator);
    $comment->save();

    (new odNewsService($new_commentator))->onMomentComment($comment);

    $news = $old_commentator->getNews()->at(0);
    $this->assertNewsUsers($news, $old_commentator, $new_commentator);
    $this->assertNewsText($news, odNewsService::MSG_MOMENT_COMMENT, ['day' => $day]);
    $this->assertEqual("odom://moment/{$moment->id}/comment/{$comment->id}", $news->link);
  }

  function testOnMomentComment_OldCommentator_DisabledInSettings()
  {
    $old_commentator = $this->_createUserWithDisabledNotification('new_replays');
    $old_commentator->save();
    $new_commentator = $this->sender;

    $day = $this->generator->day();
    $moment = $this->generator->moment($day);
    $old_comment = $this->generator->momentComment($moment, $old_commentator);
    $old_comment->save();

    $comment = $this->generator->momentComment($moment, $new_commentator);
    $comment->save();

    (new odNewsService($new_commentator))->onMomentComment($comment);

    $this->assertNoNews($old_commentator);
  }

  function testFollow_Followed()
  {
    $foo = $this->generator->user('foo');
    $foo->save();
    $bar = $this->generator->user('bar');
    $bar->save();

    (new odNewsService($bar))->onFollow($foo);

    $this->assertEqual(1, count($foo->getNews()));
    $news = $foo->getNews()->at(0);
    $this->assertNewsUsers($news, $foo, $bar);
    $this->assertNewsText($news, odNewsService::MSG_FOLLOW, ['user' => $foo], $bar);
    $this->assertEqual($foo->id, $news->user_id);
    $this->assertEqual("odom://user/{$foo->id}", $news->link);
  }

  function testFollow_Followers()
  {
    $foo = $this->generator->user('foo');
    $foo->save();
    $bar = $this->generator->user('bar');
    $bar->save();
    $dum = $this->generator->user('dum');
    $dum->save();

    $this->generator->follow($foo, $dum);

    (new odNewsService($foo))->onFollow($bar);

    $this->assertEqual(1, count($dum->getNews()));
    $news = $dum->getNews()->at(0);
    $this->assertNewsUsers($news, $dum, $foo);
    $this->assertNewsText($news, odNewsService::MSG_FOLLOW, ['user' => $bar], $foo);
    $this->assertEqual("odom://user/{$bar->id}", $news->link);
  }

  function testFollow_Followers_DisableInSettings()
  {
    $foo = $this->generator->user('foo');
    $foo->save();
    $bar = $this->generator->user('bar');
    $bar->save();
    $dum = $this->_createUserWithDisabledNotification('related_activity', 'dum');
    $dum->save();

    $this->generator->follow($foo, $dum);

    (new odNewsService($foo))->onFollow($bar);

    $this->assertNoNews($dum);
  }

  function testUserRegister()
  {
    $new_user = $this->generator->user('new');
    $new_user->save();

    $friend = $this->generator->user('friend');
    $friend->save();

    $mock = new FacebookProfileMock();
    $mock->setReturnValue('getRegisteredFriends', array($friend));
    $this->toolkit->setFacebookProfile($new_user, $mock);

    (new odNewsService($new_user))->onUserRegister($new_user);

    $news = $friend->getNews()->at(0);
    $this->assertNewsUsers($news, $friend, $new_user);
    $this->assertNewsText($news, odNewsService::MSG_FBFRIEND_REGISTERED, [], $new_user);
    $this->assertEqual("odom://user/{$new_user->id}", $news->link);
  }

  function testOnDayLike_byOwner()
  {
    $day_owner = $this->generator->user();
    $day = $this->generator->day($day_owner);
    $day->save();
    $like = $this->generator->dayLike($day);
    $like->save();

    $this->sender_service->onDayLike($day, $like);

    $news = $day_owner->getNews()->at(0);
    $this->assertNewsUsers($news, $day_owner, $this->sender);
    $this->assertNewsText($news, odNewsService::MSG_DAY_LIKED, ['day' => $day]);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual($like->id, $news->day_like_id);
    $this->assertEqual("odom://day/{$day->id}", $news->link);
  }

  function testOnDayLike_byOwner_DisabledInSettings()
  {
    $day_owner = $this->_createUserWithDisabledNotification('related_activity');
    $day = $this->generator->day($day_owner);
    $day->save();
    $like = $this->generator->dayLike($day);
    $like->save();

    $this->sender_service->onDayLike($day, $like);

    $this->assertNoNews($day_owner);
  }

  function testOnDayLike_byFollowers()
  {
    $day_owner = $this->generator->user();
    $day = $this->generator->day($day_owner);
    $day->save();
    $like = $this->generator->dayLike($day);
    $like->save();

    $this->sender_service->onDayLike($day, $like);

    $news = $this->follower->getNews()->at(0);
    $this->assertNewsUsers($news, $this->follower, $this->sender);
    $this->assertNewsText($news, odNewsService::MSG_DAY_LIKED, ['day' => $day]);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual($like->id, $news->day_like_id);
    $this->assertEqual("odom://day/{$day->id}", $news->link);
  }

  function testOnDayLike_byFollowers_DisabledInSettings()
  {
    $settings = $this->follower->getSettings();
    $settings->notifications_related_activity = 0;
    $settings->save();

    $day = $this->generator->day();
    $day->save();
    $like = $this->generator->dayLike($day);
    $like->save();

    $this->sender_service->onDayLike($day, $like);

    $this->assertNoNews($this->follower);
  }

  function testOnDayUnlike()
  {
    $day_owner = $this->generator->user();
    $day = $this->generator->day($day_owner);
    $day->save();
    $like = $this->generator->dayLike($day);
    $like->save();

    $this->sender_service->onDayLike($day, $like);
    $this->assertEqual(News::find()->count(), 1);
    $this->sender_service->onDayUnlike($day, $like);
    $this->assertEqual(News::find()->count(), 0);
  }

  function testOnMomentLike_byOwner()
  {
    $day_owner = $this->generator->user();
    $day = $this->generator->day($day_owner);
    $day->save();
    $moment = $this->generator->moment($day);
    $moment->save();
    $like = $this->generator->momentLike($moment);
    $like->save();

    $this->sender_service->onMomentLike($moment, $like);

    $news = $day_owner->getNews()->at(0);
    $this->assertNewsUsers($news, $day_owner, $this->sender);
    $this->assertNewsText($news, odNewsService::MSG_MOMENT_LIKED, ['day' => $day]);
    $this->assertEqual($moment->id, $news->moment_id);
    $this->assertEqual($like->id, $news->moment_like_id);
    $this->assertEqual("odom://moment/{$moment->id}", $news->link);
  }

  function testOnMomentLike_byOwner_DisabledInSettings()
  {
    $day_owner = $this->_createUserWithDisabledNotification('related_activity');
    $day = $this->generator->day($day_owner);
    $day->save();
    $moment = $this->generator->moment($day);
    $moment->save();
    $like = $this->generator->momentLike($moment);
    $like->save();

    $this->sender_service->onMomentLike($moment, $like);

    $this->assertNoNews($day_owner);
  }

  function testOnMomentLike_byFollowers()
  {
    $day_owner = $this->generator->user();
    $day = $this->generator->day($day_owner);
    $day->save();
    $moment = $this->generator->moment($day);
    $moment->save();
    $like = $this->generator->momentLike($moment);
    $like->save();

    $this->sender_service->onMomentLike($moment, $like);

    $news = $this->follower->getNews()->at(0);
    $this->assertNewsUsers($news, $this->follower, $this->sender);
    $this->assertNewsText($news, odNewsService::MSG_MOMENT_LIKED, ['day' => $day]);
    $this->assertEqual($moment->id, $news->moment_id);
    $this->assertEqual($like->id, $news->moment_like_id);
    $this->assertEqual("odom://moment/{$moment->id}", $news->link);
  }

  function testOnMomentLike_byFollowers_DisabledInSettings()
  {
    $settings = $this->follower->getSettings();
    $settings->notifications_related_activity = 0;
    $settings->save();

    $day = $this->generator->day();
    $day->save();
    $moment = $this->generator->moment($day);
    $moment->save();
    $like = $this->generator->momentLike($moment);
    $like->save();

    $this->sender_service->onMomentLike($moment, $like);

    $this->assertNoNews($this->follower);
  }

  function testOnMomentUnlike()
  {
    $day_owner = $this->generator->user();
    $day = $this->generator->day($day_owner);
    $day->save();
    $moment = $this->generator->moment($day);
    $moment->save();
    $like = $this->generator->momentLike($moment);
    $like->save();

    $this->sender_service->onMomentLike($moment, $like);
    $this->assertEqual(News::find()->count(), 1);
    $this->sender_service->onMomentUnlike($moment, $like);
    $this->assertEqual(News::find()->count(), 0);
  }

  function testOnDayShare()
  {
    $user_who_share = $this->generator->user();
    $user_who_share->save();
    $user_day_owner = $this->generator->user();
    $user_day_owner->save();
    $day = $this->generator->day($user_day_owner);
    $day->save();

    (new odNewsService($user_who_share))->onDayShare($day);

    $this->assertEqual(1, count($user_day_owner->getNews()));
    $news = $user_day_owner->getNews()->at(0);
    $this->assertNewsUsers($news, $user_day_owner, $user_who_share);
    $this->assertNewsText($news, odNewsService::MSG_DAY_SHARE, ['day' => $day], $user_who_share);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual("odom://day/{$day->id}", $news->link);
  }

  function testOnDayShare_DisabledInSettings()
  {
    $user_who_share = $this->generator->user();
    $user_who_share->save();
    $day_owner = $this->_createUserWithDisabledNotification('related_activity');
    $day_owner->save();
    $day = $this->generator->day($day_owner);
    $day->save();

    (new odNewsService($user_who_share))->onDayShare($day);

    $this->assertNoNews($day_owner);
  }

  function testOnDayFavorite_UserContent()
  {
    $user_who_favorite = $this->generator->user();
    $user_who_favorite->save();
    $user_day_owner = $this->generator->user();
    $user_day_owner->save();
    $day = $this->generator->day($user_day_owner);
    $day->save();

    (new odNewsService($user_who_favorite))->onDayFavorite($day);

    $this->assertEqual(1, count($user_day_owner->getNews()));
    $news = $user_day_owner->getNews()->at(0);
    $this->assertNewsUsers($news, $user_day_owner, $user_who_favorite);
    $this->assertNewsText($news, odNewsService::MSG_DAY_FAVORITE, ['day' => $day], $user_who_favorite);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual("odom://day/{$day->id}", $news->link);
  }

  function testOnDayFavorite_UserContent_DisabledInSettings()
  {
    $user_who_favorite = $this->generator->user();
    $user_who_favorite->save();
    $day_owner = $this->_createUserWithDisabledNotification('related_activity');
    $day_owner->save();
    $day = $this->generator->day($day_owner);
    $day->save();

    (new odNewsService($user_who_favorite))->onDayFavorite($day);

    $this->assertNoNews($day_owner);
  }

  function testOnDayFavorite_Followers()
  {
    $day = $this->generator->day();
    $day->save();

    $this->sender_service->onDayFavorite($day);

    $this->assertEqual(1, count($this->follower->getNews()));
    $news = $this->follower->getNews()->at(0);
    $this->assertNewsUsers($news, $this->follower, $this->sender);
    $this->assertNewsText($news, odNewsService::MSG_DAY_FAVORITE, ['day' => $day]);
    $this->assertEqual($day->id, $news->day_id);
    $this->assertEqual("odom://day/{$day->id}", $news->link);
  }

  function testOnDayFavorite_Followers_DisabledInSettings()
  {
    $settings = $this->follower->getSettings();
    $settings->notifications_related_activity = 0;
    $settings->save();

    $day = $this->generator->day();
    $day->save();

    $this->sender_service->onDayFavorite($day);

    $this->assertNoNews($this->follower);
  }

  function testNotifications()
  {
    $notifications = lmbDBAL::selectQuery('device_notification')->fetch()->getFlatArray();
    $this->assertEqual(0, count($notifications));

    $device_token = $this->generator->deviceToken($this->follower);
    $device_token->save();

    $day = $this->generator->day($this->sender, false, 'testOnDay');
    $day->save();
    $this->sender_service->onDay($day);

    $notifications = lmbDBAL::selectQuery('device_notification')->fetch()->getFlatArray();
    if($this->assertEqual(1, count($notifications)))
    {
      $notification = $notifications[0];
      $this->assertEqual($device_token->id, $notification['device_token_id']);
      $valid_text = odNewsService::getMessage(
        odNewsService::MSG_DAY_CREATED, ['sender' => $this->sender,'day' => $day]
      );
      $this->assertEqual($valid_text, $notification['text']);
      $this->assertEqual(null, $notification['icon']);
      $this->assertEqual(null, $notification['sound']);
      $this->assertEqual(0, $notification['is_sended']);
    }
  }

  protected function assertNoNews(User $user)
  {
    if(count($user->getNews()))
      return $this->fail('User have news');
    return $this->pass();
  }

  protected function assertNewsUsers(News $news, $valid_recipient, $sender)
  {
    if($news->sender_id != $sender->id)
      return $this->fail('Wrong sender');
    foreach($news->getRecipients() as $recipient)
      if($recipient->id == $valid_recipient->id)
        return $this->pass();
    return $this->fail('Wrong recipient');
  }

  protected function assertNewsText(News $news, $message, array $params = array(), $sender = null)
  {
    $params['sender'] = (!$sender) ? $this->sender : $sender;
    $text = odNewsService::getMessage($message, $params);
    return $this->assertEqual($text, $news->text);
  }

  protected function _createUserWithDisabledNotification($notification_name, $user_name = null)
  {
    $user = $this->generator->user($user_name);
    $user->save();
    $settings = $user->getSettings();
    $settings->set('notifications_'.$notification_name, 0);
    $settings->save();

    return $user;
  }
}

class UserForSettingsTests extends User
{
  protected $_db_table_name = 'user';

  function disableNotification($notification_name)
  {
    $settings = $this->getSettings();
    $settings->set('notifications_'.$notification_name, 0);
    $this->setSettings($settings);
  }
}
