{include file="admin/header.tpl"}

<!-- Content Header -->
<section class="content-header">
    <h1>
        {Lang::T('Device Access')}
        <small>{Lang::T('CPE Device Management Dashboard')}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{Text::url('dashboard')}"><i class="fa fa-dashboard"></i> {Lang::T('Dashboard')}</a></li>
        <li class="active">{Lang::T('Device Access')}</li>
    </ol>
</section>

<!-- Main content -->
<section class="content">
    <!-- Stats Cards -->
    <div class="row">
        <!-- Total Devices -->
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-wifi"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{Lang::T('Total Devices')}</span>
                    <span class="info-box-number">{$totalDevices}</span>
                </div>
            </div>
        </div>

        <!-- PPPoE Devices -->
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-plug"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{Lang::T('PPPoE Devices')}</span>
                    <span class="info-box-number">{$pppoeCount}</span>
                </div>
            </div>
        </div>

        <!-- Static Devices -->
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-orange">
                <span class="info-box-icon"><i class="fa fa-bolt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{Lang::T('Static Devices')}</span>
                    <span class="info-box-number">{$staticCount}</span>
                </div>
            </div>
        </div>

        <!-- Routers -->
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fa fa-server"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{Lang::T('Routers')}</span>
                    <span class="info-box-number">{$routerCount}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Device Type Distribution -->
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{Lang::T('Device Types')}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <canvas id="deviceCategoryChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- PPPoE vs Static Distribution -->
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{Lang::T('PPPoE vs Static')}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <canvas id="pppoeVsStaticChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Used Device Types Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{Lang::T('Most Used Device Types')}</h3>
                </div>
                <div class="box-body">
                    {foreach from=$topDeviceTypes key=type item=count}
                        <span class="badge bg-primary" style="font-size: 14px; margin: 5px;">{$type}: {$count}</span>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Table (Last 10 Devices Added) -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> {Lang::T('Last 10 Devices Added')}</h3>
            <div class="box-tools pull-right">
                <a href="{Text::url('device_access/add')}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> {Lang::T('Add Device')}
                </a>
                <a href="{Text::url('device_access/list')}" class="btn btn-default btn-sm">
                    <i class="fa fa-list"></i> {Lang::T('View All')}
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-6">
                    <!-- Device Type Filter -->
                    <form method="get" action="{Text::url('device_access/dashboard')}" class="form-inline">
                        <select name="category" class="form-control" onchange="this.form.submit()">
                            <option value="all" {if $categoryFilter == 'all'}selected{/if}>{Lang::T('All Types')}</option>
                            <option value="Tenda" {if $categoryFilter == 'Tenda'}selected{/if}>Tenda</option>
                            <option value="Ubiquiti" {if $categoryFilter == 'Ubiquiti'}selected{/if}>Ubiquiti</option>
                            <option value="Huawei" {if $categoryFilter == 'Huawei'}selected{/if}>Huawei</option>
                            <option value="TP-Link" {if $categoryFilter == 'TP-Link'}selected{/if}>TP-Link</option>
                            <option value="Other" {if $categoryFilter == 'Other'}selected{/if}>Other</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <!-- Sorting Links -->
                    <div class="btn-group">
                        <a href="{Text::url('device_access/dashboard', 'category', $categoryFilter, 'sort', 'name', 'order', {if $sortField == 'name' && $sortOrder == 'asc'}desc{else}asc{/if})}" 
                           class="btn btn-default btn-sm {if $sortField == 'name'}active{/if}">
                            {Lang::T('Name')} {if $sortField == 'name'}{if $sortOrder == 'asc'}<i class="fa fa-arrow-up"></i>{else}<i class="fa fa-arrow-down"></i>{/if}{/if}
                        </a>
                        <a href="{Text::url('device_access/dashboard', 'category', $categoryFilter, 'sort', 'type', 'order', {if $sortField == 'type' && $sortOrder == 'asc'}desc{else}asc{/if})}" 
                           class="btn btn-default btn-sm {if $sortField == 'type'}active{/if}">
                            {Lang::T('Type')} {if $sortField == 'type'}{if $sortOrder == 'asc'}<i class="fa fa-arrow-up"></i>{else}<i class="fa fa-arrow-down"></i>{/if}{/if}
                        </a>
                        <a href="{Text::url('device_access/dashboard', 'category', $categoryFilter, 'sort', 'device_type', 'order', {if $sortField == 'device_type' && $sortOrder == 'asc'}desc{else}asc{/if})}" 
                           class="btn btn-default btn-sm {if $sortField == 'device_type'}active{/if}">
                            {Lang::T('Device Type')} {if $sortField == 'device_type'}{if $sortOrder == 'asc'}<i class="fa fa-arrow-up"></i>{else}<i class="fa fa-arrow-down"></i>{/if}{/if}
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{Lang::T('Name')}</th>
                            <th>{Lang::T('Type')}</th>
                            <th>{Lang::T('Device Type')}</th>
                            <th>{Lang::T('Radio Type')}</th>
                            <th>{Lang::T('IP Address')}</th>
                            <th>{Lang::T('PPPoE Username')}</th>
                            <th>{Lang::T('Router')}</th>
                            <th>{Lang::T('Port')}</th>
                            <th>{Lang::T('Actions')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if count($devices) > 0}
                            {foreach from=$devices item=device}
                                <tr>
                                    <td>{$device['name']}</td>
                                    <td>
                                        <span class="label label-{if $device['type'] == 'PPPoE'}success{else}warning{/if}">{$device['type']}</span>
                                    </td>
                                    <td>{$device['device_type']}</td>
                                    <td>{$device['radio_type']|default:'N/A'}</td>
                                    <td>{$device['ip_address']}</td>
                                    <td>{if $device['pppoe_username']}{$device['pppoe_username']}{else}<span class="text-muted">-</span>{/if}</td>
                                    <td>{$device['router_name']}</td>
                                    <td>{$device['port']}</td>
                                    <td>
                                        <a href="{$device['access_url']}" target="_blank" class="btn btn-xs btn-success">
                                            <i class="fa fa-external-link"></i> {Lang::T('Access')}
                                        </a>
                                        <a href="{Text::url('device_access/edit', $device['id'])}" class="btn btn-xs btn-warning">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="{Text::url('device_access/delete', $device['id'])}" class="btn btn-xs btn-danger" onclick="return confirm('{Lang::T('Are you sure?')}')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="9" class="text-center text-muted">{Lang::T('No devices found.')}</td>
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Device Type Distribution Chart (Bar Chart)
var categoryCtx = document.getElementById('deviceCategoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: {json_encode($typeLabels)},
        datasets: [{
            label: '{Lang::T('Number of Devices')}',
            data: {json_encode($typeValues)},
            backgroundColor: ['#00a65a', '#f39c12', '#605ca8', '#dd4b39', '#00c0ef'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// PPPoE vs Static Distribution Chart (Pie Chart)
var pppoeVsStaticCtx = document.getElementById('pppoeVsStaticChart').getContext('2d');
new Chart(pppoeVsStaticCtx, {
    type: 'pie',
    data: {
        labels: {json_encode($pppoeVsStaticLabels)},
        datasets: [{
            label: '{Lang::T('Device Type')}',
            data: {json_encode($pppoeVsStaticValues)},
            backgroundColor: ['#00a65a', '#f39c12'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

{include file="admin/footer.tpl"}
