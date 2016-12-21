<?php
/**
 * @see \KS_Giveaways_Admin::add_meta_boxes()
 */
/** @var array $valid_services */

/** @var KS_Giveaways_Admin $admin */
$admin = KS_Giveaways_Admin::get_instance();
?>
<table class="form-table">
    <?php if ($valid_services['ks_giveaways_aweber_valid']): ?>
    <tr valign="top">
        <th scope="row">
            <label>Aweber</label>
        </th>
        <td>
            <?php $admin->input_aweber_list_id(true, get_post_meta(get_post()->ID, "_".KS_GIVEAWAYS_OPTION_AWEBER_LIST_ID, true)); ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php if ($valid_services['ks_giveaways_mailchimp_valid']): ?>
        <tr valign="top">
            <th scope="row">
                <label>MailChimp</label>
            </th>
            <td>
                <?php $admin->input_mailchimp_list_id(true, get_post_meta(get_post()->ID, "_".KS_GIVEAWAYS_OPTION_MAILCHIMP_LIST_ID, true)); ?>
            </td>
        </tr>
    <?php endif; ?>
    <?php if ($valid_services['ks_giveaways_getresponse_valid']): ?>
        <tr valign="top">
            <th scope="row">
                <label>GetResponse</label>
            </th>
            <td>
                <?php $admin->input_getresponse_campaign_id(true, get_post_meta(get_post()->ID, "_".KS_GIVEAWAYS_OPTION_GETRESPONSE_CAMPAIGN_ID, true)); ?>
            </td>
        </tr>
    <?php endif; ?>
    <?php if ($valid_services['ks_giveaways_campaignmonitor_valid']): ?>
        <tr valign="top">
            <th scope="row">
                <label>CampaignMonitor</label>
            </th>
            <td>
                <?php $admin->input_campaignmonitor_list_id(true, get_post_meta(get_post()->ID, "_".KS_GIVEAWAYS_OPTION_CAMPAIGNMONITOR_LIST_ID, true)); ?>
            </td>
        </tr>
    <?php endif; ?>
</table>