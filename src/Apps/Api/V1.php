<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\Conversation;
use MrBill\Model\Message;
use MrBill\Domain\DomainFactory;
use MrBill\PhoneNumber;

class V1
{
    /** @var PhoneNumber */
    protected $phone;

    /** @var Conversation */
    protected $conversation;

    protected $responseText = '';
    protected $addExtendedWelcomeMessages = false;

    public function __construct(DomainFactory $domainFactory, array $post)
    {
        if (empty($post['MessageSid']) || empty($post['From']) || empty($post['Body'])) {
            $this->responseText = 'Something is wrong.';
            return;
        }

        $this->phone = new PhoneNumber($post['From']);
        $this->conversation = $domainFactory->getConversation($this->phone);

        $incomingMessage = Message::createWithEntropy($this->phone, $post['Body'], time(), true);
        $messageWithMeaning = $this->conversation->addMessage($incomingMessage);

        $isTenthExpense = $messageWithMeaning->isExpenseMessage() && $this->conversation->totalExpenseMessages == 10;

        if ($messageWithMeaning->isFirstMessage()) {
            $this->responseText = $this->getWelcomeText();
            $this->addExtendedWelcomeMessages = true;

        } elseif ($messageWithMeaning->isReportRequest() || $isTenthExpense) {
            $this->responseText = $this->createReportAndGetReply();

        } elseif ($messageWithMeaning->isExpenseMessage()) {
            $this->responseText = $this->getExpenseReply();

        } elseif ($messageWithMeaning->isHelpRequest()) {
            $this->responseText = $this->getHelpText($this->conversation->totalHelpRequests - 1);

        } elseif ($messageWithMeaning->hasNoKnownMeaning() && $this->conversation->totalIncomingMessages <= 3) {
            $this->responseText = $this->getUnknownMessageReply();
        }

        if ($this->responseText) {
            $replyMessage = Message::createWithEntropy($this->phone, $this->responseText, time(), false);
            $this->conversation->addMessage($replyMessage);
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

    protected function getExpenseReply() : string
    {
        if ($this->conversation->totalExpenseMessages == 1)
            return 'Got it. I\'ll send you a report once I\'ve got a few more expenses.';
        if ($this->conversation->totalExpenseMessages == 5)
            return 'Keep them coming!';
        return '';
    }

    protected function getUnknownMessageReply() : string
    {
        return 'Not sure what you mean? For each expense you have, text me the price followed by a #hashtag';
    }

    protected function createReportAndGetReply() : string
    {
        $token = $this->conversation->getOrCreateActiveReportToken();

        return 'Your report! https://mrbill.kristopherwindsor.com/report/1?p=' .
            $this->phone . '&amp;s=' . $token->secret;
    }
}
