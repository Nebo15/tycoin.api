<?php
lmb_require('src/model/User.class.php');

class odExportHelper
{
  protected $current_user;

  function __construct(User $current_user = null)
  {
    $this->current_user = $current_user;
  }

  ############### User ###############
  function exportUser(User $user)
  {
    $exported = $user->exportForApi();

    $is_owner = $this->current_user && $this->current_user->id == $user->id;

    if($is_owner)
      $exported->email = $user->email;

    return $exported;
  }

  function exportUserItem(User $user)
  {
    $exported = $this->exportUserSubentity($user);

    if($this->current_user && $this->current_user->id != $user->id)
      $exported->following = (bool) UserFollowing::isUserFollowUser($user, $this->current_user);

    return $exported;
  }

  function exportUserSubentity(User $user)
  {
    $exported = $user->exportForApi();

    unset($exported->birthday);

    return $exported;
  }

  function exportUserItems($users)
  {
    $following = [];
    if($this->current_user)
      $following = UserFollowing::isUserFollowUsers($this->current_user, $users);

    $exported = [];
    foreach($users as $followed)
    {
      $export = $this->exportUserItem($followed);

      if(count($following))
        $export->following = (bool) $following[$followed->id];

      $exported[] = $export;
    }

    return $exported;
  }

  function attachUserSubentityToExport(User $user, stdClass $exported)
  {
    $exported->user = $this->exportUserItems([$user])[0];
    unset($exported->user_id);
  }

  function exportFacebookUserItems(array $facebook_users)
  {
    $exported_list = [];
    $users = User::findByFacebookUid(lmbArrayHelper::getColumnValues('facebook_uid', $facebook_users));
    $users = lmbArrayHelper::makeKeysFromColumnValues('facebook_uid', $users);

    foreach($facebook_users as $facebook_user)
    {
      $exported            = new stdClass;
      $exported->uid       = $facebook_user['facebook_uid'];
      $exported->name      = $facebook_user['name'];
      $exported->image_50  = $facebook_user['pic'];
      $exported->image_150 = $facebook_user['pic_big'];

      if(isset($users[$exported->uid]))
      {
        $user = $users[$exported->uid];
        $exported->user = $user->exportForApi();
      }
      else
        $exported->user = null;

      $exported_list[] = $exported;
    }
    return $exported_list;
  }

  function exportShopListItem(Shop $shop)
  {
    return $shop->exportForApi();
  }

  function exportShopsList(array $shops)
  {
    $exported = [];
    foreach($shops as $shop)
      $exported[] = $this->exportShopListItem($shop);

    return $exported;
  }

  ############### Complain ###############
  function exportComplaint(Complaint $complaint)
  {
    return $this->exportComplaintSubentity($complaint);
  }

  function exportComplaintListItem(Complaint $complaint)
  {
    return $this->exportComplaintSubentity($complaint);
  }

  function exportComplaintSubentity(Complaint $complaint)
  {
    return $complaint->exportForApi();
  }

  function exportComplaintItems($complaints)
  {
    $exported = [];
    foreach ($complaints as $complaint) {
      $exported[] = $this->exportComplaintListItem($complaint);
    }
    return $exported;
  }
}
