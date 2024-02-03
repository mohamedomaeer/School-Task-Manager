<?php
/**
 * Plugin Name: School Task Manager
 * Plugin URI: https://interestingengineering.com/school-task-manager
 * Description: A custom plugin for managing tasks in a school environment.
 * Version: 1.0
 * Author: Mohamed Elomaeer
 * Author URI: http://yourwebsite.com
 */

// Hook into the 'init' action
add_action('init', 'register_task_custom_post_type');

function register_task_custom_post_type() {
    $labels = array(
        'name'                  => _x('Tasks', 'Post type general name', 'textdomain'),
        'singular_name'         => _x('Task', 'Post type singular name', 'textdomain'),
        'menu_name'             => _x('Tasks', 'Admin Menu text', 'textdomain'),
        'name_admin_bar'        => _x('Task', 'Add New on Toolbar', 'textdomain'),
        'add_new'               => __('Add New', 'textdomain'),
        'add_new_item'          => __('Add New Task', 'textdomain'),
        'new_item'              => __('New Task', 'textdomain'),
        'edit_item'             => __('Edit Task', 'textdomain'),
        'view_item'             => __('View Task', 'textdomain'),
        'all_items'             => __('All Tasks', 'textdomain'),
        'search_items'          => __('Search Tasks', 'textdomain'),
        'parent_item_colon'     => __('Parent Tasks:', 'textdomain'),
        'not_found'             => __('No tasks found.', 'textdomain'),
        'not_found_in_trash'    => __('No tasks found in Trash.', 'textdomain'),
        'featured_image'        => _x('Task Cover Image', 'Overrides the “Featured Image” phrase', 'textdomain'),
        'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase', 'textdomain'),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase', 'textdomain'),
        'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase', 'textdomain'),
        'archives'              => _x('Task archives', 'The post type archive label used in nav menus', 'textdomain'),
        'insert_into_item'      => _x('Insert into task', 'Overrides the “Insert into post”/”Insert into page” phrase', 'textdomain'),
        'uploaded_to_this_item' => _x('Uploaded to this task', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase', 'textdomain'),
        'filter_items_list'     => _x('Filter tasks list', 'Screen reader text for the filter links', 'textdomain'),
        'items_list_navigation' => _x('Tasks list navigation', 'Screen reader text for the pagination', 'textdomain'),
        'items_list'            => _x('Tasks list', 'Screen reader text for the items list', 'textdomain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'task'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    );

    register_post_type('task', $args);
}


// Hook into the 'init' action for taxonomies
add_action('init', 'create_task_taxonomies', 0);

function create_task_taxonomies() {
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name'              => _x('Task Categories', 'taxonomy general name', 'textdomain'),
        'singular_name'     => _x('Task Category', 'taxonomy singular name', 'textdomain'),
        'search_items'      => __('Search Task Categories', 'textdomain'),
        'all_items'         => __('All Task Categories', 'textdomain'),
        'parent_item'       => __('Parent Task Category', 'textdomain'),
        'parent_item_colon' => __('Parent Task Category:', 'textdomain'),
        'edit_item'         => __('Edit Task Category', 'textdomain'),
        'update_item'       => __('Update Task Category', 'textdomain'),
        'add_new_item'      => __('Add New Task Category', 'textdomain'),
        'new_item_name'     => __('New Task Category Name', 'textdomain'),
        'menu_name'         => __('Task Categories', 'textdomain'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'task-category'),
    );

    register_taxonomy('task_category', array('task'), $args);
}


// Shortcode
function stm_list_tasks_shortcode($atts) {
    // Default attributes for the shortcode
    $atts = shortcode_atts(array(
        'category' => '',
        'status' => '',
        'priority' => '',
        'show_description' => 'false',
    ), $atts, 'list_tasks');

    // WP Query arguments
    $args = array(
        'post_type' => 'task',
        'posts_per_page' => -1,
        'tax_query' => array(),
        'meta_query' => array(),
    );

    // Apply filters based on shortcode attributes
    if (!empty($atts['category'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'task_categories',
            'field'    => 'slug',
            'terms'    => $atts['category'],
        );
    }

    if (!empty($atts['status'])) {
        $args['meta_query'][] = array(
            'key'   => 'status',
            'value' => $atts['status'],
        );
    }

    if (!empty($atts['priority'])) {
        $args['meta_query'][] = array(
            'key'   => 'priority',
            'value' => $atts['priority'],
        );
    }

    $tasks = new WP_Query($args);
    ob_start();

    if ($tasks->have_posts()) {
        echo '<div class="stm-task-list">';
        while ($tasks->have_posts()) : $tasks->the_post();
            $permalink = get_permalink();
            echo '<div class="stm-task">';
            // Make the task title clickable
            echo '<h4 class="task-title"><a href="' . esc_url($permalink) . '">' . get_the_title() . '</a></h4>';
            if ($atts['show_description'] === 'true') {
                echo '<p class="task-desc">' . get_the_content() . '</p>';
            }
            echo '<p class="task-duedate"><strong>Due Date:</strong> ' . get_field('due_date') . '</p>';
            echo '<p class="task-priority"><strong>Priority:</strong> ' . get_field('priority') . '</p>';
            echo '<p class="task-status"><strong>Status:</strong> ' . get_field('status') . '</p>';
            echo '</div>';
        endwhile;
        echo '</div>'; 
    } else {
        echo 'No tasks found.';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('list_tasks', 'stm_list_tasks_shortcode');



// Define the Widget Class
class STM_Tasks_Widget extends WP_Widget {

    // Constructor
    public function __construct() {
        parent::__construct(
            'stm_tasks_widget',
            'Recent Tasks',
            array('description' => __( 'Displays a list of recent tasks.', 'text_domain' ), )
        );
    }

    // Widget front-end display
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        // Query to fetch recent tasks
        $recent_tasks_query = new WP_Query(array('post_type' => 'task', 'posts_per_page' => 5));
        if($recent_tasks_query->have_posts()) {
            echo '<ul>';
            while($recent_tasks_query->have_posts()) : $recent_tasks_query->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            endwhile;
            echo '</ul>';
        } else {
            echo 'No recent tasks.';
        }

        echo $args['after_widget'];
    }

    // Widget Backend 
    public function form( $instance ) {
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'New title', 'text_domain' );
        // Widget admin form
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php 
    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}

// Register and load the widget
function stm_load_widget() {
    register_widget( 'STM_Tasks_Widget' );
}
add_action( 'widgets_init', 'stm_load_widget' );


// Register a Custom REST API Endpoint
function stm_register_assign_task_route() {
    register_rest_route('stm/v1', '/assign-task/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'stm_assign_task',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'assigned_to' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
}

add_action('rest_api_init', 'stm_register_assign_task_route');

// Callback function for assigning a task
function stm_assign_task($request) {
    $task_id = (int) $request['id'];
    $assigned_to = sanitize_text_field($request['assigned_to']);

    // Update the 'Assigned To' field
    update_field('assigned_to', $assigned_to, $task_id);

    return new WP_REST_Response('Task assigned successfully', 200);
}



// Enqueue the script and localize AJAX object
function stm_enqueue_scripts() {
    wp_enqueue_script('stm-ajax-script', plugin_dir_url(__FILE__) . 'js/stm-ajax.js', array('jquery'), null, true);
    wp_localize_script('stm-ajax-script', 'stm_ajax_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('stm_ajax_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'stm_enqueue_scripts');

// AJAX handler for fetching tasks
function stm_fetch_tasks() {
    // Check nonce for security
    check_ajax_referer('stm_ajax_nonce', 'security');

    // Query tasks or handle the request here
    // Example response
    $response = array('success' => true, 'data' => 'Your dynamic tasks data here');

    // Always use wp_send_json() to ensure proper encoding and handling
    wp_send_json($response);
}
add_action('wp_ajax_fetch_tasks', 'stm_fetch_tasks');
add_action('wp_ajax_nopriv_fetch_tasks', 'stm_fetch_tasks'); 





?>
