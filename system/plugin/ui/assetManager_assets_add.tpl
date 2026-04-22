{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <a class="btn btn-default btn-xs" title="Back to Assets" href="{$_url}plugin/assetManager/assets">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> {Lang::T('Back')}
                    </a>
                </div>
                {Lang::T('Add New Asset')}
            </div>
            <div class="panel-body">

                <form class="form-horizontal" method="post" role="form" id="assetForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{Lang::T('Basic Information')}</h4>
                            <hr>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Category')} *</label>
                                <div class="col-md-8">
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">{Lang::T('Select Category')}</option>
                                        {foreach $categories as $category}
                                        <option value="{$category.id}">{$category.name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Brand')} *</label>
                                <div class="col-md-8">
                                    <select class="form-control" id="brand_id" name="brand_id" required>
                                        <option value="">{Lang::T('Select Brand')}</option>
                                        {foreach $brands as $brand}
                                        <option value="{$brand.id}">{$brand.name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Model')} *</label>
                                <div class="col-md-8">
                                    <select class="form-control" id="model_id" name="model_id" required>
                                        <option value="">{Lang::T('Select Model')}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Asset Tag')} *</label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="asset_tag" name="asset_tag"
                                            placeholder="{Lang::T('Enter unique asset tag')}" required>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info"
                                                onclick="generateRandomAssetTag()"
                                                title="{Lang::T('Generate Random Asset Tag')}">
                                                <i class="fa fa-refresh"></i> {Lang::T('Generate')}
                                            </button>
                                        </span>
                                    </div>
                                    <small class="help-block">{Lang::T('Click Generate for a random asset tag or enter
                                        your own')}</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Serial Number')}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="serial_number" name="serial_number"
                                        placeholder="{Lang::T('Enter serial number')}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Asset Name')} *</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="{Lang::T('Enter asset name')}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Description')}</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="{Lang::T('Enter asset description')}"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h4>{Lang::T('Purchase & Location Details')}</h4>
                            <hr>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Purchase Date')}</label>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Purchase Cost')}</label>
                                <div class="col-md-8">
                                    <input type="number" step="0.01" class="form-control" id="purchase_cost"
                                        name="purchase_cost" placeholder="0.00">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Warranty Expiry')}</label>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Location')}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="location" name="location"
                                        placeholder="{Lang::T('Enter asset location or click on map')}">
                                    <small class="help-block">{Lang::T('Physical location or address of the
                                        asset')}</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Coordinates')}</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="lat" name="lat" placeholder="Latitude"
                                        step="any">
                                    <small class="help-block">Latitude (e.g., 6.5244)</small>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="lng" name="lng" placeholder="Longitude"
                                        step="any">
                                    <small class="help-block">Longitude (e.g., 3.3792)</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Map Picker')}</label>
                                <div class="col-md-8">
                                    <div class="btn-group btn-group-sm" style="margin-bottom: 10px;">
                                        <button type="button" class="btn btn-info" onclick="getCurrentLocation()">
                                            <i class="fa fa-location-arrow"></i> {Lang::T('Get Current Location')}
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="toggleMapPicker()">
                                            <i class="fa fa-map-marker"></i> {Lang::T('Pick from Map')}
                                        </button>
                                        <button type="button" class="btn btn-warning" onclick="clearCoordinates()">
                                            <i class="fa fa-times"></i> {Lang::T('Clear')}
                                        </button>
                                    </div>
                                    <div id="mapContainer" style="display: none;">
                                        <div id="locationMap"
                                            style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                                        </div>
                                        <small class="help-block">{Lang::T('Click on the map to set
                                            coordinates')}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Assigned To')}</label>
                                <div class="col-md-8">
                                    <select class="form-control select2" id="assigned_to" name="assigned_to">
                                        <option value="">{Lang::T('Unassigned')}</option>
                                        {foreach $customers as $user}
                                        <option value="{$user.id}">{$user.fullname} - {$user.username} - {$user.email}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Status')}</label>
                                <div class="col-md-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="Active" selected>{Lang::T('Active')}</option>
                                        <option value="Inactive">{Lang::T('Inactive')}</option>
                                        <option value="Under Maintenance">{Lang::T('Under Maintenance')}</option>
                                        <option value="Disposed">{Lang::T('Disposed')}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Condition')}</label>
                                <div class="col-md-8">
                                    <select class="form-control" id="condition_status" name="condition_status">
                                        <option value="Excellent">{Lang::T('Excellent')}</option>
                                        <option value="Good" selected>{Lang::T('Good')}</option>
                                        <option value="Fair">{Lang::T('Fair')}</option>
                                        <option value="Poor">{Lang::T('Poor')}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{Lang::T('Notes')}</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                        placeholder="{Lang::T('Additional notes')}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <div class="text-center">
                                <button class="btn btn-success" type="submit">{Lang::T('Save Asset')}</button>
                                <a href="{$_url}plugin/assetManager/assets"
                                    class="btn btn-default">{Lang::T('Cancel')}</a>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


<script>
    let map = null;
    let marker = null;

    const selectModelText = '{Lang::T('Select Model')}';
    const loadingModelsText = '{Lang::T('Loading models...')}';
    const noModelsAvailableText = '{Lang::T('No models available')}';
    const errorLoadingModelsText = '{Lang::T('Error loading models')}';
    const locationDetectedSuccessfullyText = '{Lang::T('Location detected successfully!')}';
    const locationNotDetectedText = '{Lang::T('Location not detected.Please try again.')}';
    const gettingLocationText = '{Lang::T('Getting location')}...';
    const gettingLocationErrorText = '{Lang::T('Error getting location')}';
    const locationAccessDeniedText = '{Lang::T('Location access denied by user.')}';
    const locationInformationUnavailableText = '{Lang::T('Location information unavailable.')}';
    const locationRequestTimedOutText = '{Lang::T('Location request timed out.')}';
    const locationNotSupportedText = '{Lang::T('Geolocation is not supported by this browser.')}';
    const coordinatesClearedText = '{Lang::T('Coordinates cleared.')}';
    const selectBrandText = '{Lang::T('Select Brand')}';
    const loadingBrandsText = '{Lang::T('Loading brands...')}';
    const noBrandsAvailableText = '{Lang::T('No brands available')}';
    const errorLoadingBrandsText = '{Lang::T('Error loading brands')}';
    const unknownErrorOccurredText = '{Lang::T('An unknown error occurred.')}';



    document.addEventListener('DOMContentLoaded', function () {

        // Dynamic model loading based on brand selection
        document.getElementById('brand_id').addEventListener('change', function () {
            const brandId = this.value;
            const modelSelect = document.getElementById('model_id');

            // Clear current models
            modelSelect.innerHTML = '<option value="">' + selectModelText + '</option>';

            if (brandId) {
                // Add loading indicator
                modelSelect.innerHTML = '<option value="">' + loadingModelsText + '</option>';

                const url = '{$_url}plugin/assetManager&ajax=get-models&brand_id=' + brandId;
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                        }
                        return response.text();
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text);

                            // Clear loading message
                            modelSelect.innerHTML = '<option value="">' + selectModelText + '</option>';

                            if (data.success && data.models && data.models.length > 0) {
                                data.models.forEach(function (model) {
                                    const option = document.createElement('option');
                                    option.value = model.id;
                                    option.textContent = model.name;
                                    modelSelect.appendChild(option);
                                });
                            } else if (data.success && data.models && data.models.length === 0) {
                                modelSelect.innerHTML = '<option value="">' + noModelsAvailableText + '</option>';
                            } else {
                                modelSelect.innerHTML = '<option value="">' + errorLoadingModelsText + ': ' + (data.message || 'Unknown error') + '</option>';
                            }
                        } catch (parseError) {
                            modelSelect.innerHTML = '<option value="">' + errorLoadingModelsText + ': Invalid response format</option>';
                        }
                    })
                    .catch(error => {
                        modelSelect.innerHTML = '<option value="">' + errorLoadingModelsText + '</option>';
                    });
            }
        });

        // Update coordinates display when lat/lng inputs change
        document.getElementById('lat').addEventListener('input', updateCoordinatesDisplay);
        document.getElementById('lng').addEventListener('input', updateCoordinatesDisplay);
    });

    // Get current location using browser geolocation
    function getCurrentLocation() {
        if (navigator.geolocation) {
            const button = event.target.closest('button');
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + gettingLocationText;
            button.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('lat').value = lat.toFixed(6);
                    document.getElementById('lng').value = lng.toFixed(6);

                    // Try to get address from coordinates
                    reverseGeocode(lat, lng);

                    // Update map if it's visible
                    if (map && document.getElementById('mapContainer').style.display !== 'none') {
                        updateMapLocation(lat, lng);
                    }

                    button.innerHTML = originalHtml;
                    button.disabled = false;

                    showAlert('success', locationDetectedSuccessfullyText);
                },
                function (error) {
                    button.innerHTML = originalHtml;
                    button.disabled = false;

                    let message = gettingLocationErrorText + ' ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message += locationAccessDeniedText;
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += locationInformationUnavailableText;
                            break;
                        case error.TIMEOUT:
                            message += locationRequestTimedOutText;
                            break;
                        default:
                            message += unknownErrorOccurredText;
                    }
                    showAlert('warning', message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        } else {
            showAlert('warning', locationNotSupportedText);
        }
    }

    // Toggle map picker visibility
    function toggleMapPicker() {
        const mapContainer = document.getElementById('mapContainer');

        if (mapContainer.style.display === 'none') {
            mapContainer.style.display = 'block';
            initializeMap();
        } else {
            mapContainer.style.display = 'none';
        }
    }

    // Initialize the map
    function initializeMap() {
        if (map) {
            map.remove();
        }

        // Default to Nigeria center or use existing coordinates
        let lat = parseFloat(document.getElementById('lat').value) || 9.0820;
        let lng = parseFloat(document.getElementById('lng').value) || 8.6753;

        map = L.map('locationMap').setView([lat, lng], 10);

        var s = String.fromCharCode(123) + 's' + String.fromCharCode(125);
        var z = String.fromCharCode(123) + 'z' + String.fromCharCode(125);
        var x = String.fromCharCode(123) + 'x' + String.fromCharCode(125);
        var y = String.fromCharCode(123) + 'y' + String.fromCharCode(125);
        var tileUrl = 'https://' + s + '.tile.openstreetmap.org/' + z + '/' + x + '/' + y + '.png';

        L.tileLayer(tileUrl, {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add marker if coordinates exist
        if (document.getElementById('lat').value && document.getElementById('lng').value) {
            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            marker.on('dragend', function (e) {
                const position = e.target.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });
        }

        // Add click event to map
        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            updateCoordinates(lat, lng);
            updateMapLocation(lat, lng);
            reverseGeocode(lat, lng);
        });
    }

    // Update map location and marker
    function updateMapLocation(lat, lng) {
        if (!map) return;

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            marker.on('dragend', function (e) {
                const position = e.target.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });
        }

        map.setView([lat, lng], map.getZoom());
    }

    // Update coordinate inputs
    function updateCoordinates(lat, lng) {
        document.getElementById('lat').value = lat.toFixed(6);
        document.getElementById('lng').value = lng.toFixed(6);
        updateCoordinatesDisplay();
    }

    // Update coordinates display
    function updateCoordinatesDisplay() {
        const lat = document.getElementById('lat').value;
        const lng = document.getElementById('lng').value;

        if (lat && lng) {
            console.log('Coordinates updated:', lat, lng);
        }
    }

    // Clear coordinates
    function clearCoordinates() {
        document.getElementById('lat').value = '';
        document.getElementById('lng').value = '';
        document.getElementById('location').value = '';

        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        showAlert('info', coordinatesClearedText);
    }

    // Reverse geocoding to get address from coordinates
    function reverseGeocode(lat, lng) {

        const url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    const locationInput = document.getElementById('location');
                    if (!locationInput.value) { // Only update if location is empty
                        locationInput.value = data.display_name;
                    }
                }
            })
            .catch(error => {
                console.log('Reverse geocoding failed:', error);
                // Fail silently as this is not critical
            });
    }
</script>
{literal}
<script>
    // Generate random asset tag
    function generateRandomAssetTag() {
        const categories = ['IT', 'FN', 'HR', 'OPS', 'SEC', 'MISC'];
        const now = new Date();
        const year = now.getFullYear().toString().slice(-2);
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');

        // Get random category
        const category = categories[Math.floor(Math.random() * categories.length)];

        // Generate random 4-digit number
        const randomNum = Math.floor(1000 + Math.random() * 9000);

        // Format: CATEGORY-YYMMDD-XXXX (e.g., IT-241025-1234)
        const assetTag = `${category}-${year}${month}${day}-${randomNum}`;

        document.getElementById('asset_tag').value = assetTag;

        // Show success message
        showAlert('success', `Generated asset tag: ${assetTag}`);
    }
    // Show alert messages
    function showAlert(type, message) {
        // Create alert div
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible`;
        alertDiv.innerHTML = `
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            ${message}
        `;

        // Insert at top of panel body
        const panelBody = document.querySelector('.panel-body');
        panelBody.insertBefore(alertDiv, panelBody.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
</script>
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