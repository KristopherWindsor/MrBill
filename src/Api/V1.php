<?php

namespace MrBill\Api;

use MrBill\Message;
use MrBill\ConversationFactory;
use MrBill\PhoneNumber;

class V1
{
    protected $messageProvider;

    protected $totalHelpRequests = 0;
    protected $totalMessageCount = 0;

    protected $responseText = '';
    protected $addMrBillPicture = false;

    public function __construct(ConversationFactory $messageProvider, array $post)
    {
        $this->messageProvider = $messageProvider;

        if (empty($post['MessageSid']) || empty($post['From']) || empty($post['Body'])) {
            $this->responseText = 'Something is wrong.';
            return;
        }

        $from = new PhoneNumber($post['From']);
        $this->gatherInfoAboutPhone($from);

        $incomingMessage = new Message($from, $post['Body'], time(), true);
        if (!$this->totalMessageCount) {
            $this->responseText = $this->getWelcomeText();
            $this->addMrBillPicture = true;
        } elseif ($incomingMessage->isHelpRequest()) {
            $this->responseText = $this->getHelpText($this->totalHelpRequests);
        }

        $this->messageProvider->persistNewMessage($incomingMessage);

        if ($this->responseText) {
            $replyMessage = new Message($from, $this->responseText, time(), false);
            $this->messageProvider->persistNewMessage($replyMessage);
        }
    }

    protected function gatherInfoAboutPhone(PhoneNumber $from) : void
    {
        foreach ($this->messageProvider->getHistoryForPhone($from) as $message) {
            // First message is not processed as a help request
            if ($this->totalMessageCount && $message->isHelpRequest()) {
                $this->totalHelpRequests++;
            }
            $this->totalMessageCount++;
        }
    }

    public function getResult() : string
    {
        $result = '<?xml version="1.0" encoding="UTF-8" ?><Response>';

        if ($this->responseText)
            $result .= '<Message>' . $this->responseText . '</Message>';

        if ($this->addMrBillPicture)
            $result .= '<Redirect>https://mrbill.kristopherwindsor.com/assets/mrbill.xml</Redirect>';

        $result .= '</Response>';
        return $result;
    }

    protected function getWelcomeText() : string
    {
        return 'Hello, I\'m Mr. Bill. Just let me know each time you spend $$, and I\'ll help you track expenses. Type "?" for help.';
    }

    protected function getHelpText(int $index) : string
    {
        return [
            '1/4 Let\'s see how I can help you! Text "?" again to cycle through the help messages.',
            '2/4 Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends',
            '3/4 The hashtags are important for categorizing expenses. The description is optional.',
            '4/4 Once you have given me a few bills, I\'ll show you a report about your spending.',
            'For more info, see the FAQ https://mrbill.kristopherwindsor.com/faq.php',
        ][$index % 5];
    }
}
