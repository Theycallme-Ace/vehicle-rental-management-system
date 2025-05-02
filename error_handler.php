<?php
function logError($error, $severity = 'ERROR') {
    $logFile = 'logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] [$severity] $error\n";
    
    // Create logs directory if it doesn't exist
    if (!is_dir('logs')) {
        mkdir('logs', 0777, true);
    }
    
    error_log($message, 3, $logFile);
}

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $severity = match($errno) {
        E_ERROR, E_USER_ERROR => 'FATAL',
        E_WARNING, E_USER_WARNING => 'WARNING',
        E_NOTICE, E_USER_NOTICE => 'NOTICE',
        default => 'UNKNOWN'
    };
    
    logError("$errstr in $errfile on line $errline", $severity);
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    logError($e->getMessage() . "\nStack trace: " . $e->getTraceAsString(), 'FATAL');
    die("An error occurred. Please try again later.");
});
?>
