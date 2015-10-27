<?php
namespace Art\JobtestBundle\Utils;

class Jobtest
{
    static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('/[^\pLd]+/u', '-', $text);
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }
        $text = strtolower($text);
        $text = preg_replace('/[^\w\-]+/', '', $text);
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}