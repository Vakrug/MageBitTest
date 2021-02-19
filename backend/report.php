<?php

require_once 'subscription.class.php';
require_once 'filter.class.php';

$subscription = new subscription();

$IdToDelete = filter_input(INPUT_POST, 'Id', FILTER_VALIDATE_INT);
if ($IdToDelete) {
    $subscription->delete($IdToDelete);
}

$sortfield = filter_input(INPUT_GET, 'sortfield');
$sortorder = filter_input(INPUT_GET, 'sortorder');
$providerfilter = filter_input(INPUT_GET, 'providerfilter');
$emailfilter = filter_input(INPUT_GET, 'emailfilter');
$paginationblock = filter_input(INPUT_GET, 'paginationblock', FILTER_VALIDATE_INT);

$filter = new filter($sortfield, $sortorder, $providerfilter, $emailfilter, $paginationblock);
$rows = $subscription->subscriptions($filter);

$providers = $subscription->providers();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>MageBitTest</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            .active-filter {
                background-color: lightgray;
            }
        </style>
    </head>
    <body>
        <form id="form" method="GET">
            <input id="sortfield" type="hidden" name="sortfield" value="<?= $sortfield ?>" />
            <input id="sortorder" type="hidden" name="sortorder" value="<?= $sortorder ?>" />
            <input type="hidden" name="providerfilter" value="<?= $providerfilter ?>" />
            <input id="paginationblock" type="hidden" name="paginationblock" value="<?= $filter->paginationblock ?>" />
            <div>
                <input type="text" name="emailfilter" value="<?= $emailfilter ?>" />
                <button type="submit" value="">Filter</button>
            </div>
            
            <div>
                <?php foreach($providers as $provider): ?>
                <button
                    type="submit"
                    name="providerfilter"
                    value="<?= $provider == $providerfilter ? '' : $provider ?>"
                    <?= $provider == $providerfilter ? 'class="active-filter"' : '' ?>
                    onclick="ResetPagination();"
                >
                    <?= $provider ?>
                </button>
                <?php endforeach; ?>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>
                            Email
                            <button
                                type="button"
                                onclick="SetSortAndSubmit('<?= filter::SORT_FIELD_EMAIL ?>', '<?= filter::SORT_ORDER_ASC ?>')"
                                <?= $filter->sortfield == filter::SORT_FIELD_EMAIL && $filter->sortorder == filter::SORT_ORDER_ASC ? 'class="active-filter"' : '' ?>
                            >
                                ^
                            </button>
                            <button
                                type="button" 
                                onclick="SetSortAndSubmit('<?= filter::SORT_FIELD_EMAIL ?>', '<?= filter::SORT_ORDER_DESC ?>')" 
                                <?= $filter->sortfield == filter::SORT_FIELD_EMAIL && $filter->sortorder == filter::SORT_ORDER_DESC ? 'class="active-filter"' : '' ?>
                            >
                                V
                            </button>
                        </th>
                        <th>
                            AddedAt
                            <button
                                type="button"
                                onclick="SetSortAndSubmit('<?= filter::SORT_FIELD_ADDEDAT ?>', '<?= filter::SORT_ORDER_ASC ?>')"
                                <?= $filter->sortfield == filter::SORT_FIELD_ADDEDAT && $filter->sortorder == filter::SORT_ORDER_ASC ? 'class="active-filter"' : '' ?>
                            >
                                ^
                            </button>
                            <button
                                type="button"
                                onclick="SetSortAndSubmit('<?= filter::SORT_FIELD_ADDEDAT ?>', '<?= filter::SORT_ORDER_DESC ?>')"
                                <?= $filter->sortfield == filter::SORT_FIELD_ADDEDAT && $filter->sortorder == filter::SORT_ORDER_DESC ? 'class="active-filter"' : '' ?>
                            >
                                V
                            </button>
                        </th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rows as $row): ?>
                    <tr>
                        <td><?= $row['Email'] ?></td>
                        <td><?= $row['AddedAt'] ?></td>
                        <td>
                            <button type="submit" formmethod="POST" name="Id" value="<?= $row['Id'] ?>">X</button>
                        </td>
                        <td>
                            <input type="checkbox" name="Id[]" value="<?= $row['Id'] ?>" form="exportform">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div>
                <button type="submit" name="paginationblock" value="<?= $filter->paginationblock - 1 ?>" <?= $filter->paginationblock == 0 ? 'disabled=disabled' : ''?>>&#60;</button>
                <button type="submit" name="paginationblock" value="<?= $filter->paginationblock + 1 ?>" <?= $subscription->hasMoreRows ? '' : 'disabled=disabled'?>>&#62;</button>
            </div>
        </form>
        <form id="exportform" method="POST" action="/backend/export.php" target="__blank">
            <input type="submit" value="Export selected emails" />
        </form>
        <script type="text/javascript">
            function SetSortAndSubmit(field, direction) {
                document.getElementById('sortfield').value = field;
                document.getElementById('sortorder').value = direction;
                document.getElementById('form').submit();
            }
            
            function ResetPagination() {
                document.getElementById('paginationblock').value = 0;
            }
        </script>
    </body>
</html>