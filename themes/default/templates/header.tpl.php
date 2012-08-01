<!DOCTYPE html>
<html>
    <head>
        <title><?php echo Template::getTitle() ?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php foreach (Template::getHeadTags('meta') as $tag): ?>
        <meta name="<?php echo $tag['name'] ?>" content="<?php echo $tag['content'] ?>" />
<?php endforeach; ?>
<?php foreach (Template::getHeadTags('link') as $tag): ?>
        <link rel="<?php echo $tag['rel'] ?>" href="<?php echo $tag['href'] ?>" type="<?php echo $tag['type'] ?>" />
<?php endforeach; ?>
        <link rel="stylesheet" href="<?php echo $THEME_URL ?>/css/style.css" type="text/css" />
<?php foreach (Template::getHeadTags('css') as $tag): ?>
        <link rel="stylesheet" href="<?php echo $tag['url'] ?>" type="text/css" media="<?php echo $tag['media'] ?>" />
<?php endforeach; ?>
<?php foreach (Template::getHeadTags('script') as $tag): ?>
        <script src="<?php echo $tag['url'] ?>" type="<?php echo $tag['type'] ?>"></script>
<?php endforeach; ?>
    </head>
    <body>
        <div id="wrapper">
            <div id="header">
                <div class="title"><?php echo $SETTINGS['core']['site_name'] ?></div>
            </div>
            <div id="content">
