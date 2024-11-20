<?php

/**
 * Plugin Name: Events Manager
 * Description: Manage events with ticket sales, descriptions, and media library support for event photos.
 * Author: Joshua Ibrahim
 * Author URI: https://whitelaketechnologies.com
 * Version: 1.2.0
 * Text Domain: events-manager
 */

if (!defined('ABSPATH')) {
    echo "What are you trying to do here?";
    exit;
}

class EventsManager
{
    public function __construct()
    {
        add_action('init', array($this, 'create_custom_post_type'));
        add_action('add_meta_boxes', array($this, 'add_event_meta_boxes'));
        add_action('save_post', array($this, 'save_event_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_fields'));
    }

    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'), // 'editor' enables the description field
            'labels' => array(
                'name' => 'Events',
                'singular_name' => 'Event'
            ),
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_rest' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
        );

        register_post_type('events', $args);
    }

    public function add_event_meta_boxes()
    {
        add_meta_box(
            'event_details',
            'Event Details',
            array($this, 'event_meta_box_callback'),
            'events',
            'normal',
            'high'
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'events-manager-script',
            plugins_url('events-manager.js', __FILE__),
            array('jquery'),
            null,
            true
        );
        wp_enqueue_media(); // For media library support
    }

    public function event_meta_box_callback($post)
    {
        wp_nonce_field('save_event_meta', 'event_meta_nonce');

        $title = get_post_meta($post->ID, '_event_title', true);
        $venue = get_post_meta($post->ID, '_event_venue', true);
        $date = get_post_meta($post->ID, '_event_date', true);
        $time = get_post_meta($post->ID, '_event_time', true);
        $description = get_post_meta($post->ID, '_event_description', true);
        $tickets = get_post_meta($post->ID, '_event_tickets', true) ? json_decode(get_post_meta($post->ID, '_event_tickets', true), true) : [];
        $promo_code = get_post_meta($post->ID, '_event_promo_code', true);
        $discount = get_post_meta($post->ID, '_event_discount', true);
        $event_photo = get_post_meta($post->ID, '_event_photo', true);
?>
        <style>
            .events {
                display: flex;
                flex-direction: row;
                flex-flow: wrap;
                justify-content: space-between;
                align-items: center;
            }

            .events>div {
                margin-bottom: 10px;
            }

            .events>div>input {
                height: 40px;
                width: 100%;
            }

            .events>div>textarea {
                width: 100%;
                min-height: 100px;
            }

            .events>.inputs {
                width: 48%;
            }

            .events>.full {
                width: 100%;
            }

            .bbutton {
                padding: 7px 0px 10px 0px !important;
                background-color: #e72a28 !important;
                color: white !important;
                border-radius: 5px !important;
                text-decoration: none !important;
                border: none !important;
                outline: none !important;
                line-height: normal !important;
                vertical-align: middle !important;
                font-weight: 700;
                min-width: 100px;
            }
        </style>

        <div class="events">
            <div class="full">
                <label for="event_title">Title:</label>
                <input type="text" id="event_title" name="event_title" value="<?php echo esc_attr($title); ?>" required>
            </div>
            <div class="full">
                <label for="event_venue">Venue:</label>
                <input type="text" id="event_venue" name="event_venue" value="<?php echo esc_attr($venue); ?>" required>
            </div>
            <div class="inputs">
                <label for="event_date">Date:</label>
                <input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($date); ?>" required>
            </div>
            <div class="inputs">
                <label for="event_time">Time:</label>
                <input type="time" id="event_time" name="event_time" value="<?php echo esc_attr($time); ?>" required>
            </div>
            <div class="full">
                <label for="event_description">Description:</label>
                <textarea id="event_description" name="event_description" required><?php echo esc_attr($description); ?></textarea>
            </div>
            <div class="full">
                <label for="event_photo">Event Photo:</label>
                <input type="hidden" id="event_photo" name="event_photo" value="<?php echo esc_attr($event_photo); ?>">
                <button type="button" id="event_photo_button" class="button">Select Image</button>
                <div id="event_photo_preview">
                    <?php if ($event_photo) : ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($event_photo)); ?>" height: auto;">
                    <?php endif; ?>
                </div>
            </div>
            <div id="tickets_container">
                <h3>Tickets</h3>
                <div style="margin-bottom: 5px;"><button type="button" id="add_ticket" class="bbutton">Add Ticket</button><br></div>

                <div id="tickets_list">
                    <?php
                    if (!empty($tickets)) {
                        foreach ($tickets as $ticket) {
                    ?>
                            <div class="ticket" style="margin-bottom: 5px;">
                                <input type="text" name="ticket_name[]" placeholder="Ticket Name" value="<?php echo esc_attr($ticket['name']); ?>" required>
                                <input type="number" name="ticket_price[]" placeholder="Price" value="<?php echo esc_attr($ticket['price']); ?>" required>
                                <button type="button" class="remove_ticket bbutton">Remove</button>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="inputs">
                <label for="event_promo_code">Promo Code:</label>
                <input type="text" id="event_promo_code" name="event_promo_code" value="<?php echo esc_attr($promo_code); ?>" style="width:100%;">
            </div>
            <div class="inputs">
                <label for="event_discount">Discount (%):</label>
                <input type="number" id="event_discount" name="event_discount" value="<?php echo esc_attr($discount); ?>" style="width:100%;" min="0" max="100">
            </div>
        </div>



<?php
    }

    public function save_event_meta($post_id)
    {
        if (!isset($_POST['event_meta_nonce']) || !wp_verify_nonce($_POST['event_meta_nonce'], 'save_event_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $title = isset($_POST['event_title']) ? sanitize_text_field($_POST['event_title']) : '';
        $venue = isset($_POST['event_venue']) ? sanitize_text_field($_POST['event_venue']) : '';
        $date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';
        $time = isset($_POST['event_time']) ? sanitize_text_field($_POST['event_time']) : '';
        $description = isset($_POST['event_description']) ? sanitize_text_field($_POST['event_description']) : '';
        $event_photo = isset($_POST['event_photo']) ? intval($_POST['event_photo']) : '';
        $promo_code = isset($_POST['event_promo_code']) ? sanitize_text_field($_POST['event_promo_code']) : '';
        $discount = isset($_POST['event_discount']) ? intval($_POST['event_discount']) : 0;

        update_post_meta($post_id, '_event_title', $title);
        update_post_meta($post_id, '_event_venue', $venue);
        update_post_meta($post_id, '_event_date', $date);
        update_post_meta($post_id, '_event_time', $time);
        update_post_meta($post_id, '_event_description', $description);
        update_post_meta($post_id, '_event_photo', $event_photo);
        update_post_meta($post_id, '_event_promo_code', $promo_code);
        update_post_meta($post_id, '_event_discount', $discount);

        $ticket_names = isset($_POST['ticket_name']) ? array_map('sanitize_text_field', $_POST['ticket_name']) : [];
        $ticket_prices = isset($_POST['ticket_price']) ? array_map('floatval', $_POST['ticket_price']) : [];
        $tickets = [];

        for ($i = 0; $i < count($ticket_names); $i++) {
            if (!empty($ticket_names[$i]) && isset($ticket_prices[$i])) {
                $tickets[] = [
                    'name' => $ticket_names[$i],
                    'price' => $ticket_prices[$i],
                ];
            }
        }

        update_post_meta($post_id, '_event_tickets', json_encode($tickets));
    }

    public function register_rest_fields()
    {
        register_rest_field('events', 'event_meta', array(
            'get_callback' => array($this, 'get_event_meta'),
            'schema' => null,
        ));
    }

    public function get_event_meta($object)
    {
        $post_id = $object['id'];
        return [
            'title' => get_post_meta($post_id, '_event_title', true),
            'venue' => get_post_meta($post_id, '_event_venue', true),
            'date' => get_post_meta($post_id, '_event_date', true),
            'time' => get_post_meta($post_id, '_event_time', true),
            'description' => get_post_meta($post_id, '_event_description', true),
            'photo' => wp_get_attachment_url(get_post_meta($post_id, '_event_photo', true)),
            'promo_code' => get_post_meta($post_id, '_event_promo_code', true),
            'discount' => get_post_meta($post_id, '_event_discount', true),
            'tickets' => json_decode(get_post_meta($post_id, '_event_tickets', true), true),
        ];
    }
}

new EventsManager();
