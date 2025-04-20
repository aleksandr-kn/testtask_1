<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Соответствие отеля правилам агенств</title>
</head>
<body>
<?php if (!empty($messages)): ?>
    <p>Список сработавших правил для данного отеля:</p>
    <ul>
        <?php foreach ($messages as $message): ?>
            <li>
                <?= htmlspecialchars($message) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    Ни одно правило не сработало для данного отеля.
<?php endif; ?>
</body>
</html>