{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Add Profile')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{Text::url('fiber/profile-add-post')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Profile Name')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" placeholder="100Mbps-Down-50Mbps-Up" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('OLT')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="olt_id" required>
                                <option value="">{Lang::T('Select OLT')}</option>
                                {foreach from=$olts item=olt}
                                <option value="{$olt['id']}">{$olt['name']}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Download Speed')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" class="form-control" name="download_speed" required>
                                <span class="input-group-addon">Mbps</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Upload Speed')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" class="form-control" name="upload_speed" required>
                                <span class="input-group-addon">Mbps</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Line Profile')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="line_profile" placeholder="{Lang::T('OLT specific line profile name')}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Service Profile')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="service_profile" placeholder="{Lang::T('OLT specific service profile name')}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Description')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Save')}</button>
                            <a href="{Text::url('fiber/profiles')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
