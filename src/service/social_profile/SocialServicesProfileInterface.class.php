<?php
interface SocialServicesProfileInterface
{
  function __construct(User $user);
  function getProvider();
  function getInfo_Raw();
  function getInfo();
  function getFriends();
  function getRegisteredFriends();
  function getPictures();
}
