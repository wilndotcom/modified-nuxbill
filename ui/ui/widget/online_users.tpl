<div class="box box-primary box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-users"></i> {Lang::T('Online Users')}</h3>
        <div class="box-tools pull-right">
            <span class="badge bg-green">{$online_count|default:0}</span>
        </div>
    </div>
    <div class="box-body">
        {if isset($online_users) && count($online_users) > 0}
        <div class="table-responsive">
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>{Lang::T('Username')}</th>
                        <th>{Lang::T('IP Address')}</th>
                        <th>{Lang::T('Uptime')}</th>
                        <th>{Lang::T('Router')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$online_users item=user}
                    <tr>
                        <td>{$user['username']}</td>
                        <td>{$user['ip']}</td>
                        <td>{$user['uptime']}</td>
                        <td><span class="label label-info">{$user['router']}</span></td>
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
        <small class="text-muted">{Lang::T('Last updated')}: {$last_update|default:"Just now"}</small>
    </div>
</div>
