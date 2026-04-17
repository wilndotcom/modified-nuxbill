{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Debt Notification Settings')}</div>
            <div class="panel-body">
                <form method="post" action="{Text::url('debt/settings')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <h4><i class="fa fa-cog"></i> {Lang::T('General Settings')}</h4>
                    <hr>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Enable Debt Notifications')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="enabled">
                                <option value="1" {if $settings['debt_notifications_enabled'] == '1'}selected{/if}>{Lang::T('Yes')}</option>
                                <option value="0" {if $settings['debt_notifications_enabled'] != '1'}selected{/if}>{Lang::T('No')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Notification Channels')}</label>
                        <div class="col-md-6">
                            <div class="checkbox">
                                {assign var=channels value=explode(',', $settings['debt_notification_channels'])}
                                <label><input type="checkbox" name="channels[]" value="SMS" {if in_array('SMS', $channels)}checked{/if}> {Lang::T('SMS')}</label><br>
                                <label><input type="checkbox" name="channels[]" value="WhatsApp" {if in_array('WhatsApp', $channels)}checked{/if}> {Lang::T('WhatsApp')}</label><br>
                                <label><input type="checkbox" name="channels[]" value="Email" {if in_array('Email', $channels)}checked{/if}> {Lang::T('Email')}</label><br>
                                <label><input type="checkbox" name="channels[]" value="Inbox" {if in_array('Inbox', $channels)}checked{/if}> {Lang::T('Customer Inbox')}</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Grace Period (Days)')}</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="grace_days" value="{$settings['debt_grace_period_days']}" min="1" max="90">
                            <small class="text-muted">{Lang::T('Days before disconnection')}</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Auto Disconnect')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="auto_disconnect">
                                <option value="1" {if $settings['debt_auto_disconnect'] == '1'}selected{/if}>{Lang::T('Yes')}</option>
                                <option value="0" {if $settings['debt_auto_disconnect'] != '1'}selected{/if}>{Lang::T('No')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <h4><i class="fa fa-clock-o"></i> {Lang::T('Notification Schedule')}</h4>
                    <hr>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Warning Days')}</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="warning_days" value="{$settings['debt_warning_days']}" min="1" max="30">
                            <small class="text-muted">{Lang::T('Days before deadline to send warning')}</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Final Notice Days')}</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="final_days" value="{$settings['debt_final_notice_days']}" min="1" max="5">
                            <small class="text-muted">{Lang::T('Days before deadline for final notice')}</small>
                        </div>
                    </div>
                    
                    <h4><i class="fa fa-envelope"></i> {Lang::T('Message Templates')}</h4>
                    <hr>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Initial Notification')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="message_initial" rows="2">{$settings['debt_message_initial']}</textarea>
                            <small class="text-muted">Variables: [[name]], [[amount]], [[days]]</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Warning Message')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="message_warning" rows="2">{$settings['debt_message_warning']}</textarea>
                            <small class="text-muted">Variables: [[name]], [[amount]], [[days]]</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Final Notice')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="message_final" rows="2">{$settings['debt_message_final']}</textarea>
                            <small class="text-muted">Variables: [[name]], [[amount]]</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Disconnection Notice')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="message_disconnection" rows="2">{$settings['debt_message_disconnection']}</textarea>
                            <small class="text-muted">Variables: [[name]], [[amount]]</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Save Settings')}</button>
                            <a href="{Text::url('debt/list')}" class="btn btn-default">{Lang::T('Back to List')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
