<?php

/**
 * Ticket Siren Widget
 * 
 * Audio notification widget for high priority support tickets.
 * Plays alert sound when new high/medium priority tickets are received.
 * 
 * Place this widget on the admin dashboard for immediate ticket alerts.
 */

class TicketSiren
{
    /**
     * Widget info
     */
    public static function info()
    {
        return [
            'name' => 'Ticket Siren',
            'description' => 'Audio notifications for high priority support tickets',
            'author' => 'PHPNuxBill',
            'version' => '1.0.0',
        ];
    }

    /**
     * Render widget
     */
    public static function render($admin)
    {
        // Check if user has admin/agent access
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Agent', 'Support'])) {
            return '';
        }

        // Get settings
        $enabled = $admin['ticket_siren_enabled'] ?? true;
        if (!$enabled) {
            return '';
        }

        // Get unread high/medium priority tickets count
        $highPriorityCount = ORM::for_table('tbl_tickets')
            ->where('priority', 'High')
            ->where('status', 'Open')
            ->where_null('admin_read_at')
            ->count();

        $mediumPriorityCount = ORM::for_table('tbl_tickets')
            ->where('priority', 'Medium')
            ->where('status', 'Open')
            ->where_null('admin_read_at')
            ->count();

        $totalUrgent = $highPriorityCount + $mediumPriorityCount;

        // Build widget HTML
        $html = '<div id="ticket-siren-widget" class="ticket-siren-container">';
        $html .= '<div class="ticket-siren-header">';
        $html .= '<i class="fa fa-bell"></i> ' . Lang::T('Ticket Alerts');
        $html .= '<span class="ticket-siren-toggle" onclick="toggleTicketSiren()" title="' . Lang::T('Toggle Sound') . '">';
        $html .= '<i class="fa fa-volume-up" id="siren-icon"></i>';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '<div class="ticket-siren-status">';
        
        if ($highPriorityCount > 0) {
            $html .= '<div class="ticket-alert high">';
            $html .= '<i class="fa fa-exclamation-circle"></i> ';
            $html .= $highPriorityCount . ' ' . Lang::T('High Priority');
            $html .= '</div>';
        }
        
        if ($mediumPriorityCount > 0) {
            $html .= '<div class="ticket-alert medium">';
            $html .= '<i class="fa fa-exclamation-triangle"></i> ';
            $html .= $mediumPriorityCount . ' ' . Lang::T('Medium Priority');
            $html .= '</div>';
        }
        
        if ($totalUrgent == 0) {
            $html .= '<div class="ticket-alert normal">';
            $html .= '<i class="fa fa-check-circle"></i> ' . Lang::T('No urgent tickets');
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '<div class="ticket-siren-links">';
        $html .= '<a href="' . Text::url('tickets/list') . '?priority=High" class="btn btn-xs btn-danger">' . Lang::T('View High Priority') . '</a>';
        $html .= '<a href="' . Text::url('tickets/list') . '" class="btn btn-xs btn-default">' . Lang::T('All Tickets') . '</a>';
        $html .= '</div>';
        
        // Hidden audio element
        $html .= '<audio id="ticket-siren-audio" preload="auto">';
        $html .= '<source src="' . APP_URL . '/ui/ui/sounds/alert.mp3" type="audio/mpeg">';
        $html .= '<source src="' . APP_URL . '/ui/ui/sounds/alert.ogg" type="audio/ogg">';
        $html .= '</audio>';
        
        $html .= '</div>';

        // Add CSS
        $html .= '<style>
            .ticket-siren-container {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 8px;
                padding: 15px;
                color: white;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }
            .ticket-siren-header {
                font-weight: bold;
                font-size: 16px;
                margin-bottom: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .ticket-siren-toggle {
                cursor: pointer;
                padding: 5px;
                border-radius: 4px;
                transition: background 0.3s;
            }
            .ticket-siren-toggle:hover {
                background: rgba(255,255,255,0.2);
            }
            .ticket-siren-status {
                margin: 10px 0;
            }
            .ticket-alert {
                padding: 8px 12px;
                border-radius: 6px;
                margin: 5px 0;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .ticket-alert.high {
                background: rgba(255, 107, 107, 0.9);
                animation: sirenPulse 2s infinite;
            }
            .ticket-alert.medium {
                background: rgba(255, 193, 7, 0.9);
                color: #333;
            }
            .ticket-alert.normal {
                background: rgba(82, 190, 128, 0.9);
            }
            .ticket-siren-links {
                margin-top: 10px;
                display: flex;
                gap: 8px;
            }
            .ticket-siren-links .btn {
                flex: 1;
            }
            @keyframes sirenPulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
                50% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
            }
            .ticket-siren-muted {
                opacity: 0.7;
            }
        </style>';

        // Add JavaScript
        $html .= '<script>
            let sirenEnabled = localStorage.getItem("ticketSirenEnabled") !== "false";
            let lastCheck = 0;
            let audioContext = null;
            
            // Update icon
            function updateSirenIcon() {
                const icon = document.getElementById("siren-icon");
                if (icon) {
                    icon.className = sirenEnabled ? "fa fa-volume-up" : "fa fa-volume-off";
                }
                const container = document.getElementById("ticket-siren-widget");
                if (container && !sirenEnabled) {
                    container.classList.add("ticket-siren-muted");
                } else if (container) {
                    container.classList.remove("ticket-siren-muted");
                }
            }
            
            // Toggle siren
            function toggleTicketSiren() {
                sirenEnabled = !sirenEnabled;
                localStorage.setItem("ticketSirenEnabled", sirenEnabled);
                updateSirenIcon();
            }
            
            // Play alert sound
            function playSiren() {
                if (!sirenEnabled) return;
                
                // Try audio element first
                const audio = document.getElementById("ticket-siren-audio");
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(function(e) {
                        console.log("Audio play failed:", e);
                        // Fallback to Web Audio API
                        playWebAudioSiren();
                    });
                } else {
                    playWebAudioSiren();
                }
            }
            
            // Web Audio API fallback
            function playWebAudioSiren() {
                try {
                    if (!audioContext) {
                        audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Siren sound pattern
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
            
            // Check for new tickets
            function checkNewTickets() {
                if (!sirenEnabled) return;
                
                fetch("' . APP_URL . '/?_route=ajax/ticket-check&timestamp=" + Date.now())
                    .then(response => response.json())
                    .then(data => {
                        if (data.high_priority > 0 || data.medium_priority > 0) {
                            const total = data.high_priority + data.medium_priority;
                            if (total > lastCheck) {
                                playSiren();
                            }
                            lastCheck = total;
                        }
                    })
                    .catch(error => console.log("Ticket check failed:", error));
            }
            
            // Initialize
            document.addEventListener("DOMContentLoaded", function() {
                updateSirenIcon();
                
                // Check every 60 seconds
                setInterval(checkNewTickets, 60000);
                
                // Initial check after 5 seconds
                setTimeout(checkNewTickets, 5000);
            });
        </script>';

        return $html;
    }

    /**
     * Get unread ticket counts (for AJAX endpoint)
     */
    public static function getTicketCounts()
    {
        $high = ORM::for_table('tbl_tickets')
            ->where('priority', 'High')
            ->where('status', 'Open')
            ->where_null('admin_read_at')
            ->count();

        $medium = ORM::for_table('tbl_tickets')
            ->where('priority', 'Medium')
            ->where('status', 'Open')
            ->where_null('admin_read_at')
            ->count();

        return [
            'high_priority' => $high,
            'medium_priority' => $medium,
            'total' => $high + $medium,
        ];
    }
}
