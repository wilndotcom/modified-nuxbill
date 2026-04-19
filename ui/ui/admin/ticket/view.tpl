{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-8">
        <!-- Ticket Details -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {Lang::T('Ticket')} #{$ticket['id']}: {$ticket['subject']}
                    <span class="pull-right">
                        {if $ticket['status']=='open'}
                            <span class="label label-success">{Lang::T('Open')}</span>
                        {elseif $ticket['status']=='pending'}
                            <span class="label label-warning">{Lang::T('Pending')}</span>
                        {else}
                            <span class="label label-default">{Lang::T('Closed')}</span>
                        {/if}
                    </span>
                </h3>
            </div>
            <div class="panel-body">
                <!-- Ticket Metadata -->
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{Lang::T('Customer')}:</strong><br>
                            <a href="{Text::url('customers/view/', $ticket['customer_id'])}">
                                {$ticket['customer_fullname']}
                            </a>
                            <br><small>{$ticket['customer_username']}</small>
                        </div>
                        <div class="col-md-3">
                            <strong>{Lang::T('Category')}:</strong><br>
                            <span class="label label-default">{$ticket['category']}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{Lang::T('Priority')}:</strong><br>
                            <form method="post" action="{Text::url('ticket/update/', $ticket['id'])}" class="form-inline" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                <input type="hidden" name="field" value="priority">
                                <select name="value" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="low" {if $ticket['priority']=='low'}selected{/if}>{Lang::T('Low')}</option>
                                    <option value="medium" {if $ticket['priority']=='medium'}selected{/if}>{Lang::T('Medium')}</option>
                                    <option value="high" {if $ticket['priority']=='high'}selected{/if}>{Lang::T('High')}</option>
                                    <option value="urgent" {if $ticket['priority']=='urgent'}selected{/if}>{Lang::T('Urgent')}</option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <strong>{Lang::T('Assigned To')}:</strong><br>
                            <form method="post" action="{Text::url('ticket/update/', $ticket['id'])}" class="form-inline" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                <input type="hidden" name="field" value="assigned_to">
                                <select name="value" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">{Lang::T('Unassigned')}</option>
                                    {foreach $admins as $adm}
                                        <option value="{$adm->id}" {if $ticket['assigned_to']==$adm->id}selected{/if}>{$adm->fullname}</option>
                                    {/foreach}
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-6">
                            <strong>{Lang::T('Created')}:</strong> {Lang::dateTimeFormat($ticket['created_at'])}
                        </div>
                        <div class="col-md-6">
                            <strong>{Lang::T('Last Updated')}:</strong> {Lang::dateTimeFormat($ticket['updated_at'])}
                        </div>
                    </div>
                </div>

                <!-- Original Message -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <strong>{$ticket['customer_fullname']}</strong> 
                        <span class="text-muted">({Lang::T('Customer')})</span>
                        <span class="pull-right text-muted">{Lang::dateTimeFormat($ticket['created_at'])}</span>
                    </div>
                    <div class="panel-body">
                        <p>{Lang::nl2br($ticket['message'])}</p>
                    </div>
                </div>

                <!-- Replies -->
                <h4>{Lang::T('Conversation History')}</h4>
                <hr>
                
                {foreach $replies as $reply}
                    <div class="panel {if $reply['is_staff']}panel-success{else}panel-default{/if}">
                        <div class="panel-heading">
                            {if $reply['is_staff']}
                                <strong>{$reply['admin_name']}</strong> 
                                <span class="label label-success">{Lang::T('Staff')}</span>
                            {else}
                                <strong>{$reply['customer_fullname']}</strong>
                                <span class="label label-default">{Lang::T('Customer')}</span>
                            {/if}
                            <span class="pull-right text-muted">{Lang::dateTimeFormat($reply['created_at'])}</span>
                        </div>
                        <div class="panel-body">
                            <p>{Lang::nl2br($reply['message'])}</p>
                        </div>
                    </div>
                {/foreach}

                <!-- Reply Form -->
                {if $ticket['status']!='closed'}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>{Lang::T('Add Reply')}</strong>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="{Text::url('ticket/reply-post')}">
                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                <input type="hidden" name="ticket_id" value="{$ticket['id']}">
                                <div class="form-group">
                                    <textarea name="message" class="form-control" rows="5" placeholder="{Lang::T('Type your reply here...')}" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-reply"></i> {Lang::T('Send Reply')}
                                </button>
                            </form>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> {Lang::T('This ticket is closed. Reopen it to add replies.')}
                    </div>
                {/if}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Actions Panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>{Lang::T('Actions')}</strong>
            </div>
            <div class="panel-body">
                {if $ticket['status']!='closed'}
                    <a href="{Text::url('ticket/close/', $ticket['id'])}?token={$csrf_token}" 
                       class="btn btn-warning btn-block"
                       onclick="return confirm('{Lang::T('Are you sure you want to close this ticket?')}')">
                        <i class="fa fa-check"></i> {Lang::T('Close Ticket')}
                    </a>
                {else}
                    <a href="{Text::url('ticket/reopen/', $ticket['id'])}?token={$csrf_token}" 
                       class="btn btn-success btn-block">
                        <i class="fa fa-refresh"></i> {Lang::T('Reopen Ticket')}
                    </a>
                {/if}
                <br>
                <a href="{Text::url('customers/view/', $ticket['customer_id'])}" class="btn btn-info btn-block">
                    <i class="fa fa-user"></i> {Lang::T('View Customer')}
                </a>
                <br>
                <a href="{Text::url('ticket/delete/', $ticket['id'])}?token={$csrf_token}" 
                   class="btn btn-danger btn-block"
                   onclick="return confirm('{Lang::T('Are you sure you want to delete this ticket? This action cannot be undone.')}')">
                    <i class="fa fa-trash"></i> {Lang::T('Delete Ticket')}
                </a>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>{Lang::T('Customer Information')}</strong>
            </div>
            <div class="panel-body">
                <p><strong>{Lang::T('Name')}:</strong> {$ticket['customer_fullname']}</p>
                <p><strong>{Lang::T('Username')}:</strong> {$ticket['customer_username']}</p>
                <p><strong>{Lang::T('Email')}:</strong> {$ticket['customer_email']}</p>
                <p><strong>{Lang::T('Phone')}:</strong> {$ticket['customer_phone']}</p>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
