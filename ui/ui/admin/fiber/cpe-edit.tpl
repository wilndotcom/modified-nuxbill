{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Edit CPE Router')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{Text::url('fiber/cpe-edit-post')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$router['id']}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Customer')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="customer_id" required>
                                <option value="">{Lang::T('Select Customer')}</option>
                                {foreach from=$customers item=customer}
                                <option value="{$customer['id']}" {if $router['customer_id'] == $customer['id']}selected{/if}>{$customer['fullname']} ({$customer['username']})</option>
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
                                <option value="{$onu['id']}" {if $router['onu_id'] == $onu['id']}selected{/if}>{$onu['serial_number']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('MAC Address')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="mac_address" value="{$router['mac_address']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('IP Address')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="ip_address" value="{$router['ip_address']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Brand')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="brand" required>
                                <option value="">{Lang::T('Select Brand')}</option>
                                <option value="TP-Link" {if $router['brand'] == 'TP-Link'}selected{/if}>TP-Link</option>
                                <option value="D-Link" {if $router['brand'] == 'D-Link'}selected{/if}>D-Link</option>
                                <option value="Huawei" {if $router['brand'] == 'Huawei'}selected{/if}>Huawei</option>
                                <option value="MikroTik" {if $router['brand'] == 'MikroTik'}selected{/if}>MikroTik</option>
                                <option value="Ubiquiti" {if $router['brand'] == 'Ubiquiti'}selected{/if}>Ubiquiti</option>
                                <option value="Cisco" {if $router['brand'] == 'Cisco'}selected{/if}>Cisco</option>
                                <option value="Tenda" {if $router['brand'] == 'Tenda'}selected{/if}>Tenda</option>
                                <option value="Other" {if $router['brand'] == 'Other'}selected{/if}>{Lang::T('Other')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Model')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="model" value="{$router['model']}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Protocol')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="protocol">
                                <option value="HTTP" {if $router['protocol'] == 'HTTP'}selected{/if}>HTTP</option>
                                <option value="HTTPS" {if $router['protocol'] == 'HTTPS'}selected{/if}>HTTPS</option>
                                <option value="SSH" {if $router['protocol'] == 'SSH'}selected{/if}>SSH</option>
                                <option value="Telnet" {if $router['protocol'] == 'Telnet'}selected{/if}>Telnet</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Username')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="username" value="{$router['username']}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Password')}</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password" placeholder="{Lang::T('Leave blank to keep unchanged')}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="status">
                                <option value="Active" {if $router['status'] == 'Active'}selected{/if}>{Lang::T('Active')}</option>
                                <option value="Inactive" {if $router['status'] == 'Inactive'}selected{/if}>{Lang::T('Inactive')}</option>
                                <option value="Offline" {if $router['status'] == 'Offline'}selected{/if}>{Lang::T('Offline')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Update')}</button>
                            <a href="{Text::url('fiber/cpe-routers')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
