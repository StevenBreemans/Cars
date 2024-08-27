<?php
if (isset($_GET['id']) && isset($_GET['file'])) {
    $rowIndex = $_GET['id'];
    $csvFileName = urldecode($_GET['file']);
    $csvFilePath = '../uploads/' . $csvFileName;

    if (file_exists($csvFilePath)) {
        $rows = array_map('str_getcsv', file($csvFilePath));

        $header = array_shift($rows);
        if (isset($rows[$rowIndex])) {
            $duplicateRow = $rows[$rowIndex];
            array_splice($rows, $rowIndex + 1, 0, [$duplicateRow]);


            array_unshift($rows, $header);

            $fp = fopen($csvFilePath, 'w');
            foreach ($rows as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);
        }

        header('Location: ../index.php?file=' . urlencode($csvFileName));
        exit;
    } else {
        echo '<div class="alert alert-danger mt-3">File not found.</div>';
    }
}
?>
