{include file="sections/header.tpl"}

<style>
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
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right" style="margin-top: -4px;">
                    <a class="btn btn-primary btn-xs" title="Dashboard" href="{$_url}plugin/assetManager/dashboard">
                        <i class="fa fa-dashboard"></i> {Lang::T('Dashboard')} 
                    </a>
                    <a class="btn btn-info btn-xs" title="All Assets" href="{$_url}plugin/assetManager/assets">
                        <i class="fa fa-cubes"></i> {Lang::T('All Assets')}
                    </a>
                </div>
                <div class="panel-title">{Lang::T('Asset Reports')}</div>
            </div>
            <div class="panel-body">
                
                <!-- Report Filter Form -->
                <form id="reportForm" method="post">
                    <div class="row">
                        <!-- Report Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{Lang::T('Report Type')}</label>
                                <select class="form-control" id="report_type" name="report_type" required>
                                    <option value="summary">{Lang::T('Summary Report')}</option>
                                    <option value="detailed">{Lang::T('Detailed Asset Report')}</option>
                                    <option value="category">{Lang::T('Category Analysis')}</option>
                                    <option value="maker">{Lang::T('Brand Analysis')}</option>
                                    <option value="status">{Lang::T('Status Report')}</option>
                                    <option value="assigned">{Lang::T('Assignment Report')}</option>
                                    <option value="cost">{Lang::T('Cost Analysis')}</option>
                                    <option value="warranty">{Lang::T('Warranty Report')}</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Export Format -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{Lang::T('Export Format')}</label>
                                <select class="form-control" id="export_format" name="export_format">
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Filters -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Category')}</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">{Lang::T('All Categories')}</option>
                                    {foreach $categories as $category}
                                        <option value="{$category.id}">{$category.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Brand')}</label>   
                                <select class="form-control" id="brand_id" name="brand_id">
                                    <option value="">{Lang::T('All Brands')}</option>
                                    {foreach $brands as $brand}
                                        <option value="{$brand.id}">{$brand.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Model')}</label>
                                <select class="form-control" id="model_id" name="model_id">
                                    <option value="">{Lang::T('All Models')}</option>
                                    {foreach $models as $model}
                                        <option value="{$model.id}">{$model.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Status')}</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">{Lang::T('All Status')}</option>
                                    <option value="Active">{Lang::T('Active')}</option>
                                    <option value="Inactive">{Lang::T('Inactive')}</option>
                                    <option value="Maintenance">{Lang::T('Maintenance')}</option>
                                    <option value="Disposed">{Lang::T('Disposed')}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Assigned To Filter -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{Lang::T('Assigned To')}</label>
                                <select class="form-control" id="assigned_to" name="assigned_to">
                                    <option value="">{Lang::T('All Assignments')}</option>
                                    <option value="unassigned">{Lang::T('Unassigned')}</option>
                                    {foreach $assignedToValues as $assignment}
                                        <option value="{$assignment.assigned_to}">{$assignment.assigned_name} - {$assignment.assigned_username} - {$assignment.assigned_email}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Date From')}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Date To')}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                        
                        <!-- Cost Range -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Cost From')} ({$currencyCode})</label>
                                <input type="number" step="0.01" class="form-control" id="cost_from" name="cost_from" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{Lang::T('Cost To')} ({$currencyCode})</label>
                                <input type="number" step="0.01" class="form-control" id="cost_to" name="cost_to" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-primary" id="generateReport">
                                    <i class="fa fa-chart-bar"></i> {Lang::T('Generate Report')}
                                </button>
                                <button type="button" class="btn btn-success" id="exportReport">
                                    <i class="fa fa-download"></i> {Lang::T('Export Report')}
                                </button>
                                <button type="button" class="btn btn-info" id="printReport">
                                    <i class="fa fa-print"></i> {Lang::T('Print Report')}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <hr>

                <!-- Report Results -->
                <div id="reportResults" style="display: none;">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">{Lang::T('Report Results')}</h4>
                        </div>
                        <div class="panel-body">
                            <div id="reportContent">
                                <!-- Report content will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="loadingIndicator" style="display: none; text-align: center; padding: 20px;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>{Lang::T('Generating report...')}</p>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Print styles */
@media print {
    .btn, .panel-heading, .no-print {
        display: none !important;
    }
    
    .panel {
        border: none !important;
        box-shadow: none !important;
    }
    
    .panel-body {
        padding: 0 !important;
    }
    
    table {
        font-size: 12px !important;
    }
    
    .page-break {
        page-break-before: always;
    }
}

/* Report styling */
.report-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.report-summary .metric {
    text-align: center;
    padding: 10px;
}

.report-summary .metric h3 {
    margin: 0;
    color: #2c3e50;
}

.report-summary .metric p {
    margin: 5px 0 0 0;
    color: #7f8c8d;
    font-size: 14px;
}

.report-table {
    margin-top: 20px;
}

.report-table table {
    margin-bottom: 0;
}

.report-chart {
    height: 400px;
    margin: 20px 0;
}

.filter-active {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Generate Report
    $('#generateReport').click(function() {
        generateReport();
    });
    
    // Export Report
    $('#exportReport').click(function() {
        exportReport();
    });
    
    // Print Report
    $('#printReport').click(function() {
        window.print();
    });
    
    // Update models when maker changes
    $('#maker_id').change(function() {
        var makerId = $(this).val();
        updateModels(makerId);
    });
    
    // Show/hide filters based on report type
    $('#report_type').change(function() {
        var reportType = $(this).val();
        toggleFilters(reportType);
    });
});

function generateReport() {
    var formData = $('#reportForm').serialize();
    
    $('#loadingIndicator').show();
    $('#reportResults').hide();
    
    $.ajax({
        url: '{$_url}plugin/assetManager/reports-generate',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#loadingIndicator').hide();
            
            if (response.success) {
                displayReportResults(response.data, response.reportType, response.currencyCode);
                $('#reportResults').show();
            } else {
                alert('Error generating report: ' + response.message);
            }
        },
        error: function() {
            $('#loadingIndicator').hide();
            alert('Error generating report. Please try again.');
        }
    });
}

function exportReport() {
    var formData = $('#reportForm').serialize();
    
    // Create a temporary form for file download
    var form = $('<form>', {
        'method': 'POST',
        'action': '{$_url}plugin/assetManager/reports-export'
    });
    
    // Add form data
    $.each($('#reportForm').serializeArray(), function(i, field) {
        form.append($('<input>', {
            'type': 'hidden',
            'name': field.name,
            'value': field.value
        }));
    });
    
    // Submit form
    $('body').append(form);
    form.submit();
    form.remove();
}

function displayReportResults(data, reportType, currencyCode) {
    var content = '';
    
    if (reportType === 'summary') {
        content = generateSummaryHTML(data, currencyCode);
    } else if (Array.isArray(data) && data.length > 0) {
        content = generateTableHTML(data, reportType, currencyCode);
    } else {
        content = '<div class="alert alert-info">No data found for the selected criteria.</div>';
    }
    
    $('#reportContent').html(content);
}

function generateSummaryHTML(data, currencyCode) {
    var html = '<div class="report-summary">';
    html += '<div class="row">';
    html += '<div class="col-md-3"><div class="metric"><h3>' + data.total_assets + '</h3><p>Total Assets</p></div></div>';
    html += '<div class="col-md-3"><div class="metric"><h3>' + currencyCode + ' ' + parseFloat(data.total_value || 0).toLocaleString() + '</h3><p>Total Value</p></div></div>';
    html += '</div>';
    html += '</div>';
    
    // Status breakdown
    if (data.status_breakdown && data.status_breakdown.length > 0) {
        html += '<h4>Status Breakdown</h4>';
        html += '<div class="report-table" style="overflow-x: auto;"><table class="table table-striped">';
        html += '<thead><tr><th>Status</th><th>Count</th><th>Percentage</th></tr></thead>';
        html += '<tbody>';
        
        data.status_breakdown.forEach(function(item) {
            var percentage = ((item.count / data.total_assets) * 100).toFixed(1);
            html += '<tr><td>' + item.status + '</td><td>' + item.count + '</td><td>' + percentage + '%</td></tr>';
        });
        
        html += '</tbody></table></div>';
    }
    
    // Category breakdown
    if (data.category_breakdown && data.category_breakdown.length > 0) {
        html += '<h4>Category Breakdown</h4>';
        html += '<div class="report-table"><table class="table table-striped">';
        html += '<thead><tr><th>Category</th><th>Count</th><th>Percentage</th></tr></thead>';
        html += '<tbody>';
        
        data.category_breakdown.forEach(function(item) {
            var percentage = ((item.count / data.total_assets) * 100).toFixed(1);
            html += '<tr><td>' + item.category_name + '</td><td>' + item.count + '</td><td>' + percentage + '%</td></tr>';
        });
        
        html += '</tbody></table></div>';
    }
    
    return html;
}

function generateTableHTML(data, reportType, currencyCode) {
    if (!data || data.length === 0) {
        return '<div class="alert alert-info">No data found for the selected criteria.</div>';
    }
    
    var html = '<div class="report-table">';
    html += '<table class="table table-striped table-bordered" style="overflow-x: auto;">';
    html += '<thead><tr>';
    
    // Generate headers based on first row keys
    var headers = Object.keys(data[0]);
    headers.forEach(function(header) {
        html += '<th>' + header.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</th>';
    });
    
    html += '</tr></thead><tbody>';
    
    // Generate rows
    data.forEach(function(row) {
        html += '<tr>';
        headers.forEach(function(header) {
            var value = row[header] || '';
            
            // Format currency fields
            if (header.includes('cost') || header.includes('value')) {
                if (value && !isNaN(value)) {
                    value = currencyCode + ' ' + parseFloat(value).toLocaleString();
                }
            }
            
            // Format dates
            if (header.includes('date') && value && value !== '0000-00-00') {
                value = new Date(value).toLocaleDateString();
            }
            
            html += '<td>' + value + '</td>';
        });
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    
    return html;
}

function updateModels(makerId) {
    if (!makerId) {
        $('#model_id').html('<option value="">All Models</option>');
        return;
    }
    
    $.ajax({
        url: '{$_url}plugin/assetManager/?ajax=get-models',
        type: 'POST',
        data: { maker_id: makerId },
        dataType: 'json',
        success: function(response) {
            var options = '<option value="">All Models</option>';
            if (response.success && response.models) {
                response.models.forEach(function(model) {
                    options += '<option value="' + model.id + '">' + model.name + '</option>';
                });
            }
            $('#model_id').html(options);
        }
    });
}

function toggleFilters(reportType) {
    // Reset all filters
    $('.form-group').removeClass('filter-active');
    
    // Highlight relevant filters for each report type
    switch(reportType) {
        case 'category':
            $('#category_id').closest('.form-group').addClass('filter-active');
            break;
        case 'maker':
            $('#maker_id').closest('.form-group').addClass('filter-active');
            break;
        case 'status':
            $('#status').closest('.form-group').addClass('filter-active');
            break;
        case 'cost':
            $('#cost_from, #cost_to').closest('.form-group').addClass('filter-active');
            break;
        case 'warranty':
            $('#date_from, #date_to').closest('.form-group').addClass('filter-active');
            break;
    }
}
</script>

{include file="sections/footer.tpl"}