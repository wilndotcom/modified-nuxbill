{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('My Support Tickets')}
                <a href="{Text::url('customer_ticket/create')}" class="btn btn-primary btn-xs pull-right">
                    <i class="fa fa-plus"></i> {Lang::T('Create New Ticket')}
                </a>
            </div>
            <div class="panel-body">
                <!-- Status Filter -->
                <div class="btn-group mb20" role="group">
                    <a href="{Text::url('customer_ticket/list')}" class="btn btn-sm btn-default {if $current_status=='all'}active{/if}">
                        {Lang::T('All')} <span class="badge">{$counts.all}</span>
                    </a>
                    <a href="{Text::url('customer_ticket/list', ['status' => 'open'])}" class="btn btn-sm btn-success {if $current_status=='open'}active{/if}">
                        {Lang::T('Open')} <span class="badge">{$counts.open}</span>
                    </a>
                    <a href="{Text::url('customer_ticket/list', ['status' => 'pending'])}" class="btn btn-sm btn-warning {if $current_status=='pending'}active{/if}">
                        {Lang::T('Pending')} <span class="badge">{$counts.pending}</span>
                    </a>
                    <a href="{Text::url('customer_ticket/list', ['status' => 'closed'])}" class="btn btn-sm btn-default {if $current_status=='closed'}active{/if}">
                        {Lang::T('Closed')} <span class="badge">{$counts.closed}</span>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('Ticket ID')}</th>
                                <th>{Lang::T('Subject')}</th>
                                <th>{Lang::T('Category')}</th>
                                <th>{Lang::T('Priority')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Created')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $tickets as $ticket}
                                <tr {if $ticket['priority']=='urgent' && $ticket['status']=='open'}class="danger"{elseif $ticket['priority']=='high' && $ticket['status']=='open'}class="warning"{/if}>
                                    <td>#{$ticket['id']}</td>
                                    <td>
                                        <a href="{Text::url('customer_ticket/view/', $ticket['id'])}">
                                            {Text::truncate($ticket['subject'], 40)}
                                        </a>
                                        {if $ticket['status']=='open'}
                                            <span class="label label-success">{Lang::T('New')}</span>
                                        {/if}
                                    </td>
                                    <td>
                                        <span class="label label-default">{$ticket['category']}</span>
                                    </td>
                                    <td>
                                        {if $ticket['priority']=='urgent'}
                                            <span class="label label-danger">{Lang::T('Urgent')}</span>
                                        {elseif $ticket['priority']=='high'}
                                            <span class="label label-warning">{Lang::T('High')}</span>
                                        {elseif $ticket['priority']=='medium'}
                                            <span class="label label-info">{Lang::T('Medium')}</span>
                                        {else}
                                            <span class="label label-default">{Lang::T('Low')}</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $ticket['status']=='open'}
                                            <span class="label label-success">{Lang::T('Open')}</span>
                                        {elseif $ticket['status']=='pending'}
                                            <span class="label label-warning">{Lang::T('Pending')}</span>
                                        {else}
                                            <span class="label label-default">{Lang::T('Closed')}</span>
                                        {/if}
                                    </td>
                                    <td>{Lang::dateTimeFormat($ticket['created_at'])}</td>
                                    <td>
                                        <a href="{Text::url('customer_ticket/view/', $ticket['id'])}" class="btn btn-info btn-xs">
                                            <i class="fa fa-eye"></i> {Lang::T('View')}
                                        </a>
                                        {if $ticket['status']!='closed'}
                                            <a href="{Text::url('customer_ticket/close/', $ticket['id'])}?token={$csrf_token}" 
                                               class="btn btn-warning btn-xs"
                                               onclick="return confirm('{Lang::T('Are you sure you want to close this ticket?')}')">
                                                <i class="fa fa-check"></i> {Lang::T('Close')}
                                            </a>
                                        {/if}
                                    </td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> {Lang::T('No tickets found')}
                                            <br><br>
                                            <a href="{Text::url('customer_ticket/create')}" class="btn btn-primary btn-sm">
                                                {Lang::T('Create Your First Ticket')}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {include file="pagination.tpl"}
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
