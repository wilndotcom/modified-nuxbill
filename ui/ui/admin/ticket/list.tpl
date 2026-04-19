{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('Support Tickets')}
                <div class="panel-title pull-right">
                    <a href="{Text::url('ticket/list')}" class="btn btn-xs btn-default {if $current_status=='all'}active{/if}">
                        {Lang::T('All')} <span class="badge">{$counts.all}</span>
                    </a>
                    <a href="{Text::url('ticket/list', ['status' => 'open'])}" class="btn btn-xs btn-success {if $current_status=='open'}active{/if}">
                        {Lang::T('Open')} <span class="badge">{$counts.open}</span>
                    </a>
                    <a href="{Text::url('ticket/list', ['status' => 'pending'])}" class="btn btn-xs btn-warning {if $current_status=='pending'}active{/if}">
                        {Lang::T('Pending')} <span class="badge">{$counts.pending}</span>
                    </a>
                    <a href="{Text::url('ticket/list', ['status' => 'closed'])}" class="btn btn-xs btn-default {if $current_status=='closed'}active{/if}">
                        {Lang::T('Closed')} <span class="badge">{$counts.closed}</span>
                    </a>
                </div>
            </div>
            <div class="panel-body">
                {if $priorityCounts.urgent > 0 || $priorityCounts.high > 0}
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <strong>{Lang::T('Attention Required')}</strong>
                        {if $priorityCounts.urgent > 0}
                            <span class="badge bg-red">{$priorityCounts.urgent} {Lang::T('Urgent')}</span>
                        {/if}
                        {if $priorityCounts.high > 0}
                            <span class="badge bg-orange">{$priorityCounts.high} {Lang::T('High Priority')}</span>
                        {/if}
                    </div>
                {/if}

                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-6">
                        <form method="get" action="{Text::url('ticket/list')}" class="form-inline">
                            <input type="hidden" name="status" value="{$current_status}">
                            <select name="priority" class="form-control input-sm" onchange="this.form.submit()">
                                <option value="all">{Lang::T('All Priorities')}</option>
                                <option value="urgent" {if $current_priority=='urgent'}selected{/if}>{Lang::T('Urgent')}</option>
                                <option value="high" {if $current_priority=='high'}selected{/if}>{Lang::T('High')}</option>
                                <option value="medium" {if $current_priority=='medium'}selected{/if}>{Lang::T('Medium')}</option>
                                <option value="low" {if $current_priority=='low'}selected{/if}>{Lang::T('Low')}</option>
                            </select>
                            <select name="category" class="form-control input-sm" onchange="this.form.submit()">
                                <option value="all">{Lang::T('All Categories')}</option>
                                {foreach $categories as $cat}
                                    <option value="{$cat->name}" {if $current_category==$cat->name}selected{/if}>{$cat->name}</option>
                                {/foreach}
                            </select>
                        </form>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{Text::url('ticket/categories')}" class="btn btn-info btn-sm">
                            <i class="fa fa-tags"></i> {Lang::T('Manage Categories')}
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('ID')}</th>
                                <th>{Lang::T('Subject')}</th>
                                <th>{Lang::T('Customer')}</th>
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
                                        <a href="{Text::url('ticket/view/', $ticket['id'])}">
                                            {Text::truncate($ticket['subject'], 50)}
                                        </a>
                                        {if $ticket['status']=='open'}
                                            <span class="label label-success">{Lang::T('New')}</span>
                                        {/if}
                                    </td>
                                    <td>
                                        <a href="{Text::url('customers/view/', $ticket['customer_id'])}">
                                            {$ticket['customer_fullname']}
                                        </a>
                                        <br><small>{$ticket['customer_username']}</small>
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
                                        <a href="{Text::url('ticket/view/', $ticket['id'])}" class="btn btn-info btn-xs">
                                            <i class="fa fa-eye"></i> {Lang::T('View')}
                                        </a>
                                        {if $ticket['status']!='closed'}
                                            <a href="{Text::url('ticket/close/', $ticket['id'])}?token={$csrf_token}" 
                                               class="btn btn-warning btn-xs"
                                               onclick="return confirm('{Lang::T('Are you sure you want to close this ticket?')}')">
                                                <i class="fa fa-check"></i> {Lang::T('Close')}
                                            </a>
                                        {else}
                                            <a href="{Text::url('ticket/reopen/', $ticket['id'])}?token={$csrf_token}" 
                                               class="btn btn-success btn-xs">
                                                <i class="fa fa-refresh"></i> {Lang::T('Reopen')}
                                            </a>
                                        {/if}
                                        <a href="{Text::url('ticket/delete/', $ticket['id'])}?token={$csrf_token}" 
                                           class="btn btn-danger btn-xs"
                                           onclick="return confirm('{Lang::T('Are you sure you want to delete this ticket?')}')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <p class="text-muted">{Lang::T('No tickets found')}</p>
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
