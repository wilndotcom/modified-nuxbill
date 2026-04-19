{include file="sections/header.tpl"}

<section class="content-header">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="text-success fw-bold display-5 d-flex align-items-center">
            <i class="fa fa-wifi me-3"></i> Hotspot Settings
        </h6>

    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light p-3 rounded">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hotspot Settings</li>
        </ol>
        <a href="{$app_url}/download.php?download=1" class="btn btn-lg btn-info shadow">
            <i class="fa fa-download"></i> Download Login Page
        </a>
    </nav>
</section>


<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">

                <div class="show" id="settingsForm">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label"><i class="fa fa-header"></i> Hotspot Page Title</label>
                                <input type="text" class="form-control" name="hotspot_title" value="{$hotspot_title}"
                                    required placeholder="Hotspot Page Title">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fa fa-info-circle"></i> Description /
                                    Tagline</label>
                                <input type="text" class="form-control" name="description" value="{$description}"
                                    required placeholder="Description / Tagline">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fa fa-wifi"></i> Router</label>
                                <select class="form-control" name="router_id">
                                    <option value="">Select a router</option>
                                    {foreach $routers as $router}
                                        <option value="{$router.id}" {if $router.id eq $selected_router_id}selected{/if}>
                                            {$router.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fa fa-paint-brush"></i> Color Scheme</label>
                                <select class="form-control" name="color_scheme">
                                    <option value="green" {if $selected_color_scheme == 'green'}selected{/if}>Green
                                    </option>
                                    <option value="brown" {if $selected_color_scheme == 'brown'}selected{/if}>Brown
                                    </option>
                                    <option value="orange" {if $selected_color_scheme == 'orange'}selected{/if}>Orange
                                    </option>
                                    <option value="red" {if $selected_color_scheme == 'red'}selected{/if}>Red</option>
                                    <option value="blue" {if $selected_color_scheme == 'blue'}selected{/if}>Blue
                                    </option>
                                    <option value="black" {if $selected_color_scheme == 'black'}selected{/if}>Black
                                    </option>
                                    <option value="yellow" {if $selected_color_scheme == 'yellow'}selected{/if}>Yellow
                                    </option>
                                    <option value="pink" {if $selected_color_scheme == 'pink'}selected{/if}>Pink
                                    </option>
                                </select>
                            </div>

                            <!-- Shape Selector -->
                            <div class="mb-3">
                                <label class="form-label"><i class="fa fa-info"></i> Hotspot Card Shape</label>
                                <select class="form-control" name="shape_selector">
                                    <option value="square" {if $selected_shape_selector == 'square'}selected{/if}>Square
                                    </option>
                                    <option value="rectangle" {if $selected_shape_selector == 'rectangle'}selected{/if}>
                                        Rectangle</option>
                                    <option value="circle" {if $selected_shape_selector == 'circle'}selected{/if}>Circle
                                    </option>
                                    <option value="oval" {if $selected_shape_selector == 'oval'}selected{/if}>
                                        Oval</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fa fa-info"></i> Hotspot Card Auto/ Manual
                                    Display</label>
                                <select class="form-control" name="auto_manual_display">
                                    <option value="auto" {if $selected_auto_manual_display == 'auto'}selected{/if}>Auto
                                    </option>
                                    <option value="manual" {if $selected_auto_manual_display == 'manual'}selected{/if}>
                                        Manual</option>
                                </select>
                            </div>
                            <br>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save
                                    Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0"><i class="fa fa-info-circle"></i> Usage Instructions</h3>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item">Click "Save Changes" twice for quick upload.</li>
                        <li class="list-group-item">Customize and personalize your settings.</li>
                        <li class="list-group-item">Download the <code>login.html</code> file.</li>
                        <li class="list-group-item">Upload <code>login.html</code> to your MikroTik router.</li>
                        <li class="list-group-item">Ensure the file is named <strong>login.html</strong>.</li>
                        <li class="list-group-item">Add your website URL to the MikroTik walled garden.</li>
                        <div class="relative">
                            <pre id="scriptContent"
                                class="w-full p-3 border rounded-md text-sm bg-gray-50 overflow-auto">
/ip hotspot walled-garden
add dst-host=jsdelivr.com
add dst-host=cdn.tailwindcss.com
add dst-host=cdnjs.cloudflare.com
add dst-host=cdn.jsdelivr.net
add dst-host=sweetalert2.github.io
add dst-host=jsdelivr.com
add dst-host=www.jsdelivr.com
add dst-host=ajax.googleapis.com
add dst-host=sweetalert2.github.io
add dst-host=fonts.googleapis.com
add dst-host=fonts.gstatic.com
add dst-host=unpkg.com
add dst-host=kit.fontawesome.com
add dst-host=code.jquery.com
add dst-host={$_domain}
add dst-host=*.{$_domain}
                        </pre>

                         
                        <button onclick="copyToClipboard()" class="btn btn-primary mt-4">
                        <i class="fa fa-copy"></i> Copy Script
                    </button>
                    <br> <br>


                            <p class="mt-4">Also consider adding the following domains to the walled garden:</p>
                       


                            <pre id="scriptContent_2"
                                class="w-full p-3 border rounded-md text-sm bg-gray-50 overflow-auto">

/ip hotspot walled-garden ip add action=accept dst-host="{$_domain}"
/ip hotspot walled-garden ip add action=accept dst-host="{$main_domain}"
/ip hotspot walled-garden ip add action=accept dst-host="code.jquery.com"
/ip hotspot walled-garden ip add action=accept dst-host="cdn.jsdelivr.net"
/ip hotspot walled-garden ip add action=accept dst-host="cdnjs.cloudflare.com"
/ip hotspot walled-garden ip add action=accept dst-host="fonts.googleapis.com"
/ip hotspot walled-garden ip add action=accept dst-host="cdn.tailwindcss.com"
/ip hotspot walled-garden ip add action=accept dst-host="*.{$main_domain}"
/ip hotspot walled-garden ip add action=accept dst-host="ajax.googleapis.com"

</pre>

                        <button onclick="copyToClipboardSecond()" class="btn btn-primary mt-4">
                        <i class="fa fa-copy"></i> Copy Script
                    </button>





                        </div>

                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function copyToClipboard() {
        const scriptContent = document.getElementById("scriptContent").innerText;

        navigator.clipboard.writeText(scriptContent).then(() => {
            Swal.fire({
                icon: "success",
                title: "Copied!",
                text: "Walled garden script copied to clipboard!",
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(err => {
            console.error("Failed to copy: ", err);
        });
    }

    function copyToClipboardSecond() {
        const scriptContent = document.getElementById("scriptContent_2").innerText;

        navigator.clipboard.writeText(scriptContent).then(() => {
            Swal.fire({
                icon: "success",
                title: "Copied!",
                text: "Walled garden script copied to clipboard!",
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(err => {
            console.error("Failed to copy: ", err);
        });
    }
    
</script>

{include file="sections/footer.tpl"}