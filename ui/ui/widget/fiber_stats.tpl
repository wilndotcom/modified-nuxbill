<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-network-wired"></i> {Lang::T('Fiber Network Statistics')}
            <a href="{Text::url('fiber/olt-devices')}" class="btn btn-xs btn-primary pull-right" style="margin-top: -5px;">
                <i class="fa fa-cog"></i> {Lang::T('Manage')}
            </a>
        </h3>
    </div>
    <div class="panel-body">
        {if $error}
            <div class="alert alert-danger">{$error}</div>
        {/if}
        
        <!-- OLT Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{$total_olts}</h3>
                        <p>{Lang::T('Total OLTs')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-server"></i>
                    </div>
                    <a href="{Text::url('fiber/olt-devices')}" class="small-box-footer">
                        {Lang::T('View OLTs')} <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{$active_olts}</h3>
                        <p>{Lang::T('Active OLTs')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <span class="small-box-footer">
                        {Lang::T('Online')}
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box {if $offline_olts > 0}bg-red{else}bg-gray{/if}">
                    <div class="inner">
                        <h3>{$offline_olts}</h3>
                        <p>{Lang::T('Offline OLTs')}</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-circle"></i>
                    </div>
                    <span class="small-box-footer">
                        {if $offline_olts > 0}
                            <i class="fa fa-warning"></i> {Lang::T('Attention Required')}
                        {else}
                            {Lang::T('All Online')}
                        {/if}
                    </span>
                </div>
            </div>
        </div>

        <!-- ONU Stats -->
        <div class="row">
            <div class="col-md-12">
                <h5><strong>{Lang::T('ONU Status')}</strong></h5>
                <div class="progress-group">
                    <span class="progress-text">{Lang::T('Active')}</span>
                    <span class="progress-number"><b>{$active_onus}</b>/{$total_onus}</span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-success" style="width: {if $total_onus > 0}{(($active_onus/$total_onus)*100)}{else}0{/if}%"></div>
                    </div>
                </div>
                <div class="progress-group">
                    <span class="progress-text">{Lang::T('Suspended')}</span>
                    <span class="progress-number"><b>{$suspended_onus}</b>/{$total_onus}</span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-warning" style="width: {if $total_onus > 0}{(($suspended_onus/$total_onus)*100)}{else}0{/if}%"></div>
                    </div>
                </div>
                <div class="progress-group">
                    <span class="progress-text">{Lang::T('Offline')}</span>
                    <span class="progress-number"><b>{$offline_onus}</b>/{$total_onus}</span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-danger" style="width: {if $total_onus > 0}{(($offline_onus/$total_onus)*100)}{else}0{/if}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CPE Router Stats -->
        {if $total_cpes > 0}
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12">
                <h5><strong>{Lang::T('CPE Router Status')}</strong></h5>
                <div class="progress-group">
                    <span class="progress-text">{Lang::T('Active')}</span>
                    <span class="progress-number"><b>{$active_cpes}</b>/{$total_cpes}</span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-success" style="width: {if $total_cpes > 0}{(($active_cpes/$total_cpes)*100)}{else}0{/if}%"></div>
                    </div>
                </div>
                <div class="progress-group">
                    <span class="progress-text">{Lang::T('Offline')}</span>
                    <span class="progress-number"><b>{$offline_cpes}</b>/{$total_cpes}</span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-danger" style="width: {if $total_cpes > 0}{(($offline_cpes/$total_cpes)*100)}{else}0{/if}%"></div>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        <!-- Last Sync Info -->
        {if $last_sync_time}
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-12">
                <small class="text-muted pull-right">
                    <i class="fa fa-clock-o"></i> {Lang::T('Last Sync')}: {Lang::dateTimeFormat($last_sync_time)}
                </small>
            </div>
        </div>
        {/if}

        <!-- Quick Links -->
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-3">
                <a href="{Text::url('fiber/olt-devices')}" class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-server"></i> {Lang::T('OLTs')}
                </a>
            </div>
            <div class="col-md-3">
                <a href="{Text::url('fiber/onus')}" class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-hdd-o"></i> {Lang::T('ONUs')}
                </a>
            </div>
            <div class="col-md-3">
                <a href="{Text::url('fiber/profiles')}" class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-file-text"></i> {Lang::T('Profiles')}
                </a>
            </div>
            <div class="col-md-3">
                <a href="{Text::url('fiber/cpe-routers')}" class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-wifi"></i> {Lang::T('CPEs')}
                </a>
            </div>
        </div>
    </div>
</div>
