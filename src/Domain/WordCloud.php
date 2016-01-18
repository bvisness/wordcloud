<?php

namespace WordCloud\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use WordCloud\Service\CloudMaker;
use WordCloud\Service\WordCount;

class WordCloud implements DomainInterface
{
    private $payload;

    private $wordCount;

    private $cloudMaker;

    public function __construct(PayloadInterface $payload, WordCount $wordCount, CloudMaker $cloudMaker)
    {
        $this->payload = $payload;
        $this->wordCount = $wordCount;
        $this->cloudMaker = $cloudMaker;
    }

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

        $imageId = uniqid();
        $this->cloudMaker->makeCloud(800, 600, "images/$imageId.png", $wordCounts);

        return $this->payload
            ->withStatus(PayloadInterface::OK)
            ->withMessages([
                'redirect' => "/images/$imageId.png",
            ]);
    }
}
