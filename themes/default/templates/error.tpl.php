<!DOCTYPE html>
<html>
    <head>
        <title><?php echo Template::getTitle() ?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <style type="text/css">
            body {
                background-color: #f1f1f1;
                color: #111;
                font: 12px "Lucida Grande", Arial, sans-serif;
                line-height: 1.5em;
            }
            p {
                line-height: 1.5em;
                margin: 12px 0 0;
            }
            a {
                color: #35a8c8;
                text-decoration: none;
            }
            a:hover {
                color: #2f94b1;
            }
            #content {
                background-color: #fff;
                border: #999 1px solid;
                padding: 36px 40px;
                margin: 80px auto;
                margin-bottom: 0;
                max-width: 800px;
            }
            #content h1 {
                color: #005999;
                font-size: 1.4em;
                font-weight: bold;
                margin: 0 0 18px;
            }
        </style>
    </head>
    <body>
        <div id="content">
            <h1><?php echo Template::getTitle() ?> &ndash; Error</h1>
            <p>We are sorry, an unexpected system error occurred.</p>
            <?php if (isset($debug)): ?><p><strong>Debug Info:</strong> <?php echo $debug ?></p><?php endif; ?>
        </div>
    </body>
</html>