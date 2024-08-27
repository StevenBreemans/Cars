<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Project</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <style>
        th {
            cursor: pointer;
        }

    </style>
    <script>
        function searchTable() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('csvTable');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdArray = tr[i].getElementsByTagName('td');
                let rowContainsFilter = false;

                for (let j = 0; j < tdArray.length; j++) {
                    let td = tdArray[j];
                    if (td) {
                        if (td.textContent.toLowerCase().indexOf(filter) > -1) {
                            rowContainsFilter = true;
                            break;
                        }
                    }
                }

                if (rowContainsFilter) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        function sortTable(n, isNumeric) {
            let table = document.getElementById("csvTable");
            let rows = table.rows;
            let switching = true;
            let shouldSwitch, i;
            let dir = "asc";
            let switchcount = 0;

            while (switching) {
                switching = false;
                let rowsArray = Array.from(rows).slice(1);

                for (i = 0; i < rowsArray.length - 1; i++) {
                    shouldSwitch = false;
                    let x = rowsArray[i].getElementsByTagName("TD")[n];
                    let y = rowsArray[i + 1].getElementsByTagName("TD")[n];

                    if (isNumeric) {
                        if (dir === "asc") {
                            if (parseFloat(x.innerHTML) > parseFloat(y.innerHTML)) {
                                shouldSwitch = true;
                                break;
                            }
                        } else if (dir === "desc") {
                            if (parseFloat(x.innerHTML) < parseFloat(y.innerHTML)) {
                                shouldSwitch = true;
                                break;
                            }
                        }
                    } else {
                        if (dir === "asc") {
                            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                                shouldSwitch = true;
                                break;
                            }
                        } else if (dir === "desc") {
                            if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                                shouldSwitch = true;
                                break;
                            }
                        }
                    }
                }
                if (shouldSwitch) {
                    rowsArray[i].parentNode.insertBefore(rowsArray[i + 1], rowsArray[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount === 0 && dir === "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <h1>Upload CSV File</h1>
    <form action="index.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="csvFile" class="form-label">Choose CSV File</label>
            <input class="form-control" type="file" name="csvFile" id="csvFile" accept=".csv" required>
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Upload and Display</button>
    </form>

    <?php
    $csvFileName = '';

    if (isset($_POST['upload'])) {
        if (is_uploaded_file($_FILES['csvFile']['tmp_name'])) {
            $targetDir = 'uploads/';
            $csvFileName = basename($_FILES['csvFile']['name']);
            $targetFilePath = $targetDir . $csvFileName;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['csvFile']['tmp_name'], $targetFilePath)) {
                $csvFile = $targetFilePath;
            } else {
                echo '<div class="alert alert-danger mt-3">File upload failed.</div>';
            }
        }
    } elseif (isset($_GET['file'])) {
        $csvFileName = urldecode($_GET['file']);
        $csvFile = 'uploads/' . $csvFileName;
    }

    if (!empty($csvFileName)) {
        echo '<div class="d-flex justify-content-end mt-4">';
        echo '<a href="Actions/add.php?file=' . urlencode($csvFileName) . '" class="btn btn-success">Add New Team</a>';
        echo '</div>';
    }

    if (!empty($csvFileName) && file_exists($csvFile)) {
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            echo '<h2 class="mt-5">CSV Contents:</h2>';

            echo '<div class="mb-3">';
            echo '<label for="searchInput" class="form-label">Search:</label>';
            echo '<input type="text" id="searchInput" onkeyup="searchTable()" class="form-control" placeholder="Search for names..">';
            echo '</div>';

            echo '<table id="csvTable" class="table table-dark table-striped table-bordered">';

            if (($header = fgetcsv($handle, 1000, ",")) !== FALSE) {
                echo '<thead class="thead-dark"><tr>';
                $colIndex = 0;
                foreach ($header as $column) {
                    $headerItems = explode(';', $column);
                    foreach ($headerItems as $headerItem) {
                        $isNumeric = in_array(trim($headerItem), ['ID', 'Startnumber', 'CC', 'Transponder']);
                        echo '<th onclick="sortTable(' . $colIndex . ', ' . ($isNumeric ? 'true' : 'false') . ')">' . htmlspecialchars(trim($headerItem)) . '</th>';
                        $colIndex++;
                    }
                }
                echo '<th>Actions</th>';
                echo '</tr></thead>';
            }

            echo '<tbody>';
            $rowIndex = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                echo '<tr>';
                foreach ($row as $column) {
                    $items = explode(';', $column);
                    foreach ($items as $item) {
                        echo '<td>' . htmlspecialchars(trim($item)) . '</td>';
                    }
                }

                echo '<td>';
                echo '<a href="Actions/edit.php?id=' . $rowIndex . '&file=' . urlencode($csvFileName) . '" class="btn btn-warning btn-sm">Edit</a> ';
                echo '<a href="Actions/delete.php?id=' . $rowIndex . '&file=' . urlencode($csvFileName) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this row?\');">Delete</a> ';
                echo '<a href="Actions/duplicate.php?id=' . $rowIndex . '&file=' . urlencode($csvFileName) . '" class="btn btn-info btn-sm">Duplicate</a>';
                echo '</td>';
                echo '</tr>';
                $rowIndex++;
            }
            echo '</tbody>';
            echo '</table>';
            fclose($handle);
        } else {
            echo '<div class="alert alert-danger mt-3">Error opening the file.</div>';
        }
    }
    ?>
</div>

<script src="js/bootstrap.bundle.js"></script>
</body>
</html>
