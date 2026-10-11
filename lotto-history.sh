#! /bin/php
<?php

// Program: lotto-history.sh
//    Date: 10-Oct-2026
// Descrip: Download Florida Lotto Hitory

$url = "https://files.floridalottery.com/exptkt/lotto_winning-numbers-history.pdf";
$dest = __DIR__ . '/lotto-history.pdf';

// Download only if the file is missing or not downloaded today.
if (!file_exists($dest) || date('Y-m-d', filemtime($dest)) !== date('Y-m-d')) {

    $ch = curl_init($url);
    $fp = fopen($dest, 'wb');

    curl_setopt_array($ch, [
        CURLOPT_FILE           => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FAILONERROR    => true,
    ]);

    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

// Extract the historical data within the PDF using pdfparser.

require_once __DIR__ . '/pdfparser/alt_autoload.php';

$parser = new \Smalot\PdfParser\Parser();
$pdf    = $parser->parseFile(__DIR__ . '/lotto-history.pdf');
$pages  = $pdf->getPages();

$pattern = '/(\d{1,2}\/\d{1,2}\/\d{2,4})\s+((?:\d+\s+){5}\d+)\s+(?:LOTTO|LOTTO\s+DP)/i';
preg_match_all($pattern, $pages[0]->getText(), $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $dateObj = DateTime::createFromFormat('n/j/y', $match[1]);
    if (!$dateObj) {
        $dateObj = DateTime::createFromFormat('n/j/Y', $match[1]);
    }

    if ($dateObj) {
        $formattedDate = $dateObj->format('m/d');
        $numbersArray  = explode(' ', preg_replace('/\s+/', ' ', trim($match[2])));

        $paddedNumbersArray = array_map(function($num) {
            return str_pad($num, 2, '0', STR_PAD_LEFT);
        }, $numbersArray);

        echo "{$formattedDate} " . implode(' ', $paddedNumbersArray) . "\n";
    }
}
?>
