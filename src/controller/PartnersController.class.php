<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/PartnerDeal.class.php');

class PartnersController extends BaseJsonController
{
	function doDeals()
	{
		$partners = $this->toolkit->getConf('partners')->partners;
		$answer = [];
		foreach ($partners as $partner)
		{
			foreach ($partner['deals'] as $deal)
			{
				unset($partner['deals']);
				$deal['shop'] = $partner;
				$answer[] = $deal;
			}
		}
		return $this->_answerOk($answer);
	}

	function doItem()
	{
		$id = $this->request->get('id');
		return $this->_answerOk($this->_loadDeal($id));
	}

	function doBuy()
	{
		if (!$this->request->isPost())
			return $this->_answerNotPost();

		$deal = (new PartnerDeal())->import((array)$this->_loadDeal($this->request->get('id')));
		$transaction = $this->toolkit->getMoneyService()->payment($this->_getUser(), $deal);
		if ($transaction) {
      // $this->toolkit->getFacebookProfile($this->_getUser())->shareExchange($transaction, $deal);
			return $this->_answerOk($transaction->getHash());
    }
		else
			return $this->_answerWithError('You have no enough coins');
	}

	protected function _loadDeal($id)
	{
		foreach ($this->toolkit->getConf('partners')->partners as $partner)
		{
			foreach ($partner['deals'] as $deal)
			{
				if ($deal['id'] == $id)
				{
					unset($partner['deals']);
					$deal['shop'] = $partner;
					return $deal;
				}
			}
		}
	}
}
