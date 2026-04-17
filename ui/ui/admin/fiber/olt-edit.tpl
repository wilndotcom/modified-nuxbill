{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Edit OLT Device')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{Text::url('fiber/olt-edit-post')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$device['id']}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Name')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" value="{$device['name']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Brand')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="brand" required>
                                <option value="">{Lang::T('Select Brand')}</option>
                                <option value="Huawei" {if $device['brand'] == 'Huawei'}selected{/if}>Huawei</option>
                                <option value="ZTE" {if $device['brand'] == 'ZTE'}selected{/if}>ZTE</option>
                                <option value="FiberHome" {if $device['brand'] == 'FiberHome'}selected{/if}>FiberHome</option>
                                <option value="Nokia" {if $device['brand'] == 'Nokia'}selected{/if}>Nokia</option>
                                <option value="Calix" {if $device['brand'] == 'Calix'}selected{/if}>Calix</option>
                                <option value="Other" {if $device['brand'] == 'Other'}selected{/if}>{Lang::T('Other')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('IP Address')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="ip_address" value="{$device['ip_address']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Port')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="port" value="{$device['port']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Username')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="username" value="{$device['username']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Password')}</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password" placeholder="{Lang::T('Leave blank to keep unchanged')}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Description')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="description" rows="3">{$device['description']}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="status">
                                <option value="Active" {if $device['status'] == 'Active'}selected{/if}>{Lang::T('Active')}</option>
                                <option value="Inactive" {if $device['status'] == 'Inactive'}selected{/if}>{Lang::T('Inactive')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Update')}</button>
                            <a href="{Text::url('fiber/olt-devices')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
