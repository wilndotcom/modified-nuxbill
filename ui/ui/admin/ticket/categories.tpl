{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('Ticket Categories')}
                <a href="{Text::url('ticket/list')}" class="btn btn-primary btn-xs pull-right">
                    <i class="fa fa-arrow-left"></i> {Lang::T('Back to Tickets')}
                </a>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{Lang::T('Name')}</th>
                                <th>{Lang::T('Description')}</th>
                                <th>{Lang::T('Color')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Created')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $categories as $cat}
                                <tr>
                                    <td>
                                        <span class="label" style="background-color: {$cat->color}">{$cat->name}</span>
                                    </td>
                                    <td>{$cat->description}</td>
                                    <td>
                                        <code>{$cat->color}</code>
                                    </td>
                                    <td>
                                        {if $cat->enabled}
                                            <span class="label label-success">{Lang::T('Enabled')}</span>
                                        {else}
                                            <span class="label label-default">{Lang::T('Disabled')}</span>
                                        {/if}
                                    </td>
                                    <td>{Lang::dateTimeFormat($cat->created_at)}</td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="5" class="text-center">{Lang::T('No categories found')}</td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
