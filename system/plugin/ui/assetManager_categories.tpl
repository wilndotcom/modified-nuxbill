{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-xs" title="Add Category"
                        href="{$_url}plugin/assetManager/categories-add">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> {Lang::T('Add Category')}
                    </a>
                    <a class="btn btn-default btn-xs" title="Dashboard" href="{$_url}plugin/assetManager/dashboard">
                        <span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {Lang::T('Dashboard')}
                    </a>
                </div>
                {Lang::T('Asset Categories')}
            </div>
            <div class="panel-body">

                {if $categories}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{Lang::T('Name')}</th>
                                <th>{Lang::T('Description')}</th>
                                <th>{Lang::T('Asset Count')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Created')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $categories as $category}
                            <tr>
                                <td>{$category.id}</td>
                                <td><strong>{$category.name}</strong></td>
                                <td>{$category.description|truncate:50}</td>
                                <td>
                                    <span class="badge">{$category.asset_count}</span>
                                </td>
                                <td>
                                    {if $category.status == 'Active'}
                                    <span class="label label-success">{$category.status}</span>
                                    {else}
                                    <span class="label label-danger">{$category.status}</span>
                                    {/if}
                                </td>
                                <td>{$category.created_at|date_format}</td>
                                <td>
                                    <a href="{$_url}plugin/assetManager/categories-edit/{$category.id}"
                                        class="btn btn-warning btn-xs" title="{Lang::T('Edit')}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    {if $category.asset_count == 0}
                                    <a href="{$_url}plugin/assetManager/categories-delete/{$category.id}"
                                        class="btn btn-danger btn-xs"
                                        onclick="return confirm('{Lang::T('Are you sure you want to delete this category?')}')"
                                        title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    {else}
                                    <button class="btn btn-danger btn-xs" disabled
                                        title="Cannot delete category with assets">
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
                        <h4><i class="fa fa-info-circle"></i> {Lang::T('No Categories Found')}</h4>
                        <p>{Lang::T('You haven\'t created any asset categories yet. Categories help organize your assets by type.')}
                        </p>
                        <a href="{$_url}plugin/assetManager/categories-add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> {Lang::T('Add First Category')}
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