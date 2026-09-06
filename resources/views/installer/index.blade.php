<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('installer.app_name', config('installer.author')) }} Installer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    @include('installer::installer.partials._styles')
</head>
<body>

<div class="shell">
    @include('installer::installer.partials._sidebar')

    <main class="panel">
        <div class="panel-top">
            <div style="flex:1">
                <div class="eyebrow" id="stepEyebrow">Step 1 of 6</div>
                <h1 id="stepTitle" style="font-size:19px;">License agreement</h1>
                <div class="progress-track"><div class="progress-fill" id="progressFill" style="width:6%"></div></div>
            </div>
        </div>

        <div class="panel-body">
            @include('installer::installer.partials._step-license')
            @include('installer::installer.partials._step-readiness')
            @include('installer::installer.partials._step-purchase')
            @include('installer::installer.partials._step-database')
            @include('installer::installer.partials._step-admin')
            @include('installer::installer.partials._step-finish')
        </div>
    </main>
</div>

@include('installer::installer.partials._scripts')
</body>
</html>
