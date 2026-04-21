<?php
// Register the Mpesa Transactions menu
register_menu("Mpesa Transactions", true, "mpesa_transactions", 'AFTER_REPORTS', 'ion ion-ios-list', '', '', ['Admin', 'SuperAdmin']);

function mpesa_transactions()
{
    global $ui, $config, $admin;
    _admin();

    // Check if the table exists, if not, create it
    if (!check_table_exists('tbl_mpesa_transactions')) {
        createTableIfMpesaNotExists();
    }

    // Handle search input
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Fetch transactions based on search input or get all if no search input
    if ($search != '') {
        $transactions = ORM::for_table('tbl_mpesa_transactions')
            ->where_raw(
                "(TransID LIKE ? OR FirstName LIKE ? OR TransAmount LIKE ? OR MSISDN LIKE ? OR BillRefNumber LIKE ?)",
                ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]
            )
            ->order_by_desc('id')
            ->find_many();
    } else {
        $transactions = ORM::for_table('tbl_mpesa_transactions')
            ->order_by_desc('id')
            ->find_many();
    }

    // Assign variables to the template
    $ui->assign('t', $transactions);
    $ui->assign('search', $search);  // Pass the search term to the template
    $ui->assign('_title', 'Mpesa Transactions');
    $ui->assign('_system_menu', 'plugin/mpesa_transactions');
    $ui->assign('_admin', Admin::_info());

    // Display the template
    $ui->display('mpesa_transactions.tpl');
}

// Function to check if a table exists in the database
function check_table_exists($table_name)
{
    try {
        ORM::for_table($table_name)->find_one();
        return true;
    } catch (Exception $e) {
        return false; // Table doesn't exist or some other error occurred
    }
}

// Function to create the Mpesa transactions table if it doesn't exist
function createTableIfMpesaNotExists()
{
    $db = ORM::get_db();
    $tableCheckQuery = "CREATE TABLE IF NOT EXISTS tbl_mpesa_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        TransID VARCHAR(255) NOT NULL,
        TransactionType VARCHAR(255) NOT NULL,
        TransTime VARCHAR(255) NOT NULL,
        TransAmount DECIMAL(10, 2) NOT NULL,
        BusinessShortCode VARCHAR(255) NOT NULL,
        BillRefNumber VARCHAR(255) NOT NULL,
        OrgAccountBalance DECIMAL(10, 2) NOT NULL,
        MSISDN VARCHAR(255) NOT NULL,
        FirstName VARCHAR(255) NOT NULL
    )";
    $db->exec($tableCheckQuery);
}
