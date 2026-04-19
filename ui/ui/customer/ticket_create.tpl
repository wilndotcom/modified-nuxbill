{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('Create New Support Ticket')}
                <a href="{Text::url('customer_ticket/list')}" class="btn btn-default btn-xs pull-right">
                    <i class="fa fa-arrow-left"></i> {Lang::T('Back to Tickets')}
                </a>
            </div>
            <div class="panel-body">
                <form method="post" action="{Text::url('customer_ticket/create-post')}" class="form-horizontal">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Subject')} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" name="subject" class="form-control" placeholder="{Lang::T('Brief description of your issue')}" required maxlength="255">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Category')}</label>
                        <div class="col-md-9">
                            <select name="category" class="form-control">
                                {foreach $categories as $cat}
                                    <option value="{$cat->name}">{$cat->name}</option>
                                {/foreach}
                            </select>
                            <span class="help-block">{Lang::T('Select the category that best describes your issue')}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Priority')}</label>
                        <div class="col-md-9">
                            <select name="priority" class="form-control">
                                <option value="low">{Lang::T('Low')} - {Lang::T('General inquiry')}</option>
                                <option value="medium" selected>{Lang::T('Medium')} - {Lang::T('Minor issue')}</option>
                                <option value="high">{Lang::T('High')} - {Lang::T('Service affected')}</option>
                                <option value="urgent">{Lang::T('Urgent')} - {Lang::T('Critical issue')}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Message')} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <textarea name="message" class="form-control" rows="8" placeholder="{Lang::T('Please describe your issue in detail. Include any relevant information such as error messages, steps to reproduce, etc.')}" required></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-paper-plane"></i> {Lang::T('Submit Ticket')}
                            </button>
                            <a href="{Text::url('customer_ticket/list')}" class="btn btn-default">
                                {Lang::T('Cancel')}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
