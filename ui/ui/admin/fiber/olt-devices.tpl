{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('OLT Devices')}
                <div class="panel-title pull-right">
                    <a href="{Text::url('fiber/olt-add')}" class="btn btn-primary btn-xs">
                        <i class="fa fa-plus"></i> {Lang::T('Add OLT')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('Name')}</th>
                                <th>{Lang::T('Brand')}</th>
                                <th>{Lang::T('IP Address')}</th>
                                <th>{Lang::T('Port')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Description')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$devices item=device}
                            <tr>
                                <td>{$device['name']}</td>
                                <td><span class="label label-info">{$device['brand']}</span></td>
                                <td>{$device['ip_address']}</td>
                                <td>{$device['port']}</td>
                                <td>
                                    {if $device['status'] == 'Active'}
                                    <span class="label label-success">{Lang::T('Active')}</span>
                                    {else}
                                    <span class="label label-danger">{Lang::T('Inactive')}</span>
                                    {/if}
                                </td>
                                <td>{$device['description']}</td>
                                <td>
                                    <a href="{Text::url('fiber/olt-edit/', $device['id'])}" class="btn btn-info btn-xs">
                                        <i class="fa fa-edit"></i> {Lang::T('Edit')}
                                    </a>
                                    <a href="{Text::url('fiber/olt-delete/', $device['id'])}?token={$csrf_token}"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('{Lang::T('Are you sure you want to delete this OLT device?')}');">
                                        <i class="fa fa-trash"></i> {Lang::T('Delete')}
                                    </a>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td colspan="7" class="text-center">{Lang::T('No OLT devices found')}</td>
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
