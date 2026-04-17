<div class="box box-primary box-solid" id="online-users-widget">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-users"></i> {Lang::T('Online Users')}</h3>
        <div class="box-tools pull-right">
            <button class="btn btn-box-tool" onclick="refreshOnlineUsers()" title="{Lang::T('Refresh')}">
                <i class="fa fa-refresh" id="refresh-icon"></i>
            </button>
            <span class="badge bg-green" id="total-count">{$online_count|default:0}</span>
        </div>
    </div>
    <div class="box-body">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-6">
                <a href="{Text::url('dashboard/online-users/hotspot')}" class="small-box bg-aqua" style="display: block; text-decoration: none;">
                    <div class="inner">
                        <h3 id="hotspot-count">{$hotspot_count|default:0}</h3>
                        <p>{Lang::T('Hotspot Users')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-wifi"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <a href="{Text::url('dashboard/online-users/pppoe')}" class="small-box bg-green" style="display: block; text-decoration: none;">
                    <div class="inner">
                        <h3 id="pppoe-count">{$pppoe_count|default:0}</h3>
                        <p>{Lang::T('PPPoE Users')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-plug"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <a href="{Text::url('dashboard/online-users/static')}" class="small-box bg-yellow" style="display: block; text-decoration: none;">
                    <div class="inner">
                        <h3 id="static-count">{$static_count|default:0}</h3>
                        <p>{Lang::T('Static Users')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-desktop"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <a href="{Text::url('dashboard/online-users/all')}" class="small-box bg-red" style="display: block; text-decoration: none;">
                    <div class="inner">
                        <h3 id="total-count-box">{$online_count|default:0}</h3>
                        <p>{Lang::T('Total Online')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </a>
            </div>
        </div>
        
        {if isset($online_users) && count($online_users) > 0}
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>{Lang::T('Username')}</th>
                        <th>{Lang::T('Type')}</th>
                        <th>{Lang::T('IP Address')}</th>
                        <th>{Lang::T('Uptime')}</th>
                        <th>{Lang::T('Router')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$online_users item=user}
                    <tr>
                        <td>{$user['username']}</td>
                        <td><span class="label label-{if $user['type']=='hotspot'}info{elseif $user['type']=='pppoe'}success{else}warning{/if}">{$user['type']|ucfirst}</span></td>
                        <td>{$user['ip']}</td>
                        <td>{$user['uptime']}</td>
                        <td><span class="label label-default">{$user['router']}</span></td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        {else}
        <p class="text-center text-muted">{Lang::T('No users currently online')}</p>
        {/if}
    </div>
    <div class="box-footer text-center">
        <small class="text-muted">{Lang::T('Last updated')}: <span id="last-update">{$last_update|default:"Just now"}</span></small>
        <small class="text-muted pull-right">{Lang::T('Auto-refresh every 30s')}</small>
    </div>
</div>

<script>
function refreshOnlineUsers() {
    var icon = document.getElementById('refresh-icon');
    icon.classList.add('fa-spin');
    
    fetch('{Text::url('dashboard')}?action=online-users-refresh')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('hotspot-count').textContent = data.data.hotspot;
                document.getElementById('pppoe-count').textContent = data.data.pppoe;
                document.getElementById('static-count').textContent = data.data.static;
                document.getElementById('total-count').textContent = data.data.total;
                document.getElementById('total-count-box').textContent = data.data.total;
                document.getElementById('last-update').textContent = data.data.last_update;
            }
            icon.classList.remove('fa-spin');
        })
        .catch(error => {
            console.error('Error refreshing online users:', error);
            icon.classList.remove('fa-spin');
        });
}

// Auto-refresh every 30 seconds
setInterval(refreshOnlineUsers, 30000);
</script>
