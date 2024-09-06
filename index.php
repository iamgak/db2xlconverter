<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Generate a large file

  // Connect to the database
  $mysqli = new mysqli('localhost', 'newuser', 'password', 'url_shortner');

  if ($mysqli->connect_errno) {
    die('Connection failed: ' . $mysqli->connect_error);
  }

  // Create the dummy_data table
  $mysqli->query('CREATE TABLE if not EXISTS dummy_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  column1 VARCHAR(255),
  column2 VARCHAR(255),
  column3 VARCHAR(255),
  column4 VARCHAR(255),
  column5 VARCHAR(255)
)');

  // Insert 1,00,000 rows of dummy data into the table
  for ($i = 0; $i < 100000; $i++) {
    $mysqli->query("INSERT INTO dummy_data (column1, column2, column3, column4, column5)
                 VALUES ('Dummy Data $i', 'Dummy Data $i', 'Dummy Data $i', 'Dummy Data $i', 'Dummy Data $i')");
  }

  // Fetch the data from the database

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('A1', 'column1');
  $sheet->setCellValue('B1', 'column2');
  $sheet->setCellValue('C1', 'column3');
  $sheet->setCellValue('D1', 'column4');

  $spreadsheet->getProperties()
    ->setCreator("Me")
    ->setLastModifiedBy("Me")
    ->setTitle("My Excel File")
    ->setSubject("My Excel File")
    ->setDescription("My Excel File generated using PHP classes.")
    ->setKeywords("excel php")
    ->setCategory("Test result file");
  $row = 2;
  $result = $mysqli->query('SELECT * FROM dummy_data');

  while ($row_data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $row_data['column1']);
    $sheet->setCellValue('B' . $row, $row_data['column2']);
    $sheet->setCellValue('C' . $row, $row_data['column3']);
    $sheet->setCellValue('D' . $row, $row_data['column4']);
    $row++;
  }


  $filePath = 'exported_data.xlsx';
  $writer = new Xlsx($spreadsheet);
  $writer->save($filePath);
  $fileSize = filesize($filePath);
  // Provide a download link 
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;  filename="exported_data.xlsx"');
  header('Cache-Control: max-age=0');
  header('Content-Length: ' . $fileSize); // Set the Content-Length header
  readfile($filePath);
  unlink($filePath);
  // exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Large File Download</title>
</head>

<body>
  <form action="#" method="post">
    <button type="button" id="load-button">Download Large File</button>
  </form>
  <div id="message"></div>
  <div id="download-message" style="display: none;">Downloading data... Please wait.</div>
  <script>
    // Select the button and the message div
    const button = document.querySelector('#load-button');
    const messageDiv = document.querySelector('#message');

    // Add a click event listener to the button
    button.addEventListener('click', function() {
      // Send an XHR request to the server to download the large file
      const xhr = new XMLHttpRequest();
      xhr.open('POST', window.location.href, true);
      xhr.responseType = "blob";
      xhr.onload = function() {
        if (this.status == 200) {
          filename = "exported_data.xlsx";
          blob = this.response;
          url = window.URL.createObjectURL(blob);
          a = document.createElement("a");
          a.href = url;
          a.download = filename;
          a.click();
        }
      };
      xhr.onprogress = function(event) {
        console.log('Working fine')
        if (event.lengthComputable) {
          percent = Math.round((event.loaded / event.total) * 100);
          console.log(percent + '% downloaded');
          messageDiv.innerHTML = percent + '% downloaded'
        }
      };

      xhr.send();
    });
  </script>
</body>

</html>