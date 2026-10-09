<?php

date_default_timezone_set("Africa/Nairobi");

// Errors are raised as exceptions and funnelled into error_log() by the
// handler below, instead of being suppressed and left to surface as blank
// pages with nothing written anywhere.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

set_exception_handler(function($error){
    error_log("Opunga DB error: " . $error->getMessage());

    http_response_code(500);

    die("Sorry, the shop is temporarily unavailable. Please try again shortly.");
});

$db_host = getenv("OPUNGA_DB_HOST");
$db_name = getenv("OPUNGA_DB_NAME");
$db_user = getenv("OPUNGA_DB_USER");
$db_pass = getenv("OPUNGA_DB_PASS");

if($db_host === false || $db_host === ""){
    $db_host = "localhost";
}

if($db_name === false || $db_name === ""){
    $db_name = "opunga_shop";
}

if($db_user === false){
    $db_user = "root";
}

if($db_pass === false){
    $db_pass = "";
}

// On shared hosting the credentials usually cannot be set as environment
// variables, so allow a config.php placed outside the public folder. Any
// candidate that would be reachable over HTTP is refused, because a
// readable config.php hands the database password to the whole internet.
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

$configCandidates = array(
    dirname(__DIR__, 2) . '/config.php',
    dirname(__DIR__) . '/config.php'
);

foreach($configCandidates as $configFile){

    $realConfig = realpath($configFile);

    if($realConfig === false || !is_file($realConfig)){
        continue;
    }

    if($documentRoot !== false && strpos($realConfig, $documentRoot) === 0){
        error_log("Opunga: ignoring $realConfig because it sits inside the web root at $documentRoot");

        continue;
    }

    require_once $realConfig;

    if(defined('OPUNGA_DB_HOST')){ $db_host = OPUNGA_DB_HOST; }
    if(defined('OPUNGA_DB_NAME')){ $db_name = OPUNGA_DB_NAME; }
    if(defined('OPUNGA_DB_USER')){ $db_user = OPUNGA_DB_USER; }
    if(defined('OPUNGA_DB_PASS')){ $db_pass = OPUNGA_DB_PASS; }

    break;

}

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Set explicitly rather than relying on the server default, so that
// mysqli_real_escape_string() cannot be bypassed by a multi-byte charset
// on older MySQL configurations.
mysqli_set_charset($conn, "utf8mb4");

?>
