/**
 * Player ID Admin JavaScript
 * Handles real-time validation of Player IDs
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        var $playerIdField = $('#player_id');
        var $form = $playerIdField.closest('form');
        var userId = $form.find('input[name="user_id"]').val() || 0;
        var checkTimer;
        var $feedback = $('<span class="player-id-feedback"></span>');
        
        // Add feedback element
        $playerIdField.after($feedback);
        
        // Validate on input
        $playerIdField.on('input', function() {
            clearTimeout(checkTimer);
            var playerId = $(this).val().trim();
            
            if (!playerId) {
                $feedback.removeClass('available unavailable').text('');
                return;
            }
            
            $feedback.removeClass('available unavailable').text('Checking...');
            
            checkTimer = setTimeout(function() {
                checkPlayerIdAvailability(playerId, userId);
            }, 500);
        });
        
        // Check availability via REST API
        function checkPlayerIdAvailability(playerId, excludeUserId) {
            $.ajax({
                url: playerIdAdmin.apiUrl + 'player-id/v1/check',
                method: 'GET',
                data: {
                    player_id: playerId,
                    user_id: excludeUserId
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', playerIdAdmin.nonce);
                },
                success: function(response) {
                    if (response.available) {
                        $feedback.removeClass('unavailable').addClass('available').text('✓ Available');
                    } else {
                        $feedback.removeClass('available').addClass('unavailable').text('✗ Already in use');
                    }
                },
                error: function() {
                    $feedback.removeClass('available unavailable').text('Error checking availability');
                }
            });
        }
        
        // Prevent form submission if Player ID is not available
        $form.on('submit', function(e) {
            if ($feedback.hasClass('unavailable')) {
                e.preventDefault();
                alert('The Player ID is already in use. Please choose another one.');
                $playerIdField.focus();
            }
        });
    });
    
})(jQuery);