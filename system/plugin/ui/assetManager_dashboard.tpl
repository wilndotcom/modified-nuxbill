{include file="sections/header.tpl"}
<style>
    .huge {
        font-size: 40px;
    }
    
    /* Purple button style */
    .btn-purple {
        color: #fff;
        background-color: #6f42c1;
        border-color: #6f42c1;
    }

    .btn-purple:hover,
    .btn-purple:focus,
    .btn-purple:active {
        color: #fff;
        background-color: #5a359a;
        border-color: #5a359a;
    }

    .chart-container {
        height: 250px;
        position: relative;
    }

    .chart-container canvas {
        max-height: 200px;
        max-width: 100%;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-xs" title="Add Asset" href="{$_url}plugin/assetManager/assets-add">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> {Lang::T('Add Asset')}
                    </a>
                    <a class="btn btn-info btn-xs" title="Categories" href="{$_url}plugin/assetManager/categories">
                        <span class="glyphicon glyphicon-list" aria-hidden="true"></span> {Lang::T('Categories')}
                    </a>
                    <a class="btn btn-success btn-xs" title="Brands" href="{$_url}plugin/assetManager/brands">
                        <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> {Lang::T('Brands')}
                    </a>
                    <a class="btn btn-warning btn-xs" title="Models" href="{$_url}plugin/assetManager/models">
                        <span class="glyphicon glyphicon-cog" aria-hidden="true"></span> {Lang::T('Models')}
                    </a>
                    <a class="btn btn-default btn-xs" title="All Assets" href="{$_url}plugin/assetManager/assets">
                        <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> {Lang::T('All Assets')}
                    </a>
                    <a class="btn btn-purple btn-xs" title="Reports" href="{$_url}plugin/assetManager/reports">
                        <span class="glyphicon glyphicon-stats" aria-hidden="true"></span> {Lang::T('Reports')}
                    </a>
                </div>
                {Lang::T('Asset Manager Dashboard')}
            </div>
            <div class="panel-body">

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="panel panel-info">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-cubes fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge">{$totalAssets}</div>
                                        <div>{Lang::T('Total Assets')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="panel panel-success">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-check-circle fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge">{$activeAssets}</div>
                                        <div>{Lang::T('Active Assets')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="panel panel-warning">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-wrench fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge">{$maintenanceAssets}</div>
                                        <div>{Lang::T('Under Maintenance')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="panel panel-danger">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-times-circle fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge">{$inactiveAssets}</div>
                                        <div>{Lang::T('Inactive Assets')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h3>{$totalCategories}</h3>
                                <p>{Lang::T('Categories')}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h3>{$totalBrands}</h3>
                                <p>{Lang::T('Brands')}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h3>{$totalModels}</h3>
                                <p>{Lang::T('Models')}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h3>{$currencyCode}{$totalAssetValue|number_format:2}</h3>
                                <p>{Lang::T('Total Asset Value')}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">{Lang::T('Assets by Category')}</div>
                            <div class="panel-body chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">{Lang::T('Assets by Status')}</div>
                            <div class="panel-body chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Analysis Charts Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">{Lang::T('Assets by Cost Range')}</div>
                            <div class="panel-body chart-container">
                                <canvas id="costRangeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">{Lang::T('Total Cost by Category')}</div>
                            <div class="panel-body chart-container">
                                <canvas id="costByCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Expensive Assets -->
                {if $expensiveAssets}
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">{Lang::T('Most Expensive Assets')}</div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{Lang::T('Asset Tag')}</th>
                                                <th>{Lang::T('Name')}</th>
                                                <th>{Lang::T('Category')}</th>
                                                <th class="text-right">{Lang::T('Purchase Cost')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach $expensiveAssets as $asset}
                                            <tr>
                                                <td><strong>{$asset.asset_tag}</strong></td>
                                                <td>{$asset.name}</td>
                                                <td>{$asset.category_name}</td>
                                                <td class="text-right">
                                                    <strong>{Lang::moneyFormat($asset.purchase_cost)}</strong></td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {/if}

                <!-- Recent Assets -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">{Lang::T('Recent Assets')}</div>
                            <div class="panel-body">
                                {if $recentAssets}
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{Lang::T('Asset Tag')}</th>
                                                <th>{Lang::T('Name')}</th>
                                                <th>{Lang::T('Category')}</th>
                                                <th>{Lang::T('Brand')}</th>
                                                <th>{Lang::T('Model')}</th>
                                                <th>{Lang::T('Status')}</th>
                                                <th>{Lang::T('Created')}</th>
                                                <th>{Lang::T('Actions')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach $recentAssets as $asset}
                                            <tr>
                                                <td><strong>{$asset.asset_tag}</strong></td>
                                                <td>{$asset.name}</td>
                                                <td>{$asset.category_name}</td>
                                                <td>{$asset.brand_name}</td>
                                                <td>{$asset.model_name}</td>
                                                <td>
                                                    {if $asset.status == 'Active'}
                                                    <span class="label label-success">{$asset.status}</span>
                                                    {elseif $asset.status == 'Inactive'}
                                                    <span class="label label-danger">{$asset.status}</span>
                                                    {elseif $asset.status == 'Under Maintenance'}
                                                    <span class="label label-warning">{$asset.status}</span>
                                                    {else}
                                                    <span class="label label-default">{$asset.status}</span>
                                                    {/if}
                                                </td>
                                                <td>{$asset.created_at|date_format}</td>
                                                <td>
                                                    <a href="{$_url}plugin/assetManager/assets-edit/{$asset.id}"
                                                        class="btn btn-warning btn-xs">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr>
                                        <div class="text-center">
                                            <a href="{$_url}plugin/assetManager/assets"
                                                class="btn btn-primary">{Lang::T('View All')}</a>
                                        </div>
                                    </div>
                                </div>
                                {else}
                                <div class="text-center">
                                    <p>{Lang::T('No assets found.')} <a
                                            href="{$_url}plugin/assetManager/assets-add">{Lang::T('Add your first
                                            asset')}</a></p>
                                </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


{if $_c['asset_welcome_message_viewed'] neq 'yes'}
<div class="modal fade" id="welcomeAssetManagerModal" tabindex="-1" role="dialog" aria-labelledby="welcomeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="welcomeModalLabel"><i class="fa fa-cubes"></i> {Lang::T('Welcome to Asset
                    Manager!')}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img src="https://shop.focuslinkstech.com.ng/public/storage/settings/172805218211.png" alt="Logo"
                        style="max-height: 80px;" class="mb-3">
                    <h4>{Lang::T('Thank you for installing the Asset Manager plugin')}</h4>
                    <p class="text-muted">{Lang::T('Manage your organization\'s assets with ease')}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fa fa-star"></i> {Lang::T('Key Features')}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><i class="fa fa-check text-success"></i> {Lang::T('Track
                                        all your physical assets')}</li>
                                    <li class="list-group-item"><i class="fa fa-check text-success"></i>
                                        {Lang::T('Schedule and manage maintenance')}</li>
                                    <li class="list-group-item"><i class="fa fa-check text-success"></i>
                                        {Lang::T('Generate detailed reports')}</li>
                                    <li class="list-group-item"><i class="fa fa-check text-success"></i>
                                        {Lang::T('Export data to PDF')}</li>
                                    <li class="list-group-item"><i class="fa fa-check text-success"></i> {Lang::T('Track
                                        assignments and warranties')}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fa fa-rocket"></i> {Lang::T('Getting Started')}</h5>
                            </div>
                            <div class="card-body">
                                <ol class="pl-3">
                                    <li class="mb-2">{Lang::T('Add your first asset by clicking the')}
                                        <strong>{Lang::T('Add Asset')}</strong> {Lang::T('button')}
                                    </li>
                                    <li class="mb-2">{Lang::T('View your assets by clicking the')}
                                        <strong>{Lang::T('View Assets')}</strong> {Lang::T('button')}
                                    </li>
                                    <li class="mb-2">{Lang::T('Categorize your assets')} (Network, Infrastructure, etc.)
                                    </li>
                                    <li class="mb-2">{Lang::T('Set up locations to track where assets are deployed')}
                                    </li>
                                    <li class="mb-2">{Lang::T('Schedule maintenance for important equipment')}</li>
                                    <li class="mb-2">{Lang::T('Generate your first report to see the system in action')}
                                    </li>
                                    <li class="mb-2">{Lang::T('Please don\'t forget to donate to support the
                                        development')} <a href="https://www.paypal.com/paypalme/focuslinkstech"
                                            target="_blank">{Lang::T('Donate Now')}</a></li>
                                    <li class="mb-2">{Lang::T('Click on Get Started to continue')}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="https://www.paypal.com/paypalme/focuslinkstech" target="_blank" class="btn btn-primary"><i
                        class="fa fa-paypal"></i> {Lang::T('Donate')}</a>
                <button type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-check-circle"></i>
                    {Lang::T('Get Started')}</button>
            </div>
        </div>
    </div>
</div>
{/if}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const currencyCode = '{$_c['currency_code']}';
    $(document).ready(function () {
        $("#welcomeAssetManagerModal").modal({
            backdrop: "static",
            keyboard: false
        });

        // Handle welcome message acknowledgment
        $("#welcomeAssetManagerModal .btn-success").on("click", function () {
            $.post("?_route=plugin/assetManager/welcome", {}, function (data) {
                console.log("Welcome message acknowledged");
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryData = {
            labels: [
                {foreach $assetsByCategory as $item}
                '{$item.category_name}',
                {/foreach}
        ],
        datasets: [{
            data: [
                {foreach $assetsByCategory as $item}
                    {$item.count},
                {/foreach}
        ],
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
    }]
    };

    new Chart(categoryCtx, {
        type: 'doughnut',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        fontSize: 11,
                        padding: 10
                    }
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = {
        labels: [
            {foreach $assetsByStatus as $item }
                '{$item.status}',
            {/foreach}
        ],
    datasets: [{
        data: [
            {foreach $assetsByStatus as $item }
                    {$item.count},
            {/foreach}
    ],
        backgroundColor: [
            '#28a745',
            '#dc3545',
            '#ffc107',
            '#6c757d'
        ]
        }]
    };

    new Chart(statusCtx, {
        type: 'pie',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        fontSize: 11,
                        padding: 10
                    }
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            }
        }
    });

    // Cost Range Chart
    const costRangeCtx = document.getElementById('costRangeChart').getContext('2d');
    const costRangeData = {
        labels: [
            {foreach $assetsByCostRange as $item }
                '{$item.cost_range}',
            {/foreach}
        ],
    datasets: [{
        data: [
            {foreach $assetsByCostRange as $item }
                    {$item.count},
            {/foreach}
    ],
        backgroundColor: [
            '#6f42c1',
            '#20c997',
            '#fd7e14',
            '#e83e8c',
            '#17a2b8',
            '#6c757d'
        ]
        }]
    };

    new Chart(costRangeCtx, {
        type: 'doughnut',
        data: costRangeData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        fontSize: 11,
                        padding: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.label + ': ' + context.parsed + ' assets';
                        }
                    }
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            }
        }
    });

    // Cost by Category Chart
    const costByCategoryCtx = document.getElementById('costByCategoryChart').getContext('2d');
    const costByCategoryData = {
        labels: [
            {foreach $costByCategory as $item }
                '{$item.category_name}',
            {/foreach}
        ],
    datasets: [{
        label: 'Total Cost (' + currencyCode + ')',
        data: [
            {foreach $costByCategory as $item }
                    {$item.total_cost},
            {/foreach}
    ],
        backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6B6B',
            '#4ECDC4'
        ],
            borderWidth: 1
        }]
    };

    new Chart(costByCategoryCtx, {
        type: 'bar',
        data: costByCategoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + currencyCode + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return currencyCode + value.toLocaleString();
                        }
                    }
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            }
        }
    });
});
</script>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        const portalLink = "https://t.me/focuslinkstech";
        const donateLink = "https://www.paypal.com/paypalme/focuslinkstech";
        const updateLink = "{$updateLink}";
        const productName = "Asset Manager";
        const version = "{$version}";
        $('#version').html(productName + ' | Ver: ' + version + ' | by: <a href="' + portalLink + '">Focuslinks Tech</a> | <a href="' + donateLink + '">Donate</a>');

    });
</script>

{include file="sections/footer.tpl"}