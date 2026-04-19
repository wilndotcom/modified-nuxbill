{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('CPE Routers')}
                <div class="panel-title pull-right">
                    <a href="{Text::url('fiber/cpe-add')}" class="btn btn-primary btn-xs">
                        <i class="fa fa-plus"></i> {Lang::T('Add CPE Router')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('MAC Address')}</th>
                                <th>{Lang::T('IP Address')}</th>
                                <th>{Lang::T('Brand/Model')}</th>
                                <th>{Lang::T('Customer')}</th>
                                <th>{Lang::T('ONU')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$routers item=router}
                            <tr>
                                <td>{$router['mac_address']}</td>
                                <td>{$router['ip_address']}</td>
                                <td>{$router['brand']} {$router['model']}</td>
                                <td>
                                    {if $router['customer_username']}
                                        {$router['customer_fullname']}
                                    {else}
                                        <span class="text-muted">{Lang::T('Unassigned')}</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $router['onu_serial']}
                                        {$router['onu_serial']}
                                    {else}
                                        <span class="text-muted">-</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $router['status'] == 'Active'}
                                    <span class="label label-success">{Lang::T('Active')}</span>
                                    {elseif $router['status'] == 'Offline'}
                                    <span class="label label-danger">{Lang::T('Offline')}</span>
                                    {else}
                                    <span class="label label-default">{$router['status']}</span>
                                    {/if}
                                </td>
                                <td>
                                    <a href="{Text::url('fiber/cpe-status/', $router['id'])}" class="btn btn-success btn-xs">
                                        <i class="fa fa-eye"></i> {Lang::T('Status')}
                                    </a>
                                    <a href="{Text::url('fiber/cpe-configure/', $router['id'])}" class="btn btn-primary btn-xs">
                                        <i class="fa fa-cog"></i> {Lang::T('Configure')}
                                    </a>
                                    <a href="{Text::url('fiber/cpe-edit/', $router['id'])}" class="btn btn-info btn-xs">
                                        <i class="fa fa-edit"></i> {Lang::T('Edit')}
                                    </a>
                                    <a href="{Text::url('fiber/cpe-delete/', $router['id'])}?token={$csrf_token}"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('{Lang::T('Are you sure you want to delete this CPE router?')}');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td colspan="7" class="text-center">{Lang::T('No CPE routers found')}</td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
