<!DOCTYPE html>
<html>
    <head>
        <title>{Template::getTitle()}</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
{foreach Template::getHeadTags('meta') as $tag}
        <meta name="{$tag['name']}" content="{$tag['content']}" />
{/foreach}
{foreach Template::getHeadTags('link') as $tag}
        <link rel="{$tag['rel']}" href="{$tag['href']}" type="{$tag['type']}" />
{/foreach}
        <link rel="stylesheet" href="{%THEME_URL%}/css/style.css" type="text/css" />
{foreach Template::getHeadTags('css') as $tag}
        <link rel="stylesheet" href="{$tag['url']}" type="text/css" media="{$tag['media']}" />
{/foreach}
{foreach Template::getHeadTags('script') as $tag}
        <script src="{$tag['url']}" type="{$tag['type']}"></script>
{/foreach}
    </head>
    <body>
        <div id="wrapper">
            <div id="header">
                <div class="title">{$SETTINGS['core']['site_name']}</div>
            </div>
            <div id="content">
