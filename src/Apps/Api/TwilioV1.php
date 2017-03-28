<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\Conversation;
use MrBill\Domain\TokenSet;
use MrBill\Model\Message;
use MrBill\Domain\DomainFactory;
use MrBill\PhoneNumber;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class TwilioV1
{
    /** @var Container */
    protected $slimContainer;

    /** @var string */
    protected $publicSiteUrl;

    /** @var Conversation */
    protected $conversation;

    /** @var TokenSet */
    protected $tokenSet;

    // Below represent input

    /** @var PhoneNumber */
    protected $phone;

    protected $accountId;

    /** @var string */
    protected $messageText;

    // Below represent output

    protected $responseText = '';

    protected $addExtendedWelcomeMessages = false;

    public function __construct(Container $slimContainer = null)
    {
        $this->slimContainer = $slimContainer;
    }

    public function __invoke(Request $request, Response $response, $args) : Response
    {
        assert((bool) $this->slimContainer);

        $body = $request->getParsedBody();
        $isValid = isset($body['MessageSid']) && isset($body['From']) && isset($body['Body']);

        if ($isValid) {
            $myContainer = $this->slimContainer['myContainer'];
            $this->run(
                $myContainer->get('domainFactory'),
                $myContainer->get('config')->publicUrl,
                new PhoneNumber($body['From']),
                $body['Body']
            );
        } else
            $this->responseText = 'Something is wrong.';

        return $response->write($this->getResult());
    }

    public function run(
        DomainFactory $domainFactory,
        string $publicSiteUrl,
        PhoneNumber $fromPhone,
        string $messageText
    ) : void {

        $this->accountId = $domainFactory->getAccountByPhoneNumber($fromPhone)->getByID();
        $this->phone = $fromPhone;
        $this->publicSiteUrl = $publicSiteUrl;
        $this->conversation = $domainFactory->getConversation($this->accountId, $this->phone);
        $this->tokenSet = $domainFactory->getTokenSet($this->accountId);
        $this->messageText = $messageText;

        $this->computeResult();
    }

    protected function computeResult()
    {
        $incomingMessage = Message::createWithEntropy($this->accountId, $this->phone, $this->messageText, time(), true);
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
            $replyMessage = Message::createWithEntropy(
                $this->accountId,
                $this->phone,
                $this->responseText,
                time(),
                false
            );
            $this->conversation->addMessage($replyMessage);
        }
    }

    public function getResult() : string
    {
        $result = '<?xml version="1.0" encoding="UTF-8" ?><Response>';

        if ($this->responseText)
            $result .= '<Message>' . $this->responseText . '</Message>';

        if ($this->addExtendedWelcomeMessages)
            $result .= '<Redirect>' . $this->publicSiteUrl . '/api/sleep.php?sleep=6&amp;content=welcome2</Redirect>';

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
            '5/5 For more info, see the FAQ ' . $this->publicSiteUrl . '/faq.html',
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
        $token = $this->tokenSet->getSecretIfActive(TokenSet::REPORT_ID) ?:
            $this->tokenSet->createActiveTokenForDocument(TokenSet::REPORT_ID);

        return 'Your report! ' . $this->publicSiteUrl . '/report?a=' .
            $this->accountId . '&amp;s=' . $token;
    }
}
