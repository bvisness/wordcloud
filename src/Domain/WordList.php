<?php

namespace WordCloud\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use WordCloud\Service\WordCount;

class WordList implements DomainInterface
{
    /**
     * @var PayloadInterface
     */
    private $payload;

    private $wordCount;

    /**
     * @param PayloadInterface $payload
     */
    public function __construct(PayloadInterface $payload, WordCount $wordCount)
    {
        $this->payload = $payload;
        $this->wordCount = $wordCount;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(array $input)
    {
        if (empty($input['text'])) {
            return $this->payload
                ->withStatus(PayloadInterface::INVALID)
                ->withOutput([
                    'error' => 'Missing `text` field on input'
                ]);
        }

        $wordCounts = $this->wordCount->countWords($input['text']);

        return $this->payload
            ->withStatus(PayloadInterface::OK)
            ->withOutput([
                'wordcounts' => $wordCounts,
            ]);
    }
}
