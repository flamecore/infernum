<!DOCTYPE html>
<html>
    <head>
        <title>{Template::getTitle()}</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="@URL_THEME@/css/style.css" type="text/css" />
{foreach Template::getHeadTags() as $tag}
        {$tag}
{/foreach}
    </head>
    <body>
        <div id="wrapper">
            <div id="header">
                <div class="title">{Settings::get('core', 'site_name')}</div>
            </div>
            <div id="content">
