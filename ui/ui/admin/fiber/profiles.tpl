{include file="sections/header.tpl"}

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-default">
            <div class="panel-heading">
                {Lang::T('OLT Profiles')}
                <div class="panel-title pull-right">
                    <a href="{Text::url('fiber/profile-add')}" class="btn btn-primary btn-xs">
                        <i class="fa fa-plus"></i> {Lang::T('Add Profile')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{Lang::T('Name')}</th>
                                <th>{Lang::T('OLT')}</th>
                                <th>{Lang::T('Download')}</th>
                                <th>{Lang::T('Upload')}</th>
                                <th>{Lang::T('Line Profile')}</th>
                                <th>{Lang::T('Service Profile')}</th>
                                <th>{Lang::T('Actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$profiles item=profile}
                            <tr>
                                <td>{$profile['name']}</td>
                                <td>{$profile['olt_name']}</td>
                                <td>{$profile['download_speed']} Mbps</td>
                                <td>{$profile['upload_speed']} Mbps</td>
                                <td>{$profile['line_profile']}</td>
                                <td>{$profile['service_profile']}</td>
                                <td>
                                    <a href="{Text::url('fiber/profile-edit/', $profile['id'])}" class="btn btn-info btn-xs">
                                        <i class="fa fa-edit"></i> {Lang::T('Edit')}
                                    </a>
                                    <a href="{Text::url('fiber/profile-delete/', $profile['id'])}?token={$csrf_token}"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('{Lang::T('Are you sure you want to delete this profile?')}');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td colspan="7" class="text-center">{Lang::T('No profiles found')}</td>
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
