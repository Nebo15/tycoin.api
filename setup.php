<?php
error_reporting(E_ALL | ~E_STRICT);

$app_dir = __DIR__;
$libs_dir = $app_dir . '/lib';

set_include_path(implode(PATH_SEPARATOR,
  array($app_dir, $libs_dir, get_include_path())
));

if(!defined('LIMB_PACKAGES_DIR'))
  define('LIMB_PACKAGES_DIR', dirname(__FILE__) . '/lib/limb');

require_once('limb/core/common.inc.php');

if(file_exists(dirname(__FILE__) . '/setup.override.php'))
  require_once(dirname(__FILE__) . '/setup.override.php');

lmb_package_require('cms');
lmb_package_require('cache2');
lmb_package_require('profile');
lmb_package_require('mail');

lmb_env_setor('APP_DIR', $app_dir);
lmb_env_setor('LIBS_DIR', $libs_dir);
lmb_env_setor('LIMB_VAR_DIR', $app_dir . '/var/');

if(array_key_exists('HTTP_NAME', $_SERVER))
  lmb_env_setor('HOST_URL', 'http://'.$_SERVER['HTTP_HOST'].'/');

lmb_env_setor('LIMB_APP_MODE' , 'production');

define('COIN_USUAL', 'usual');
define('COIN_BIG', 'big');

lmb_require('limb/toolkit/src/lmbToolkit.class.php');
lmb_require('src/toolkit/odTools.class.php');
lmbToolkit :: merge(new odTools());
lmb_require('limb/dbal/src/toolkit/lmbDbTools.class.php');
lmbToolkit :: merge(new lmbDbTools());

lmb_require('limb/core/src/lmbSys.class.php');
lmb_require('src/model/base/BaseModel.class.php');

lmb_env_set('AIRBRAKE_KEY', '8127215e404ca993f5b90df75a9a13ad');
lmb_require('src/service/AirBrakeErrorHandler.class.php');

if(lmb_env_get('LIMB_APP_MODE') != 'devel')
{
  lmbErrorGuard::registerExceptionHandler(array('AirBrakeErrorHandler', 'onException'));
  lmbErrorGuard::registerErrorHandler(array('lmbErrorGuard', 'convertErrorsToExceptions'));
}
