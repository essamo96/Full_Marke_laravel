<?php
// Script to upload the fixed files via FTP to Hostinger

$ftp_server = "92.113.19.218";
$ftp_username = "u515448360";
echo "Please enter your FTP password: ";
$handle = fopen ("php://stdin","r");
$ftp_userpass = trim(fgets($handle));

$conn_id = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
$login_result = ftp_login($conn_id, $ftp_username, $ftp_userpass);
ftp_pasv($conn_id, true);

if ($login_result) {
    echo "Connected to FTP server successfully!\n";
    
    $files = [
        "resources/views/layouts/partials/student-header.blade.php" => "/domains/fullmarkacademy.com/public_html/resources/views/layouts/partials/student-header.blade.php",
        "resources/views/student/exams/review.blade.php" => "/domains/fullmarkacademy.com/public_html/resources/views/student/exams/review.blade.php",
        "resources/views/admin/exams/form.blade.php" => "/domains/fullmarkacademy.com/public_html/resources/views/admin/exams/form.blade.php",
        "bootstrap/app.php" => "/domains/fullmarkacademy.com/public_html/bootstrap/app.php",
        "routes/admin.php" => "/domains/fullmarkacademy.com/public_html/routes/admin.php"
    ];
    
    foreach($files as $local => $remote) {
        if(file_exists($local)) {
            echo "Uploading $local...\n";
            if (ftp_put($conn_id, $remote, $local, FTP_BINARY)) {
                echo "Successfully uploaded $local\n";
            } else {
                echo "There was a problem while uploading $local\n";
            }
        } else {
            echo "Local file not found: $local\n";
        }
    }
    
    ftp_close($conn_id);
    echo "All done! Now visit https://fullmarkacademy.com/fix-cache\n";
} else {
    echo "Failed to login to FTP!\n";
}
