<div class="options_group qlwapp-premium-field">
  <p class="form-field">
      <label><?php esc_html_e('Message', 'wp-whatsapp-chat'); ?></label>
      <textarea style="width:100%" name="message">{{ _.escapeHtml(data.message)}}</textarea>
      <# if(data.chat == false) { #>
        <span class="description"><small><?php esc_html_e('First message sent to the user in the chat box.', 'wp-whatsapp-chat'); ?></small></span>
      <# } else { #>
        <span class="description"><small><?php esc_html_e('Default message sent to the contact number.', 'wp-whatsapp-chat'); ?></small></span>
      <# } #>
  </p>
</div>
<div class="options_group qlwapp-premium-field">
  <p class="form-field">
    <label><?php esc_html_e('Chat', 'wp-whatsapp-chat'); ?></label>
    <input type="radio" class="media-modal-change media-modal-subview" name="chat" value="1" <# if(data.chat) {#> checked <#}#> />
    <label><?php esc_html_e('Enabled', 'wp-whatsapp-chat'); ?></label>
    <input type="radio" class="media-modal-change  media-modal-subview" name="chat" value="0" <# if(data.chat==false) {#> checked <#}#> />
    <label><?php esc_html_e('Disabled', 'wp-whatsapp-chat'); ?></label>
    <span style="float: right;" class="description hidden"><small><?php esc_html_e('This is a premium feature', 'wp-whatsapp-chat'); ?></small></span>
  </p>
</div>