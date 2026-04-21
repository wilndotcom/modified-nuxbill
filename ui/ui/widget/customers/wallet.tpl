
{if $_c['enable_balance'] == 'yes'}
    <div class="box box-solid {if $wallet_is_debt}box-danger{else}box-success{/if} mb30">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-wallet"></i> {Lang::T('My Wallet')}</h3>
            <div class="box-tools pull-right">
                <a href="{Text::url('order/balance')}" class="btn btn-xs {if $wallet_is_debt}btn-danger{else}btn-success{/if}">
                    <i class="fa fa-plus"></i> {Lang::T('Top Up')}
                </a>
            </div>
        </div>
        <div class="box-body text-center">
            <div style="padding: 20px;">
                <h2 style="margin: 0; font-size: 36px; font-weight: bold; {if $wallet_is_debt}color: #dd4b39;{else}color: #00a65a;{/if}">
                    {if $wallet_is_debt}-{else}+{/if}{Lang::moneyFormat(abs($wallet_balance))}
                </h2>
                <p style="margin-top: 10px; font-size: 16px; color: #666;">
                    {if $wallet_is_debt}
                        <i class="fa fa-exclamation-triangle"></i> {Lang::T('You have an outstanding debt')}
                    {else}
                        <i class="fa fa-check-circle"></i> {Lang::T('Available balance for purchases')}
                    {/if}
                </p>
            </div>
        </div>
        <div class="box-footer">
            <div class="row">
                <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4;">
                    <a href="{Text::url('order/balance')}" class="btn btn-block btn-default">
                        <i class="fa fa-plus-circle"></i> {Lang::T('Add Funds')}
                    </a>
                </div>
                <div class="col-xs-6 text-center">
                    {if $_c['allow_balance_transfer'] == 'yes'}
                        <a href="{Text::url('home')}" onclick="showTransferForm(); return false;" class="btn btn-block btn-default">
                            <i class="fa fa-exchange"></i> {Lang::T('Transfer')}
                        </a>
                    {else}
                        <a href="{Text::url('order/internet')}" class="btn btn-block btn-default">
                            <i class="fa fa-shopping-cart"></i> {Lang::T('Buy Plan')}
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function showTransferForm() {
        // Scroll to transfer form if it exists
        var transferBox = document.querySelector('.balance-transfer-box');
        if (transferBox) {
            transferBox.scrollIntoView({ behavior: 'smooth' });
            transferBox.style.boxShadow = '0 0 15px rgba(0,0,0,0.3)';
            setTimeout(function() {
                transferBox.style.boxShadow = '';
            }, 2000);
        }
    }
    </script>
{/if}
