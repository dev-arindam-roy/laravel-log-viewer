<!DOCTYPE html>
<html lang="en">
<head>
  <title>{{ env('APP_NAME') }} | LOG-VIEWER</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=0.41, maximum-scale=1" />
  @include('logviewer::assets.style')
</head>
<body>
  <div class="onex-container">
    @include('logviewer::page-title')

    <!-- Admin Access Area -->
    @if(!empty($config_data['authentication']['is_enabled']) && $config_data['authentication']['is_enabled'] && Session::get('logViewerAdminAccessEnabled') == 'NO')
      @include('logviewer::system-access')
    @endif

    <!-- INFORMTION AREA -->
    @if(!$config_data['authentication']['is_enabled'] || (Session::has('logViewerAdminAccessEnabled') && Session::get('logViewerAdminAccessEnabled') == 'YES'))
      @include('logviewer::extract-logs')
    @endif
  </div>
  @include('logviewer::assets.script')
</body>
</html>
