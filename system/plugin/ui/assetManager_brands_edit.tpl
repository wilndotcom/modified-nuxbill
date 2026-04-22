{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <a class="btn btn-default btn-xs" title="Back to Brands" href="{$_url}plugin/assetManager/brands">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> {Lang::T('Back')}
                    </a>
                </div>
                {Lang::T('Edit Brand')}
            </div>
            <div class="panel-body">
                
                <form class="form-horizontal" method="post" role="form">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Brand Name')} *</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="name" name="name" value="{$brand.name}" placeholder="Enter brand name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Description')}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter brand description">{$brand.description}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Country')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="country" name="country" value="{$brand.country}" placeholder="Enter country">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Website')}</label>
                        <div class="col-md-6">
                            <input type="url" class="form-control" id="website" name="website" value="{$brand.website}" placeholder="https://www.example.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-6">
                            <select class="form-control" id="status" name="status">
                                <option value="Active" {if $brand.status == 'Active'}selected{/if}>{Lang::T('Active')}</option>
                                <option value="Inactive" {if $brand.status == 'Inactive'}selected{/if}>{Lang::T('Inactive')}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-success" type="submit">{Lang::T('Update Brand')}</button>
                            <a href="{$_url}plugin/assetManager/brands" class="btn btn-default">{Lang::T('Cancel')}</a>
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