{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">{Lang::T('Create Support Ticket')}</h3>
            </div>
            <div class="panel-body">
                <form method="post" action="{Text::url('ticket/create')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <div class="form-group">
                        <label for="customer_id">{Lang::T('Customer')} <span class="text-danger">*</span></label>
                        <select id="customer_id" name="customer_id" class="form-control select2" required>
                            <option value="">{Lang::T('Select Customer')}</option>
                            {foreach $customers as $customer}
                                <option value="{$customer->id}">{$customer->fullname} ({$customer->username})</option>
                            {/foreach}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">{Lang::T('Subject')} <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control" required maxlength="255" placeholder="{Lang::T('Brief description of the issue')}">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">{Lang::T('Category')}</label>
                        <select id="category" name="category" class="form-control">
                            {foreach $categories as $cat}
                                <option value="{$cat->name}">{$cat->name}</option>
                            {/foreach}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">{Lang::T('Priority')}</label>
                        <select id="priority" name="priority" class="form-control">
                            <option value="low">{Lang::T('Low')} - {Lang::T('General inquiry')}</option>
                            <option value="medium" selected>{Lang::T('Medium')} - {Lang::T('Minor issue')}</option>
                            <option value="high">{Lang::T('High')} - {Lang::T('Service affected')}</option>
                            <option value="urgent">{Lang::T('Urgent')} - {Lang::T('Critical issue')}</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">{Lang::T('Message')} <span class="text-danger">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="8" required placeholder="{Lang::T('Please describe the issue in detail. Include any relevant information such as error messages, steps to reproduce, etc.')}"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <a href="{Text::url('ticket/list')}" class="btn btn-default">{Lang::T('Cancel')}</a>
                        <button type="submit" class="btn btn-primary pull-right">{Lang::T('Create Ticket')}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
