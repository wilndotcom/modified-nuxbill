{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Add CPE Router')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{Text::url('fiber/cpe-add-post')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Customer')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="customer_id" required>
                                <option value="">{Lang::T('Select Customer')}</option>
                                {foreach from=$customers item=customer}
                                <option value="{$customer['id']}">{$customer['fullname']} ({$customer['username']})</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Linked ONU')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="onu_id">
                                <option value="">{Lang::T('Select ONU (Optional)')}</option>
                                {foreach from=$onus item=onu}
                                <option value="{$onu['id']}">{$onu['serial_number']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('MAC Address')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="mac_address" placeholder="00:11:22:33:44:55" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('IP Address')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Brand')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="brand" required>
                                <option value="">{Lang::T('Select Brand')}</option>
                                <option value="TP-Link">TP-Link</option>
                                <option value="D-Link">D-Link</option>
                                <option value="Huawei">Huawei</option>
                                <option value="MikroTik">MikroTik</option>
                                <option value="Ubiquiti">Ubiquiti</option>
                                <option value="Cisco">Cisco</option>
                                <option value="Tenda">Tenda</option>
                                <option value="Other">{Lang::T('Other')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Model')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="model" placeholder="Archer C6">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Protocol')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="protocol">
                                <option value="HTTP">HTTP</option>
                                <option value="HTTPS">HTTPS</option>
                                <option value="SSH">SSH</option>
                                <option value="Telnet">Telnet</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Username')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="username" placeholder="admin">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Password')}</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="status">
                                <option value="Active">{Lang::T('Active')}</option>
                                <option value="Inactive">{Lang::T('Inactive')}</option>
                                <option value="Offline">{Lang::T('Offline')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Save')}</button>
                            <a href="{Text::url('fiber/cpe-routers')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
