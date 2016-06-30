<?php
    if ( isset($_GET['csv_dir'] ) ) {
        $dir = $_GET['csv_dir'];            
        $dir = str_replace('?', '', $dir);
    } else
        $dir = dirname(__FILE__) . '/../../../uploads' ;     
    $filename = 'bookings_export.csv';
    if ( ! file_exists( "$dir/$filename" ) ){
        die('Wrong Path. Error during exporting CSV file!');
    }   
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream'); 
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    readfile("$dir/$filename");
?>