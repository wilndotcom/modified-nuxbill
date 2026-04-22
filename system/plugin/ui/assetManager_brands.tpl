{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-xs" title="Add Brand" href="{$_url}plugin/assetManager/brands-add">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> {Lang::T('Add Brand')}
                    </a>
                    <a class="btn btn-default btn-xs" title="Dashboard" href="{$_url}plugin/assetManager/dashboard">
                        <span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {Lang::T('Dashboard')}
                    </a>
                </div>
                {Lang::T('Asset Brands')}
            </div>
            <div class="panel-body">

                {if $brands}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{Lang::T('Name')}</th>
                                <th>{Lang::T('Country')}</th>
                                <th>{Lang::T('Website')}</th>
                                <th>{Lang::T('Models')}</th>
                                <th>{Lang::T('Assets')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Created')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $brands as $brand}
                            <tr>
                                <td>{$brand.id}</td>
                                <td><strong>{$brand.name}</strong></td>
                                <td>{$brand.country}</td>
                                <td>
                                    {if $brand.website}
                                    <a href="{$brand.website}" target="_blank">{$brand.website|truncate:30}</a>
                                    {else}
                                    -
                                    {/if}
                                </td>
                                <td>
                                    <span class="badge">{$brand.model_count}</span>
                                </td>
                                <td>
                                    <span class="badge">{$brand.asset_count}</span>
                                </td>
                                <td>
                                    {if $brand.status == 'Active'}
                                    <span class="label label-success">{$brand.status}</span>
                                    {else}
                                    <span class="label label-danger">{$brand.status}</span>
                                    {/if}
                                </td>
                                <td>{$brand.created_at|date_format}</td>
                                <td>
                                    <a href="{$_url}plugin/assetManager/brands-edit/{$brand.id}"
                                        class="btn btn-warning btn-xs" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    {if $brand.model_count == 0 && $brand.asset_count == 0}
                                    <a href="{$_url}plugin/assetManager/brands-delete/{$brand.id}"
                                        class="btn btn-danger btn-xs"
                                        onclick="return confirm('{Lang::T('Are you sure you want to delete this brand?')}')"
                                        title="{Lang::T('Delete')}">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    {else}
                                    <button class="btn btn-danger btn-xs" disabled
                                        title="{Lang::T('Cannot delete brand with models or assets')}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    {/if}
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {else}
                <div class="text-center">
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> {Lang::T('No Brands Found')}</h4>
                        <p>{Lang::T('You haven\'t added any asset brands yet. Brands are the manufacturers or companies of your assets.')}</p>
                        <a href="{$_url}plugin/assetManager/brands-add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> {Lang::T('Add First Brand')}
                        </a>
                    </div>
                </div>
                {/if}
                {include file="pagination.tpl"}
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