{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                <a href="{Text::url('fiber/cpe-routers')}" class="btn btn-primary btn-xs pull-right">
                    <i class="fa fa-arrow-left"></i> {Lang::T('Back to List')}
                </a>
                {Lang::T('Configure CPE Router')}: {$router['brand']} {$router['model']}
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" role="form" action="{Text::url('fiber/cpe-configure-post/', $router['id'])}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <!-- WiFi Settings -->
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-wifi"></i> {Lang::T('WiFi Settings')}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('2.4GHz SSID')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="wifi_2g_ssid" value="{$config['wifi_2g_ssid']|default:''}" placeholder="MyWiFi-2G">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('2.4GHz Password')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="wifi_2g_password" value="{$config['wifi_2g_password']|default:''}" placeholder="Min 8 characters">
                                    <span class="help-block">{Lang::T('Leave blank to keep current password')}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('2.4GHz Channel')}</label>
                                <div class="col-md-5">
                                    <select name="wifi_2g_channel" class="form-control">
                                        <option value="auto" {if $config['wifi_2g_channel'] == 'auto'}selected{/if}>{Lang::T('Auto')}</option>
                                        <option value="1" {if $config['wifi_2g_channel'] == '1'}selected{/if}>1</option>
                                        <option value="6" {if $config['wifi_2g_channel'] == '6'}selected{/if}>6</option>
                                        <option value="11" {if $config['wifi_2g_channel'] == '11'}selected{/if}>11</option>
                                    </select>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('5GHz SSID')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="wifi_5g_ssid" value="{$config['wifi_5g_ssid']|default:''}" placeholder="MyWiFi-5G">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('5GHz Password')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="wifi_5g_password" value="{$config['wifi_5g_password']|default:''}" placeholder="Min 8 characters">
                                    <span class="help-block">{Lang::T('Leave blank to keep current password')}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('5GHz Channel')}</label>
                                <div class="col-md-5">
                                    <select name="wifi_5g_channel" class="form-control">
                                        <option value="auto" {if $config['wifi_5g_channel'] == 'auto'}selected{/if}>{Lang::T('Auto')}</option>
                                        <option value="36" {if $config['wifi_5g_channel'] == '36'}selected{/if}>36</option>
                                        <option value="40" {if $config['wifi_5g_channel'] == '40'}selected{/if}>40</option>
                                        <option value="44" {if $config['wifi_5g_channel'] == '44'}selected{/if}>44</option>
                                        <option value="149" {if $config['wifi_5g_channel'] == '149'}selected{/if}>149</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('WiFi Security')}</label>
                                <div class="col-md-5">
                                    <select name="wifi_security" class="form-control">
                                        <option value="WPA2" {if $config['wifi_security'] == 'WPA2'}selected{/if}>WPA2</option>
                                        <option value="WPA3" {if $config['wifi_security'] == 'WPA3'}selected{/if}>WPA3</option>
                                        <option value="WPA2/WPA3" {if $config['wifi_security'] == 'WPA2/WPA3'}selected{/if}>WPA2/WPA3 Mixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- LAN Settings -->
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-network-wired"></i> {Lang::T('LAN Settings')}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('LAN IP Address')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="lan_ip" value="{$config['lan_ip']|default:'192.168.1.1'}" placeholder="192.168.1.1">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('Subnet Mask')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="lan_subnet" value="{$config['lan_subnet']|default:'255.255.255.0'}" placeholder="255.255.255.0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('DHCP Server')}</label>
                                <div class="col-md-5">
                                    <select name="dhcp_enabled" class="form-control">
                                        <option value="1" {if $config['dhcp_enabled'] != '0'}selected{/if}>{Lang::T('Enabled')}</option>
                                        <option value="0" {if $config['dhcp_enabled'] == '0'}selected{/if}>{Lang::T('Disabled')}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('DHCP Range Start')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="dhcp_start" value="{$config['dhcp_start']|default:'192.168.1.100'}" placeholder="192.168.1.100">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('DHCP Range End')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="dhcp_end" value="{$config['dhcp_end']|default:'192.168.1.200'}" placeholder="192.168.1.200">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Management Settings -->
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-cogs"></i> {Lang::T('Management Settings')}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('Admin Username')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="admin_username" value="{$config['admin_username']|default:'admin'}" placeholder="admin">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('Admin Password')}</label>
                                <div class="col-md-5">
                                    <input type="password" class="form-control" name="admin_password" placeholder="{Lang::T('Leave blank to keep unchanged')}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('Remote Management')}</label>
                                <div class="col-md-5">
                                    <select name="remote_management" class="form-control">
                                        <option value="1" {if $config['remote_management'] == '1'}selected{/if}>{Lang::T('Enabled')}</option>
                                        <option value="0" {if $config['remote_management'] != '1'}selected{/if}>{Lang::T('Disabled')}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('TR-069 ACS URL')}</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="tr069_acs_url" value="{$config['tr069_acs_url']|default:''}" placeholder="http://acs.example.com:7547">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Firewall Settings -->
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-shield-alt"></i> {Lang::T('Firewall Settings')}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('SPI Firewall')}</label>
                                <div class="col-md-5">
                                    <select name="firewall_spi" class="form-control">
                                        <option value="1" {if $config['firewall_spi'] != '0'}selected{/if}>{Lang::T('Enabled')}</option>
                                        <option value="0" {if $config['firewall_spi'] == '0'}selected{/if}>{Lang::T('Disabled')}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">{Lang::T('Ping from WAN')}</label>
                                <div class="col-md-5">
                                    <select name="wan_ping" class="form-control">
                                        <option value="0" {if $config['wan_ping'] != '1'}selected{/if}>{Lang::T('Disabled')}</option>
                                        <option value="1" {if $config['wan_ping'] == '1'}selected{/if}>{Lang::T('Enabled')}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Save Button -->
                    <div class="form-group">
                        <div class="col-md-offset-3 col-md-9">
                            <button class="btn btn-success" type="submit">
                                <i class="fa fa-save"></i> {Lang::T('Save Configuration')}
                            </button>
                            <a href="{Text::url('fiber/cpe-status/', $router['id'])}" class="btn btn-info">
                                <i class="fa fa-eye"></i> {Lang::T('View Status')}
                            </a>
                            <a href="{Text::url('fiber/cpe-routers')}" class="btn btn-default">
                                {Lang::T('Cancel')}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
