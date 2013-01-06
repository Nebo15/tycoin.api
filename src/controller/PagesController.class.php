<?php
lmb_require('limb/web_app/src/controller/lmbController.class.php');
lmb_require('tests/src/odStaticObjectMother.class.php');
lmb_require('src/Json.class.php');
lmb_require('src/model/Transaction.class.php');

class PagesController extends lmbController
{
  function doTransaction()
  {
    $id = $this->request->get('id');

    $this->transaction = Transaction::findById($id);
  }

  function doNotFound()
  {
    $this->response->setCode(404);
  }

  function doRedirect()
  {

  }
}
