</section>
</div>
<footer class="main-footer">
    <div class="pull-right" id="version" onclick="location.href = '{Text::url('community')}#latestVersion';"></div>
    PHPNuxBill by <a href="https://github.com/hotspotbilling/phpnuxbill" rel="nofollow noreferrer noopener"
        target="_blank">iBNuX</a>, Theme by <a href="https://adminlte.io/" rel="nofollow noreferrer noopener"
        target="_blank">AdminLTE</a>
</footer>
</div>
<script src="{$app_url}/ui/ui/scripts/jquery.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/bootstrap.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/adminlte.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/plugins/select2.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/pace.min.js"></script>
<script src="{$app_url}/ui/ui/summernote/summernote.min.js"></script>
<script src="{$app_url}/ui/ui/scripts/custom.js?2025.2.5"></script>

<script>
    document.getElementById('openSearch').addEventListener('click', function () {
        document.getElementById('searchOverlay').style.display = 'flex';
    });

    document.getElementById('closeSearch').addEventListener('click', function () {
        document.getElementById('searchOverlay').style.display = 'none';
    });

    document.getElementById('searchTerm').addEventListener('keyup', function () {
        let query = this.value;
        $.ajax({
            url: '{Text::url('search_user')}',
            type: 'GET',
            data: { query: query },
            success: function (data) {
                if (data.trim() !== '') {
                    $('#searchResults').html(data).show();
                } else {
                    $('#searchResults').html('').hide();
                }
            }
        });
    });
</script>

<script>
    const toggleIcon = document.getElementById('toggleIcon');
    const body = document.body;
    const savedMode = localStorage.getItem('mode');
    if (savedMode === 'dark') {
        body.classList.add('dark-mode');
        toggleIcon.className = 'fa fa-sun-o';
    }

    function setMode(mode) {
        if (mode === 'dark') {
            body.classList.add('dark-mode');
            toggleIcon.className = 'fa fa-sun-o';
        } else {
            body.classList.remove('dark-mode');
            toggleIcon.className = 'fa fa-moon-o';
        }
    }

    toggleIcon.addEventListener('click', () => {
        if (body.classList.contains('dark-mode')) {
            setMode('light');
            localStorage.setItem('mode', 'light');
        } else {
            setMode('dark');
            localStorage.setItem('mode', 'dark');
        }
    });
</script>

{if isset($xfooter)}
    {$xfooter}
{/if}
{literal}
    <script>
        var listAttApi;
        var posAttApi = 0;
        $(document).ready(function() {
            $('.select2').select2({theme: "bootstrap"});
            $('.select2tag').select2({theme: "bootstrap", tags: true});
            var listAtts = document.querySelectorAll(`button[type="submit"]`);
            listAtts.forEach(function(el) {
                if (el.addEventListener) { // all browsers except IE before version 9
                    el.addEventListener("click", function() {
                        var txt = $(this).html();
                        $(this).html(
                            `<span class="loading"></span>`
                        );
                        setTimeout(() => {
                            $(this).prop("disabled", true);
                        }, 100);
                        setTimeout(() => {
                            $(this).html(txt);
                            $(this).prop("disabled", false);
                        }, 5000);
                    }, false);
                } else {
                    if (el.attachEvent) { // IE before version 9
                        el.attachEvent("click", function() {
                            var txt = $(this).html();
                            $(this).html(
                                `<span class="loading"></span>`
                            );
                            setTimeout(() => {
                                $(this).prop("disabled", true);
                            }, 100);
                            setTimeout(() => {
                                $(this).html(txt);
                                $(this).prop("disabled", false);
                            }, 5000);
                        });
                    }
                }

            });
            setTimeout(() => {
                listAttApi = document.querySelectorAll(`[api-get-text]`);
                apiGetText();
            }, 500);
        });

        function ask(field, text){
            var txt = field.innerHTML;
            if (confirm(text)) {
                setTimeout(() => {
                    field.innerHTML = field.innerHTML.replace(`<span class="loading"></span>`, txt);
                    field.removeAttribute("disabled");
                }, 5000);
                return true;
            } else {
                setTimeout(() => {
                    field.innerHTML = field.innerHTML.replace(`<span class="loading"></span>`, txt);
                    field.removeAttribute("disabled");
                }, 500);
                return false;
            }
        }

        function apiGetText(){
            var el = listAttApi[posAttApi];
            if(el != undefined){
                $.get(el.getAttribute('api-get-text'), function(data) {
                    el.innerHTML = data;
                    posAttApi++;
                    if(posAttApi < listAttApi.length){
                        apiGetText();
                    }
                });
            }
        }

        function setKolaps() {
            var kolaps = getCookie('kolaps');
            if (kolaps) {
                setCookie('kolaps', false, 30);
            } else {
                setCookie('kolaps', true, 30);
            }
            return true;
        }

        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
        $("[data-toggle=popover]").popover();
    </script>
{/literal}

<script>
    (function() {
        function colorSidebar() {
            // Main sidebar background
            $('.main-sidebar').css({
                'background': 'linear-gradient(180deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important',
                'color': '#fff !important'
            });
            
            // Menu items - beautiful design
            $('.sidebar-menu > li > a').css({
                'color': '#ffffff !important',
                'font-weight': '600 !important',
                'font-size': '14px !important',
                'border-left': '4px solid transparent',
                'border-radius': '0 30px 30px 0',
                'margin': '3px 15px 3px 0',
                'padding': '12px 15px 12px 20px',
                'transition': 'all 0.3s ease',
                'box-shadow': '0 2px 5px rgba(0,0,0,0.1)'
            });
            
            // Add hover effects
            $('.sidebar-menu > li > a').hover(function() {
                $(this).css({
                    'transform': 'translateX(5px)',
                    'box-shadow': '0 4px 15px rgba(0,0,0,0.2)',
                    'background': 'rgba(255,255,255,0.15)'
                });
            }, function() {
                $(this).css({
                    'transform': 'translateX(0)',
                    'box-shadow': '0 2px 5px rgba(0,0,0,0.1)',
                    'background': 'transparent'
                });
            });
            
            // Active menu item
            $('.sidebar-menu > li.active > a').css({
                'background': '#fff !important',
                'color': '#667eea !important',
                'border-radius': '0 25px 25px 0',
                'margin-right': '15px'
            });
            
            // Submenu
            $('.treeview-menu').css({
                'background': 'rgba(255,255,255,0.15) !important'
            });
            $('.treeview-menu > li > a').css({
                'color': '#fff !important'
            });
            
            // Individual menu item colors
            var colors = [
                '#FF6B6B', // Dashboard - Red
                '#4ECDC4', // Customer - Teal
                '#45B7D1', // Services - Blue
                '#96CEB4', // Internet Plan - Mint
                '#FFEAA7', // Maps - Yellow
                '#DDA0DD', // Reports - Plum
                '#98D8C8', // Send Message - Sage
                '#F7DC6F', // Support Tickets - Gold
                '#BB8FCE', // Network - Lavender
                '#85C1E9', // Fiber Management - Sky
                '#F8B739', // Settings - Orange
                '#52BE80', // Internet Speedtest - Green
                '#EC7063', // Logs - Salmon
                '#AF7AC5', // Documentation - Amethyst
                '#5DADE2'  // Community - Azure
            ];
            
            $('.sidebar-menu > li').each(function(index) {
                if (index < colors.length) {
                    var color = colors[index];
                    $(this).find('> a').css({
                        'border-left-color': color + ' !important'
                    });
                    
                    // Add hover effect via inline style (will be overridden by CSS)
                    $(this).hover(function() {
                        $(this).find('> a').css('background', 'linear-gradient(90deg, ' + color + '4D 0%, transparent 100%)');
                    }, function() {
                        $(this).find('> a').css('background', '');
                    });
                }
            });
        }
        // Run immediately
        if (typeof jQuery !== 'undefined') {
            colorSidebar();
            $(document).ready(colorSidebar);
            $(window).on('load', colorSidebar);
            setTimeout(colorSidebar, 100);
            setTimeout(colorSidebar, 500);
            setTimeout(colorSidebar, 1000);
        } else {
            setTimeout(arguments.callee, 50);
        }
    })();
</script>

<!-- Immediate color application -->
<script>
    // Apply styles directly without waiting
    (function applyColors() {
        var sidebar = document.querySelector('.main-sidebar');
        var menuItems = document.querySelectorAll('.sidebar-menu > li > a');
        
        if (sidebar) {
            sidebar.style.background = 'linear-gradient(180deg, #667eea 0%, #764ba2 50%, #f093fb 100%)';
            sidebar.style.color = '#ffffff';
        }
        
        menuItems.forEach(function(item) {
            item.style.color = '#ffffff';
            item.style.fontWeight = '600';
            item.style.borderRadius = '0 30px 30px 0';
            item.style.margin = '3px 15px 3px 0';
            item.style.padding = '12px 15px 12px 20px';
            item.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
            item.style.transition = 'all 0.3s ease';
        });
    })();
</script>

</body>

</html>