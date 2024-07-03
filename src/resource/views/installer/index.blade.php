<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Script Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Laravel Script Installer</h1>
        <div id="installation-steps">
            <!-- Step 1: License Agreement -->
            <div class="step" id="step-1">
                <h3>Step 1: License Agreement</h3>
                <!-- Add license agreement content -->
                <button class="btn btn-primary next-step" data-step="2">I Agree, Next Step</button>
            </div>

            <!-- Step 2: Purchase Verification -->
            <div class="step" id="step-2" style="display: none;">
                <h3>Step 2: Purchase Verification</h3>
                <form id="purchase-form">
                    <div class="mb-3">
                        <label for="envato_username" class="form-label">Envato Username</label>
                        <input type="text" class="form-control" id="envato_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="purchase_code" class="form-label">Purchase Code</label>
                        <input type="text" class="form-control" id="purchase_code" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Verify Purchase</button>
                </form>
            </div>

            <!-- Step 3: Server Requirements -->
            <div class="step" id="step-3" style="display: none;">
                <h3>Step 3: Server Requirements</h3>
                <!-- Add server requirements check -->
                <button class="btn btn-primary next-step" data-step="4">Next</button>
            </div>

            <!-- Step 4: Permissions -->
            <div class="step" id="step-4" style="display: none;">
                <h3>Step 4: Permissions</h3>
                <!-- Add permissions check -->
                <button class="btn btn-primary next-step" data-step="5">Next</button>
            </div>

            <!-- Step 5: Database Information -->
            <div class="step" id="step-5" style="display: none;">
                <h3>Step 5: Database Information</h3>
                <form id="database-form">
                    <div class="mb-3">
                        <label for="database_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="database_host" required>
                    </div>
                    <div class="mb-3">
                        <label for="database_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="database_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="database_username" class="form-label">Database Username</label>
                        <input type="text" class="form-control" id="database_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="database_password" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="database_password">
                    </div>
                    <button type="submit" class="btn btn-primary">Check Database Connection</button>
                </form>
            </div>

            <!-- Step 6: Installation -->
            <div class="step" id="step-6" style="display: none;">
                <h3>Step 6: Installation</h3>
                <button id="install-button" class="btn btn-primary">Install Now</button>
                <div id="installation-progress"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.next-step').click(function() {
                var nextStep = $(this).data('step');
                $('.step').hide();
                $('#step-' + nextStep).show();
            });

            $('#purchase-form').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("installer.verify-purchase") }}',
                    method: 'POST',
                    data: {
                        envato_username: $('#envato_username').val(),
                        purchase_code: $('#purchase_code').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.step').hide();
                            $('#step-3').show();
                        } else {
                            alert('Purchase verification failed.');
                        }
                    }
                });
            });

            $('#database-form').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("installer.check-database") }}',
                    method: 'POST',
                    data: {
                        database_host: $('#database_host').val(),
                        database_name: $('#database_name').val(),
                        database_username: $('#database_username').val(),
                        database_password: $('#database_password').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.step').hide();
                            $('#step-6').show();
                        } else {
                            alert('Database connection failed.');
                        }
                    }
                });
            });

            $('#install-button').click(function() {
                $.ajax({
                    url: '{{ route("installer.install") }}',
                    method: 'POST',
                    data: {
                        app_name: 'Your App Name',
                        app_url: window.location.origin,
                        database_host: $('#database_host').val(),
                        database_name: $('#database_name').val(),
                        database_username: $('#database_username').val(),
                        database_password: $('#database_password').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#installation-progress').html('<div class="alert alert-success">Installation completed successfully!</div>');
                        } else {
                            $('#installation-progress').html('<div class="alert alert-danger">Installation failed.</div>');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>