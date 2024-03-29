<?php
lmb_require('tests/cases/integration/odIntegrationTestCase.class.php');

class FacebookProfileTest extends odIntegrationTestCase
{
  /**
   * @var Client
   */
  protected $proxy_client;

  function setUp()
  {
    parent::setUp();
    $this->proxy_client = new Client('http://stage.onedayofmine.com/proxy.php', 'http://onedayofmine.dev/');
  }

  function testGetInfoRaw()
  {
    $info = (new FacebookProfile($this->main_user))->getInfo_Raw();
    $this->assertTrue(count($info));
    $this->assertEqual($info['uid'], $this->main_user->facebook_uid);
  }

  function testGetInfo()
  {
    $info = (new FacebookProfile($this->main_user))->getInfo();
    $this->assertTrue(count($info));
    $this->assertEqual($info['facebook_uid'], $this->main_user->facebook_uid);
  }

  function testGetFriends()
  {
    $friends = (new FacebookProfile($this->additional_user))->getFriends();
    $this->assertEqual(count($friends), 1);
    $this->assertEqual($friends[0]['facebook_uid'], $this->main_user->facebook_uid);
  }

  function testGetRegisteredFriends()
  {
    $additional_user = $this->additional_user->copy();
    $this->additional_user->destroy();

    $friends = (new FacebookProfile($this->main_user))->getRegisteredFriends();
    $this->assertEqual(0, count($friends));

    $additional_user->save();
    $friends = (new FacebookProfile($this->main_user))->getRegisteredFriends();
    $this->assertEqual(count($friends), 1);
    $this->assertEqual($friends[0]->id, $additional_user->id);
  }

  function testGetPictures()
  {
    $pictures = (new FacebookProfile($this->additional_user))->getPictures();
    $this->assertTrue(count($pictures));
  }

  function testGetPictures_PicturesIfDefault()
  {
    // foo should have default avatar
    $pictures = (new FacebookProfile($this->main_user))->getPictures();
    $this->assertEqual(count($pictures), 0);
  }

  function testShareEndDay()
  {
    $day = $this->generator->day();
    $day->title ='testShareEndDay - Day';
    $day->save();

    $this->proxy_client->copyObjectPageToProxy($this->toolkit->getPagePath($day));

    (new FacebookProfileForTests($this->main_user))->shareDayBegin($day);
    (new FacebookProfileForTests($this->main_user))->shareDayEnd($day);
  }
}

class FacebookProfileForTests extends FacebookProfile
{
  protected function _getPageUrl($object)
  {
    $page_url = parent::_getPageUrl($object);
    return str_replace('onedayofmine.dev', 'stage.onedayofmine.com', $page_url);
  }
}
