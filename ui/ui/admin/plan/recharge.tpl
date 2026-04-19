{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="panel panel-primary panel-hovered panel-stacked mb30">
            <div class="panel-heading">{Lang::T('Recharge Account')}</div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" role="form" action="{Text::url('')}plan/recharge-confirm">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Select Account')}</label>
                        <div class="col-md-6">
                            <select {if $cust}{else}id="personSelect" {/if} class="form-control select2"
                                name="id_customer" style="width: 100%"
                                data-placeholder="{Lang::T('Select a customer')}...">
                                {if $cust}
                                    <option value="{$cust['id']}">{$cust['username']} &bull; {$cust['fullname']} &bull;
                                        {$cust['email']}</option>
                                {/if}
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Type')}</label>
                        <div class="col-md-6">
                            <label><input type="radio" id="Hot" name="type" value="Hotspot">
                                {Lang::T('Hotspot Plans')}</label>
                            <label><input type="radio" id="POE" name="type" value="PPPOE">
                                {Lang::T('PPPOE Plans')}</label>
                            <label><input type="radio" id="VPN" name="type" value="VPN"> {Lang::T('VPN Plans')}</label>
                            <label><input type="radio" id="OLT" name="type" value="OLT"> {Lang::T('OLT/Fiber Plans')}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Routers')}</label>
                        <div class="col-md-6">
                            <select id="server" data-type="server" name="server" class="form-control select2">
                                <option value=''>{Lang::T('Select Routers')}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Service Plan')}</label>
                        <div class="col-md-6">
                            <select id="plan" name="plan" class="form-control select2">
                                <option value=''>{Lang::T('Select Plans')}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Using')}</label>
                        <div class="col-md-6">
                            <select name="using" class="form-control">
                                {foreach $usings as $using}
                                    <option value="{trim($using)}">{trim(ucWords($using))}</option>
                                {/foreach}
                                {if $_c['enable_balance'] eq 'yes'}
                                    <option value="balance">{Lang::T('Customer Balance')}</option>
                                {/if}
                                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                    <option value="zero">{$_c['currency_code']} 0</option>
                                {/if}
                            </select>
                        </div>
                        <p class="help-block col-md-4">{Lang::T('Postpaid Recharge for the first time use')}
                            {$_c['currency_code']} 0</p>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-success"
                                onclick="return ask(this, '{Lang::T('Continue the Recharge process')}?')"
                                type="submit">{Lang::T('Recharge')}</button>
                            {Lang::T('Or')} <a href="{Text::url('')}customers/list">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Handle type change for OLT
    var typeRadios = document.querySelectorAll('input[name="type"]');
    var serverSelect = document.getElementById('server');
    var planSelect = document.getElementById('plan');
    var serverLabel = document.querySelector('label[for="server"]') || document.querySelector('label:contains("Routers")');

    function updateServerLabel(type) {
        var label = document.querySelector('label.col-md-2.control-label');
        var labels = document.querySelectorAll('label.col-md-2.control-label');
        for (var i = 0; i < labels.length; i++) {
            if (labels[i].textContent.indexOf('Routers') !== -1 || labels[i].textContent.indexOf('OLT') !== -1) {
                if (type === 'OLT') {
                    labels[i].textContent = 'OLT Device';
                } else {
                    labels[i].textContent = 'Routers';
                }
                break;
            }
        }
    }

    function loadServers(type) {
        serverSelect.innerHTML = '<option value="">{Lang::T('Select ')}' + (type === 'OLT' ? 'OLT Device' : 'Routers') + '</option>';

        if (type === 'OLT') {
            // Load OLT devices
            fetch('{Text::url('')}autoload/olt-devices')
                .then(response => response.text())
                .then(html => {
                    var temp = document.createElement('div');
                    temp.innerHTML = html;
                    var options = temp.querySelectorAll('option');
                    options.forEach(function(opt) {
                        if (opt.value) {
                            var newOpt = document.createElement('option');
                            newOpt.value = opt.value;
                            newOpt.textContent = opt.textContent;
                            serverSelect.appendChild(newOpt);
                        }
                    });
                });
        } else {
            // Load regular routers
            fetch('{Text::url('')}autoload/server')
                .then(response => response.text())
                .then(html => {
                    var temp = document.createElement('div');
                    temp.innerHTML = html;
                    var options = temp.querySelectorAll('option');
                    options.forEach(function(opt) {
                        if (opt.value) {
                            var newOpt = document.createElement('option');
                            newOpt.value = opt.value;
                            newOpt.textContent = opt.textContent;
                            serverSelect.appendChild(newOpt);
                        }
                    });
                });
        }
    }

    function loadPlans(server, type) {
        planSelect.innerHTML = '<option value="">{Lang::T('Select Plans')}</option>';
        if (!server) return;

        var formData = new FormData();
        formData.append('server', server);
        formData.append('jenis', type);

        fetch('{Text::url('')}autoload/plan', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            var temp = document.createElement('div');
            temp.innerHTML = html;
            var options = temp.querySelectorAll('option');
            options.forEach(function(opt) {
                if (opt.value) {
                    var newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.textContent = opt.textContent;
                    planSelect.appendChild(newOpt);
                }
            });
        });
    }

    // Type change handler
    typeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                updateServerLabel(this.value);
                loadServers(this.value);
                planSelect.innerHTML = '<option value="">{Lang::T('Select Plans')}</option>';
            }
        });
    });

    // Server change handler
    serverSelect.addEventListener('change', function() {
        var selectedType = document.querySelector('input[name="type"]:checked');
        if (selectedType && this.value) {
            loadPlans(this.value, selectedType.value);
        }
    });
});
</script>

{include file="sections/footer.tpl"}