{include file="sections/header.tpl"}
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
<style>
    .asset-hero {
        background: linear-gradient(135deg, #3c8dbc 0%, #2c6aa0 100%);
        color: white;
        padding: 30px 20px;
        margin-bottom: 20px;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .asset-hero h1 {
        font-size: 28px;
        font-weight: 300;
        margin: 0 0 10px 0;
    }

    .asset-hero .meta-info {
        opacity: 0.9;
        font-size: 14px;
    }

    .asset-tag-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-right: 10px;
    }

    .info-tile {
        background: #fff;
        padding: 20px;
        border-radius: 6px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .info-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .info-tile .icon {
        width: 50px;
        height: 50px;
        background: #f4f4f4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 20px;
        color: #666;
    }

    .info-tile.primary .icon {
        background: #3c8dbc;
        color: white;
    }

    .info-tile.success .icon {
        background: #00a65a;
        color: white;
    }

    .info-tile.warning .icon {
        background: #f39c12;
        color: white;
    }

    .info-tile.danger .icon {
        background: #dd4b39;
        color: white;
    }

    .info-tile.info .icon {
        background: #00c0ef;
        color: white;
    }

    .info-tile h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 5px 0;
        color: #333;
    }

    .info-tile p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .map-container {
        height: 350px;
        width: 100%;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .coordinates-display {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        background: #2c3e50;
        color: #ecf0f1;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 13px;
        line-height: 1.6;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #d5edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-maintenance {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .condition-excellent {
        background: #d5edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .condition-good {
        background: #cce7ff;
        color: #0056b3;
        border: 1px solid #99d6ff;
    }

    .condition-fair {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .condition-poor {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .timeline-item {
        padding: 15px 0;
        border-bottom: 1px solid #eee;
        position: relative;
        padding-left: 40px;
    }

    .timeline-item:last-child {
        border-bottom: none;
    }

    .timeline-item:before {
        content: '';
        position: absolute;
        left: 15px;
        top: 20px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #3c8dbc;
    }

    .timeline-item .date {
        font-size: 12px;
        color: #999;
        margin-bottom: 5px;
    }

    .timeline-item .event {
        font-weight: 500;
        color: #333;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .quick-stats {
        display: flex;
        justify-content: space-around;
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 6px;
        margin-top: 20px;
    }

    .quick-stats .stat {
        text-align: center;
        color: white;
    }

    .quick-stats .stat .number {
        font-size: 24px;
        font-weight: 300;
        display: block;
    }

    .quick-stats .stat .label {
        font-size: 12px;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .asset-hero {
            padding: 20px 15px;
        }

        .asset-hero h1 {
            font-size: 22px;
        }

        .quick-stats {
            flex-direction: column;
            gap: 15px;
        }

        .action-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Dark Mode Styles */
    .dark-mode .asset-hero {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #ecf0f1;
        border: 1px solid #34495e;
    }

    .dark-mode .asset-hero .asset-tag-badge {
        background: rgba(52, 73, 94, 0.8);
        color: #ecf0f1;
        border: 1px solid #5a6c7d;
    }

    .dark-mode .asset-hero .quick-stats {
        background: rgba(52, 73, 94, 0.3);
        border: 1px solid #5a6c7d;
    }

    .dark-mode .info-tile {
        background: #34495e;
        color: #ecf0f1;
        border: 1px solid #5a6c7d;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .dark-mode .info-tile:hover {
        background: #3d566e;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    .dark-mode .info-tile .icon {
        background: #2c3e50;
        color: #bdc3c7;
        border: 1px solid #5a6c7d;
    }

    .dark-mode .info-tile.primary .icon {
        background: #2980b9;
        color: white;
        border-color: #3498db;
    }

    .dark-mode .info-tile.success .icon {
        background: #27ae60;
        color: white;
        border-color: #2ecc71;
    }

    .dark-mode .info-tile.warning .icon {
        background: #d68910;
        color: white;
        border-color: #f39c12;
    }

    .dark-mode .info-tile.danger .icon {
        background: #c0392b;
        color: white;
        border-color: #e74c3c;
    }

    .dark-mode .info-tile.info .icon {
        background: #2980b9;
        color: white;
        border-color: #3498db;
    }

    .dark-mode .info-tile h3 {
        color: #ecf0f1;
    }

    .dark-mode .info-tile p {
        color: #bdc3c7;
    }



    .dark-mode .table {
        background: #34495e;
        color: #ecf0f1;
    }

    .dark-mode .table td {
        border-color: #5a6c7d;
        color: #ecf0f1;
    }

    .dark-mode .table td strong {
        color: #3498db;
    }

    .dark-mode .text-muted {
        color: #95a5a6 !important;
    }

    .dark-mode code {
        background: #2c3e50;
        color: #e67e22;
        border: 1px solid #5a6c7d;
    }

    .dark-mode .coordinates-display {
        background: #1a252f;
        color: #ecf0f1;
        border: 1px solid #34495e;
    }

    .dark-mode .status-active {
        background: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        border-color: rgba(46, 204, 113, 0.3);
    }

    .dark-mode .status-inactive {
        background: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border-color: rgba(231, 76, 60, 0.3);
    }

    .dark-mode .status-maintenance {
        background: rgba(243, 156, 18, 0.2);
        color: #f39c12;
        border-color: rgba(243, 156, 18, 0.3);
    }

    .dark-mode .condition-excellent {
        background: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        border-color: rgba(46, 204, 113, 0.3);
    }

    .dark-mode .condition-good {
        background: rgba(52, 152, 219, 0.2);
        color: #3498db;
        border-color: rgba(52, 152, 219, 0.3);
    }

    .dark-mode .condition-fair {
        background: rgba(243, 156, 18, 0.2);
        color: #f39c12;
        border-color: rgba(243, 156, 18, 0.3);
    }

    .dark-mode .condition-poor {
        background: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border-color: rgba(231, 76, 60, 0.3);
    }

    .dark-mode .timeline-item {
        border-bottom-color: #5a6c7d;
    }

    .dark-mode .timeline-item:before {
        background: #3498db;
    }

    .dark-mode .timeline-item .date {
        color: #95a5a6;
    }

    .dark-mode .timeline-item .event {
        color: #ecf0f1;
    }

    .dark-mode .info-box {
        background: #34495e;
        border: 1px solid #5a6c7d;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .dark-mode .info-box-content {
        color: #ecf0f1;
    }

    .dark-mode .info-box-text {
        color: #bdc3c7;
    }

    .dark-mode .info-box-number {
        color: #ecf0f1;
    }

    .dark-mode .info-box-icon {
        border-right: 1px solid #5a6c7d;
    }

    .dark-mode .callout {
        border-left: 5px solid #3498db;
        background: #2c3e50;
        color: #ecf0f1;
    }

    .dark-mode .callout-info {
        border-left-color: #3498db;
        background: rgba(52, 152, 219, 0.1);
    }

    .dark-mode .alert-info {
        background: rgba(52, 152, 219, 0.1);
        border: 1px solid rgba(52, 152, 219, 0.3);
        color: #3498db;
    }

    .dark-mode .btn {
        border-width: 1px;
    }

    .dark-mode .btn-default {
        background: #34495e;
        border-color: #5a6c7d;
        color: #ecf0f1;
    }

    .dark-mode .btn-default:hover {
        background: #3d566e;
        border-color: #6c7b7d;
        color: #ecf0f1;
    }

    .dark-mode .btn-primary {
        background: #2980b9;
        border-color: #3498db;
    }

    .dark-mode .btn-primary:hover {
        background: #3498db;
        border-color: #5dade2;
    }

    .dark-mode .btn-success {
        background: #27ae60;
        border-color: #2ecc71;
    }

    .dark-mode .btn-success:hover {
        background: #2ecc71;
        border-color: #58d68d;
    }

    .dark-mode .btn-warning {
        background: #d68910;
        border-color: #f39c12;
        color: white;
    }

    .dark-mode .btn-warning:hover {
        background: #f39c12;
        border-color: #f8c471;
        color: white;
    }

    .dark-mode .btn-danger {
        background: #c0392b;
        border-color: #e74c3c;
    }

    .dark-mode .btn-danger:hover {
        background: #e74c3c;
        border-color: #ec7063;
    }

    .dark-mode .btn-info {
        background: #2980b9;
        border-color: #3498db;
    }

    .dark-mode .btn-info:hover {
        background: #3498db;
        border-color: #5dade2;
    }

    .dark-mode .btn-box-tool {
        color: #bdc3c7;
    }

    .dark-mode .btn-box-tool:hover {
        color: #ecf0f1;
    }

    /* Dark mode text colors */
    .dark-mode .text-success {
        color: #2ecc71 !important;
    }

    .dark-mode .text-danger {
        color: #e74c3c !important;
    }

    .dark-mode .text-warning {
        color: #f39c12 !important;
    }

    .dark-mode .text-info {
        color: #3498db !important;
    }

    .dark-mode .text-primary {
        color: #3498db !important;
    }

    /* Dark mode map container */
    .dark-mode .map-container {
        border: 1px solid #5a6c7d;
    }

    /* Dark mode leaflet controls */
    .dark-mode .leaflet-control-layers,
    .dark-mode .leaflet-control-zoom,
    .dark-mode .leaflet-control-geocoder {
        background: #34495e;
        border: 1px solid #5a6c7d;
    }

    .dark-mode .leaflet-control-layers a,
    .dark-mode .leaflet-control-zoom a {
        background: #34495e;
        border-bottom: 1px solid #5a6c7d;
        color: #ecf0f1;
    }

    .dark-mode .leaflet-control-layers a:hover,
    .dark-mode .leaflet-control-zoom a:hover {
        background: #3d566e;
    }

    .dark-mode .leaflet-control-layers-base label,
    .dark-mode .leaflet-control-layers-overlays label {
        color: #ecf0f1;
    }

    /* Dark mode popup styling */
    .dark-mode .leaflet-popup-content-wrapper {
        background: #34495e;
        color: #ecf0f1;
        border: 1px solid #5a6c7d;
        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.4);
    }

    .dark-mode .leaflet-popup-tip {
        background: #34495e;
        border-left: 1px solid #5a6c7d;
        border-bottom: 1px solid #5a6c7d;
    }

    .dark-mode .map-popup h4 {
        color: #3498db;
        border-bottom-color: #5a6c7d;
    }

    /* Dark mode print styles */
    @media print {
        .dark-mode .asset-hero {
            background: #2c3e50 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .dark-mode .box {
            border: 1px solid #333 !important;
        }

        .dark-mode .box-header {
            background: #2c3e50 !important;
            color: white !important;
        }
    }

    /* Summary metrics styling */
    .summary-metric {
        text-align: center;
        padding: 8px;
    }

    .summary-metric strong {
        display: block;
        font-size: 18px;
        color: #333;
        margin-bottom: 2px;
    }

    .summary-metric small {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .trend-positive {
        color: #5cb85c !important;
    }

    .trend-negative {
        color: #d9534f !important;
    }

    .trend-neutral {
        color: #f0ad4e !important;
    }

    /* Summary Cards */
    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    }

    .summary-card h5 {
        color: #a3a6b4;
        margin: 0;
        font-size: 14px;
    }

    .summary-card .value {
        font-size: 24px;
        font-weight: 600;
        margin: 10px 0;
    }

    /* Dark Mode Styles for Summary Cards */
    .dark-mode .summary-card {
        background: #1e1e2d;
        color: #ffffff;
    }

    /* Summary Cards */
    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    }

    .summary-card h5 {
        color: #a3a6b4;
        margin: 0;
        font-size: 14px;
    }

    .summary-card .value {
        font-size: 24px;
        font-weight: 600;
        margin: 10px 0;
    }
</style>

<!-- Asset Hero Section -->
<div class="row">
    <div class="col-md-12">
        <div class="asset-hero">
            <div class="row">
                <div class="col-md-8">
                    <h1><i class="fa fa-cube"></i> {$asset.name}</h1>
                    <div class="meta-info">
                        <span class="asset-tag-badge">
                            <i class="fa fa-tag"></i> {$asset.asset_tag}
                        </span>
                        <span class="asset-tag-badge">
                            <i class="fa fa-folder"></i> {$asset.category_name}
                        </span>
                        {if $asset.location}
                        <span class="asset-tag-badge">
                            <i class="fa fa-map-marker"></i> {$asset.location|truncate:30:"..."}
                        </span>
                        {/if}
                    </div>

                    <!-- Quick Stats -->
                    <div class="quick-stats">
                        <div class="stat">
                            <span class="number">
                                {if $asset.status == 'Active'}
                                <i class="fa fa-check-circle"></i>
                                {elseif $asset.status == 'Inactive'}
                                <i class="fa fa-times-circle"></i>
                                {else}
                                <i class="fa fa-wrench"></i>
                                {/if}
                            </span>
                            <span class="label">{$asset.status}</span>
                        </div>
                        <div class="stat">
                            <span class="number">
                                {if $asset.condition_status == 'Excellent'}
                                <i class="fa fa-star"></i>
                                {elseif $asset.condition_status == 'Good'}
                                <i class="fa fa-thumbs-up"></i>
                                {elseif $asset.condition_status == 'Fair'}
                                <i class="fa fa-minus-circle"></i>
                                {else}
                                <i class="fa fa-exclamation-triangle"></i>
                                {/if}
                            </span>
                            <span class="label">{$asset.condition_status}</span>
                        </div>
                        <div class="stat">
                            <span class="number">
                                {assign var="created_timestamp" value=$asset.created_at|strtotime}
                                {assign var="current_timestamp" value=$smarty.now}
                                {assign var="asset_age" value=($current_timestamp - $created_timestamp) / 86400}
                                {$asset_age|floor}
                            </span>
                            <span class="label">{Lang::T('Days Old')}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="action-grid">
                        <a href="{$_url}plugin/assetManager/assets-edit/{$asset.id}" class="btn btn-warning btn-lg">
                            <i class="fa fa-edit"></i><br><small>{Lang::T('Edit Asset')}</small>
                        </a>
                        <a href="{$_url}plugin/assetManager/assets" class="btn btn-default btn-lg">
                            <i class="fa fa-list"></i><br><small>{Lang::T('All Assets')}</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Asset Information Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="summary-card">
            <div class="row">
                <div class="col-xs-3 text-center">
                    <i class="fa fa-folder" style="font-size: 24px; color: #3498db; margin-top: 10px;"></i>
                </div>
                <div class="col-xs-9">
                    <h5>{Lang::T('Category')}</h5>
                    <div class="value" style="color: #3498db;">{$asset.category_name}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="summary-card">
            <div class="row">
                <div class="col-xs-3 text-center">
                    <i class="fa fa-building-o" style="font-size: 24px; color: #2ecc71; margin-top: 10px;"></i>
                </div>
                <div class="col-xs-9">
                    <h5>{Lang::T('Manufacturer')}</h5>
                    <div class="value" style="color: #2ecc71;">{$asset.brand_name}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="summary-card">
            <div class="row">
                <div class="col-xs-3 text-center">
                    <i class="fa fa-cogs" style="font-size: 24px; color: #f39c12; margin-top: 10px;"></i>
                </div>
                <div class="col-xs-9">
                    <h5>{Lang::T('Model')}</h5>
                    <div class="value" style="color: #f39c12;">{$asset.model_name}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="summary-card">
            <div class="row">
                <div class="col-xs-3 text-center">
                    {if $asset.status == 'Active'}
                    <i class="fa fa-signal" style="font-size: 24px; color: #27ae60; margin-top: 10px;"></i>
                    {elseif $asset.status == 'Under Maintenance'}
                    <i class="fa fa-signal" style="font-size: 24px; color: #f39c12; margin-top: 10px;"></i>
                    {else}
                    <i class="fa fa-signal" style="font-size: 24px; color: #e74c3c; margin-top: 10px;"></i>
                    {/if}
                </div>
                <div class="col-xs-9">
                    <h5>{Lang::T('Status')}</h5>
                    <div class="value">
                        <span>
                            {$asset.status}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Information -->
<div class="row">
    <div class="col-md-6">
        <div class="box box-hovered mb20 box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> {Lang::T('Asset Details')}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-borderless">
                    <tr>
                        <td><i class="fa fa-tag text-muted"></i> <strong>{Lang::T('Asset Tag')}</strong></td>
                        <td><code>{$asset.asset_tag}</code></td>
                    </tr>
                    {if $asset.serial_number}
                    <tr>
                        <td><i class="fa fa-barcode text-muted"></i> <strong>{Lang::T('Serial Number')}</strong></td>
                        <td><code>{$asset.serial_number}</code></td>
                    </tr>
                    {/if}
                    <tr>
                        <td><i class="fa fa-calendar text-muted"></i> <strong>{Lang::T('Created')}</strong></td>
                        <td>{$asset.created_at|date_format}</td>
                    </tr>
                    {if $asset.updated_at}
                    <tr>
                        <td><i class="fa fa-calendar-check-o text-muted"></i> <strong>{Lang::T('Last Updated')}</strong></td>
                        <td>{$asset.updated_at|date_format}</td>
                    </tr>
                    {/if}
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-hovered mb20 box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-check-circle"></i> {Lang::T('Condition & Warranty')}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-borderless">
                    <tr>
                        <td><i class="fa fa-check-circle text-muted"></i> <strong>{Lang::T('Condition')}</strong></td>
                        <td>
                            <code>{$asset.condition_status}</code>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-user text-muted"></i> <strong>{Lang::T('Assigned To')}</strong></td>
                        <td>
                            <code>{$asset.assigned_name}</code>
                        </td>
                    </tr>
                    {if $asset.purchase_date && $asset.purchase_date != '0000-00-00'}
                    <tr>
                        <td><i class="fa fa-shopping-cart text-muted"></i> <strong>{Lang::T('Purchase Date')}</strong></td>
                        <td>{$asset.purchase_date|date_format}</td>
                    </tr>
                    {/if}
                    {if $asset.purchase_cost && $asset.purchase_cost != '0000-00-00'}
                    <tr>
                        <td><i class="fa fa-money text-muted"></i> <strong>{Lang::T('Purchase Cost')}</strong></td>
                        <td>{Lang::moneyFormat($asset.purchase_cost)}</td>
                    </tr>
                    {/if}
                    {if $asset.warranty_expiry && $asset.warranty_expiry != '0000-00-00'}
                    <tr>
                        <td><i class="fa fa-shield text-muted"></i> <strong>{Lang::T('Warranty Expiry')}</strong></td>
                        <td>
                            {$asset.warranty_expiry|date_format}
                            {assign var="expiry_timestamp" value=$asset.warranty_expiry|strtotime}
                            {assign var="current_timestamp" value=$smarty.now}
                            {assign var="days_diff" value=($expiry_timestamp - $current_timestamp) / 86400}
                            {if $days_diff > 0}
                            <br><small class="text-success"><i class="fa fa-check"></i> {$days_diff|floor} {Lang::T('days remaining')}</small>
                            {else}
                            <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> {Lang::T('Expired')}
                                {$days_diff|abs|floor} {Lang::T('days ago')}</small>
                            {/if}
                        </td>
                    </tr>
                    {/if}
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Location Information -->
{if $asset.location || ($asset.latitude && $asset.longitude)}
<div class="row">
    <div class="col-md-12">
        <div class="box box-hovered mb20 box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-map-marker"></i> {Lang::T('Location & Mapping')}</h3>
                <div class="box-tools pull-right">
                    {if $asset.latitude && $asset.longitude}
                    <button type="button" class="btn btn-box-tool" onclick="openFullScreenMap()"
                        title="{Lang::T('Full Screen Map')}">
                        <i class="fa fa-expand"></i>
                    </button>
                    {/if}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                {if $asset.location || ($asset.latitude && $asset.longitude)}
                <div class="row">
                    <div class="col-md-4">
                        {if $asset.location}
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-map-marker"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{Lang::T('Address')}</span>
                                <span class="info-box-number" style="font-size: 14px;">{$asset.location}</span>
                            </div>
                        </div>
                        {/if}

                        {if $asset.latitude && $asset.longitude}
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-crosshairs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{Lang::T('Coordinates')}</span>
                                <div class="coordinates-display">
                                    <strong>Lat:</strong> {$asset.latitude}<br>
                                    <strong>Lng:</strong> {$asset.longitude}
                                </div>
                                <button class="btn btn-xs btn-default" onclick="copyCoordinates()"
                                    title="{Lang::T('Copy Coordinates')}">
                                    <i class="fa fa-copy"></i> {Lang::T('Copy')}
                                </button>
                                <a href="https://www.google.com/maps?q={$asset.latitude},{$asset.longitude}"
                                    target="_blank" class="btn btn-xs btn-primary" title="{Lang::T('Open in Google Maps')}">
                                    <i class="fa fa-external-link"></i> {Lang::T('Google Maps')}
                                </a>
                            </div>
                        </div>
                        {/if}
                    </div>

                    <div class="col-md-8">
                        {if $asset.latitude && $asset.longitude}
                        <div id="assetLocationMap" class="map-container"></div>
                        {else}
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            {Lang::T('No coordinates available for this asset. Add coordinates in the edit form to see the map.')}
                        </div>
                        {/if}
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>
</div>
{/if}
{if $asset.description}
<!-- Description -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-hovered mb20 box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-file-text"></i> {Lang::T('Description & Notes')}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="callout callout-info">
                    <p>{$asset.description|nl2br}</p>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}

<!-- Asset Timeline & Actions -->
<div class="row">
    <div class="col-md-8">
        <div class="box box-hovered mb20 box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> {Lang::T('Asset Timeline')}</h3>
            </div>
            <div class="box-body">
                <div class="timeline-item">
                    <div class="date">{$asset.created_at|date_format}</div>
                    <div class="event">
                        <i class="fa fa-plus-circle text-success"></i>
                        {Lang::T('Asset created in system')}
                    </div>
                </div>

                {if $asset.updated_at && $asset.updated_at != $asset.created_at}
                <div class="timeline-item">
                    <div class="date">{$asset.updated_at|date_format}</div>
                    <div class="event">
                        <i class="fa fa-edit text-warning"></i>
                        {Lang::T('Asset information updated')}
                    </div>
                </div>
                {/if}

                {if $asset.purchase_date && $asset.purchase_date != '0000-00-00'}
                <div class="timeline-item">
                    <div class="date">{$asset.purchase_date|date_format}</div>
                    <div class="event">
                        <i class="fa fa-shopping-cart text-info"></i>
                        {Lang::T('Asset purchased on')}
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-hovered mb20 box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cogs"></i> {Lang::T('Quick Actions')}</h3>
            </div>
            <div class="box-body">
                <div class="action-grid">
                    <a href="{$_url}plugin/assetManager/assets-edit/{$asset.id}" class="btn btn-warning btn-block">
                        <i class="fa fa-edit"></i><br>
                        <strong>{Lang::T('Edit Asset')}</strong><br>
                        <small>{Lang::T('Modify asset details')}</small>
                    </a>

                    <a href="{$_url}plugin/assetManager/assets" class="btn btn-info btn-block">
                        <i class="fa fa-list"></i><br>
                        <strong>{Lang::T('View All Assets')}</strong><br>
                        <small>{Lang::T('Browse asset inventory')}</small>
                    </a>

                    <a href="{$_url}plugin/assetManager/dashboard" class="btn btn-primary btn-block">
                        <i class="fa fa-dashboard"></i><br>
                        <strong>{Lang::T('Dashboard')}</strong><br>
                        <small>{Lang::T('Asset analytics')}</small>
                    </a>

                    <button class="btn btn-success btn-block" onclick="generateQRCode()">
                        <i class="fa fa-qrcode"></i><br>
                        <strong>{Lang::T('Generate QR')}</strong><br>
                        <small>{Lang::T('Asset QR code')}</small>
                    </button>

                    <button class="btn btn-default btn-block" onclick="printAsset()">
                        <i class="fa fa-print"></i><br>
                        <strong>{Lang::T('Print')}</strong><br>
                        <small>{Lang::T('Asset details')}</small>
                    </button>

                    <a href="{$_url}plugin/assetManager/assets-delete/{$asset.id}" class="btn btn-danger btn-block"
                        onclick="return ask(this, '{Lang::T('Are you sure you want to delete this asset? This action cannot be undone.')}')">
                        <i class="fa fa-trash"></i><br>
                        <strong>{Lang::T('Delete Asset')}</strong><br>
                        <small>{Lang::T('Permanent removal')}</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden fields for JavaScript -->
{if $asset.latitude && $asset.longitude}
<input type="hidden" id="assetLat" value="{$asset.latitude}">
<input type="hidden" id="assetLng" value="{$asset.longitude}">
<input type="hidden" id="assetName" value="{$asset.name|escape}">
<input type="hidden" id="assetTag" value="{$asset.asset_tag|escape}">
{/if}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{literal}
<script>
    var assetMap;

    $(document).ready(function () {
        // Initialize map
        initializeMap();

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Auto-collapse boxes on mobile
        if ($(window).width() < 768) {
            $('.box-tools [data-widget="collapse"]').click();
        }
    });

    function initializeMap() {
        var latInput = document.getElementById('assetLat');
        var lngInput = document.getElementById('assetLng');
        var nameInput = document.getElementById('assetName');
        var tagInput = document.getElementById('assetTag');

        if (latInput && lngInput) {
            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            var assetName = nameInput ? nameInput.value : 'Asset';
            var assetTag = tagInput ? tagInput.value : '';

            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                // Initialize the map
                assetMap = L.map('assetLocationMap').setView([lat, lng], 15);

                // Add multiple tile layer options
                var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                });

                var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: '© Esri'
                });

                // Add default layer
                osmLayer.addTo(assetMap);

                // Layer control
                var baseMaps = {
                    "Street Map": osmLayer,
                    "Satellite": satelliteLayer
                };
                L.control.layers(baseMaps).addTo(assetMap);

                // Custom marker icon
                var assetIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color:#3c8dbc;width:30px;height:30px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa fa-cube" style="color:white;font-size:12px;"></i></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                // Add marker for the asset location
                var marker = L.marker([lat, lng], { icon: assetIcon }).addTo(assetMap);

                // Enhanced popup
                var popupContent = '<div class="map-popup" style="text-align: center; min-width: 200px;">' +
                    '<h4 style="margin: 0 0 10px 0; color: #3c8dbc;"><i class="fa fa-cube"></i> ' + assetName + '</h4>' +
                    '<div style="margin: 8px 0;"><strong>Tag:</strong> ' + assetTag + '</div>' +
                    '<div style="margin: 8px 0; font-family: monospace; background: #f4f4f4; padding: 5px; border-radius: 3px;">' +
                    '<strong>Coordinates:</strong><br>' + lat + ', ' + lng + '</div>' +
                    '<div style="margin-top: 10px;">' +
                    '<button onclick="copyToClipboard(\'' + lat + ', ' + lng + '\')" class="btn btn-xs btn-default" style="margin: 2px;">' +
                    '<i class="fa fa-copy"></i> Copy</button>' +
                    '<a href="https://www.google.com/maps?q=' + lat + ',' + lng + '" target="_blank" class="btn btn-xs btn-primary" style="margin: 2px;">' +
                    '<i class="fa fa-external-link"></i> Google</a>' +
                    '</div></div>';

                marker.bindPopup(popupContent).openPopup();

                // Add search control
                var geocoder = L.Control.extend({
                    options: {
                        position: 'topright'
                    },
                    onAdd: function (map) {
                        var container = L.DomUtil.create('div', 'leaflet-control-geocoder');
                        container.innerHTML = '<button class="btn btn-sm btn-default" onclick="centerOnAsset()" title="Center on Asset"><i class="fa fa-crosshairs"></i></button>';
                        return container;
                    }
                });
                assetMap.addControl(new geocoder());

            } else {
                $('#assetLocationMap').parent().parent().hide();
            }
        } else {
            $('#assetLocationMap').parent().parent().hide();
        }
    }

    function centerOnAsset() {
        if (assetMap) {
            var latInput = document.getElementById('assetLat');
            var lngInput = document.getElementById('assetLng');
            if (latInput && lngInput) {
                var lat = parseFloat(latInput.value);
                var lng = parseFloat(lngInput.value);
                assetMap.setView([lat, lng], 15);
            }
        }
    }

    function copyCoordinates() {
        var latInput = document.getElementById('assetLat');
        var lngInput = document.getElementById('assetLng');
        if (latInput && lngInput) {
            var coordinates = latInput.value + ', ' + lngInput.value;
            copyToClipboard(coordinates);
        }
    }

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                showNotification('Coordinates copied to clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Coordinates copied to clipboard!', 'success');
        }
    }

    function openFullScreenMap() {
        // Implementation for full screen map modal
        showNotification('Full screen map feature coming soon!', 'info');
    }

    function generateQRCode() {
        var assetUrl = window.location.href;
        var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(assetUrl);

        Swal.fire({
            title: 'Asset QR Code',
            html: '<img src="' + qrUrl + '" style="max-width: 100%;"><br><small>Scan to view asset details</small>',
            showCancelButton: true,
            confirmButtonText: 'Download QR Code',
            cancelButtonText: 'Close'
        }).then((result) => {
            if (result.isConfirmed) {
                var link = document.createElement('a');
                link.href = qrUrl;
                link.download = 'asset-qr-{/literal}{$asset.asset_tag}{literal}.png';
                link.click();
            }
        });
    }

    function printAsset() {
        window.print();
    }

    function showNotification(message, type) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                icon: type,
                title: message
            });
        } else {
            alert(message);
        }
    }

    // Print styles
    window.addEventListener('beforeprint', function () {
        document.body.classList.add('printing');
    });

    window.addEventListener('afterprint', function () {
        document.body.classList.remove('printing');
    });
</script>

<style>
    @media print {

        .box-tools,
        .btn,
        .action-grid {
            display: none !important;
        }

        .box {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .asset-hero {
            background: #3c8dbc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    .leaflet-control-geocoder {
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .map-popup h4 {
        border-bottom: 2px solid #3c8dbc;
        padding-bottom: 5px;
    }
</style>
{/literal}

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