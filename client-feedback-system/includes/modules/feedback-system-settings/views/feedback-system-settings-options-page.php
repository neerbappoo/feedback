<div class="wrap">
    <h1>Feedback System Settings</h1>

    <h2>Entry Points</h2>
    <?php $this->render_cpt_table('entry_point'); ?>

    <h2>Modes of Contact</h2>
    <?php $this->render_cpt_table('contact_mode'); ?>

    <h2>Feedback Forms</h2>
    <?php $this->render_cpt_table('landing_page'); ?>

    <dialog id="crud-dialog">
        <div class="modal-content" id="modal-content">
            <form id="crud-form" method="dialog">
                <input type="hidden" id="crud-action" name="crud_action" value="">
                <input type="hidden" id="crud-post-type" name="post_type" value="">
                <input type="hidden" id="crud-post-id" name="post_id" value="">
                <?php wp_nonce_field('cfs_feedback_settings', 'cfs_feedback_settings_nonce'); ?>
                <label for="crud-title">Title:</label>
                <input type="text" id="crud-title" name="title" required>

                <div id="entry-point-modes">
                    <label for="associated-modes">Associated Modes of Contact:</label>
                    <select id="associated-modes" name="associated_modes[]" multiple>
                        <?php
                        $all_modes = get_posts(['post_type' => 'contact_mode', 'numberposts' => -1]);
                        foreach ($all_modes as $mode) {
                            echo "<option value='{$mode->ID}'>{$mode->post_title}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div id="contact-mode-forms">
                    <label for="preferred-forms">Preferred Feedback Forms:</label>
                    <select id="preferred-forms" name="preferred_forms[]" multiple>
                        <?php
                        $all_forms = get_posts(['post_type' => 'landing_page', 'numberposts' => -1]);
                        foreach ($all_forms as $form) {
                            echo "<option value='{$form->ID}'>{$form->post_title}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="dialog-buttons">
                    <button type="submit" id="crud-save">Save</button>
                    <button type="button" id="crud-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>
</div>