<?php
require 'init.php';
echo "URL for ticket/list: " . Text::url('ticket/list') . "\n";
echo "File should be: system/controllers/ticket.php\n";
echo "File exists: " . (file_exists('system/controllers/ticket.php') ? 'YES' : 'NO') . "\n";
