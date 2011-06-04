<?php
/**
 * ObfuscateEmail plugin for MODx.
 * By Aloysius Lim.
 *
 * Version 0.9.2 (Jun 03, 2011) by W. Shawn Wilkerson
 * Updated Snippet to be compliant with changes in Revolution 2.1
 *
 * Version: 0.9.1 (Apr 15, 2007)
 *
 * This plugin searches for all email addresses and "mailto:" strings in the
 * html output, both inside and outside href attributes. In other words, it also
 * encodes link text.
 *
 * It can find all common email addresses as specified by RFC2822, including all
 * unusual but allowed characters. Any email addresses that satisfy the
 * the construct below will be detected:
 *
 * The plugin than randomly leaves 10% of the characters alone, encodes 45% of
 * them in decimal, and 45% of them in hexadecimal.
 *
 * Changelog:
 * Version 0.9.1 (Apr 15, 2007)
 * Fixed: Regex for atom allowed empty string.
 *
 * Version 0.9.0 (Mar 16, 2007)
 * Original release.
 **/

function email_regex()
{
    /* Set up email regex that partially conforms to RFC2822
    * (the ignored parts are indicated):
    *
    * addr-spec       =       local-part "@" domain
    *
    * local-part      =       dot-atom
    *                         / quoted-string              // Ignored
    *                         / obs-local-part             // Ignored
    *
    * domain          =       dot-atom
    *                         / domain-literal             // Ignored
    *                         / obs-domain                 // Ignored
    *
    * dot-atom        =       [CFWS] dot-atom-text [CFWS]  // Ignored CFWS
    *
    * dot-atom-text   =       1*atext *("." 1*atext)
    * atext           =       ALPHA / DIGIT / ; Any character except controls,
    *                         "!" / "#" /     ;  SP, and specials.
    *                         "$" / "%" /     ;  Used for atoms
    *                         "&" / "'" /
    *                         "*" / "+" /
    *                         "-" / "/" /
    *                         "=" / "?" /
    *                         "^" / "_" /
    *                         "`" / "{" /
    *                         "|" / "}" /
    *                         "~"
    */

    $atom = "[-!#$%&'*+/=?^_`{|}~0-9A-Za-z]+";
    $email_half = $atom . '(?:\\.' . $atom . ')*';
    $email = $email_half . '@' . $email_half;
    $email_regex = '<(' . $email . ')>';
    return $email_regex;
}

function replaceEntities($matches)
{
    $address = html_entity_decode($matches[1]);
    $replaced = '';

    for ($i = 0; $i < strlen($address); $i++) {
        $char = $address[$i];
        $r = rand(0, 100);

        # roughly 10% raw, 45% hex, 45% dec
        if ($r > 90) {
            $replaced .= $char;
        }
        else if ($r < 45) {
            $replaced .= '&#x' . dechex(ord($char)) . ';';
        }
        else
        {
            $replaced .= '&#' . ord($char) . ';';
        }
    }

    return $replaced;
}

$output = &$modx->resource->_output;
$output = preg_replace_callback(email_regex(), "replaceEntities", $output);
$output = preg_replace_callback('/(mailto:)/', "replaceEntities", $output);