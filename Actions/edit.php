<?php
if (isset($_GET['id']) && isset($_GET['file'])) {
    $rowIndex = $_GET['id'];
    $csvFileName = urldecode($_GET['file']);
    $csvFilePath = '../uploads/' . $csvFileName;

    if (file_exists($csvFilePath)) {
        $rows = array_map('str_getcsv', file($csvFilePath));

        $header = array_shift($rows);

        if (isset($rows[$rowIndex])) {
            $row = [];
            foreach ($rows[$rowIndex] as $column) {
                $row = array_merge($row, explode(';', $column));
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $newRow = [];
                $columnsPerField = count($row) / count($header);

                for ($i = 0; $i < count($header); $i++) {
                    $fieldData = [];
                    for ($j = 0; $j < $columnsPerField; $j++) {
                        $fieldData[] = $_POST['data'][$i * $columnsPerField + $j];
                    }
                    $newRow[] = implode(';', $fieldData);
                }

                $rows[$rowIndex] = $newRow;

                array_unshift($rows, $header);

                $fp = fopen($csvFilePath, 'w');
                foreach ($rows as $fields) {
                    fputcsv($fp, $fields);
                }
                fclose($fp);

                header('Location: ../index.php?file=' . urlencode($csvFileName));
                exit;
            }
        } else {
            echo '<div class="alert alert-danger mt-3">Row not found.</div>';
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
    <title>Edit Row</title>
    <link href="../css/bootstrap.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="container mt-5">
    <h1>Edit Row</h1>
    <form method="post">
        <?php if (!empty($row)): ?>
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                <tr>
                    <?php foreach (explode(';', implode(';', $header)) as $column): ?>
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
                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                <a href="../index.php?file=<?php echo urlencode($csvFileName); ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>

        <?php else: ?>
            <div class="alert alert-danger">Row not found.</div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
