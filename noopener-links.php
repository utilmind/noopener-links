<?php

// if $local_domains specified w/o protocol:// prefix, we checking all protocols before "://".
function noopener_links(string $html, array $local_domains = []): string {
    static $NULL_CHAR = "\x00";

    /*  ATTN! The quotes (") in text could be already escaped.
        We should gracefully process text in either cases with escaped quites and unescaped.
        So, we:
            Check whether text contain escaped quotes.
                - No? Just process as is.
                - Yes? Check, whether text has unescaped quotes.
                        - No? convert \" to ", process text, then return " back to \".
                        - Yes? Convert unescaped " into some unused character, eg \x00. Then convert \" to " to process the text, then return " back to \" and \x00 to ".
    */
    $has_escaped_quotes = -1 !== strpos($html, '\"');
    if ($has_escaped_quotes) {
        $has_unescaped_quotes = preg_match('/(?<!\\\\)"/', $html);
        if ($has_unescaped_quotes) {
            $html = preg_replace('/(?<!\\\\)"/', $NULL_CHAR, $html);
        }
        $html = str_replace('\"', '"', $html);
    }

    if ($local_domains) {
        $domains = [];
        foreach ($local_domains as $domain) {
            if ($domain) {
                $q_domain = preg_quote($domain, '/');
                $domains[] = false === strpos($domain, '://')
                    ? '\w*?:\/\/'.$q_domain
                    : $q_domain;
            }
        }        

        $domains = implode('|', $domains);

        // (a href) Convert all links to our domain into local links
        $html = preg_replace_callback(
            '/<a\s+([^>]*href=\s*("|\'))(' . $domains . ')(\/[^\\2]*)"/i',
            function ($matches) {
                $attributes = $matches[1];
                $path = $matches[4];
                return '<a ' . $attributes . $path . '"';
            }, $html);

        // (img src) Convert all links to our domain into local links
        $html = preg_replace_callback(
            '/<img\s+([^>]*src=\s*("|\'))(' . $domains . ')(\/[^\\2]*)"/i',
            function ($matches) {
                $attributes = $matches[1];
                $path = $matches[4];
                return '<img ' . $attributes . $path . '"';
            }, $html);
    }

    // External links
    $html = preg_replace_callback(
        '/<a\s+([^>]*href="[^"]*:\/\/[^"]*"[^>]*)>/i',
        function ($matches) {
            $attributes = $matches[1];

            // remove all target="..." except the first one
            //$attributes = preg_replace('/\s+target="[^"]*"/i', '', $attributes, 1);
            // No, remove all target="..."
            $attributes = preg_replace('/\s+target="[^"]*"/i', '', $attributes);

            // add target="_blank"
            //if (!preg_match('/\s+target="_blank"/', $attributes)) {
                $attributes .= ' target="_blank"';
            //}

            // rel
            if (preg_match('/\brel="([^"]*)"/i', $attributes, $rel_matches)) {
                $rel = $rel_matches[1];
                if (!preg_match('/\bnoopener\b/', $rel)) {
                    $rel .= ' noopener';
                }
                $attributes = preg_replace('/\brel="[^"]*"/i', 'rel="' . trim($rel) . '"', $attributes);
            }else {
                $attributes .= ' rel="noopener"';
            }

            return '<a ' . $attributes . '>';
        }, $html);

    // Internal links
    $html = preg_replace_callback(
        '/<a\s+([^>]*href="(?:(?!:\/\/)[^"]*)"[^>]*)>/i',
        //'/<a\s+([^>]*href="(?!http|ftp|mailto|tel|://)[^"]*"[^>]*)>/i',
        //'/<a\s+([^>]*href="([^"]*)(?!:)[^"]*"[^>]*)>/i',
        function ($matches) {
            $attributes = $matches[1];

            // target="_blank"
            $attributes = preg_replace('/\btarget="_blank"\b\s*/i', '', $attributes);

            // rel
            $attributes = preg_replace_callback(
                '/\brel="([^"]*)"/i',
                function ($rel_matches) {
                    $rel = trim(preg_replace('/\bnoopener\b/', '', $rel_matches[1]));
                    return $rel ? 'rel="' . $rel . '"' : '';
                },
                $attributes
            );

            return '<a ' . trim($attributes) . '>';
        }, $html);

    if ($has_escaped_quotes) {
        $html = str_replace('"', '\"', $html);
        if ($has_unescaped_quotes) {
            $html = str_replace($NULL_CHAR, '"', $html);
        }
    }

    return $html;
}

/*
$html = '<a rel="nofollow noopener" href="https://silkcards.com/test/page.html">External Link</a>
<a rel="noopener" href="/local/page">Internal Link</a>
<a href="https://another.com" target="_blank" rel="nofollow">Another External Link</a>
<a href="/another-local/page" target="_blank" rel="noopener noreferer">Another Internal Link</a>
<img src="https://silkcards.com/i/image.gif" />
<img src="https://silkcardspro.com/i/image.gif" />
<img src="https://the-domain.com/img/image.gif" />
';

echo noopener_links($html, ['https://silkcards.com', 'https://silkcardspro.com']);
*/