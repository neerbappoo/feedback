<?php
$create_nonce = wp_create_nonce('cfs_crud_client');
$edit_nonce = wp_create_nonce('cfs_edit_client');

// Fetch Entry Points and Modes of Contact
$entry_points = get_posts(array('post_type' => 'entry_point', 'numberposts' => -1));
$contact_modes = get_posts(array('post_type' => 'contact_mode', 'numberposts' => -1));
?>
<div class="wrap">
    <h1>Client Management</h1>
    <form id="client-form">
        <input type="hidden" name="nonce" value="<?php echo $create_nonce; ?>">
        <input type="hidden" name="action" value="cfs_create_client">
        <input type="hidden" name="client_id" value="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        <div class="form-group">
            <select id="company" name="company">
                <option value="">Select Company</option>
                <?php
                $companies = get_posts(array('post_type' => 'company', 'posts_per_page' => -1));
                foreach ($companies as $company) {
                    $is_authorized = get_post_meta($company->ID, 'authorized', true);
                    if ($is_authorized === 'yes') {
                        echo '<option value="' . esc_attr($company->post_title) . '">' . esc_html($company->post_title) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="feedback">Agreed to Give Feedback</label>
            <select id="feedback" name="feedback">
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>

        <div class="form-group feedback-options">
            <label>Entry Points</label>
            <?php foreach ($entry_points as $entry_point): ?>
                <div>
                    <input type="checkbox" name="entry_points[]" value="<?php echo $entry_point->ID; ?>"
                        id="<?php echo $entry_point->post_name; ?>">
                    <label for="<?php echo $entry_point->post_name; ?>"><?php echo $entry_point->post_title; ?></label>
                    <select name="contact_mode[<?php echo $entry_point->ID; ?>]"
                        data-point="<?php echo $entry_point->ID; ?>" disabled>
                        <?php
                        $associated_modes = get_post_meta($entry_point->ID, '_associated_modes', true);
                        foreach ($associated_modes as $mode_id):
                            $mode = get_post($mode_id);
                            echo '<option value="' . esc_attr($mode->ID) . '">' . esc_html($mode->post_title) . '</option>';
                        endforeach;
                        ?>
                    </select>
                    <select name="feedback_model[<?php echo $entry_point->ID; ?>]"
                        data-point="<?php echo $entry_point->ID; ?>" disabled>
                        <!-- Options will be populated via JavaScript -->
                    </select>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Create Client">
        </p>
    </form>
    <?php
    $args = array(
        'post_type' => 'client',
        'posts_per_page' => -1,
    );
    $clients = get_posts($args);

    // Display clients
    foreach ($clients as $client):

        $client_id = $client->ID;
        $client_name = $client->post_title;
        $client_email = get_post_meta($client_id, 'client_email', true);
        $client_phone = get_post_meta($client_id, 'client_phone', true);
        $client_company = get_post_meta($client_id, 'client_company', true);
        $client_feedback = get_post_meta($client_id, 'client_feedback', true);
        $entry_points = get_post_meta($client_id, 'client_entry_points', true);
        $feedback_data = get_post_meta($client_id, 'client_feedback_data', true);

        if (!is_array($entry_points)) {
            $entry_points = array();
        }

        $feedback_data = json_decode($feedback_data, true);
        if (!is_array($feedback_data)) {
            $feedback_data = array();
        }
    ?>
        <div class="client-accordion">
            <button class="accordion"><?php echo esc_html($client_name); ?></button>
            <div class="panel">
                <p>Email: <?php echo esc_html($client_email); ?></p>
                <p>Phone: <?php echo esc_html($client_phone); ?></p>
                <p>Company: <?php echo esc_html($client_company); ?></p>
                <p>Agreed to Give Feedback: <?php echo esc_html($client_feedback); ?></p>
                <p>Entry Points:</p>
                <ul>
                    <?php foreach ($entry_points as $entry_point): ?>
                        <li>
                            <?php echo esc_html(get_the_title($entry_point)); ?>
                            <?php if (isset($feedback_data[$entry_point])): ?>
                                <br>Contact Mode:
                                <?php echo esc_html(get_the_title($feedback_data[$entry_point]['contact_mode'])); ?>
                                <br>Feedback Model:
                                <?php echo esc_html(get_the_title($feedback_data[$entry_point]['feedback_model'])); ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="actions">
                    <a href="#" class="edit-client" data-client-id="<?php echo esc_attr($client->ID); ?>">Edit</a> |
                    <a href="#" class="delete-client" data-client-id="<?php echo esc_attr($client->ID); ?>"
                        data-nonce="<?php echo esc_attr(wp_create_nonce('cfs_delete_client_' . $client->ID)); ?>"
                        data-confirm-message="Are you sure you want to delete this client?">Delete</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <dialog id="edit-client-dialog">
        <div class="modal-content" id="modal-content">
            <form id="edit-client-form">
                <input type="hidden" name="nonce" value="<?php echo $edit_nonce; ?>">
                <input type="hidden" name="action" value="cfs_update_client">
                <input type="hidden" name="client_id" value="">
                <table class="form-table">
                    <tr>
                        <th><label for="edit-name">Name</label></th>
                        <td><input type="text" id="edit-name" name="name" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-email">Email</label></th>
                        <td><input type="email" id="edit-email" name="email" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-phone">Phone</label></th>
                        <td><input type="tel" id="edit-phone" name="phone" required></td>
                    </tr>
                    <tr>
                        <th><label for="edit-company">Company</label></th>
                        <td>
                            <select id="edit-company" name="company_name" required>
                                <option value="">Select Company</option>
                                <?php
                                foreach ($companies as $company) {
                                    $is_authorized = get_post_meta($company->ID, 'authorized', true);
                                    if ($is_authorized === 'yes') {
                                        echo '<option value="' . esc_attr($company->post_title) . '">' . esc_html($company->post_title) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-feedback">Agreed to Give Feedback</label></th>
                        <td>
                            <select id="edit-feedback" name="feedback">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="edit-feedback-options">
                        <th><label>Entry Points</label></th>
                        <td>
                            <?php foreach ($entry_points as $entry_point): ?>
                                <div>
                                    <input type="checkbox" name="entry_points[]" value="<?php echo $entry_point->ID; ?>"
                                        id="edit-<?php echo $entry_point->post_name; ?>">
                                    <label
                                        for="edit-<?php echo $entry_point->post_name; ?>"><?php echo $entry_point->post_title; ?></label>
                                    <select name="contact_mode[<?php echo $entry_point->ID; ?>]"
                                        data-point="<?php echo $entry_point->ID; ?>">
                                        <?php
                                        $associated_modes = get_post_meta($entry_point->ID, '_associated_modes', true);
                                        foreach ($associated_modes as $mode_id):
                                            $mode = get_post($mode_id);
                                            echo '<option value="' . esc_attr($mode->ID) . '">' . esc_html($mode->post_title) . '</option>';
                                        endforeach;
                                        ?>
                                    </select>
                                    <select name="feedback_model[<?php echo $entry_point->ID; ?>]"
                                        data-point="<?php echo $entry_point->ID; ?>" disabled>
                                        <!-- Options will be populated via JavaScript -->
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                <div class="submit-wrapper">
                    <input type="submit" name="submit" id="edit-submit" class="button button-primary"
                        value="Update Client">
                    <button type="button" id="edit-cancel" class="button">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>
</div>