{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Edit ONU')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{Text::url('fiber/onu-edit-post')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$onu['id']}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('OLT')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="olt_id" required>
                                <option value="">{Lang::T('Select OLT')}</option>
                                {foreach from=$olts item=olt}
                                <option value="{$olt['id']}" {if $onu['olt_id'] == $olt['id']}selected{/if}>{$olt['name']} ({$olt['ip_address']})</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Serial Number')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="serial_number" value="{$onu['serial_number']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('ONU ID')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="onu_id" value="{$onu['onu_id']}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('PON Port')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="pon_port" value="{$onu['pon_port']}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Customer')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="customer_id">
                                <option value="">{Lang::T('Select Customer')}</option>
                                {foreach from=$customers item=customer}
                                <option value="{$customer['id']}" {if $onu['customer_id'] == $customer['id']}selected{/if}>{$customer['fullname']} ({$customer['username']})</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Profile')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="profile_id">
                                <option value="">{Lang::T('Select Profile')}</option>
                                {foreach from=$profiles item=profile}
                                <option value="{$profile['id']}" {if $onu['profile_id'] == $profile['id']}selected{/if}>{$profile['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="status">
                                <option value="Inactive" {if $onu['status'] == 'Inactive'}selected{/if}>{Lang::T('Inactive')}</option>
                                <option value="Active" {if $onu['status'] == 'Active'}selected{/if}>{Lang::T('Active')}</option>
                                <option value="Suspended" {if $onu['status'] == 'Suspended'}selected{/if}>{Lang::T('Suspended')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Update')}</button>
                            <a href="{Text::url('fiber/onus')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
