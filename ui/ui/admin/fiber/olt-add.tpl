{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">{Lang::T('Add OLT Device')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{Text::url('fiber/olt-add-post')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Name')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Brand')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="brand" required>
                                <option value="">{Lang::T('Select Brand')}</option>
                                <option value="Huawei">Huawei</option>
                                <option value="ZTE">ZTE</option>
                                <option value="VSOL">VSOL</option>
                                <option value="BDCOM">BDCOM</option>
                                <option value="HiOSO">HiOSO</option>
                                <option value="FiberHome">FiberHome</option>
                                <option value="Nokia">Nokia</option>
                                <option value="Calix">Calix</option>
                                <option value="Other">{Lang::T('Other')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('IP Address')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.10" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Port')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="port" value="23" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Username')} <span class="text-danger">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Password')}</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Description')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="status">
                                <option value="Active">{Lang::T('Active')}</option>
                                <option value="Inactive">{Lang::T('Inactive')}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{Lang::T('Save')}</button>
                            <a href="{Text::url('fiber/olt-devices')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
