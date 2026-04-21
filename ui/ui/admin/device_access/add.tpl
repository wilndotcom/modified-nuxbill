{include file="admin/header.tpl"}

<section class="content-header">
    <h1>
        {Lang::T('Add Device')}
        <small>{Lang::T('CPE Device')}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{Text::url('dashboard')}"><i class="fa fa-dashboard"></i> {Lang::T('Dashboard')}</a></li>
        <li><a href="{Text::url('device_access/dashboard')}">{Lang::T('Device Access')}</a></li>
        <li class="active">{Lang::T('Add Device')}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{Lang::T('Device Information')}</h3>
        </div>
        <form method="post" action="{Text::url('device_access/add-post')}">
            <input type="hidden" name="csrf_token" value="{$_SESSION['csrf_token']}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Device Name')} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="CPE-001">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Connection Type')} <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" required>
                                <option value="PPPoE">PPPoE</option>
                                <option value="Static">Static</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Device Type')} <span class="text-danger">*</span></label>
                            <select class="form-control" name="device_type" required>
                                <option value="Tenda">Tenda</option>
                                <option value="Ubiquiti">Ubiquiti</option>
                                <option value="Huawei">Huawei</option>
                                <option value="TP-Link">TP-Link</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('IP Address')} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ip_address" required placeholder="192.168.1.100">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('PPPoE Username')}</label>
                            <input type="text" class="form-control" name="pppoe_username" placeholder="pppoe_user">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Port')}</label>
                            <input type="number" class="form-control" name="port" value="80" placeholder="80">
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
                                    <option value="{$router['id']}">{$router['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{Lang::T('Access URL')}</label>
                            <input type="text" class="form-control" name="access_url" placeholder="http://192.168.1.100:80">
                            <small class="text-muted">Leave empty to auto-generate from IP and Port</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> {Lang::T('Save Device')}
                </button>
                <a href="{Text::url('device_access/dashboard')}" class="btn btn-default">
                    <i class="fa fa-times"></i> {Lang::T('Cancel')}
                </a>
            </div>
        </form>
    </div>
</section>

{include file="admin/footer.tpl"}
