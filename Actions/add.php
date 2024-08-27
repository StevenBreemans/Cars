<?php
if (isset($_GET['file'])) {
    $csvFileName = urldecode($_GET['file']);
    $csvFilePath = '../uploads/' . $csvFileName;

    if (file_exists($csvFilePath)) {
        $rows = array_map('str_getcsv', file($csvFilePath), array_fill(0, count(file($csvFilePath)), ';'));

        $header = array_shift($rows);

        $row = array_fill(0, count($header), '');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newRow = [];

            for ($i = 0; $i < count($header); $i++) {
                $newRow[] = trim($_POST['data'][$i], '"');
            }

            $rows[] = $newRow;

            array_unshift($rows, $header);

            $fp = fopen($csvFilePath, 'w');
            foreach ($rows as $fields) {
                $line = [];
                foreach ($fields as $field) {
                    $field = str_replace('"', '""', $field);
                    if (strpos($field, ';') !== false) {
                        $line[] = '"' . $field . '"';
                    } else {
                        $line[] = $field;
                    }
                }
                fwrite($fp, implode(';', $line) . "\n");
            }
            fclose($fp);

            header('Location: ../index.php?file=' . urlencode($csvFileName)); // Redirect back to index.php with file reference
            exit;
        }
    } else {
        echo '<div class="alert alert-danger mt-3">File not found.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Team</title>
    <link href="../css/bootstrap.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="container mt-5">
    <h1>Add New Team</h1>
    <form method="post">
        <?php if (!empty($row)): ?>
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                <tr>
                    <?php foreach ($header as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php foreach ($row as $key => $value): ?>
                        <td>
                            <input type="text" name="data[]" class="form-control" value="<?php echo htmlspecialchars($value); ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">Add Team</button>
                <a href="../index.php?file=<?php echo urlencode($csvFileName); ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>

        <?php else: ?>
            <div class="alert alert-danger">File not found.</div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
