<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Правила агентств</title>
</head>
<body>
<h1>Список агентств</h1>
<p>Перейдите на страницу агенства для добавления/редактирования правил</p>

<?php if (!empty($agencies)): ?>
<ul>
    <?php foreach ($agencies as $agency): ?>
        <li>
            <a href="/rules_edit?agency_id=<?= $agency['id'] ?>">
                <?= htmlspecialchars($agency['name']) ?> (ID: <?= $agency['id'] ?>)
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
 Список агенств пуст
<?php endif; ?>
</body>
</html>