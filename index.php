<?php
$host = '127.0.0.1';
$db = 'bcc';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Set up PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// Input params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

// Build conditions
$where = "1";
$params = [];

if (!empty($search)) {
    $where .= " AND br.registration_key LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($selectedYear)) {
    $where .= " AND YEAR(br.registration_date) = :year";
    $params[':year'] = $selectedYear;
}

// Count total results
$countSql = "SELECT COUNT(*) FROM business_registrations br LEFT JOIN sub_activities sa ON br.sub_activity_id = sa.sub_activity_id WHERE $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalResults = $countStmt->fetchColumn();
$totalPages = ceil($totalResults / $pageSize);

// Fetch paginated data
$sql = "
    SELECT
        br.id AS business_id,
        br.registration_number,
        br.registration_date,
        br.registration_key,
        br.sub_activity_id,
        br.registration_section,
        br.document_number,
        sa.sub_activity_name
    FROM
        business_registrations br
    LEFT JOIN
        sub_activities sa ON br.sub_activity_id = sa.sub_activity_id
    WHERE $where
    ORDER BY br.registration_date DESC
    LIMIT :offset, :limit
";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();

// Get available years
$years = $pdo->query("
    SELECT DISTINCT YEAR(registration_date) AS year
    FROM business_registrations
    WHERE registration_date IS NOT NULL
    ORDER BY year DESC
")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Search Business Registrations by Year</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .tag {
            display: inline-block;
            padding: 6px 12px;
            margin: 4px;
            background: #f0f0f0;
            border-radius: 20px;
            text-decoration: none;
            color: #333;
        }

        .tag.selected {
            background: #007BFF;
            color: #fff;
        }

        .result {
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }

        .label {
            font-weight: bold;
            width: 200px;
            display: inline-block;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
        }

        .pagination strong {
            margin: 0 5px;
            color: red;
        }

        form {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h1>Search Business Registrations</h1>

    <!-- Search form -->
    <form method="GET" action="">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter registration key..." style="width: 300px; padding: 6px;">
        <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">
        <button type="submit" style="padding: 6px 12px;">Search</button>
    </form>

    <!-- Year tags -->
    <div class="year-tags">
        <a href="?<?= http_build_query(array_merge($_GET, ['year' => '', 'page' => 1])) ?>" class="tag <?= empty($selectedYear) ? 'selected' : '' ?>">All Years</a>
        <?php foreach ($years as $year): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['year' => $year, 'page' => 1])) ?>" class="tag <?= ($selectedYear == $year) ? 'selected' : '' ?>">
                <?= $year ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($search) || !empty($selectedYear)): ?>
        <p>Found <strong><?= $totalResults ?></strong> results<?= $selectedYear ? " in <strong>$selectedYear</strong>" : '' ?>.</p>
    <?php endif; ?>

    <?php if (count($results) > 0): ?>
        <?php foreach ($results as $row): ?>
            <div class="result">
                <div><span class="label">Business ID:</span> <?= $row['business_id'] ?></div>
                <div><span class="label">Registration Number:</span> <?= $row['registration_number'] ?></div>
                <div><span class="label">Registration Date:</span> <?= $row['registration_date'] ?></div>
                <div><span class="label">Registration Key:</span> <?= $row['registration_key'] ?></div>
                <div><span class="label">Sub-Activity ID:</span> <?= $row['sub_activity_id'] ?></div>
                <div><span class="label">Sub-Activity Name:</span> <?= $row['sub_activity_name'] ?: '—' ?></div>
                <div><span class="label">Registration Section:</span> <?= $row['registration_section'] ?></div>
                <div><span class="label">Document Number:</span> <?= $row['document_number'] ?></div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <div class="pagination" style="margin-top: 20px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <strong><?= $i ?></strong>
                <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>

</html>