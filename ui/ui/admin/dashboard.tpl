{include file="sections/header.tpl"}

<!-- Ticket Siren Notification Banner -->
{if $total_urgent_tickets > 0}
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning alert-dismissible" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white;">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-bell"></i> {Lang::T('Urgent Support Tickets')}</h4>
            <p>
                {if $high_priority_tickets > 0}
                    <span class="badge bg-red">{$high_priority_tickets} {Lang::T('High Priority')}</span>
                {/if}
                {if $medium_priority_tickets > 0}
                    <span class="badge bg-yellow">{$medium_priority_tickets} {Lang::T('Medium Priority')}</span>
                {/if}
                <a href="{Text::url('ticket/list')}" class="btn btn-xs btn-default pull-right">{Lang::T('View Tickets')}</a>
            </p>
        </div>
    </div>
</div>
{/if}

<!-- Quick Access - Support Tickets (Always Visible) -->
<div class="row">
    <div class="col-md-12">
        <div class="small-box bg-purple" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
            <div class="inner">
                <h3><i class="fa fa-ticket"></i> {Lang::T('Support Tickets')}</h3>
                <p>
                    {if $total_urgent_tickets > 0}
                        <span class="badge bg-red">{$total_urgent_tickets} {Lang::T('Urgent')}</span> {Lang::T('tickets require attention')}
                    {else}
                        {Lang::T('No urgent tickets')}
                    {/if}
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-headphones"></i>
            </div>
            <a href="{Text::url('ticket/list')}" class="small-box-footer">
                {Lang::T('View All Tickets')} <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{function showWidget pos=0}
    {foreach $widgets as $w}
        {if $w['position'] == $pos}
            {$w['content']}
        {/if}
    {/foreach}
{/function}

{assign dtipe value="dashboard_`$tipeUser`"}

{assign rows explode(".", $_c[$dtipe])}
{assign pos 1}
{foreach $rows as $cols}
    {if $cols == 12}
        <div class="row">
            <div class="col-md-12">
                {showWidget widgets=$widgets pos=$pos}
            </div>
        </div>
        {assign pos value=$pos+1}
    {else}
        {assign colss explode(",", $cols)}
        <div class="row">
            {foreach $colss as $c}
                <div class="col-md-{$c}">
                    {showWidget widgets=$widgets pos=$pos}
                </div>
                {assign pos value=$pos+1}
            {/foreach}
        </div>
    {/if}
{/foreach}

<!-- Ticket Siren Audio Notification -->
{if $total_urgent_tickets > 0}
    <script>
        // Ticket Siren Audio
        (function() {
            let audioContext = null;
            let sirenEnabled = localStorage.getItem("ticketSirenEnabled") !== "false";
            
            // Play siren sound using Web Audio API
            function playSiren() {
                if (!sirenEnabled) return;
                
                try {
                    if (!audioContext) {
                        audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Siren sound pattern (alternating frequencies)
                    oscillator.type = "sawtooth";
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    oscillator.frequency.linearRampToValueAtTime(600, audioContext.currentTime + 0.5);
                    oscillator.frequency.linearRampToValueAtTime(800, audioContext.currentTime + 1);
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 1);
                } catch (e) {
                    console.log("Web Audio API failed:", e);
                }
            }
            
            // Play once on page load if there are urgent tickets
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(playSiren, 2000);
            });
        })();
    </script>
{/if}

{if $_c['new_version_notify'] != 'disable'}
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            $.getJSON("./version.json?" + Math.random(), function(data) {
                var localVersion = data.version;
                $('#version').html('Version: ' + localVersion);
                $.getJSON(
                    "https://raw.githubusercontent.com/hotspotbilling/phpnuxbill/master/version.json?" +
                    Math
                    .random(),
                    function(data) {
                        var latestVersion = data.version;
                        if (localVersion !== latestVersion) {
                            $('#version').html('Latest Version: ' + latestVersion);
                            if (getCookie(latestVersion) != 'done') {
                                Swal.fire({
                                    icon: 'info',
                                    title: "New Version Available\nVersion: " + latestVersion,
                                    toast: true,
                                    position: 'bottom-right',
                                    showConfirmButton: true,
                                    showCloseButton: true,
                                    timer: 30000,
                                    confirmButtonText: '<a href="{Text::url('community')}#latestVersion" style="color: white;">Update Now</a>',
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                        toast.addEventListener('mouseleave', Swal
                                            .resumeTimer)
                                    }
                                });
                                setCookie(latestVersion, 'done', 7);
                            }
                        }
                    });
            });

        });
    </script>
{/if}

{include file="sections/footer.tpl"}