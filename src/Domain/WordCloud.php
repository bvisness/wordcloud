<?php

namespace WordCloud\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use Equip\Env;
use WordCloud\Service\CloudMaker;
use WordCloud\Service\WordCount;

class WordCloud implements DomainInterface
{
    private $payload;

    private $wordCount;

    private $cloudMaker;

    private $env;

    public function __construct(PayloadInterface $payload, WordCount $wordCount, CloudMaker $cloudMaker, Env $env)
    {
        $this->payload = $payload;
        $this->wordCount = $wordCount;
        $this->cloudMaker = $cloudMaker;
        $this->env = $env;
    }

    public function __invoke(array $input)
    {
        if (empty($input['text'])) {
            return $this->payload
                ->withStatus(PayloadInterface::INVALID)
                ->withOutput([
                    'error' => 'Missing `text` field on input',
                ]);
        }

        $wordCounts = $this->wordCount->countWords($input['text']);

        $imageUrl = $this->cloudMaker->makeCloud(800, 600, 'images', $wordCounts);

        return $this->payload
            ->withStatus(PayloadInterface::OK)
            ->withMessages([
                'redirect' => $imageUrl,
            ]);
    }
}
