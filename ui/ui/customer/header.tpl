<!DOCTYPE html>
<html lang="en" class="has-aside-left has-aside-mobile-transition has-navbar-fixed-top has-aside-expanded">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$_title} - {$_c['CompanyName']}</title>

    <script>
        var appUrl = '{$app_url}';
    </script>

    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/modern-AdminLTE.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css" />
    <script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill.customer.css?2025.2.5" />
    <!-- Colorful Modern Theme -->
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/colorful-theme.css" />

    <style>
        /* ULTRA-SPECIFIC - Force Bright White Text */
        html body.hold-transition.modern-skin-dark.sidebar-mini div.wrapper aside.main.sidebar section.sidebar ul.sidebar-menu li a,
        html body.modern-skin-dark div.wrapper aside.main.sidebar section.sidebar ul.sidebar-menu li a,
        body .wrapper .main-sidebar .sidebar .sidebar-menu > li > a,
        .sidebar-menu > li > a,
        .sidebar-menu > li > a span,
        .sidebar-menu > li > a i {
            color: #ffffff !important;
            opacity: 1 !important;
            font-weight: 600 !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4) !important;
        }
    </style>

    {if isset($xheader)}
        {$xheader}
    {/if}

    <!-- Customer Message Notification System -->
    {literal}
    <script>
    (function() {
        let lastUnreadCount = 0;
        let audioContext = null;
        let notificationEnabled = localStorage.getItem("customerMsgSoundEnabled") !== "false";

        // Play notification sound using Web Audio API
        function playMessageSound() {
            if (!notificationEnabled) return;
            
            try {
                if (!audioContext) {
                    audioContext = new (window.AudioContext || window.webkitAudioContext)();
                }
                
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                // Pleasant chime sound (C major chord)
                oscillator.type = "sine";
                oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime); // C5
                oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1); // E5
                oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2); // G5
                
                gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                console.log("Audio notification failed:", e);
            }
        }

        // Show notification banner
        function showNotificationBanner(count) {
            // Remove existing banner
            const existing = document.getElementById('customer-msg-banner');
            if (existing) existing.remove();
            
            if (count > 0) {
                const banner = document.createElement('div');
                banner.id = 'customer-msg-banner';
                var msgText = count + ' New Message' + (count > 1 ? 's' : '');
                banner.innerHTML = '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; text-align: center; position: fixed; top: 50px; left: 0; right: 0; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.3); animation: slideDown 0.5s ease;">' +
                    '<i class="fa fa-envelope" style="margin-right: 10px;"></i>' +
                    '<strong>' + msgText + '</strong>' +
                    '<a href="' + appUrl + '?_route=mail" style="color: #fff; text-decoration: underline; margin-left: 15px;">View Inbox</a>' +
                    '<button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; color: white; font-size: 20px; cursor: pointer;">&times;</button>' +
                    '</div>';
                document.body.insertBefore(banner, document.body.firstChild);
                
                // Auto hide after 10 seconds
                setTimeout(function() {
                    if (banner.parentElement) banner.remove();
                }, 10000);
            }
        }

        // Check for new messages
        function checkMessages() {
            console.log('Checking messages... appUrl=' + appUrl);
            fetch(appUrl + '?_route=autoload_user/inbox_unread&_=' + Date.now())
                .then(function(r) { 
                    console.log('Message check response:', r.status);
                    return r.text(); 
                })
                .then(function(count) {
                    console.log('Message count raw:', count);
                    var unread = parseInt(count) || 0;
                    console.log('Message count parsed:', unread);
                    
                    // Update header inbox badge
                    var headerBadge = document.querySelector('.notifications-menu .label');
                    console.log('Header badge found:', headerBadge ? 'yes' : 'no');
                    if (headerBadge) {
                        headerBadge.textContent = unread > 0 ? unread : '';
                        headerBadge.style.display = unread > 0 ? 'inline-block' : 'none';
                        console.log('Header badge updated to:', unread);
                    }
                    
                    // Update sidebar inbox badge
                    var sidebarInboxLink = document.querySelector('a[href*="mail"]');
                    console.log('Sidebar inbox link found:', sidebarInboxLink ? 'yes' : 'no');
                    if (sidebarInboxLink) {
                        var sidebarBadge = sidebarInboxLink.querySelector('.badge');
                        console.log('Sidebar badge exists:', sidebarBadge ? 'yes' : 'no');
                        if (!sidebarBadge && unread > 0) {
                            sidebarBadge = document.createElement('span');
                            sidebarBadge.className = 'badge bg-red pull-right';
                            sidebarBadge.style.cssText = 'background: #dd4b39; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 5px;';
                            sidebarInboxLink.appendChild(sidebarBadge);
                            console.log('Created new sidebar badge');
                        }
                        if (sidebarBadge) {
                            sidebarBadge.textContent = unread;
                            sidebarBadge.style.display = unread > 0 ? 'inline-block' : 'none';
                            console.log('Sidebar badge updated to:', unread);
                        }
                    }
                    
                    // If new messages arrived, notify
                    if (unread > lastUnreadCount && unread > 0) {
                        console.log('New messages detected! Playing sound...');
                        playMessageSound();
                        showNotificationBanner(unread);
                    }
                    
                    lastUnreadCount = unread;
                })
                .catch(function(e) { 
                    console.log('Message check failed:', e);
                });
        }

        // Check for new ticket replies (from admin)
        let lastTicketUnreadCount = 0;
        
        function showTicketNotification(count) {
            const existing = document.getElementById('ticket-notification-banner');
            if (existing) existing.remove();
            
            if (count > 0) {
                const banner = document.createElement('div');
                banner.id = 'ticket-notification-banner';
                var ticketText = count + ' ticket' + (count > 1 ? 's' : '') + ' with new reply' + (count > 1 ? 'ies' : '');
                banner.innerHTML = '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; text-align: center; position: fixed; top: 50px; left: 0; right: 0; z-index: 9998; box-shadow: 0 4px 15px rgba(0,0,0,0.3); animation: slideDown 0.5s ease;">' +
                    '<i class="fa fa-ticket" style="margin-right: 10px;"></i>' +
                    '<strong>' + ticketText + '</strong>' +
                    '<a href="' + appUrl + '?_route=customer_ticket/list" style="color: #fff; text-decoration: underline; margin-left: 15px;">View Tickets</a>' +
                    '<button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; color: white; font-size: 20px; cursor: pointer;">&times;</button>' +
                    '</div>';
                document.body.insertBefore(banner, document.body.firstChild);
                
                setTimeout(function() {
                    if (banner.parentElement) banner.remove();
                }, 10000);
            }
        }
        
        function updateTicketBadge(count) {
            // Update sidebar ticket badge
            var sidebarTicketLink = document.querySelector('a[href*="customer_ticket"]');
            if (sidebarTicketLink) {
                var ticketBadge = sidebarTicketLink.querySelector('.badge');
                if (!ticketBadge && count > 0) {
                    ticketBadge = document.createElement('span');
                    ticketBadge.className = 'badge bg-red pull-right';
                    ticketBadge.style.cssText = 'background: #dd4b39; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 5px;';
                    sidebarTicketLink.appendChild(ticketBadge);
                }
                if (ticketBadge) {
                    ticketBadge.textContent = count;
                    ticketBadge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            }
        }
        
        function checkTicketReplies() {
            fetch(appUrl + '?_route=autoload_user/ticket_unread&_=' + Date.now())
                .then(function(r) { return r.text(); })
                .then(function(count) {
                    var unread = parseInt(count) || 0;
                    console.log('Ticket unread count:', unread);
                    
                    updateTicketBadge(unread);
                    
                    // If new ticket replies arrived, notify
                    if (unread > lastTicketUnreadCount && unread > 0) {
                        console.log('New ticket replies detected!');
                        playMessageSound();
                        showTicketNotification(unread);
                    }
                    
                    lastTicketUnreadCount = unread;
                })
                .catch(function(e) { console.log('Ticket check failed:', e); });
        }

        // Check on page load and every 30 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(checkMessages, 2000);
            setInterval(checkMessages, 30000);
            setTimeout(checkTicketReplies, 3000);
            setInterval(checkTicketReplies, 30000);
        });
    })();
    </script>
    {/literal}
    <style>
    @keyframes slideDown {
        from { transform: translateY(-100%); }
        to { transform: translateY(0); }
    }
    </style>

</head>

<body class="hold-transition modern-skin-dark sidebar-mini">
    <div class="wrapper">
        <header class="main-header" style="position:fixed; width: 100%">
            <a href="{Text::url('home')}" class="logo">
                <span class="logo-mini"><b>N</b>uX</span>
                <span class="logo-lg">{$_c['CompanyName']}</span>
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <a class="toggle-container" href="#">
                                <i class="fa fa-moon-o" id="toggleIcon"></i>
                            </a>
                        </li>
                        <li class="dropdown tasks-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                <i class="fa fa-flag-o"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu" api-get-text="{Text::url('autoload_user/language&select=',$user_language)}"></ul>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown notifications-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-envelope-o"></i>
                                <span class="label label-warning"
                                    api-get-text="{Text::url('autoload_user/inbox_unread')}"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu" api-get-text="{Text::url('autoload_user/inbox')}"></ul>
                                </li>
                                <li class="footer"><a href="{Text::url('mail')}">{Lang::T('Inbox')}</a></li>
                            </ul>
                        </li>
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                {if $_c['enable_balance'] == 'yes'}
                                    <span
                                        style="color: whitesmoke;">&nbsp;{Lang::moneyFormat($_user['balance'])}&nbsp;</span>
                                {else}
                                    <span>{$_user['fullname']}</span>
                                {/if}
                                <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg"
                                    onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'" class="user-image"
                                    alt="User Image">
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg"
                                        onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'" class="img-circle"
                                        alt="User Image">

                                    <p>
                                        {$_user['fullname']}
                                        <small>{$_user['phonenumber']}<br>
                                            {$_user['email']}</small>
                                    </p>
                                </li>
                                <li class="user-body">
                                    <div class="row">
                                        <div class="col-xs-7 text-center text-sm">
                                            <a href="{Text::url('accounts/change-password')}"><i class="ion ion-settings"></i>
                                                {Lang::T('Change Password')}</a>
                                        </div>
                                        <div class="col-xs-5 text-center text-sm">
                                            <a href="{Text::url('accounts/profile')}"><i class="ion ion-person"></i>
                                                {Lang::T('My Account')}</a>
                                        </div>
                                    </div>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-right">
                                        <a href="{Text::url('logout')}" class="btn btn-default btn-flat"><i
                                                class="ion ion-power"></i> {Lang::T('Logout')}</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <aside class="main-sidebar" style="position:fixed;">
            <section class="sidebar">
                <ul class="sidebar-menu">
                    <li {if $_system_menu eq 'home'}class="active" {/if}>
                        <a href="{Text::url('home')}">
                            <i class="ion ion-monitor"></i>
                            <span>{Lang::T('Dashboard')}</span>
                        </a>
                    </li>
                    {$_MENU_AFTER_DASHBOARD}
                    {if $_c['enable_balance'] == 'yes'}
                        <li {if $_system_menu eq 'wallet'}class="active" {/if}>
                            <a href="{Text::url('order/balance')}">
                                <i class="fa fa-wallet"></i>
                                <span>{Lang::T('My Wallet')}</span>
                                {if $_user['balance'] > 0}
                                    <span class="badge bg-green pull-right">{Lang::moneyFormat($_user['balance'])}</span>
                                {elseif $_user['balance'] < 0}
                                    <span class="badge bg-red pull-right">{Lang::moneyFormat($_user['balance'])}</span>
                                {/if}
                            </a>
                        </li>
                    {/if}
                    <li {if $_system_menu eq 'inbox'}class="active" {/if}>
                        <a href="{Text::url('mail')}">
                            <i class="fa fa-envelope"></i>
                            <span>{Lang::T('Inbox')}</span>
                        </a>
                    </li>
                    {$_MENU_AFTER_INBOX}
                    <li {if $_system_menu eq 'tickets'}class="active" {/if}>
                        <a href="{Text::url('customer_ticket/list')}">
                            <i class="fa fa-ticket"></i>
                            <span>{Lang::T('Support Tickets')}</span>
                        </a>
                    </li>
                    {if $_c['disable_voucher'] != 'yes'}
                        <li {if $_system_menu eq 'voucher'}class="active" {/if}>
                            <a href="{Text::url('voucher/activation')}">
                                <i class="fa fa-ticket"></i>
                                <span>Voucher</span>
                            </a>
                        </li>
                    {/if}
                    {if $_c['payment_gateway'] != 'none' or $_c['payment_gateway'] == '' }
                        {if $_c['enable_balance'] == 'yes'}
                            <li {if $_system_menu eq 'balance'}class="active" {/if}>
                                <a href="{Text::url('order/balance')}">
                                    <i class="ion ion-ios-cart"></i>
                                    <span>{Lang::T('Buy Balance')}</span>
                                </a>
                            </li>
                        {/if}
                        <li {if $_system_menu eq 'package'}class="active" {/if}>
                            <a href="{Text::url('order/package')}">
                                <i class="ion ion-ios-cart"></i>
                                <span>{Lang::T('Buy Package')}</span>
                            </a>
                        </li>
                        <li {if $_system_menu eq 'history'}class="active" {/if}>
                            <a href="{Text::url('order/history')}">
                                <i class="fa fa-file-text"></i>
                                <span>{Lang::T('Payment History')}</span>
                            </a>
                        </li>
                    {/if}
                    {$_MENU_AFTER_ORDER}
                    <li {if $_system_menu eq 'list-activated'}class="active" {/if}>
                        <a href="{Text::url('voucher/list-activated')}">
                            <i class="fa fa-list-alt"></i>
                            <span>{Lang::T('Activation History')}</span>
                        </a>
                    </li>
                    {$_MENU_AFTER_HISTORY}
                </ul>
            </section>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    {$_title}
                </h1>
            </section>
            <section class="content">


                {if isset($notify)}
                    <script>
                        // Display SweetAlert toast notification
                        Swal.fire({
                            icon: '{if $notify_t == "s"}success{else}warning{/if}',
                            title: '{$notify}',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });
                    </script>
{/if}
