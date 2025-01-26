<?php
include 'config.php';

// Membaca semua file konfigurasi hanya sekali di awal
$folderNames = explode(PHP_EOL, trim(file_get_contents($config['folder'])));
$htmlTemplate = file_get_contents($config['template']);
$keywords = explode(PHP_EOL, file_get_contents($config['keywords']));
$emot = explode(PHP_EOL, file_get_contents($config['emot']));
$title = explode(PHP_EOL, file_get_contents($config['title']));
$description = explode(PHP_EOL, file_get_contents($config['description']));

// Validasi jika file konfigurasi ada
if (!$folderNames || !$htmlTemplate || !$keywords || !$emot || !$title || !$description) {
    die("Terjadi kesalahan: Beberapa file konfigurasi tidak ditemukan.");
}

foreach ($folderNames as $folderName) {
    $folderName = trim($folderName);
    if (!empty($folderName)) {
        // Cek apakah folder sudah ada
        if (!file_exists($folderName)) {
            mkdir($folderName, 0777, true);

            // Pilih emotikon acak
            $randomEmotIndex = array_rand($emot);
            $selectedEmot = isset($emot[$randomEmotIndex]) ? trim($emot[$randomEmotIndex]) : '';

            // URL untuk folder
            $url = $config['url'] . urlencode($folderName);

            // Pilih judul acak dari array title
            $selectedTitle = !empty($title) ? trim($title[array_rand($title)]) : '';

            // Pilih deskripsi acak dari array description
            $selectedDescription = !empty($description) ? trim($description[array_rand($description)]) : '';

            // Ganti placeholder di template HTML
            $htmlContent = str_replace('{{ BRAND }}', $folderName, $htmlTemplate);
            $htmlContent = str_replace('{{ URL }}', $url, $htmlContent);
            $htmlContent = str_replace('{{ EMOT }}', $selectedEmot, $htmlContent);
            $htmlContent = str_replace('{{ TITLE }}', $selectedTitle, $htmlContent);
            $htmlContent = str_replace('{{ DESCRIPTION }}', $selectedDescription, $htmlContent);

            // Simpan file HTML ke dalam folder yang dibuat
            file_put_contents("$folderName/index.html", $htmlContent);
            echo "Folder '$folderName' dan file 'index.html' telah dibuat.<br>";
        } else {
            echo "Folder '$folderName' sudah ada.<br>";
        }
    }
}

// Jika file sitemap belum ada, buat sitemap baru
if (!file_exists($config['sitemap'])) {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    $urlset = $doc->createElement('urlset');
    $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    $doc->appendChild($urlset);

    foreach ($keywords as $keyword) {
        $url = $config['url'] . urlencode(trim($keyword)); 
        
        $urlElement = $doc->createElement('url');
        
        $locElement = $doc->createElement('loc', htmlspecialchars($url));
        $urlElement->appendChild($locElement);
        
        $lastmodElement = $doc->createElement('lastmod', date("Y-m-d"));
        $urlElement->appendChild($lastmodElement);

        $changefreqElement = $doc->createElement('changefreq', 'daily');
        $urlElement->appendChild($changefreqElement);

        $urlset->appendChild($urlElement);
    }

    // Simpan sitemap
    $doc->save($config['sitemap']);
    echo "Sitemap telah dibuat.<br>";
}

exit; // Skrip selesai
?>
