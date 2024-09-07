<div class="wrap">
    <h1>Manage Companies</h1>
    <form method="get" action="">
        <input type="hidden" name="page" value="cfs-company-options">
        <label for="filter-authorized" class="screen-reader-text">Filter by Authorization Status</label>
        <select name="filter_authorized" id="filter-authorized">
            <option value="">All Authorization Status</option>
            <option value="yes" <?php selected($_GET['filter_authorized'], 'yes'); ?>>Authorized</option>
            <option value="no" <?php selected($_GET['filter_authorized'], 'no'); ?>>Not Authorized</option>
        </select>
    </form>
    <form id="bulk-action-form" method="post" action="">
        <label for="bulk-actions" class="screen-reader-text">Select Bulk Action</label>
        <select name="bulk_action" id="bulk-actions">
            <option value="">Bulk Actions</option>
            <option value="trash">Trash</option>
        </select>
        <input type="submit" id="doaction" class="button action" value="Apply">
        <?php wp_nonce_field('cfs_bulk_action', 'cfs_bulk_action_nonce'); ?>
    </form>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="manage-column column-cb check-column"><input type="checkbox" id="select-all-companies"></th>
                <th scope="col" class="manage-column column-id">ID</th>
                <th scope="col" class="manage-column column-name">Name</th>
                <th scope="col" class="manage-column column-authorized">Authorized</th>
                <th scope="col" class="manage-column column-employees">Employees</th>
                <th scope="col" class="manage-column column-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $args = array('post_type' => 'company', 'posts_per_page' => -1);

            if (!empty($_GET['filter_authorized'])) {
                $args['meta_query'] = array(
                    array(
                        'key' => 'authorized',
                        'value' => sanitize_text_field($_GET['filter_authorized']),
                        'compare' => '=',
                    ),
                );
            }

            $companies = get_posts($args);
            foreach ($companies as $company) {
                $authorized = get_post_meta($company->ID, 'authorized', true);
                $employees = $this->get_company_employees($company->ID);
            ?>
                <tr>
                    <td><input type="checkbox" name="company_ids[]" value="<?php echo esc_attr($company->ID); ?>"></td>
                    <td><?php echo esc_html($company->ID); ?></td>
                    <td><?php echo esc_html($company->post_title); ?></td>
                    <td><?php echo esc_html(ucfirst($authorized)); ?></td>
                    <td>
                        <?php
                        if (!empty($employees)) {
                            echo '<ul>';
                            foreach ($employees as $employee) {
                                echo '<li>' . esc_html($employee->post_title) . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo 'No employees';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="#" class="edit-company" data-company-id="<?php echo esc_attr($company->ID); ?>">Edit</a> |
                        <a href="#" class="delete-company" data-company-id="<?php echo esc_attr($company->ID); ?>"
                            data-nonce="<?php echo esc_attr(wp_create_nonce('cfs_delete_company_' . $company->ID)); ?>"
                            data-confirm-message="Are you sure you want to delete this company and all its employees?">Delete</a>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    <dialog id="edit-company-form">
        <div class="modal-content" id="modal-content">
            <h2>Edit Company</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                <input type="hidden" name="action" value="cfs_update_company">
                <input type="hidden" name="company_id" id="edit-company-id">
                <?php wp_nonce_field('cfs_update_company', 'cfs_update_company_nonce'); ?>
                <p>
                    <label for="edit-company-name">Company Name:</label>
                    <input type="text" id="edit-company-name" name="name" required>
                </p>
                <p>
                    <label for="edit-company-authorized">Authorized:</label>
                    <select id="edit-company-authorized" name="authorized">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </p>
                <input type="submit" value="Update Company" class="button button-primary">
            </form>
            <p class="error"></p>
            <p class="success"></p>
            <p class="debug"></p>
            <button id="close-edit-company-form" class="close-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="currentColor"
                    class="icon icon-tabler icons-tabler-filled icon-tabler-square-rounded-x">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M12 2l.324 .001l.318 .004l.616 .017l.299 .013l.579 .034l.553 .046c4.785 .464 6.732 2.411 7.196 7.196l.046 .553l.034 .579c.005 .098 .01 .198 .013 .299l.017 .616l.005 .642l-.005 .642l-.017 .616l-.013 .299l-.034 .579l-.046 .553c-.464 4.785 -2.411 6.732 -7.196 7.196l-.553 .046l-.579 .034c-.098 .005 -.198 .01 -.299 .013l-.616 .017l-.642 .005l-.642 -.005l-.616 -.017l-.299 -.013l-.579 -.034l-.553 -.046c-4.785 -.464 -6.732 -2.411 -7.196 -7.196l-.046 -.553l-.034 -.579a28.058 28.058 0 0 1 -.013 -.299l-.017 -.616c-.003 -.21 -.005 -.424 -.005 -.642l.001 -.324l.004 -.318l.017 -.616l.013 -.299l.034 -.579l.046 -.553c.464 -4.785 2.411 -6.732 7.196 -7.196l.553 -.046l.579 -.034c.098 -.005 .198 -.01 .299 -.013l.616 -.017c.21 -.003 .424 -.005 .642 -.005zm-1.489 7.14a1 1 0 0 0 -1.218 1.567l1.292 1.293l-1.292 1.293l-.083 .094a1 1 0 0 0 1.497 1.32l1.293 -1.292l1.293 1.292l.094 .083a1 1 0 0 0 1.32 -1.497l-1.292 -1.293l1.292 -1.293l.083 -.094a1 1 0 0 0 -1.497 -1.32l-1.293 1.292l-1.293 -1.292l-.094 -.083z"
                        fill="currentColor" stroke-width="0" />
                </svg></button>
        </div>
    </dialog>
    <h2>Create a new company:</h2>
    <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
        <input type="hidden" name="action" value="cfs_create_company">
        <label for="company_name">Company Name:</label>
        <input type="text" id="company_name" placeholder="Company Name" name="company_name" required><br><br>
        <label for="authorized">Authorized:</label>
        <select id="authorized" name="authorized">
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>
        <?php wp_nonce_field('cfs_create_company', 'cfs_create_company_nonce'); ?>
        <input type="submit" value="Create Company" class="button button-primary">
    </form>
</div>