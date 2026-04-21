<?php

/**
 * Admin Support Ticket Controller
 * 
 * Handles ticket management for administrators
 */

_admin();
$ui->assign('_title', Lang::T('Support Tickets'));
$ui->assign('_system_menu', 'ticket');

$action = $routes['1'] ?? 'list';
$ui->assign('_admin', $admin);

// Check permissions
if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Agent', 'Support'])) {
    _alert(Lang::T('You do not have permission to access this page'), 'danger', 'dashboard');
}

switch ($action) {
    // List all tickets
    case 'list':
        $status = _req('status', 'all');
        $priority = _req('priority', 'all');
        
        // Build base query
        $query = ORM::for_table('tbl_tickets')
            ->select('tbl_tickets.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_name')
            ->left_outer_join('tbl_customers', array('tbl_tickets.customer_id', '=', 'tbl_customers.id'))
            ->order_by_desc('tbl_tickets.created_at');
        
        // Apply filters
        if ($status != 'all') {
            $query->where('tbl_tickets.status', $status);
        }
        if ($priority != 'all') {
            $query->where('tbl_tickets.priority', ucfirst($priority));
        }
        
        $tickets = Paginator::findMany($query);
        
        // Get counts for filter buttons
        $counts = [
            'all' => ORM::for_table('tbl_tickets')->count(),
            'open' => ORM::for_table('tbl_tickets')->where('status', 'open')->count(),
            'pending' => ORM::for_table('tbl_tickets')->where('status', 'pending')->count(),
            'closed' => ORM::for_table('tbl_tickets')->where('status', 'closed')->count(),
        ];
        
        // Get priority counts for alerts
        $priorityCounts = [
            'urgent' => ORM::for_table('tbl_tickets')->where('priority', 'Urgent')->where('status', 'Open')->count(),
            'high' => ORM::for_table('tbl_tickets')->where('priority', 'High')->where('status', 'Open')->count(),
            'medium' => ORM::for_table('tbl_tickets')->where('priority', 'Medium')->where('status', 'Open')->count(),
            'low' => ORM::for_table('tbl_tickets')->where('priority', 'Low')->where('status', 'Open')->count(),
        ];
        
        $ui->assign('tickets', $tickets);
        $ui->assign('counts', $counts);
        $ui->assign('priorityCounts', $priorityCounts);
        $ui->assign('current_status', $status);
        $ui->assign('current_priority', $priority);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/list.tpl');
        break;
        
    // View single ticket
    case 'view':
        $id = $routes['2'] ?? 0;
        
        $ticket = ORM::for_table('tbl_tickets')
            ->select('tbl_tickets.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_name')
            ->select('tbl_customers.email', 'customer_email')
            ->select('tbl_customers.phonenumber', 'customer_phone')
            ->left_outer_join('tbl_customers', array('tbl_tickets.customer_id', '=', 'tbl_customers.id'))
            ->find_one($id);
        
        if (!$ticket) {
            _alert(Lang::T('Ticket not found'), 'danger', 'ticket/list');
        }
        
        // Mark as read by admin
        if (empty($ticket->admin_read_at)) {
            $ticket->admin_read_at = date('Y-m-d H:i:s');
            $ticket->save();
        }
        
        // Get replies
        $replies = ORM::for_table('tbl_ticket_replies')
            ->select('tbl_ticket_replies.*')
            ->select('tbl_admins.fullname', 'admin_name')
            ->select('tbl_customers.fullname', 'customer_name')
            ->left_outer_join('tbl_admins', array('tbl_ticket_replies.admin_id', '=', 'tbl_admins.id'))
            ->left_outer_join('tbl_customers', array('tbl_ticket_replies.customer_id', '=', 'tbl_customers.id'))
            ->where('tbl_ticket_replies.ticket_id', $id)
            ->order_by_asc('tbl_ticket_replies.created_at')
            ->find_many();
        
        // Get categories for assignment
        $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
        
        // Get admin users for assignment
        $admins = ORM::for_table('tbl_admins')
            ->where_in('user_type', ['SuperAdmin', 'Admin', 'Support'])
            ->find_many();
        
        $ui->assign('ticket', $ticket->as_array());
        $ui->assign('replies', $replies);
        $ui->assign('categories', $categories);
        $ui->assign('admins', $admins);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/view.tpl');
        break;
        
    // Reply to ticket
    case 'reply-post':
        $id = _post('ticket_id');
        $message = _post('message');
        $csrf_token = _post('csrf_token');
        
        if (!Csrf::check($csrf_token)) {
            _alert(Lang::T('Invalid CSRF Token'), 'danger', 'ticket/view/' . $id);
        }
        
        if (empty($message)) {
            _alert(Lang::T('Message is required'), 'danger', 'ticket/view/' . $id);
        }
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if (!$ticket) {
            _alert(Lang::T('Ticket not found'), 'danger', 'ticket/list');
        }
        
        // Add reply
        $reply = ORM::for_table('tbl_ticket_replies')->create();
        $reply->ticket_id = $id;
        $reply->admin_id = $admin['id'];
        $reply->message = $message;
        $reply->created_at = date('Y-m-d H:i:s');
        $reply->save();
        
        // Update ticket status and admin read
        $ticket->status = 'pending';
        $ticket->admin_read_at = date('Y-m-d H:i:s');
        $ticket->updated_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        _log('Admin replied to ticket #' . $id, 'Ticket');
        _alert(Lang::T('Reply added successfully'), 'success', 'ticket/view/' . $id);
        break;
        
    // Update ticket status
    case 'status-post':
        $id = $routes['2'] ?? 0;
        $status = _post('status');
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if (!$ticket) {
            _alert(Lang::T('Ticket not found'), 'danger', 'ticket/list');
        }
        
        $ticket->status = $status;
        if ($status == 'closed') {
            $ticket->closed_at = date('Y-m-d H:i:s');
        }
        $ticket->updated_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        _log('Ticket #' . $id . ' status changed to ' . $status, 'Ticket');
        _alert(Lang::T('Status updated successfully'), 'success', 'ticket/view/' . $id);
        break;
        
    // Assign ticket to admin
    case 'assign-post':
        $id = $routes['2'] ?? 0;
        $assigned_to = _post('assigned_to');
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if (!$ticket) {
            _alert(Lang::T('Ticket not found'), 'danger', 'ticket/list');
        }
        
        $ticket->assigned_to = $assigned_to;
        $ticket->updated_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        _log('Ticket #' . $id . ' assigned to admin #' . $assigned_to, 'Ticket');
        _alert(Lang::T('Ticket assigned successfully'), 'success', 'ticket/view/' . $id);
        break;
        
    // Update ticket priority
    case 'priority-post':
        $id = $routes['2'] ?? 0;
        $priority = _post('priority');
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if (!$ticket) {
            _alert(Lang::T('Ticket not found'), 'danger', 'ticket/list');
        }
        
        $ticket->priority = $priority;
        $ticket->updated_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        _log('Ticket #' . $id . ' priority changed to ' . $priority, 'Ticket');
        _alert(Lang::T('Priority updated successfully'), 'success', 'ticket/view/' . $id);
        break;
        
    // Create new ticket (admin creates for customer)
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customer_id = _post('customer_id');
            $subject = _post('subject');
            $message = _post('message');
            $category = _post('category', 'General');
            $priority = _post('priority', 'medium');
            
            if (empty($customer_id) || empty($subject) || empty($message)) {
                _alert(Lang::T('All fields are required'), 'danger', 'ticket/create');
            }
            
            // Verify customer exists
            $customer = ORM::for_table('tbl_customers')->find_one($customer_id);
            if (!$customer) {
                _alert(Lang::T('Customer not found'), 'danger', 'ticket/create');
            }
            
            $ticket = ORM::for_table('tbl_tickets')->create();
            $ticket->customer_id = $customer_id;
            $ticket->subject = $subject;
            $ticket->message = $message;
            $ticket->category = $category;
            $ticket->priority = $priority;
            $ticket->status = 'open';
            $ticket->created_at = date('Y-m-d H:i:s');
            $ticket->save();
            
            _log('Admin created ticket #' . $ticket->id . ' for customer ' . $customer->username, 'Ticket');
            _alert(Lang::T('Ticket created successfully'), 'success', 'ticket/list');
        }
        
        // Get customers for dropdown
        $customers = ORM::for_table('tbl_customers')
            ->select('id')
            ->select('username')
            ->select('fullname')
            ->order_by_asc('username')
            ->find_many();
        
        // Get categories
        $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
        
        $ui->assign('customers', $customers);
        $ui->assign('categories', $categories);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/create.tpl');
        break;
        
    // Ticket categories management
    case 'categories':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = _post('name');
            $enabled = _post('enabled', 1);
            
            if (!empty($name)) {
                $category = ORM::for_table('tbl_ticket_categories')->create();
                $category->name = $name;
                $category->enabled = $enabled;
                $category->save();
                
                _alert(Lang::T('Category added successfully'), 'success', 'ticket/categories');
            }
        }
        
        $categories = ORM::for_table('tbl_ticket_categories')->find_many();
        $ui->assign('categories', $categories);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/ticket/categories.tpl');
        break;
        
    // Delete ticket
    case 'delete':
        $id = $routes['2'] ?? 0;
        
        $ticket = ORM::for_table('tbl_tickets')->find_one($id);
        if ($ticket) {
            // Delete replies first
            ORM::for_table('tbl_ticket_replies')->where('ticket_id', $id)->delete_many();
            
            $ticket->delete();
            _log('Ticket #' . $id . ' deleted', 'Ticket');
            _alert(Lang::T('Ticket deleted successfully'), 'success', 'ticket/list');
        } else {
            _alert(Lang::T('Ticket not found'), 'danger', 'ticket/list');
        }
        break;
        
    default:
        $ui->display('admin/404.tpl');
}
