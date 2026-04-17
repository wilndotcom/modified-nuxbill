{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('Debt Notifications')}
                <div class="panel-title pull-right">
                    <a href="{Text::url('debt/settings')}" class="btn btn-primary btn-xs">
                        <i class="fa fa-cog"></i> {Lang::T('Settings')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                {if isset($debts) && count($debts) > 0}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('Customer')}</th>
                                <th>{Lang::T('Amount')}</th>
                                <th>{Lang::T('Detected')}</th>
                                <th>{Lang::T('Deadline')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Notifications')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$debts item=debt}
                            <tr>
                                <td>
                                    <strong>{$debt['customer_fullname']}</strong>
                                    <br><small class="text-muted">{$debt['customer_username']}</small>
                                </td>
                                <td class="text-danger"><strong>{$_c['currency_code']} {number_format($debt['amount'], 2)}</strong></td>
                                <td>{date('Y-m-d', strtotime($debt['detected_date']))}</td>
                                <td>
                                    {if $debt['status'] == 'Disconnected'}
                                        <span class="text-muted">-</span>
                                    {else}
                                        {date('Y-m-d', strtotime($debt['deadline_date']))}
                                    {/if}
                                </td>
                                <td>
                                    {if $debt['status'] == 'Active'}
                                    <span class="label label-default">{Lang::T('Active')}</span>
                                    {elseif $debt['status'] == 'Notified'}
                                    <span class="label label-info">{Lang::T('Notified')}</span>
                                    {elseif $debt['status'] == 'Warning'}
                                    <span class="label label-warning">{Lang::T('Warning')}</span>
                                    {elseif $debt['status'] == 'Final'}
                                    <span class="label label-danger">{Lang::T('Final Notice')}</span>
                                    {elseif $debt['status'] == 'Disconnected'}
                                    <span class="label label-danger"><i class="fa fa-ban"></i> {Lang::T('Disconnected')}</span>
                                    {elseif $debt['status'] == 'Settled'}
                                    <span class="label label-success">{Lang::T('Settled')}</span>
                                    {/if}
                                </td>
                                <td>{$debt['notification_count']}</td>
                                <td>
                                    <a href="{Text::url('debt/view/', $debt['id'])}" class="btn btn-info btn-xs" title="{Lang::T('View Details')}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{Text::url('customers/view/', $debt['customer_id'])}" class="btn btn-primary btn-xs" title="{Lang::T('View Customer')}">
                                        <i class="fa fa-user"></i>
                                    </a>
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {else}
                <div class="text-center text-muted" style="padding: 50px;">
                    <i class="fa fa-check-circle fa-4x text-success mb20"></i>
                    <h4>{Lang::T('No Active Debts')}</h4>
                    <p>{Lang::T('All customers are up to date with their payments.')}</p>
                </div>
                {/if}
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
