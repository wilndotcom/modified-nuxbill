{include file="admin/header.tpl"}

<section class="content-header">
    <h1>
        {Lang::T('All Devices')}
        <small>{Lang::T('CPE Device Management')}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{Text::url('dashboard')}"><i class="fa fa-dashboard"></i> {Lang::T('Dashboard')}</a></li>
        <li><a href="{Text::url('device_access/dashboard')}">{Lang::T('Device Access')}</a></li>
        <li class="active">{Lang::T('All Devices')}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> {Lang::T('All Devices')}</h3>
            <div class="box-tools pull-right">
                <a href="{Text::url('device_access/add')}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> {Lang::T('Add Device')}
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{Lang::T('ID')}</th>
                            <th>{Lang::T('Name')}</th>
                            <th>{Lang::T('Type')}</th>
                            <th>{Lang::T('Device Type')}</th>
                            <th>{Lang::T('IP Address')}</th>
                            <th>{Lang::T('PPPoE Username')}</th>
                            <th>{Lang::T('Router')}</th>
                            <th>{Lang::T('Port')}</th>
                            <th>{Lang::T('Created')}</th>
                            <th>{Lang::T('Actions')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if count($devices) > 0}
                            {foreach from=$devices item=device}
                                <tr>
                                    <td>{$device['id']}</td>
                                    <td>{$device['name']}</td>
                                    <td>
                                        <span class="label label-{if $device['type'] == 'PPPoE'}success{else}warning{/if}">{$device['type']}</span>
                                    </td>
                                    <td>{$device['device_type']}</td>
                                    <td>{$device['ip_address']}</td>
                                    <td>{if $device['pppoe_username']}{$device['pppoe_username']}{else}<span class="text-muted">-</span>{/if}</td>
                                    <td>{if $device['router_name']}{$device['router_name']}{else}<span class="text-muted">-</span>{/if}</td>
                                    <td>{$device['port']}</td>
                                    <td>{if $device['created_at']}{$device['created_at']}{else}<span class="text-muted">-</span>{/if}</td>
                                    <td>
                                        <a href="{Text::url('device_access/edit', $device['id'])}" class="btn btn-xs btn-warning">
                                            <i class="fa fa-edit"></i> {Lang::T('Edit')}
                                        </a>
                                        <a href="{Text::url('device_access/delete', $device['id'])}" class="btn btn-xs btn-danger" onclick="return confirm('{Lang::T('Are you sure?')}')">
                                            <i class="fa fa-trash"></i> {Lang::T('Delete')}
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="10" class="text-center text-muted">{Lang::T('No devices found.')}</td>
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{include file="admin/footer.tpl"}
