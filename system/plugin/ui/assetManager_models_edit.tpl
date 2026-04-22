{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <a class="btn btn-default btn-xs" title="Back to Models" href="{$_url}plugin/assetManager/models">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> {Lang::T('Back')}
                    </a>
                </div>
                {Lang::T('Edit Model')}
            </div>
            <div class="panel-body">
                
                <form class="form-horizontal" method="post" role="form">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Brand')} *</label>
                        <div class="col-md-6">
                            <select class="form-control" id="brand_id" name="brand_id" required>
                                <option value="">{Lang::T('Select Brand')}</option>
                                {foreach $brands as $brand}
                                    <option value="{$brand.id}" {if $brand.id == $model.brand_id}selected{/if}>{$brand.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Model Name')} *</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="name" name="name" value="{$model.name}" placeholder="{Lang::T('Enter model name')}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Model Number')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="model_number" name="model_number" value="{$model.model_number}" placeholder="{Lang::T('Enter model number')}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Description')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter model description">{$model.description}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Specifications')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="specifications" name="specifications" rows="4" placeholder="{Lang::T('Enter technical specifications')}">{$model.specifications}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" id="status" name="status">
                                <option value="Active" {if $model.status == 'Active'}selected{/if}>{Lang::T('Active')}</option>
                                <option value="Inactive" {if $model.status == 'Inactive'}selected{/if}>{Lang::T('Inactive')}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-success" type="submit">Lang::T('Update Model')</button>
                            <a href="{$_url}plugin/assetManager/models" class="btn btn-default">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        const portalLink = "https://t.me/focuslinkstech";
        const donateLink = "https://www.paypal.com/paypalme/focuslinkstech";
        const updateLink = "{$updateLink}";
        const productName = "Asset Manager";
        const version = "{$version}";
        $('#version').html(productName + ' | Ver: ' + version + ' | by: <a href="' + portalLink + '">Focuslinks Tech</a> | <a href="' + donateLink + '">Donate</a>');

    });
</script>
{include file="sections/footer.tpl"}