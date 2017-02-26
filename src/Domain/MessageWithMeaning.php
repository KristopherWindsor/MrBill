<?php

namespace MrBill\Domain;

use MrBill\Model\Message;

class MessageWithMeaning
{
    protected const IN_EXPENSE        = 'ie';
    protected const IN_FIRST_MESSAGE  = 'ifm';
    protected const IN_HELP_REQUEST   = 'ih';
    protected const IN_REPORT_REQUEST = 'ir';
    protected const IN_UNKNOWN        = 'iu';
    protected const OUT_MISC          = 'om';
    protected const OUT_QUESTION      = 'oq';

    /** @var Message */
    public $message;

    public $meaning;

    public function __construct(
        Message $message,
        int $totalPrecedingIncomingMessages
      // Future: add parameters like isThereARecentlyAskedQuestion
    ) {
        $this->message = $message;

        $text = strtolower(trim($message->message));

        $hasExpenseRecords = (bool) (new ExpensesFromMessageParser)->parse($message);

        if ($message->isFromUser) {
            if (!$totalPrecedingIncomingMessages)
                $this->meaning = self::IN_FIRST_MESSAGE;
            elseif ($text == '?')
                $this->meaning = self::IN_HELP_REQUEST;
            elseif ($text == 'report')
                $this->meaning = self::IN_REPORT_REQUEST;
            elseif ($hasExpenseRecords)
                $this->meaning = self::IN_EXPENSE;
            else
                $this->meaning = self::IN_UNKNOWN;
        } else {
            $this->meaning = self::OUT_MISC;
        }
    }

    public function isExpenseMessage()   {return $this->meaning == self::IN_EXPENSE;}
    public function isFirstMessage()     {return $this->meaning == self::IN_FIRST_MESSAGE;}
    public function isHelpRequest()      {return $this->meaning == self::IN_HELP_REQUEST;}
    public function isReportRequest()    {return $this->meaning == self::IN_REPORT_REQUEST;}
    public function hasNoKnownMeaning()  {return $this->meaning == self::IN_UNKNOWN;}
    public function isOutgoingMisc()     {return $this->meaning == self::OUT_MISC;}
    public function isOutgoingQuestion() {return $this->meaning == self::OUT_QUESTION;}
}
