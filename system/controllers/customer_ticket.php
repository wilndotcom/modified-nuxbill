<?php

/**
 * Customer Support Ticket Controller
 * 
 * Handles ticket management for customers in their portal
 */

_auth();
$ui->assign('_title', Lang::T('My Support Tickets'));
$ui->assign('_system_menu', 'tickets');

$action = $routes['1'] ?? 'list';
$user = User::_info();

// Debug: Check if user is properly loaded
if (!$user || !$user->id) {
    // Try alternative method to get user
    $uid = User::getID();
    if ($uid) {
        $user = ORM::for_table('tbl_customers')->find_one($uid);
    }
}

$ui->assign('_user', $user);

switch ($action) {
    // List customer's tickets
    case 'list':
        $status = _req('status', 'all');
        
        // Build query
        $query = ORM::for_table('tbl_tickets')
            ->where('customer_id', $user->id)
            ->order_by_desc('created_at');
        
        if ($status != 'all') {
            $query->where('status', $status);
        }
        
        $tickets = Paginator::findMany($query);
        
        // Get counts
        $counts = [
            'all' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->count(),
            'open' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->where('status', 'open')->count(),
            'pending' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->where('status', 'pending')->count(),
            'closed' => ORM::for_table('tbl_tickets')->where('customer_id', $user->id)->where('status', 'closed')->count(),
        ];
        
        // Get categories
        $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
        
        $ui->assign('tickets', $tickets);
        $ui->assign('counts', $counts);
        $ui->assign('categories', $categories);
        $ui->assign('current_status', $status);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('customer/ticket.tpl');
        break;

    // View single ticket
    case 'view':
        $id = $routes['2'] ?? 0;
        
        $ticket = ORM::for_table('tbl_tickets')
            ->where('customer_id', $user->id)
            ->find_one($id);
        
        if (!$ticket) {
            r2(getUrl('customer_ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        
        // Get replies
        $replies = ORM::for_table('tbl_ticket_replies')
            ->select('tbl_ticket_replies.*')
            ->select('tbl_admins.fullname', 'admin_name')
            ->left_outer_join('tbl_admins', ['tbl_ticket_replies.admin_id', '=', 'tbl_admins.id'])
            ->where('tbl_ticket_replies.ticket_id', $id)
            ->order_by_asc('tbl_ticket_replies.created_at')
            ->find_many();
        
        $ui->assign('ticket', $ticket);
        $ui->assign('replies', $replies);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('customer/ticket_view.tpl');
        break;

    // Create new ticket form
    case 'create':
        $categories = ORM::for_table('tbl_ticket_categories')->where('enabled', 1)->find_many();
        
        $ui->assign('categories', $categories);
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('customer/ticket_create.tpl');
        break;

    // Save new ticket
    case 'create-post':
        $subject = _post('subject');
        $message = _post('message');
        $category = _post('category', 'General Support');
        $priority = _post('priority', 'medium');
        $csrf_token = _post('csrf_token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('customer_ticket/create'), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        if (empty($subject) || empty($message)) {
            r2(getUrl('customer_ticket/create'), 'e', Lang::T('Subject and message are required'));
        }
        
        // Create ticket
        $ticket = ORM::for_table('tbl_tickets')->create();
        $ticket->customer_id = $user->id;
        $ticket->subject = $subject;
        $ticket->message = $message;
        $ticket->category = $category;
        $ticket->priority = $priority;
        $ticket->status = 'open';
        $ticket->created_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        // Notify admins (optional - can be implemented later)
        // Message::sendNewTicketNotification($ticket);
        
        _log('Customer ' . $user->username . ' created ticket #' . $ticket->id, 'Ticket');
        r2(getUrl('customer_ticket/view/', $ticket->id), 's', Lang::T('Ticket created successfully'));
        break;

    // Add reply to ticket
    case 'reply-post':
        $id = _post('ticket_id');
        $message = _post('message');
        $csrf_token = _post('csrf_token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('customer_ticket/view/', $id), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        if (empty($message)) {
            r2(getUrl('customer_ticket/view/', $id), 'e', Lang::T('Reply message is required'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')
            ->where('customer_id', $user->id)
            ->find_one($id);
        
        if (!$ticket) {
            r2(getUrl('customer_ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        
        // Cannot reply to closed tickets
        if ($ticket->status == 'closed') {
            r2(getUrl('customer_ticket/view/', $id), 'e', Lang::T('Cannot reply to closed ticket'));
        }
        
        // Add reply
        $reply = ORM::for_table('tbl_ticket_replies')->create();
        $reply->ticket_id = $id;
        $reply->customer_id = $user['id'];
        $reply->message = $message;
        $reply->is_staff = false;
        $reply->save();
        
        // Update ticket status back to open if it was pending
        $ticket->status = 'open';
        $ticket->updated_at = date('Y-m-d H:i:s');
        $ticket->save();
        
        r2(getUrl('customer_ticket/view/', $id), 's', Lang::T('Reply added successfully'));
        break;

    // Close own ticket
    case 'close':
        $id = $routes['2'] ?? 0;
        $csrf_token = _req('token');
        
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('customer_ticket/view/', $id), 'e', Lang::T('Invalid CSRF Token'));
        }
        
        $ticket = ORM::for_table('tbl_tickets')
            ->where('customer_id', $user->id)
            ->find_one($id);
        
        if ($ticket) {
            $ticket->status = 'closed';
            $ticket->closed_at = date('Y-m-d H:i:s');
            $ticket->save();
            
            _log('Customer ' . $user->username . ' closed ticket #' . $id, 'Ticket');
            r2(getUrl('customer_ticket/list'), 's', Lang::T('Ticket closed'));
        } else {
            r2(getUrl('customer_ticket/list'), 'e', Lang::T('Ticket not found'));
        }
        break;

    // Default - redirect to list
    default:
        r2(getUrl('customer_ticket/list'));
        break;
}
