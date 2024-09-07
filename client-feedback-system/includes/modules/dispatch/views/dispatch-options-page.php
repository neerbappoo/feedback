<?php
$companies = get_posts(array('post_type' => 'company', 'posts_per_page' => -1));
$contact_modes = get_posts(array('post_type' => 'contact_mode', 'posts_per_page' => -1));
$client_posts = get_posts(array('post_type' => 'client', 'posts_per_page' => -1));

foreach ($client_posts as $post) {
    echo "<h2>Post ID: {$post->ID} - Post Title: {$post->post_title}</h2>";
    var_dump(get_post_meta($post->ID));
    echo "<hr>";
}
?>
<div class="wrap">
    <h1>Client Feedback Display</h1>
    <form id="client-feedback-filter-form">
        <label for="company_id">Filter by Company:</label>
        <select id="company_id" name="company_id">
            <option value="">All Companies</option>
            <?php foreach ($companies as $company): ?>
            <option value="<?php echo $company->ID; ?>"><?php echo $company->post_title; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="contact_mode_id">Filter by Contact Mode:</label>
        <select id="contact_mode_id" name="contact_mode_id">
            <option value="">All Contact Modes</option>
            <?php foreach ($contact_modes as $mode): ?>
            <option value="<?php echo $mode->ID; ?>"><?php echo $mode->post_title; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="filter-clients">Filter</button>
    </form>

    <form id="client-feedback-send-form">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-clients"></th>
                    <th>Client ID</th>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Company</th>
                    <th>Contact Mode</th>
                    <th>Feedback Model</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="client-feedback-table-body">
                <!-- Table rows will be populated via AJAX -->
            </tbody>
        </table>
        <button type="button" id="send-feedback">Send Feedback</button>
    </form>
</div>