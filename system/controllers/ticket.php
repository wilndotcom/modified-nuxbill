<?php

/**
 * Support Ticket System - Admin Controller
 * 
 * Handles ticket management for administrators
 */

_admin();
$ui->assign('_title', Lang::T('Support Tickets'));
$ui->assign('_system_menu', 'ticket');

$action = $routes['1'] ?? 'list';
$ui->assign('_admin', $admin);

// Add Ticket menu to admin sidebar
run_hook('admin_menu');

switch ($action) {
    // List all tickets
    case 'list':
        $status = _req('status', 'all');
        $priority = _req('priority', 'all');
        $category = _req('category', 'all');
        
        // Build query
        $query = ORM::for_table('tbl_tickets')
            ->select('tbl_tickets.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->left_outer_join('tbl_customers', ['tbl_tickets.customer_id', '=', 'tbl_customers.id'])
            ->order_by_desc('tbl_tickets.created_at');
        
        // Apply filters
        if ($status != 'all') {
            $query->where('tbl_tickets.status', $status);
        }
        if ($priority != 'all') {
            $query->where('tbl_tickets.priority', $priority);
        }
        if ($category != 'all') {
            $query->where('tbl_tickets.category', $category);
        }
        
        // Get counts for filter display
        $counts = [
            'all' => ORM::for_table('tbl_tickets')->count(),
            'open' => ORM::for_table('tbl_tickets')->where('status', 'open')->count(),
            'pending' => ORM::for_table('tbl_tickets')->where('status', 'pending')->count(),
            'closed' => ORM::for_table('tbl_tickets')->where('status', 'closed')->count(),
        ];
        
        $priorityCounts = [
            'urgent' => ORM::for_table('tbl_tickets')->where('priority', 'urgent')->where('status', 'open')->count(),
            'high' => ORM::for_table('tbl_tickets')->where('priority', 'high')->where('status', 'open')->count(),
        ];
        
        $tickets = Paginator::findMany($query);
        $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
        
        $ui->assign('tickets', $tickets);
        $ui->assign('paginator', $paginator);
        $ui->assign('counts', $counts);
        $ui->assign('priorityCounts', $priorityCounts);
        $ui->assign('categories', $categories);
        $ui->assign('current_status', $status);
        $ui->assign('current_priority', $priority);
        $ui->assign('categories', $categories);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/list.tpl');
        break;

    // Create new ticket (admin creates on behalf of customer)
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF
            if (!Csrf::check($_POST['csrf_token'] ?? '')) {
                r2(getUrl('ticket/create'), 'e', Lang::T('Security token expired. Please try again.'));
            }
            
            $customer_id = intval($_POST['customer_id'] ?? 0);
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $category = $_POST['category'] ?? 'General';
            $priority = $_POST['priority'] ?? 'medium';
            
            if (empty($customer_id) || empty($subject) || empty($message)) {
                r2(getUrl('ticket/create'), 'e', Lang::T('Customer, subject and message are required'));
            }
            
            // Verify customer exists
            $customer = ORM::for_table('tbl_customers')->find_one($customer_id);
            if (!$customer) {
                r2(getUrl('ticket/create'), 'e', Lang::T('Customer not found'));
            }
            
            // Create ticket
            $ticket = ORM::for_table('tbl_tickets')->create();
            $ticket->customer_id = $customer_id;
            $ticket->subject = $subject;
            $ticket->message = $message;
            $ticket->category = $category;
            $ticket->priority = $priority;
            $ticket->status = 'open';
            $ticket->created_at = date('Y-m-d H:i:s');
            $ticket->save();
            
            r2(getUrl('ticket/list'), 's', Lang::T('Ticket created successfully'));
        }
        
        // Get customers for dropdown
        $customers = ORM::for_table('tbl_customers')->where('status', 'Active')->order_by_asc('fullname')->find_many();
        
        // Get admins for assignment dropdown
        $admins = ORM::for_table('tbl_admins')->where_in('user_type', ['SuperAdmin', 'Admin'])->find_many();
        
        $ui->assign('customers', $customers);
        $ui->assign('admins', $admins);
        $ui->assign('categories', $categories);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/create.tpl');
        break;

    // View single ticket
    case 'view':
        $id = $routes['2'] ?? 0;
        
        $ticket = ORM::for_table('tbl_tickets')
            ->select('tbl_tickets.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->select('tbl_customers.email', 'customer_email')
            ->select('tbl_customers.phonenumber', 'customer_phone')
            ->left_outer_join('tbl_customers', ['tbl_tickets.customer_id', '=', 'tbl_customers.id'])
            ->find_one($id);
        
        if (!$ticket) {
            r2(getUrl('ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        
        // Get replies
        $replies = ORM::for_table('tbl_ticket_replies')
            ->select('tbl_ticket_replies.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->select('tbl_admins.fullname', 'admin_name')
            ->left_outer_join('tbl_customers', ['tbl_ticket_replies.customer_id', '=', 'tbl_customers.id'])
            ->left_outer_join('tbl_admins', ['tbl_ticket_replies.admin_id', '=', 'tbl_admins.id'])
            ->where('tbl_ticket_replies.ticket_id', $id)
            ->order_by_asc('tbl_ticket_replies.created_at')
            ->find_many();
        
        // Get categories for edit form
        $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
        
        // Get admins for assignment
        $admins = ORM::for_table('tbl_admins')->where_in('user_type', ['SuperAdmin', 'Admin'])->find_many();
        
        $ui->assign('ticket', $ticket);
        $ui->assign('replies', $replies);
        $ui->assign('categories', $categories);
        $ui->assign('admins', $admins);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/view.tpl');
        break;

    // Add reply to ticket
    case 'reply-post':
        $id = _post('ticket_id');
        $message = _post('message');
        $csrf_token = _post('csrf_token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('ticket/view/', $id), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        if (empty($message)) {
            r2(getUrl('ticket/view/', $id), 'e', Lang::T('Reply message is required'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if (!$ticket) {
            r2(getUrl('ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        
        // Add reply
        $reply = ORM::for_table('tbl_ticket_replies')->create();
        $reply->ticket_id = $id;
        $reply->admin_id = $admin['id'];
        $reply->message = $message;
        $reply->is_staff = true;
        $reply->save();
        
        // Update ticket status to pending if it was open
        if ($ticket->status == 'open') {
            $ticket->status = 'pending';
        }
        $ticket->updated_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        // Send notification to customer (optional)
        // Message::sendTicketReplyNotification($ticket->customer_id, $ticket->subject);
        
        _log('Admin ' . $admin['username'] . ' replied to ticket #' . $id, 'Ticket');
        r2(getUrl('ticket/view/', $id), 's', Lang::T('Reply added successfully'));
        break;

    // Update ticket status/priority/assignment
    case 'update':
        $id = $routes['2'] ?? 0;
        $csrf_token = _req('token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('ticket/view/', $id), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if (!$ticket) {
            r2(getUrl('ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        
        $field = _post('field');
        $value = _post('value');
        
        if (in_array($field, ['status', 'priority', 'category', 'assigned_to'])) {
            $ticket->$field = $value;
            
            if ($field == 'status' && $value == 'closed') {
                $ticket->closed_at = date('Y-m-d H:i:s');
            }
            
            $ticket->save();
            
            _log('Admin ' . $admin['username'] . ' updated ticket #' . $id . ' ' . $field . ' to ' . $value, 'Ticket');
            r2(getUrl('ticket/view/', $id), 's', Lang::T('Ticket updated'));
        } else {
            r2(getUrl('ticket/view/', $id), 'e', Lang::T('Invalid field'));
        }
        break;

    // Close ticket
    case 'close':
        $id = $routes['2'] ?? 0;
        $csrf_token = _req('token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('ticket/view/', $id), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if ($ticket) {
            $ticket->status = 'closed';
            $ticket->closed_at = date('Y-m-d H:i:s');
            $ticket->save();
            
            _log('Admin ' . $admin['username'] . ' closed ticket #' . $id, 'Ticket');
            r2(getUrl('ticket/list'), 's', Lang::T('Ticket closed'));
        } else {
            r2(getUrl('ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        break;

    // Reopen ticket
    case 'reopen':
        $id = $routes['2'] ?? 0;
        $csrf_token = _req('token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('ticket/view/', $id), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if ($ticket) {
            $ticket->status = 'open';
            $ticket->closed_at = null;
            $ticket->save();
            
            _log('Admin ' . $admin['username'] . ' reopened ticket #' . $id, 'Ticket');
            r2(getUrl('ticket/view/', $id), 's', Lang::T('Ticket reopened'));
        } else {
            r2(getUrl('ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        break;

    // Delete ticket
    case 'delete':
        $id = $routes['2'] ?? 0;
        $csrf_token = _req('token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('ticket/list'), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if ($ticket) {
            // Delete replies first (cascade should handle this but just in case)
            ORM::for_table('tbl_ticket_replies')->where('ticket_id', $id)->delete_many();
            
            $ticket->delete();
            _log('Admin ' . $admin['username'] . ' deleted ticket #' . $id, 'Ticket');
            r2(getUrl('ticket/list'), 's', Lang::T('Ticket deleted'));
        } else {
            r2(getUrl('ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        break;

    // Manage categories
    case 'categories':
        $categories = ORM::for_table('tbl_ticket_categories')->order_by_asc('name')->find_many();
        $ui->assign('categories', $categories);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/categories.tpl');
        break;

    // Default - redirect to list
    default:
        r2(getUrl('ticket/list'));
        break;
}
