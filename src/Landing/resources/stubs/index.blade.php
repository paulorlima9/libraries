<!DOCTYPE html>
<html lang="en" data-kit-theme="default">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

    <title>{{data_get($data, 'name')}}</title>

    <script src="//polyfill.io/v3/polyfill.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        window.__APP__ = @json($data);
    </script>
</head>

<body>

<div id="root">
    <noscript>
        <div style="margin-left: 20px;">
            You need to enable JavaScript to run this app.
        </div>
    </noscript>
</div>

<!-- Scripts Goes Here -->

</body>

</html>
