{include file="admin/header.tpl"}

<section class="content-header">
    <h1>
        {Lang::T('Edit Device')}
        <small>{$device->name}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{Text::url('dashboard')}"><i class="fa fa-dashboard"></i> {Lang::T('Dashboard')}</a></li>
        <li><a href="{Text::url('device_access/dashboard')}">{Lang::T('Device Access')}</a></li>
        <li class="active">{Lang::T('Edit Device')}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title">{Lang::T('Device Information')}</h3>
        </div>
        <form method="post" action="{Text::url('device_access/edit-post')}">
            <input type="hidden" name="csrf_token" value="{$_SESSION['csrf_token']}">
            <input type="hidden" name="id" value="{$device->id}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Device Name')} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{$device->name}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Connection Type')} <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" required>
                                <option value="PPPoE" {if $device->type == 'PPPoE'}selected{/if}>PPPoE</option>
                                <option value="Static" {if $device->type == 'Static'}selected{/if}>Static</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Device Type')} <span class="text-danger">*</span></label>
                            <select class="form-control" name="device_type" required>
                                <option value="Tenda" {if $device->device_type == 'Tenda'}selected{/if}>Tenda</option>
                                <option value="Ubiquiti" {if $device->device_type == 'Ubiquiti'}selected{/if}>Ubiquiti</option>
                                <option value="Huawei" {if $device->device_type == 'Huawei'}selected{/if}>Huawei</option>
                                <option value="TP-Link" {if $device->device_type == 'TP-Link'}selected{/if}>TP-Link</option>
                                <option value="Other" {if $device->device_type == 'Other'}selected{/if}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('IP Address')} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ip_address" value="{$device->ip_address}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('PPPoE Username')}</label>
                            <input type="text" class="form-control" name="pppoe_username" value="{$device->pppoe_username}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Port')}</label>
                            <input type="number" class="form-control" name="port" value="{$device->port}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Router')}</label>
                            <select class="form-control" name="router_id">
                                <option value="">{Lang::T('Select Router')}</option>
                                {foreach from=$routers item=router}
                                    <option value="{$router['id']}" {if $device->router_id == $router['id']}selected{/if}>{$router['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Access URL')}</label>
                            <input type="text" class="form-control" name="access_url" value="{$device->access_url}" placeholder="http://192.168.1.100:80">
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fa fa-save"></i> {Lang::T('Update Device')}
                </button>
                <a href="{Text::url('device_access/dashboard')}" class="btn btn-default">
                    <i class="fa fa-times"></i> {Lang::T('Cancel')}
                </a>
            </div>
        </form>
    </div>
</section>

{include file="admin/footer.tpl"}
