<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\Conversation;
use MrBill\Model\Message;
use MrBill\Domain\ConversationFactory;
use MrBill\PhoneNumber;

class V1
{
    /** @var Conversation */
    protected $conversation;

    protected $responseText = '';
    protected $addExtendedWelcomeMessages = false;

    public function __construct(ConversationFactory $conversationFactory, array $post)
    {
        if (empty($post['MessageSid']) || empty($post['From']) || empty($post['Body'])) {
            $this->responseText = 'Something is wrong.';
            return;
        }

        $from = new PhoneNumber($post['From']);
        $this->conversation = $conversationFactory->getConversation($from);

        $incomingMessage = Message::createWithEntropy($from, $post['Body'], time(), true);
        $messageWithMeaning = $this->conversation->persistNewMessage($incomingMessage);

        if ($messageWithMeaning->isFirstMessage()) {
            $this->responseText = $this->getWelcomeText();
            $this->addExtendedWelcomeMessages = true;
        } elseif ($messageWithMeaning->isHelpRequest()) {
            $this->responseText = $this->getHelpText($this->conversation->totalHelpRequests - 1);
        }

        if ($this->responseText) {
            $replyMessage = Message::createWithEntropy($from, $this->responseText, time(), false);
            $this->conversation->persistNewMessage($replyMessage);
        }
    }

    public function getResult() : string
    {
        $result = '<?xml version="1.0" encoding="UTF-8" ?><Response>';

        if ($this->responseText)
            $result .= '<Message>' . $this->responseText . '</Message>';

        if ($this->addExtendedWelcomeMessages)
            $result .= '<Redirect>https://mrbill.kristopherwindsor.com/api/sleep.php?sleep=6' .
                '&amp;content=welcome2</Redirect>';

        $result .= '</Response>';
        return $result;
    }

    protected function getWelcomeText() : string
    {
        return 'Hi, I\'m Mr. Bill. Just text me each time you spend $$, and I\'ll help you track expenses. ' .
            'That\'s right... you keep track of your expenses by texting them to me.';
    }

    protected function getHelpText(int $index) : string
    {
        return [
            '1/5 Let\'s see how I can help you! Text "?" again to cycle through the help messages.',
            '2/5 Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends',
            '3/5 The hashtags are important for categorizing expenses. The description is optional.',
            '4/5 Once you have given me a few bills, I\'ll show you a report about your spending.',
            '5/5 For more info, see the FAQ https://mrbill.kristopherwindsor.com/faq.php',
        ][$index % 5];
    }
}
