jQuery(document).ready(function($) {
    function updateTasks() {
        $.ajax({
            url: stm_ajax_obj.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'fetch_tasks', // The action hook name for the AJAX handler
                'security': stm_ajax_obj.nonce // Nonce for security verification
            },
            success: function(response) {
                if (response.success) {
                    // Assuming 'data' contains HTML of your tasks
                    $('#tasks-list-container').html(response.data);
                } else {
                    // Handle errors or empty data scenarios
                    $('#tasks-list-container').html('<p>No tasks found.</p>');
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                console.error("AJAX Error:", status, error);
            }
        });
    }

    
    setInterval(updateTasks, 300000); // 300000 milliseconds = 5 minutes

});
