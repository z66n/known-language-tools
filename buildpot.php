<?php

/**
 * Script to build a .pot Gettext language file.
 *
 * Usage: cat MyFile.php | php buildpot.php > strings.pot
 *
 * @author Marcus Povey <marcus@marcus-povey.co.uk>
 * @author z66n <47192580+z66n@users.noreply.github.com>
 * @package Known-Language-Tools
 */


$in = file_get_contents("php://stdin");
$filenames = array_filter(array_map('trim', explode("\n", $in)));
$handled = [];

function getLineNumber($content, $charpos)
{
    list($before) = str_split($content, $charpos); // fetches all the text before the match

    return strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
}

foreach ($filenames as $filename) {

    $file = @file_get_contents($filename);

    if (!empty($file)) {
        if (preg_match_all('/_\((\'|")(.*)(\'|")(\)|,)/imsU', $file, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[2] as $translation) {

                $string = $translation[0];
                $offset = $translation[1];
                $linenumber = getLineNumber($file, $offset);

                $string = str_replace("\'","'",$string);
                $string = str_replace('\"','"',$string);
                //$normalised_string = str_replace('"', '\"', $string);
                $normalised_string = addcslashes($string, '"');

                if (!in_array($normalised_string, $handled)) {
                    echo "#: $filename:$linenumber\n";
                    echo "msgid \"$normalised_string\"\n";
                    echo "msgstr \"\"\n\n";
                    $handled[] = $normalised_string; // duplication prevention
                }
            }
        }
    }
}
