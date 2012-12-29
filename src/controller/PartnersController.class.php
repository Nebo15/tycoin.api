<?php
lmb_require('src/controller/BaseJsonController.class.php');
lmb_require('src/model/PartnerDeal.class.php');

class PartnersController extends BaseJsonController
{
  function doGuestDeals()
  {
	  $partners = $this->toolkit->getConf('partners')->partners;
	  $answer = [];
	  foreach($partners as $partner)
	  {
		  foreach($partner['deals'] as $deal)
		  {
			  unset($partner['deals']);
			  $deal['shop'] = $partner;
			  $answer[] = $deal;
		  }
	  }
    return $this->_answerOk($answer);
  }

  function doGuestItem()
  {
	  $id = $this->request->get('id');
		return $this->_answerOk($this->_loadDeal($id));
  }

  function doGuestBuy()
  {
	  $deal = (new PartnerDeal())->import((array) $this->_loadDeal($this->request->get('id')));
    $transaction = $this->toolkit->getMoneyService()->payment($this->_getUser(), $deal)->getHash();
	  if($transaction)
	    return $this->_answerOk($transaction);
	  else
		  return $this->_answerWithError('You have no enough coins');
  }

	protected function _loadDeal($id)
	{
		foreach($this->toolkit->getConf('partners')->partners as $partner)
		{
			foreach($partner['deals'] as $deal)
			{
				if($deal['id'] == $id)
				{
					unset($partner['deals']);
					$deal['shop'] = $partner;
					return $deal;
				}
			}
		}
	}
}
