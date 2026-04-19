{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                <a href="{Text::url('fiber/cpe-routers')}" class="btn btn-primary btn-xs pull-right">
                    <i class="fa fa-arrow-left"></i> {Lang::T('Back to List')}
                </a>
                {Lang::T('CPE Router Status')}: {$router['brand']} {$router['model']}
            </div>
            <div class="panel-body">
                <!-- Router Info Card -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-info-circle"></i> {Lang::T('Router Information')}</h3>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>{Lang::T('MAC Address')}</strong></td>
                                        <td>{$router['mac_address']}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('IP Address')}</strong></td>
                                        <td>{$router['ip_address']}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Brand / Model')}</strong></td>
                                        <td>{$router['brand']} {$router['model']}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Protocol')}</strong></td>
                                        <td>{$router['protocol']}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Status')}</strong></td>
                                        <td>
                                            {if $router['status'] == 'Active'}
                                                <span class="label label-success">{Lang::T('Active')}</span>
                                            {elseif $router['status'] == 'Offline'}
                                                <span class="label label-danger">{Lang::T('Offline')}</span>
                                            {else}
                                                <span class="label label-default">{$router['status']}</span>
                                            {/if}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Last Seen')}</strong></td>
                                        <td>{$router['updated_at']|default:$router['created_at']}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-user"></i> {Lang::T('Customer & ONU')}</h3>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>{Lang::T('Customer')}</strong></td>
                                        <td>
                                            {if $router['customer_id']}
                                                <a href="{Text::url('customers/view/', $router['customer_id'])}">
                                                    {$router['customer_fullname']|default:$router['customer_username']}
                                                </a>
                                            {else}
                                                <span class="text-muted">{Lang::T('Unassigned')}</span>
                                            {/if}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('ONU')}</strong></td>
                                        <td>
                                            {if $router['onu_id']}
                                                <a href="{Text::url('fiber/onu-edit/', $router['onu_id'])}">
                                                    {$router['onu_serial']|default:Lang::T('View ONU')}
                                                </a>
                                            {else}
                                                <span class="text-muted">{Lang::T('Not Connected')}</span>
                                            {/if}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-bolt"></i> {Lang::T('Quick Actions')}</h3>
                            </div>
                            <div class="panel-body">
                                <a href="{Text::url('fiber/cpe-configure/', $router['id'])}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-cog"></i> {Lang::T('Configure')}
                                </a>
                                <a href="{Text::url('fiber/cpe-reboot/', $router['id'])}?token={$csrf_token}" 
                                   class="btn btn-warning btn-sm"
                                   onclick="return confirm('{Lang::T('Are you sure you want to reboot this router?')}')">
                                    <i class="fa fa-refresh"></i> {Lang::T('Reboot')}
                                </a>
                                <a href="{Text::url('fiber/cpe-reset/', $router['id'])}?token={$csrf_token}" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('{Lang::T('WARNING: This will reset the router to factory defaults. Continue?')}')">
                                    <i class="fa fa-undo"></i> {Lang::T('Factory Reset')}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status Information -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-chart-bar"></i> {Lang::T('Router Status')}</h3>
                            </div>
                            <div class="panel-body">
                                {if $status_data}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="well text-center">
                                                <h4>{Lang::T('Uptime')}</h4>
                                                <p class="h2">{$status_data['uptime']|default:'--'}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="well text-center">
                                                <h4>{Lang::T('CPU Load')}</h4>
                                                <p class="h2">{$status_data['cpu']|default:'--'}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="well text-center">
                                                <h4>{Lang::T('Memory')}</h4>
                                                <p class="h2">{$status_data['memory']|default:'--'}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="well text-center">
                                                <h4>{Lang::T('Temperature')}</h4>
                                                <p class="h2">{$status_data['temperature']|default:'--'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {if $status_data['interfaces']}
                                        <h4>{Lang::T('Network Interfaces')}</h4>
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{Lang::T('Interface')}</th>
                                                    <th>{Lang::T('Status')}</th>
                                                    <th>{Lang::T('RX Data')}</th>
                                                    <th>{Lang::T('TX Data')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {foreach $status_data['interfaces'] as $iface}
                                                    <tr>
                                                        <td>{$iface['name']}</td>
                                                        <td>
                                                            {if $iface['status'] == 'up'}
                                                                <span class="label label-success">{Lang::T('Up')}</span>
                                                            {else}
                                                                <span class="label label-danger">{Lang::T('Down')}</span>
                                                            {/if}
                                                        </td>
                                                        <td>{$iface['rx']}</td>
                                                        <td>{$iface['tx']}</td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                    {/if}
                                {else}
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> {Lang::T('Status information is not available. The router may be offline or status retrieval is not supported for this device type.')}
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Connection Log -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-history"></i> {Lang::T('Connection History')}</h3>
                            </div>
                            <div class="panel-body">
                                {if $connection_logs}
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>{Lang::T('Date/Time')}</th>
                                                <th>{Lang::T('Event')}</th>
                                                <th>{Lang::T('IP Address')}</th>
                                                <th>{Lang::T('Status')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach $connection_logs as $log}
                                                <tr>
                                                    <td>{$log['created_at']}</td>
                                                    <td>{$log['event']}</td>
                                                    <td>{$log['ip_address']}</td>
                                                    <td>{$log['status']}</td>
                                                </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                {else}
                                    <p class="text-muted">{Lang::T('No connection history available.')}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
