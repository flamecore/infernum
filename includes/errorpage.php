<!DOCTYPE html>
<html>
    <head>
        <title>System Error</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <style type="text/css">
            body {
                background-color: #f1f1f1;
                color: #111;
                font: 14px "Lucida Grande", Arial, sans-serif;
                line-height: 1.6em;
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
            #content fieldset {
                margin-top: 20px;
            }
            #content pre {
                margin: 2px 4px;
                font-size: .8em;
            }
        </style>
    </head>
    <body>
        <div id="content">
            <h1>System Error</h1>
            <p>We are sorry, an unexpected system error occurred.</p>
<?php if (isset($GLOBALS['CONFIG']['enable_debugmode']) && $GLOBALS['CONFIG']['enable_debugmode']): ?>
            <fieldset>
                <legend>Debug Information</legend>
                <table width="100%">
                    <tr>
                        <td colspan="2"><strong><?php echo $exception->getMessage() ?></strong></td>
                    </tr>
                    <tr>
                        <td>Type:</td>
                        <td><?php echo get_class($exception) ?></td>
                    </tr>
                    <tr>
                        <td>File:</td>
                        <td><?php echo $exception->getFile() ?></td>
                    </tr>
                    <tr>
                        <td>Line:</td>
                        <td><?php echo $exception->getLine() ?></td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Stack Trace</legend>
                <pre><?php echo str_replace(WW_ENGINE_PATH, '.', $exception->getTraceAsString()) ?></pre>
            </fieldset>
<?php endif; ?>
        </div>
    </body>
</html>