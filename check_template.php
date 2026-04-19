<?php
include 'init.php';
$ui = $GLOBALS['ui'];

// Try to compile the template manually
try {
    $result = $ui->fetch('customer/ticket.tpl');
    echo "Template compiled successfully!<br>";
    echo "Output length: " . strlen($result) . " chars";
} catch (Exception $e) {
    echo "Template Error: " . $e->getMessage();
}
