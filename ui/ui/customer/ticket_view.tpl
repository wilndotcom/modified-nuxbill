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
                        <div class="col-md-4">
                            <strong>{Lang::T('Category')}:</strong><br>
                            <span class="label label-default">{$ticket['category']}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>{Lang::T('Priority')}:</strong><br>
                            {if $ticket['priority']=='urgent'}
                                <span class="label label-danger">{Lang::T('Urgent')}</span>
                            {elseif $ticket['priority']=='high'}
                                <span class="label label-warning">{Lang::T('High')}</span>
                            {elseif $ticket['priority']=='medium'}
                                <span class="label label-info">{Lang::T('Medium')}</span>
                            {else}
                                <span class="label label-default">{Lang::T('Low')}</span>
                            {/if}
                        </div>
                        <div class="col-md-4">
                            <strong>{Lang::T('Created')}:</strong><br>
                            {Lang::dateTimeFormat($ticket['created_at'])}
                        </div>
                    </div>
                </div>

                <!-- Original Message -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <strong>{Lang::T('You')}</strong>
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
                                <strong>{Lang::T('Support Staff')}</strong>
                                {if $reply['admin_name']}
                                    <small>({$reply['admin_name']})</small>
                                {/if}
                                <span class="label label-success">{Lang::T('Staff')}</span>
                            {else}
                                <strong>{Lang::T('You')}</strong>
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
                            <form method="post" action="{Text::url('customer_ticket/reply-post')}">
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
                        <i class="fa fa-info-circle"></i> {Lang::T('This ticket is closed. You cannot add replies.')}
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
                    <a href="{Text::url('customer_ticket/close/', $ticket['id'])}?token={$csrf_token}" 
                       class="btn btn-warning btn-block"
                       onclick="return confirm('{Lang::T('Are you sure you want to close this ticket?')}')">
                        <i class="fa fa-check"></i> {Lang::T('Close Ticket')}
                    </a>
                    <br>
                {/if}
                <a href="{Text::url('customer_ticket/list')}" class="btn btn-default btn-block">
                    <i class="fa fa-arrow-left"></i> {Lang::T('Back to Tickets')}
                </a>
            </div>
        </div>

        <!-- Help Info -->
        <div class="panel panel-info">
            <div class="panel-heading">
                <strong>{Lang::T('Need Help?')}</strong>
            </div>
            <div class="panel-body">
                <p>{Lang::T('Our support team typically responds within 24 hours.')}</p>
                <p>{Lang::T('For urgent issues, please call our hotline.')}</p>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
