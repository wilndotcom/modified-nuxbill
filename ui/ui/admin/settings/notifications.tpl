{include file="sections/header.tpl"}

<form class="form-horizontal" method="post" role="form" action="{Text::url('settings/notifications-post')}">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">
                    <div class="btn-group pull-right">
                        <button class="btn btn-primary btn-xs" title="save" type="submit"><span
                                class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span></button>
                    </div>
                    {Lang::T('User Notification')}
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Expired Notification Message')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="expired" name="expired"
                                placeholder="{Lang::T('Hello')} [[name]], {Lang::T('your internet package')} [[package]] {Lang::T('has been expired')}"
                                rows="4">{if $_json['expired']!=''}{Lang::htmlspecialchars($_json['expired'])}{else}{Lang::T('Hello')} [[name]], {Lang::T('your internet package')} [[package]] {Lang::T('has been expired')}.{/if}</textarea>
                        </div>
                        <p class="help-block col-md-4">
                            <b>[[name]]</b> - {Lang::T('will be replaced with Customer Name')}.<br>
                            <b>[[username]]</b> - {Lang::T('will be replaced with Customer username')}.<br>
                            <b>[[package]]</b> - {Lang::T('will be replaced with Package name')}.<br>
                            <b>[[price]]</b> - {Lang::T('will be replaced with Package price')}.<br>
                            <b>[[bills]]</b> - {Lang::T('additional bills for customers')}.<br>
                            <b>[[payment_link]]</b> - <a href="{$app_url}/docs/#Reminder%20with%20payment%20link"
                                target="_blank">{Lang::T("read documentation")}</a>.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Reminder 7 days')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="reminder_7_day" name="reminder_7_day"
                                rows="4">{Lang::htmlspecialchars($_json['reminder_7_day'])}</textarea>
                        </div>
                        <p class="help-block col-md-4">
                            <b>[[name]]</b> - {Lang::T('will be replaced with Customer Name')}.<br>
                            <b>[[username]]</b> - {Lang::T('will be replaced with Customer username')}.<br>
                            <b>[[package]]</b> - {Lang::T('will be replaced with Package name')}.<br>
                            <b>[[price]]</b> - {Lang::T('will be replaced with Package price')}.<br>
                            <b>[[expired_date]]</b> - {Lang::T('will be replaced with Expiration date')}.<br>
                            <b>[[bills]]</b> - {Lang::T('additional bills for customers')}.<br>
                            <b>[[payment_link]]</b> - <a href="{$app_url}/docs/#Reminder%20with%20payment%20link"
                                target="_blank">{Lang::T("read documentation")}</a>.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Reminder 3 days')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="reminder_3_day" name="reminder_3_day"
                                rows="4">{Lang::htmlspecialchars($_json['reminder_3_day'])}</textarea>
                        </div>
                        <p class="help-block col-md-4">
                            <b>[[name]]</b> - {Lang::T('will be replaced with Customer Name')}.<br>
                            <b>[[username]]</b> - {Lang::T('will be replaced with Customer username')}.<br>
                            <b>[[package]]</b> - {Lang::T('will be replaced with Package name')}.<br>
                            <b>[[price]]</b> - {Lang::T('will be replaced with Package price')}.<br>
                            <b>[[expired_date]]</b> - {Lang::T('will be replaced with Expiration date')}.<br>
                            <b>[[bills]]</b> - {Lang::T('additional bills for customers')}.<br>
                            <b>[[payment_link]]</b> - <a href="{$app_url}/docs/#Reminder%20with%20payment%20link"
                                target="_blank">{Lang::T("read documentation")}</a>.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Reminder 1 day')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="reminder_1_day" name="reminder_1_day"
                                rows="4">{Lang::htmlspecialchars($_json['reminder_1_day'])}</textarea>
                        </div>
                        <p class="help-block col-md-4">
                            <b>[[name]]</b> - {Lang::T('will be replaced with Customer Name')}.<br>
                            <b>[[username]]</b> - {Lang::T('will be replaced with Customer username')}.<br>
                            <b>[[package]]</b> - {Lang::T('will be replaced with Package name')}.<br>
                            <b>[[price]]</b> - {Lang::T('will be replaced with Package price')}.<br>
                            <b>[[expired_date]]</b> - {Lang::T('will be replaced with Expiration date')}.<br>
                            <b>[[bills]]</b> - {Lang::T('additional bills for customers')}.<br>
                            <b>[[payment_link]]</b> - <a href="{$app_url}/docs/#Reminder%20with%20payment%20link"
                                target="_blank">{Lang::T("read documentation")}</a>.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Invoice Notification Payment')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="invoice_paid" name="invoice_paid"
                                placeholder="{Lang::T('Hello')} [[name]], {Lang::T('your internet package')} [[package]] {Lang::T('has been expired')}"
                                rows="20">{Lang::htmlspecialchars($_json['invoice_paid'])}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[company_name]]</b> {Lang::T('Your Company Name at Settings')}.<br>
                            <b>[[address]]</b> {Lang::T('Your Company Address at Settings')}.<br>
                            <b>[[phone]]</b> - {Lang::T('Your Company Phone at Settings')}.<br>
                            <b>[[invoice]]</b> - {Lang::T('Invoice number')}.<br>
                            <b>[[date]]</b> - {Lang::T('Date invoice created')}.<br>
                            <b>[[payment_gateway]]</b> - {Lang::T('Payment gateway user paid from')}.<br>
                            <b>[[payment_channel]]</b> - {Lang::T('Payment channel user paid from')}.<br>
                            <b>[[type]]</b> - {Lang::T('is Hotspot or PPPOE')}.<br>
                            <b>[[plan_name]]</b> - {Lang::T('Internet Package')}.<br>
                            <b>[[plan_price]]</b> - {Lang::T('Internet Package Prices')}.<br>
                            <b>[[name]]</b> - {Lang::T('Receiver name')}.<br>
                            <b>[[user_name]]</b> - {Lang::T('Username internet')}.<br>
                            <b>[[user_password]]</b> - {Lang::T('User password')}.<br>
                            <b>[[expired_date]]</b> - {Lang::T('Expired datetime')}.<br>
                            <b>[[footer]]</b> - {Lang::T('Invoice Footer')}.<br>
                            <b>[[note]]</b> - {Lang::T('For Notes by admin')}.<br>
                            <b>[[invoice_link]]</b> - <a href="{$app_url}/docs/#Reminder%20with%20payment%20link"
                            target="_blank">{Lang::T("read documentation")}</a>.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Balance Notification Payment')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="invoice_balance" name="invoice_balance"
                                placeholder="{Lang::T('Hello')} [[name]], {Lang::T('your internet package')} [[package]] {Lang::T('has been expired')}"
                                rows="20">{Lang::htmlspecialchars($_json['invoice_balance'])}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[company_name]]</b> - {Lang::T('Your Company Name at Settings')}.<br>
                            <b>[[address]]</b> - {Lang::T('Your Company Address at Settings')}.<br>
                            <b>[[phone]]</b> - {Lang::T('Your Company Phone at Settings')}.<br>
                            <b>[[invoice]]</b> - {Lang::T('Invoice number')}.<br>
                            <b>[[date]]</b> - {Lang::T('Date invoice created')}.<br>
                            <b>[[payment_gateway]]</b> - {Lang::T('Payment gateway user paid from')}.<br>
                            <b>[[payment_channel]]</b> - {Lang::T('Payment channel user paid from')}.<br>
                            <b>[[type]]</b> - {Lang::T('is Hotspot or PPPOE')}.<br>
                            <b>[[plan_name]]</b> - {Lang::T('Internet Package')}.<br>
                            <b>[[plan_price]]</b> - {Lang::T('Internet Package Prices')}.<br>
                            <b>[[name]]</b> - {Lang::T('Receiver name')}.<br>
                            <b>[[user_name]]</b> - {Lang::T('Username internet')}.<br>
                            <b>[[user_password]]</b> - {Lang::T('User password')}.<br>
                            <b>[[trx_date]]</b> - {Lang::T('Transaction datetime')}.<br>
                            <b>[[balance_before]]</b> - {Lang::T('Balance Before')}.<br>
                            <b>[[balance]]</b> - {Lang::T('Balance After')}.<br>
                            <b>[[footer]]</b> - {Lang::T('Invoice Footer')}.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Welcome Message')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="welcome_message" name="welcome_message"
                                rows="4">{Lang::htmlspecialchars($_json['welcome_message'])}</textarea>
                        </div>
                        <p class="help-block col-md-4">
                            <b>[[name]]</b> - {Lang::T('will be replaced with Customer Name')}.<br>
                            <b>[[username]]</b> - {Lang::T('will be replaced with Customer username')}.<br>
                            <b>[[password]]</b> - {Lang::T('will be replaced with Customer password')}.<br>
                            <b>[[url]]</b> - {Lang::T('will be replaced with Customer Portal URL')}.<br>
                            <b>[[company]]</b> - {Lang::T('will be replaced with Company Name')}.<br>
                        </p>
                    </div>
                </div>
                {if $_c['enable_balance'] == 'yes'}
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Send Balance')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="balance_send" name="balance_send"
                                rows="4">{if $_json['balance_send']}{Lang::htmlspecialchars($_json['balance_send'])}{else}{Lang::htmlspecialchars($_default['balance_send'])}{/if}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[name]]</b> - {Lang::T('Receiver name')}.<br>
                            <b>[[balance]]</b> - {Lang::T('how much balance have been send')}.<br>
                            <b>[[current_balance]]</b> - {Lang::T('Current Balance')}.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Received Balance')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="balance_received" name="balance_received"
                                rows="4">{if $_json['balance_received']}{Lang::htmlspecialchars($_json['balance_received'])}{else}{Lang::htmlspecialchars($_default['balance_received'])}{/if}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[name]]</b> - {Lang::T('Sender name')}.<br>
                            <b>[[balance]]</b> - {Lang::T('how much balance have been received')}.<br>
                            <b>[[current_balance]]</b> - {Lang::T('Current Balance')}.
                        </p>
                    </div>
                </div>
                {/if}
                <div class="panel-heading" style="margin-top: 20px;">
                    <h4><i class="fa fa-money"></i> {Lang::T('Debt Notification')}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Enable Debt Notifications')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="debt_notifications_enabled">
                                <option value="1" {if $debt_settings['debt_notifications_enabled'] == '1'}selected{/if}>{Lang::T('Yes')}</option>
                                <option value="0" {if $debt_settings['debt_notifications_enabled'] != '1'}selected{/if}>{Lang::T('No')}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-md-2"><strong>{Lang::T('Notification Channels')}</strong></div>
                        <div class="col-md-6">
                            <div class="checkbox">
                                <label><input type="checkbox" name="debt_channels[]" value="SMS" {if in_array('SMS', explode(',', $debt_settings['debt_notification_channels']))}checked{/if}> {Lang::T('SMS')}</label><br>
                                <label><input type="checkbox" name="debt_channels[]" value="WhatsApp" {if in_array('WhatsApp', explode(',', $debt_settings['debt_notification_channels']))}checked{/if}> {Lang::T('WhatsApp')}</label><br>
                                <label><input type="checkbox" name="debt_channels[]" value="Email" {if in_array('Email', explode(',', $debt_settings['debt_notification_channels']))}checked{/if}> {Lang::T('Email')}</label><br>
                                <label><input type="checkbox" name="debt_channels[]" value="Inbox" {if in_array('Inbox', explode(',', $debt_settings['debt_notification_channels']))}checked{/if}> {Lang::T('Customer Inbox')}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Grace Period (Days)')}</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="debt_grace_period_days" value="{$debt_settings['debt_grace_period_days']}" min="1" max="90">
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Auto Disconnect')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="debt_auto_disconnect">
                                <option value="1" {if $debt_settings['debt_auto_disconnect'] == '1'}selected{/if}>{Lang::T('Yes')}</option>
                                <option value="0" {if $debt_settings['debt_auto_disconnect'] != '1'}selected{/if}>{Lang::T('No')}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Debt Notification (Initial)')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="debt_message_initial" rows="3">{$debt_settings['debt_message_initial']}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[name]]</b> - {Lang::T('Customer Name')}.<br>
                            <b>[[amount]]</b> - {Lang::T('Debt amount')}.<br>
                            <b>[[days]]</b> - {Lang::T('Days remaining')}.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Debt Warning (3 days before)')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="debt_message_warning" rows="3">{$debt_settings['debt_message_warning']}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[name]]</b> - {Lang::T('Customer Name')}.<br>
                            <b>[[amount]]</b> - {Lang::T('Debt amount')}.<br>
                            <b>[[days]]</b> - {Lang::T('Days remaining')}.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Debt Final Notice (1 day before)')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="debt_message_final" rows="3">{$debt_settings['debt_message_final']}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[name]]</b> - {Lang::T('Customer Name')}.<br>
                            <b>[[amount]]</b> - {Lang::T('Debt amount')}.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Debt Disconnection Notice')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="debt_message_disconnection" rows="3">{$debt_settings['debt_message_disconnection']}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[name]]</b> - {Lang::T('Customer Name')}.<br>
                            <b>[[amount]]</b> - {Lang::T('Debt amount')}.
                        </p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('PDF Invoice Template')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="email_invoice" name="email_invoice" rows="20">{if !empty($_json['email_invoice'])}{Lang::htmlspecialchars($_json['email_invoice'])}{else}<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Invoice No: [[invoice]]</title>
    <style>
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 16px; line-height: 24px; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
        @media only screen and (max-width: 600px) { .invoice-box table tr.top table td { width: 100%; display: block; text-align: center; } .invoice-box table tr.information table td { width: 100%; display: block; text-align: center; } }
        .invoice-box.rtl { direction: rtl; font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; }
        .invoice-box.rtl table { text-align: right; }
        .invoice-box.rtl table tr td:nth-child(2) { text-align: left; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="[[logo]]" style="max-width: 100px" />
                            </td>
                            <td>
                                Invoice #: [[invoice]]<br />
                                Created: [[created_at]]<br />
                                Due: [[due_date]]
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                [[company_name]]<br />
                                [[company_address]]<br />
                                [[company_phone]]<br />
                            </td>
                            <td>
                                [[fullname]]<br />
                                [[address]] <br />
                                [[email]] <br />
                                [[phone]] <br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            [[bill_rows]]
        </table>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h4 style="margin: 0;">Payment Options:</h4>
            <p style="margin: 0;">Online Portal: <a href="">https://yoursite-domain.com/[[payment_link]]</a> <br> Bank Transfer: Account # 1234-567890<br> Auto Pay: Enabled (Next payment: 2023-11-12)</p>
        </div>
        <footer style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 0.9em; color: #666; text-align: center;">
            <p style="margin: 0;">Thank you for choosing [[company_name]]!<br> Late payments may result in service interruption<br> Need help? Contact support@[[company_name]].com or call [[company_phone]]</p>
        </footer>
    </div>
</body>
</html>{/if}</textarea>
                        </div>
                        <p class="col-md-4 help-block">
                            <b>[[company_name]]</b> {Lang::T('Your Company Name at Settings')}.<br>
                            <b>[[company_address]]</b> {Lang::T('Your Company Address at Settings')}.<br>
                            <b>[[company_phone]]</b> - {Lang::T('Your Company Phone at Settings')}.<br>
                            <b>[[invoice]]</b> - {Lang::T('Invoice number')}.<br>
                            <b>[[created_at]]</b> - {Lang::T('Date invoice created')}.<br>
                            <b>[[payment_gateway]]</b> - {Lang::T('Payment gateway user paid from')}.<br>
                            <b>[[payment_channel]]</b> - {Lang::T('Payment channel user paid from')}.<br>
                            <b>[[bill_rows]]</b> - {Lang::T('Bills table, where bills are listed')}.<br>
                            <b>[[currency]]</b> - {Lang::T('Your currency code at localisation Settings')}.<br>
                            <b>[[status]]</b> - {Lang::T('Invoice status')}.<br>
                            <b>[[fullname]]</b> - {Lang::T('Receiver name')}.<br>
                            <b>[[user_name]]</b> - {Lang::T('Username internet')}.<br>
                            <b>[[email]]</b> - {Lang::T('Customer email')} .<br>
                            <b>[[phone]]</b> - {Lang::T('Customer phone')}. <br>
                            <b>[[address]]</b> - {Lang::T('Customer phone')}. <br>
                            <b>[[expired_date]]</b> - {Lang::T('Expired datetime')}.<br>
                            <b>[[logo]]</b> - {Lang::T('Your company logo at Settings')}.<br>
                            <b>[[due_date]]</b> - {Lang::T('Invoice Due date, 7 Days after invoice created')}.<br>
                            <b>[[payment_link]]</b> - <a href="{$app_url}/docs/#Reminder%20with%20payment%20link" target="_blank">{Lang::T("read documentation")}</a>.
                            <br><br>
                            <button type="button" class="btn btn-info btn-sm" onclick="previewInvoiceTemplate()">
                                <i class="fa fa-eye"></i> {Lang::T('Preview Template')}
                            </button>
                        </p>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <button class="btn btn-success btn-block" type="submit">{Lang::T('Save Changes')}</button>
                    </div>
                </div>
            </div>
        </div>
</form>

<script>
function previewInvoiceTemplate() {
    // Get the template content from textarea
    var templateContent = document.getElementById('email_invoice').value;
    
    // Get today's date and due date
    var today = new Date();
    var dueDate = new Date();
    dueDate.setDate(today.getDate() + 7);
    
    var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    var dueDateStr = dueDate.getFullYear() + '-' + String(dueDate.getMonth() + 1).padStart(2, '0') + '-' + String(dueDate.getDate()).padStart(2, '0');
    
    // Replace placeholders with sample data for preview
    var previewContent = templateContent
        .replace(/\[\[company_name\]\]/g, '{addslashes($_c['CompanyName'])}')
        .replace(/\[\[company_address\]\]/g, '{addslashes($_c['address'])}')
        .replace(/\[\[company_phone\]\]/g, '{addslashes($_c['phone'])}')
        .replace(/\[\[invoice\]\]/g, 'INV-00123')
        .replace(/\[\[created_at\]\]/g, todayStr)
        .replace(/\[\[due_date\]\]/g, dueDateStr)
        .replace(/\[\[fullname\]\]/g, 'John Doe')
        .replace(/\[\[user_name\]\]/g, 'johndoe')
        .replace(/\[\[email\]\]/g, 'john@example.com')
        .replace(/\[\[phone\]\]/g, '+1234567890')
        .replace(/\[\[address\]\]/g, '123 Main St, City')
        .replace(/\[\[logo\]\]/g, '{$app_url}/{$UPLOAD_PATH}{$_c['logo']}')
        .replace(/\[\[payment_link\]\]/g, 'payment/123')
        .replace(/\[\[bill_rows\]\]/g, '<tr class="heading"><td>Item</td><td>Price</td></tr><tr class="item"><td>Internet Plan (Monthly)</td><td>$50.00</td></tr><tr class="item"><td>Installation Fee</td><td>$25.00</td></tr><tr class="total"><td></td><td>Total: $75.00</td></tr>')
        .replace(/\[\[payment_gateway\]\]/g, 'PayPal')
        .replace(/\[\[payment_channel\]\]/g, 'Credit Card')
        .replace(/\[\[status\]\]/g, 'PAID')
        .replace(/\[\[currency\]\]/g, '{$_c['currency_code']}')
        .replace(/\[\[type\]\]/g, 'Hotspot')
        .replace(/\[\[plan_name\]\]/g, 'Premium Plan')
        .replace(/\[\[plan_price\]\]/g, '$50.00')
        .replace(/\[\[footer\]\]/g, 'Thank you for your business!')
        .replace(/\[\[note\]\]/g, 'Please pay on time to avoid service interruption.');
    
    // Open preview in new window
    var previewWindow = window.open('', '_blank', 'width=900,height=700,scrollbars=yes');
    if (previewWindow) {
        previewWindow.document.open();
        previewWindow.document.write(previewContent);
        previewWindow.document.close();
    } else {
        alert('Popup blocked! Please allow popups for this website to see the preview.');
    }
}
</script>
{include file="sections/footer.tpl"}