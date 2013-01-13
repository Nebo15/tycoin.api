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
		if (!$this->transaction = Transaction::findById($id))
			return $this->forwardTo404();

		if (Transaction::TRANSFER != $this->transaction->type)
			return $this->forwardTo404();


	}

	function doNotFound()
	{
		$this->response->setCode(404);
	}
}
