{include file="sections/header.tpl"}
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                Mpesa Transactions
            </div>
            <div class="panel-body">
                <!-- Search Form -->
                <form class="form-inline mb20">
                    <div class="form-group">
                        <input type="text" id="live-search" class="form-control" placeholder="Search by Transaction ID, First Name, Amount, Phone, or Account No">
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <!-- Table to display transactions -->
                <table class="table table-bordered table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>First Name</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Account No</th>
                            <th>Org Account Balance</th>
                            <th>Transaction ID</th>
                            <th>Transaction Type</th>
                            <th>Transaction Time</th>
                            <th>Business Short Code</th>
                        </tr>
                    </thead>
                    <tbody id="transaction-table-body">
                        {foreach $t as $key => $ts}
                            <tr class="transaction-row">
                                <td>{$key + 1}</td>
                                <td class="search-target">{$ts['FirstName']}</td>
                                <td class="search-target">{if $ts['MSISDN']}{$ts['MSISDN']|truncate:20:"..."}{else}No MSISDN available{/if}</td>
                                <td class="search-target">{$ts['TransAmount']}</td>
                                <td class="search-target">{$ts['BillRefNumber']}</td>
                                <td>{$ts['OrgAccountBalance']}</td>
                                <td class="search-target">{$ts['TransID']}</td>
                                <td>{$ts['TransactionType']}</td>
                                <td>{$ts['TransTime']|date_format:"%B %e, %Y, %I:%M %p"}</td>
                                <td>{$ts['BusinessShortCode']}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                <div id="no-results" class="alert alert-warning" style="display: none;">
                    <strong>No M-Pesa Transactions Found</strong>
                    <p>There are currently no M-Pesa transactions to display.</p>
                </div>
            </div>
        </div>
    </div>
</div>
{include file="sections/footer.tpl"}

<!-- jQuery (if not already included) -->
{literal}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#live-search').on('keyup', function() {
        let searchValue = $(this).val().toLowerCase();
        let hasResults = false;

        $('.transaction-row').each(function() {
            let rowText = $(this).text().toLowerCase();
            if (rowText.includes(searchValue)) {
                $(this).show();
                hasResults = true;
            } else {
                $(this).hide();
            }
        });

        if (hasResults) {
            $('#no-results').hide();
        } else {
            $('#no-results').show();
        }
    });
});
</script>
{/literal}
