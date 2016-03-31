<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    <?php echo htmlspecialchars($name,ENT_QUOTES, "UTF-8"); ?>

    <?php foreach( $list as $item ){ ?>
        <?php echo htmlspecialchars($item,ENT_QUOTES, "UTF-8"); ?>
    <?php } ?>
</body>
</html>