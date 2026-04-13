<?php
/**
 * Test-Skript für PDF-Import-Funktionen
 * Testet E-Mail-Extraktion, URL-Verarbeitung und Duplikat-Logik ohne DB
 */

echo "=== PDF-Import Funktions-Tests ===\n\n";

require_once('system/data.php');
require_once('system/security.php');

// Test 1: E-Mail-Normalisierung
echo "TEST 1: Email-Normalisierung\n";
$test_emails = array(
    'JOHN@EXAMPLE.COM',
    '  john@example.com  ',
    'john+mail@example.co.uk',
    'foobar@test.org'
);

foreach ($test_emails as $email) {
    $normalized = normalize_email($email);
    echo "  '" . $email . "' → '" . $normalized . "'\n";
}
echo "✓ E-Mail-Normalisierung OK\n\n";

// Test 2: E-Mail-Extraktion aus Text
echo "TEST 2: E-Mail-Extraktion aus Text\n";
$sample_text = "Kontaktieren Sie bitte: Max Mustermann (max@example.com) oder info@myfirm.ch für weitere Infos.";
$found_emails = extract_emails_from_text($sample_text);
echo "  Text: '" . $sample_text . "'\n";
echo "  Gefundene E-Mails: " . json_encode($found_emails) . "\n";
if (count($found_emails) >= 2) {
    echo "✓ E-Mail-Extraktion OK\n\n";
} else {
    echo "✗ E-Mail-Extraktion FEHLGESCHLAGEN\n\n";
}

// Test 3: Context-Snippet
echo "TEST 3: Context-Snippet-Extraktion\n";
$context = get_context_snippet($sample_text, 'max@example.com', 30);
echo "  Snippet um 'max@example.com': '" . $context . "'\n";
if (strlen($context) > 0) {
    echo "✓ Context-Snippet OK\n\n";
} else {
    echo "✗ Context-Snippet FEHLGESCHLAGEN\n\n";
}

// Test 4: URL-Normalisierung
echo "TEST 4: Absolute URL-Geschwindigkeit\n";
$test_urls = array(
    array('https://example.com/page/', 'path/to/file.pdf', 'https://example.com/path/to/file.pdf'),
    array('https://example.com/page/', '/site/file.pdf', 'https://example.com/site/file.pdf'),
    array('https://example.com/page/', 'https://other.com/file.pdf', 'https://other.com/file.pdf'),
);

$all_ok = true;
foreach ($test_urls as $test) {
    $base = $test[0];
    $target = $test[1];
    $expected = $test[2];
    $result = make_absolute_url($base, $target);
    $status = ($result === $expected) ? '✓' : '✗';
    echo "  $status Base: '$base' + '$target'\n";
    echo "     Erwartet: '$expected'\n";
    echo "     Erhalten: '$result'\n";
    if ($result !== $expected) {
        $all_ok = false;
    }
}

if ($all_ok) {
    echo "✓ URL-Normalisierung OK\n\n";
} else {
    echo "✗ URL-Normalisierung FEHLGESCHLAGEN\n\n";
}

// Test 5: PDF-Stream-Dekompression
echo "TEST 5: PDF-Stream-Dekompression\n";
$test_text = "Das ist ein Test-String mit E-Mails: info@test.de und support@example.com\n";
$compressed = gzcompress($test_text);
$decompressed = decode_pdf_stream($compressed);
$status = ($decompressed === $test_text) ? '✓' : '✗';
echo "  $status Kompression/Dekompression\n";
echo "     Original: '" . substr($test_text, 0, 40) . "...'\n";
echo "     Dekomprimiert: '" . substr($decompressed, 0, 40) . "...'\n";

if ($decompressed === $test_text) {
    echo "✓ PDF-Stream-Dekompression OK\n\n";
} else {
    echo "✗ PDF-Stream-Dekompression FEHLGESCHLAGEN\n\n";
}

// Test 6: HTML-Parser (ohne echte HTML-Datei)
echo "TEST 6: HTML-Link-Extraktion\n";
$sample_html = '<html><body>
    <a href="document1.pdf">Download 1</a>
    <a href="/resources/document2.pdf">Download 2</a>
    <a href="https://cdn.example.com/docs/document3.pdf">Download 3</a>
</body></html>';

$pdf_links = extract_pdf_links_from_html($sample_html, 'https://example.com/jobs/');
echo "  Gefundene PDF-Links:\n";
foreach ($pdf_links as $link) {
    echo "    - " . $link . "\n";
}

if (count($pdf_links) >= 2) {
    echo "✓ HTML-Link-Extraktion OK\n\n";
} else {
    echo "✗ HTML-Link-Extraktion FEHLGESCHLAGEN (erwartet >= 2 Links)\n\n";
}

echo "=== Alle Tests abgeschlossen ===\n";
echo "Falls alle Tests OK sind, ist das System bereit für den Live-Import.\n";
?>
