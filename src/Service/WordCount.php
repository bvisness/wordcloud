<?php

namespace WordCloud\Service;

class WordCount
{
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

        return $wordcounts;
    }
}
