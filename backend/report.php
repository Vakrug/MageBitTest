<?php

require_once 'config.php';

/* @var $config array */

$dbh = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['password']);

$IdToDelete = filter_input(INPUT_POST, 'Id', FILTER_VALIDATE_INT);
if ($IdToDelete) {
    $sql = 'delete from subscriptions where Id = :Id';
    $sth = $dbh->prepare($sql);
    $sth->execute([
        ':Id' => $IdToDelete
    ]);
}

$sortfield = filter_input(INPUT_GET, 'sortfield');
if (!$sortfield || !in_array($sortfield, ['Email', 'AddedAt'])) {
    $sortfield = 'AddedAt';
}

$sortorder = filter_input(INPUT_GET, 'sortorder');
if (!$sortorder || !in_array($sortorder, ['asc', 'desc'])) {
    $sortorder = 'asc';
}

$whereKeyword = '';
$whereParts = [];
$executeParams = [];

$providerfilter = filter_input(INPUT_GET, 'providerfilter');
if ($providerfilter) {
    $whereKeyword = ' where ';
    $whereParts[] = ' Provider = :provider ';
    $executeParams[':provider'] = $providerfilter;
}

$emailfilter = filter_input(INPUT_GET, 'emailfilter');
if ($emailfilter) {
    $whereKeyword = ' where ';
    $whereParts[] = ' Email like :emailfilter ';
    $executeParams[':emailfilter'] = '%' . $emailfilter . '%';
}

$paginationblock = filter_input(INPUT_GET, 'paginationblock');
if (!$paginationblock) {
    $paginationblock = 0;
}

$sql = 'select Id, Email, AddedAt from subscriptions ' . 
        $whereKeyword . implode(' and ', $whereParts) .
        ' order by ' . $sortfield . ' ' . $sortorder .
        ' limit ' . $paginationblock * $config['pagination'] . ', ' . ($config['pagination'] + 1); //+1 to check if there are more rows next
$sth = $dbh->prepare($sql);
$sth->execute($executeParams);
$rows = $sth->fetchAll();
$hasMoreRows = false;
if (count($rows) == $config['pagination'] + 1) {
    $hasMoreRows = true;
    array_pop($rows); //Remove that last element. It served its purpose.
}

$sql = 'select Provider from subscriptions group by Provider order by Provider asc';
$sth = $dbh->prepare($sql);
$sth->execute();
$providerRows = $sth->fetchAll();
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
            <input id="paginationblock" type="hidden" name="paginationblock" value="<?= $paginationblock ?>" />
            <div>
                <input type="text" name="emailfilter" value="<?= $emailfilter ?>" />
                <button type="submit" value="">Filter</button>
            </div>
            
            <div>
                <?php foreach($providerRows as $providerRow): ?>
                <button
                    type="submit"
                    name="providerfilter"
                    value="<?= $providerRow['Provider'] == $providerfilter ? '' : $providerRow['Provider'] ?>"
                    <?= $providerRow['Provider'] == $providerfilter ? 'class="active-filter"' : '' ?>
                    onclick="ResetPagination();"
                >
                    <?= $providerRow['Provider'] ?>
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
                                onclick="SetSortAndSubmit('Email', 'asc')"
                                <?= $sortfield == 'Email' && $sortorder == 'asc' ? 'class="active-filter"' : '' ?>
                            >
                                ^
                            </button>
                            <button
                                type="button" 
                                onclick="SetSortAndSubmit('Email', 'desc')" 
                                <?= $sortfield == 'Email' && $sortorder == 'desc' ? 'class="active-filter"' : '' ?>
                            >
                                V
                            </button>
                        </th>
                        <th>
                            AddedAt
                            <button
                                type="button"
                                onclick="SetSortAndSubmit('AddedAt', 'asc')"
                                <?= $sortfield == 'AddedAt' && $sortorder == 'asc' ? 'class="active-filter"' : '' ?>
                            >
                                ^
                            </button>
                            <button
                                type="button"
                                onclick="SetSortAndSubmit('AddedAt', 'desc')"
                                <?= $sortfield == 'AddedAt' && $sortorder == 'desc' ? 'class="active-filter"' : '' ?>
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
                <button type="submit" name="paginationblock" value="<?= $paginationblock - 1 ?>" <?= $paginationblock == 0 ? 'disabled=disabled' : ''?>>&#60;</button>
                <button type="submit" name="paginationblock" value="<?= $paginationblock + 1 ?>" <?= $hasMoreRows ? '' : 'disabled=disabled'?>>&#62;</button>
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