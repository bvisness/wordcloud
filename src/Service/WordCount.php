<?php

namespace WordCloud\Service;

use Equip\Env;

class WordCount
{
    private $env;

    public function __construct(Env $env) {
        $this->env = $env;
    }

    public function countWords($text)
    {
        $words = [];
        preg_match_all("/(\w|')+/", $text, $words);

        $wordcounts = [];
        foreach (current($words) as $word) {
            $word = strtolower($word);

            if (!array_key_exists($word, $wordcounts)) {
                $wordcounts[$word] = 0;
            }

            $wordcounts[$word]++;
        }

        arsort($wordcounts);

        // Remove boring words
        $ignoreFile = file_get_contents($this->env['STATIC_HOSTING_URL'] . 'ignore.txt');
        $ignoreWords = [];
        preg_match_all('/.+$/m', $ignoreFile, $ignoreWords);
        $ignoreWords = array_flip(current($ignoreWords));

        foreach ($wordcounts as $word => $count) {
            if (isset($ignoreWords[$word])) {
                unset($wordcounts[$word]);
            }
        }

        return $wordcounts;
    }
}
