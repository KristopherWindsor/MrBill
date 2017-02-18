<?php

namespace MrBill\Api;

use MrBill\Message;
use MrBill\Messages;

class V1
{
	public $result;

	public function __construct(array $post)
	{
		if (empty($post['MessageSid'])) {
			$this->result = '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Something is wrong.</Message></Response>';
			return;
		}

		$from = (int) str_replace('+', '', $post['From']);

		$message = new Message($from, $post['Body'], time(), true);
		if (!iterator_to_array(Messages::getHistoryForPhone($from))) {
			$this->result = '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Welcome!!</Message></Response>';
		} elseif ($message->isHelpRequest()) {
			$this->result = '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Help text goes here.</Message></Response>';
		}

		Messages::persistNewMessage($message);
	}
}
