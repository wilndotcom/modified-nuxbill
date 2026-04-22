{include file="sections/header.tpl"}
<style>
    .map-container {
        height: 400px;
        border-radius: 8px;
        border: 1px solid #ddd;
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

    /* Highlight marker animation */
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.5);
            opacity: 0.7;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
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
                    <a class="btn btn-default btn-xs" title="Dashboard" href="{$_url}plugin/assetManager/dashboard">
                        <span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {Lang::T('Dashboard')}
                    </a>
                    <a class="btn btn-purple btn-xs" title="Reports" href="{$_url}plugin/assetManager/reports">
                        <span class="glyphicon glyphicon-stats" aria-hidden="true"></span> {Lang::T('Reports')}
                    </a>
                </div>
                {Lang::T('All Assets')}
            </div>
            <div class="panel-body">

                {if $assets}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{Lang::T('Asset Tag')}</th>
                                <th>{Lang::T('Name')}</th>
                                <th>{Lang::T('Category')}</th>
                                <th>{Lang::T('Brand')}</th>
                                <th>{Lang::T('Model')}</th>
                                <th>{Lang::T('Location')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Condition')}</th>
                                <th>{Lang::T('Assigned To')}</th>
                                <th>{Lang::T('Created')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $assets as $asset}
                            <tr>
                                <td><strong>{$asset.asset_tag}</strong></td>
                                <td>{$asset.name}</td>
                                <td>{$asset.category_name}</td>
                                <td>{$asset.brand_name}</td>
                                <td>{$asset.model_name}</td>
                                <td>{$asset.location}</td>
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
                                <td>
                                    {if $asset.condition_status == 'Excellent'}
                                    <span class="label label-success">{$asset.condition_status}</span>
                                    {elseif $asset.condition_status == 'Good'}
                                    <span class="label label-info">{$asset.condition_status}</span>
                                    {elseif $asset.condition_status == 'Fair'}
                                    <span class="label label-warning">{$asset.condition_status}</span>
                                    {else}
                                    <span class="label label-danger">{$asset.condition_status}</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $asset.assigned_to_name}
                                        {$asset.assigned_to_name}
                                    {else}
                                        {Lang::T('Unassigned')}
                                    {/if}
                                </td>
                                <td>{$asset.created_at|date_format}</td>
                                <td>
                                    <a href="{$_url}plugin/assetManager/assets-view/{$asset.id}"
                                        class="btn btn-default btn-xs" title="{Lang::T('View')}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button class="btn btn-xs btn-primary"
                                        onclick="showMap('{$asset.latitude}', '{$asset.longitude}')"><i
                                            class="fa fa-map-marker" aria-hidden="true"></i></button>
                                    <a href="{$_url}plugin/assetManager/assets-edit/{$asset.id}"
                                        class="btn btn-warning btn-xs" title="{Lang::T('Edit')}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="{$_url}plugin/assetManager/assets-delete/{$asset.id}"
                                        class="btn btn-danger btn-xs"
                                        onclick="return ask(this, '{Lang::T('Are you sure you want to delete this asset?')}')"
                                        title="{Lang::T('Delete')}">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {else}
                <div class="text-center">
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> {Lang::T('No Assets Found')}</h4>
                        <p>{Lang::T('You haven\'t added any assets yet. Assets are the physical items you want to track.')}</p>
                        <a href="{$_url}plugin/assetManager/assets-add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> {Lang::T('Add First Asset')}
                        </a>
                    </div>
                </div>
                {/if}
                {include file="pagination.tpl"}
            </div>
        </div>
    </div>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{Lang::T('Asset Location')}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div id="mapContainer" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>



<script>
    var map;
    var marker;

    function showMap(lat, lng) {
        // Convert to numbers and validate
        lat = parseFloat(lat);
        lng = parseFloat(lng);

        if (isNaN(lat) || isNaN(lng) || lat === 0 && lng === 0) {
            Swal.fire({
                title: "{Lang::T('Error')}",
                text: "{Lang::T('Invalid coordinates for this asset')}",
                icon: 'error',
                confirmButtonText: "{Lang::T('OK')}"
            });
            return;
        }

        // Show modal first
        $('#mapModal').modal('show').on('shown.bs.modal', function () {
            // Cleanup previous map
            if (map) {
                map.remove();
                map = null;
            }

            // Wait a bit for modal to fully show before initializing map
            setTimeout(function () {
                // Initialize new map
                map = L.map('mapContainer').setView([lat, lng], 13);

                var s = String.fromCharCode(123) + 's' + String.fromCharCode(125);
                var z = String.fromCharCode(123) + 'z' + String.fromCharCode(125);
                var x = String.fromCharCode(123) + 'x' + String.fromCharCode(125);
                var y = String.fromCharCode(123) + 'y' + String.fromCharCode(125);
                var tileUrl = 'https://' + s + '.tile.openstreetmap.org/' + z + '/' + x + '/' + y + '.png';

                L.tileLayer(tileUrl, {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Add the marker for this asset
                L.marker([lat, lng]).addTo(map)
                    .bindPopup('Asset Location<br>Lat: ' + lat + '<br>Lng: ' + lng)
                    .openPopup();
            }, 200);
        });
    }
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