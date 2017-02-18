<?php

namespace MrBill\Api;

use MrBill\Message;
use MrBill\Messages;

class V1
{
    protected $responseText;

    public function __construct(array $post)
    {
        if (empty($post['MessageSid'])) {
            $this->responseText = 'Something is wrong.';
            return;
        }

        $from = (int) str_replace('+', '', $post['From']);

        $incomingMessage = new Message($from, $post['Body'], time(), true);
        if (!iterator_to_array(Messages::getHistoryForPhone($from))) {
            $this->responseText = $this->getWelcomeText();
        } elseif ($incomingMessage->isHelpRequest()) {
            $this->responseText = $this->getHelpText();
        }

        Messages::persistNewMessage($incomingMessage);

        if ($this->responseText) {
            $replyMessage = new Message($from, $this->responseText, time(), false);
            Messages::persistNewMessage($replyMessage);
        }
    }

    public function getResult() : string
    {
        return $this->responseText ? $this->wrapTextIntoResponse($this->responseText) : '';
    }

    protected function wrapTextIntoResponse(string $text) : string
    {
        return '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>' . $text . '</Message></Response>';
    }

    protected function getWelcomeText() : string
    {
        return 'Hello, I\'m Mr. Bill. Just let me know each time you spend $$, and I\'ll help you track expenses. Type "?" for help.' .
            '<Media>https://mrbill.kristopherwindsor.com/assets/mrbill.png</Media>';
    }

    protected function getHelpText() : string
    {
        return 'Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends';
    }
}
