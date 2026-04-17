{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {if $user_type == 'hotspot'}
                    <i class="fa fa-wifi"></i> {Lang::T('Online Hotspot Users')}
                {elseif $user_type == 'pppoe'}
                    <i class="fa fa-plug"></i> {Lang::T('Online PPPoE Users')}
                {elseif $user_type == 'static'}
                    <i class="fa fa-desktop"></i> {Lang::T('Online Static Users')}
                {else}
                    <i class="fa fa-users"></i> {Lang::T('All Online Users')}
                {/if}
                <span class="badge bg-green">{$total_count|default:0}</span>
                <div class="panel-title pull-right">
                    <a href="{Text::url('dashboard')}" class="btn btn-default btn-xs">
                        <i class="fa fa-arrow-left"></i> {Lang::T('Back to Dashboard')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                {if isset($online_users) && count($online_users) > 0}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="online-users-table">
                        <thead>
                            <tr>
                                <th>{Lang::T('Username')}</th>
                                <th>{Lang::T('Type')}</th>
                                <th>{Lang::T('IP Address')}</th>
                                <th>{Lang::T('MAC Address')}</th>
                                <th>{Lang::T('Uptime')}</th>
                                <th>{Lang::T('Router')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$online_users item=user}
                            <tr>
                                <td>
                                    <strong>{$user['username']}</strong>
                                    {if isset($user['customer_id'])}
                                        <br><small class="text-muted">{$user['customer_fullname']}</small>
                                    {/if}
                                </td>
                                <td>
                                    {if $user['type'] == 'hotspot'}
                                    <span class="label label-info"><i class="fa fa-wifi"></i> Hotspot</span>
                                    {elseif $user['type'] == 'pppoe'}
                                    <span class="label label-success"><i class="fa fa-plug"></i> PPPoE</span>
                                    {else}
                                    <span class="label label-warning"><i class="fa fa-desktop"></i> Static</span>
                                    {/if}
                                </td>
                                <td><code>{$user['ip']}</code></td>
                                <td><code>{$user['mac']|default:'-'}</code></td>
                                <td>{$user['uptime']|default:'-'}</td>
                                <td><span class="label label-default">{$user['router']}</span></td>
                                <td>
                                    {if isset($user['customer_id'])}
                                    <a href="{Text::url('customers/view/', $user['customer_id'])}" class="btn btn-info btn-xs" title="{Lang::T('View Customer')}">
                                        <i class="fa fa-user"></i>
                                    </a>
                                    {/if}
                                    <button class="btn btn-danger btn-xs" onclick="kickUser('{$user['username']}', '{$user['router']}')" title="{Lang::T('Kick User')}">
                                        <i class="fa fa-sign-out"></i>
                                    </button>
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {else}
                <div class="text-center text-muted" style="padding: 50px;">
                    <i class="fa fa-users fa-4x mb20"></i>
                    <h4>{Lang::T('No online users found')}</h4>
                    <p>{Lang::T('There are currently no users connected to the network.')}</p>
                </div>
                {/if}
            </div>
            <div class="panel-footer">
                <small class="text-muted">
                    {Lang::T('Last updated')}: {$last_update|default:date('Y-m-d H:i:s')} | 
                    {Lang::T('Total')}: {$total_count|default:0} {Lang::T('users')}
                </small>
                <button class="btn btn-primary btn-xs pull-right" onclick="refreshData()">
                    <i class="fa fa-refresh"></i> {Lang::T('Refresh')}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function kickUser(username, router) {
    if (confirm('{Lang::T('Are you sure you want to disconnect this user?')}')) {
        fetch('{Text::url('dashboard')}?action=kick-user&username=' + encodeURIComponent(username) + '&router=' + encodeURIComponent(router) + '&token={$csrf_token}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('{Lang::T('User has been disconnected')}');
                    location.reload();
                } else {
                    alert('{Lang::T('Error')}: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{Lang::T('Failed to disconnect user')}');
            });
    }
}

function refreshData() {
    location.reload();
}

// Auto-refresh every 60 seconds
setTimeout(function() {
    location.reload();
}, 60000);
</script>

{include file="sections/footer.tpl"}
