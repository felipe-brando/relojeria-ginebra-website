<div class="wrap about-wrap full-width-layout qlwrap">
  <form id="qlwapp_button_form" method="post" action="options.php">
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row"><?php esc_html_e('Layout', 'wp-whatsapp-chat'); ?></th>
          <td>
            <select name="layout" class="qlwapp-select2">
              <option value="button" <?php selected($button['layout'], 'button'); ?>><?php esc_html_e('Button', 'wp-whatsapp-chat'); ?></option>
              <option value="bubble" <?php selected($button['layout'], 'bubble'); ?>><?php esc_html_e('Bubble', 'wp-whatsapp-chat'); ?></option>
            </select>
            <p class="description hidden"><?php esc_html_e('Switch to change the button layout.', 'wp-whatsapp-chat'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Box', 'wp-whatsapp-chat'); ?></th>
          <td>
            <select name="box" class="qlwapp-select2">
              <option value="yes" <?php selected($button['box'], 'yes'); ?>><?php esc_html_e('Enable contact box', 'wp-whatsapp-chat'); ?></option>
              <option value="no" <?php selected($button['box'], 'no'); ?>><?php esc_html_e('Disable contact box', 'wp-whatsapp-chat'); ?></option>
            </select>
          </td>
        </tr>
        <tr class="qlwapp-phone-alert <?php echo esc_attr($button['box'] == 'yes' ? '' : 'hidden'); ?>">
          <th scope="row"></th>
          <td>
            <span style="display:block!important;" class="notice notice-error">
              <p>
                <?php printf(__('Contact box is enabled. Please set the contact phone number in the <a href="%s">contacts tab</a>', 'wp-whatsapp-chat'), admin_url('admin.php?page=' . QLWAPP_DOMAIN . '_contacts')); ?>.
              </p>
            </span>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Rounded', 'wp-whatsapp-chat'); ?></th>
          <td>
            <select name="rounded" class="qlwapp-select2">
              <option value="yes" <?php selected($button['rounded'], 'yes'); ?>><?php esc_html_e('Add rounded border', 'wp-whatsapp-chat'); ?></option>
              <option value="no" <?php selected($button['rounded'], 'no'); ?>><?php esc_html_e('Remove rounded border', 'wp-whatsapp-chat'); ?></option>
            </select>
            <p class="description hidden"><?php esc_html_e('Add rounded border to the button.', 'wp-whatsapp-chat'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Position', 'wp-whatsapp-chat'); ?></th>
          <td>
            <select name="position" class="qlwapp-select2">
              <option value="middle-left" <?php selected($button['position'], 'middle-left'); ?>><?php esc_html_e('Middle Left', 'wp-whatsapp-chat'); ?></option>
              <option value="middle-right" <?php selected($button['position'], 'middle-right'); ?>><?php esc_html_e('Middle Right', 'wp-whatsapp-chat'); ?></option>
              <option value="bottom-left" <?php selected($button['position'], 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'wp-whatsapp-chat'); ?></option>
              <option value="bottom-right" <?php selected($button['position'], 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'wp-whatsapp-chat'); ?></option>
            </select>
            <p class="description hidden"><?php esc_html_e('Switch to change the button position.', 'wp-whatsapp-chat'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Icon', 'wp-whatsapp-chat'); ?></th>
          <td>
            <div class="submit qlwapp-premium-field">
              <?php submit_button(esc_html__('Add Icon', 'wp-whatsapp-chat'), 'secondary', null, false, array('id' => 'qlwapp_icon_add')); ?>
              <p class="description hidden"><small><?php esc_html_e('This is a premium feature', 'wp-whatsapp-chat'); ?></small></p>
            </div>
            <input type="text" name="icon" placeholder="<?php echo esc_html($button['icon']); ?>" value="<?php echo esc_attr($button['icon']); ?>" class="qlwapp-input" />
          </td>
        </tr>
        <!--<tr>
          <th scope="row"><?php esc_html_e('Discreet link', 'wp-whatsapp-chat'); ?></th>
          <td>
            <select name="developer" class="qlwapp-select2">
              <option value="yes" <?php selected($button['developer'], 'yes'); ?>><?php esc_html_e('Show developer link', 'wp-whatsapp-chat'); ?></option>
              <option value="no" <?php selected($button['developer'], 'no'); ?>><?php esc_html_e('Hide developer link', 'wp-whatsapp-chat'); ?></option>
            </select>
            <p class="description hidden"><?php esc_html_e('Leave a discrete link to developer to help and keep new updates and support.', 'wp-whatsapp-chat'); ?></p>        
          </td>        
        </tr>-->
        <tr>
          <th scope="row"><?php esc_html_e('Text', 'wp-whatsapp-chat'); ?></th>
          <td>
            <input type="text" name="text" placeholder="<?php echo esc_html($button['text']); ?>" value="<?php echo esc_attr($button['text']); ?>" class="qlwapp-input" />
            <p class="description"><?php esc_html_e('Customize your button text.', 'wp-whatsapp-chat'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Phone', 'wp-whatsapp-chat'); ?></th>
          <td>
            <input type="text" name="phone" placeholder="" value="<?php echo esc_attr($button['phone']); ?>" class="qlwapp-input <?php echo esc_attr($button['box'] == 'yes' ? 'disabled' : ''); ?>" required="required" />
            <p class="description"><?php esc_html_e('Full phone number in international format. Only nnumbers.', 'wp-whatsapp-chat'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Message', 'wp-whatsapp-chat'); ?></th>
          <td>
            <textarea maxlength="500" style="width:75%;height:50px;padding:8px;" name="message" placeholder="<?php echo esc_html($button['message']); ?>"><?php echo esc_html(trim($button['message'])); ?></textarea>
            <p class="description"><?php esc_html_e('Message that will automatically appear in the text field of a chat.', 'wp-whatsapp-chat'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Schedule', 'wp-whatsapp-chat'); ?></th>
          <td class="qlwapp-premium-field">
            <b><?php esc_html_e('From', 'wp-whatsapp-chat'); ?></b>
            <input type="time" name="timefrom" placeholder="<?php echo esc_html($button['timefrom']); ?>" value="<?php echo esc_html($button['timefrom']); ?>" />
            <b><?php esc_html_e('To', 'wp-whatsapp-chat'); ?></b>
            <input type="time" name="timeto" placeholder="<?php echo esc_html($button['timeto']); ?>" value="<?php echo esc_html($button['timeto']); ?>" />
            <p class="description hidden"><small><?php esc_html_e('This is a premium feature', 'wp-whatsapp-chat'); ?></small></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Timezone', 'wp-whatsapp-chat'); ?></th>
          <td class="qlwapp-premium-field">
            <select name="timezone" aria-describedby="timezone-description" required="" class="qlwapp-select2">
              <?php echo wp_timezone_choice($button['timezone'], get_user_locale()); ?>
            </select>
            <p class="description"><small><?php esc_html_e('Hide button if the user is out of the available hours.', 'wp-whatsapp-chat'); ?></small></p>
            <p class="description hidden"><small><?php esc_html_e('This is a premium feature', 'wp-whatsapp-chat'); ?></small></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Available days', 'wp-whatsapp-chat'); ?></th>
          <td class="qlwapp-premium-field">
            <select name="timedays[]" multiple style="height:100px;">
              <option value="0" <?php echo in_array('0', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Sunday', 'wp-whatsapp-chat'); ?></option>
              <option value="1" <?php echo in_array('1', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Monday', 'wp-whatsapp-chat'); ?></option>
              <option value="2" <?php echo in_array('2', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Tuesday', 'wp-whatsapp-chat'); ?></option>
              <option value="3" <?php echo in_array('3', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Wednesday', 'wp-whatsapp-chat'); ?></option>
              <option value="4" <?php echo in_array('4', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Thursday', 'wp-whatsapp-chat'); ?></option>
              <option value="5" <?php echo in_array('5', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Friday', 'wp-whatsapp-chat'); ?></option>
              <option value="6" <?php echo in_array('6', $button['timedays']) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Saturday', 'wp-whatsapp-chat'); ?></option>
            </select>
            <p class="description hidden"><small><?php esc_html_e('This is a premium feature', 'wp-whatsapp-chat'); ?></small></p>
          </td>
          </th>
        </tr>
        <!--      <tr>
          <th scope="row"><?php // esc_html_e('Timeout', 'wp-whatsapp-chat');                             
                          ?></th>
          <td  class="qlwapp-premium-field">
            <select name="timeout" class="qlwapp-select2">
              <option value="yes" <?php // selected($button['timeout'], 'yes');                             
                                  ?>><?php esc_html_e('Show the button as read only', 'wp-whatsapp-chat'); ?></option>
              <option value="no" <?php // selected($button['timeout'], 'no');                             
                                  ?>><?php esc_html_e('Do not show the button', 'wp-whatsapp-chat'); ?></option>
            </select> 
            <p class="description hidden"><small><?php //esc_html_e('This is a premium feature', 'wp-whatsapp-chat');                             
                                                  ?></small></p>    
          </td>        
        </tr> -->
      </tbody>
    </table>
    <?php wp_nonce_field('qlwapp_save_button', 'qlwapp_button_form_nonce'); ?>
    <p class="submit">
      <?php submit_button(esc_html__('Save', 'wp-whatsapp-chat'), 'primary', 'submit', false); ?>
      <span class="settings-save-status">
        <span class="saved"><?php esc_html_e('Saved successfully!'); ?></span>
        <span class="spinner" style="float: none"></span>
      </span>
    </p>
  </form>
</div>