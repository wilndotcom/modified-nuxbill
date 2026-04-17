{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('ONUs')}
                <div class="panel-title pull-right">
                    <a href="{Text::url('fiber/onu-add')}" class="btn btn-primary btn-xs">
                        <i class="fa fa-plus"></i> {Lang::T('Add ONU')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('Serial Number')}</th>
                                <th>{Lang::T('ONU ID')}</th>
                                <th>{Lang::T('OLT')}</th>
                                <th>{Lang::T('Customer')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$onus item=onu}
                            <tr>
                                <td>{$onu['serial_number']}</td>
                                <td>{$onu['onu_id']}</td>
                                <td>{$onu['olt_name']}</td>
                                <td>
                                    {if $onu['customer_username']}
                                        {$onu['customer_fullname']} ({$onu['customer_username']})
                                    {else}
                                        <span class="text-muted">{Lang::T('Unassigned')}</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $onu['status'] == 'Active'}
                                    <span class="label label-success">{Lang::T('Active')}</span>
                                    {elseif $onu['status'] == 'Suspended'}
                                    <span class="label label-warning">{Lang::T('Suspended')}</span>
                                    {else}
                                    <span class="label label-default">{Lang::T('Inactive')}</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $onu['status'] != 'Active'}
                                    <a href="{Text::url('fiber/onu-activate/', $onu['id'])}?token={$csrf_token}"
                                       class="btn btn-success btn-xs" title="{Lang::T('Activate')}">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    {/if}
                                    {if $onu['status'] == 'Active'}
                                    <a href="{Text::url('fiber/onu-suspend/', $onu['id'])}?token={$csrf_token}"
                                       class="btn btn-warning btn-xs" title="{Lang::T('Suspend')}">
                                        <i class="fa fa-pause"></i>
                                    </a>
                                    {/if}
                                    <a href="{Text::url('fiber/onu-edit/', $onu['id'])}" class="btn btn-info btn-xs">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="{Text::url('fiber/onu-delete/', $onu['id'])}?token={$csrf_token}"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('{Lang::T('Are you sure you want to delete this ONU?')}');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td colspan="6" class="text-center">{Lang::T('No ONUs found')}</td>
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
