@if(Session::has('logViewerAdminAccessEnabled') && Session::get('logViewerAdminAccessEnabled') == 'YES')
    <a href="{{ route('onexloginfoAdminLogout') }}" class="onexlogout">Logout</a>
@else 
    <a href="javascript:void(0);" id="tab_close" class="onexlogout" onclick="closeCurrentWindow();">Close</a>
@endif

@if ($is_filesize_over)
    <hr/>
    <div class="alert alert-danger">
        <p>This <span><strong>"{{ $file_name }}"</strong></span> - size is too long to read.
            <br/>So please <a class="force-download" href="{{ route('cssLogViewer.downloadlogs', array('file' => $file_name)) }}"><span>download</span></a> the file and check the log information.
            <br/><br/><span><strong>Thanks</strong></span>
        </p>
    </div>
@endif

@if (!$is_filesize_over)
    <div class="table-box">
        <table class="table">
            <thead>
                <tr>
                    <th colspan="3">{{ $file_name }} - ({{ count($logs_data) }})</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($logs_data) && count($logs_data))
                    @foreach ($logs_data as $k => $v)
                        <tr>
                            <th style="width: 60px;">{{ $k + 1 }}</th>
                            <td style="width: 20%;">{{ !empty($v[1]) ? $v[1] : '' }} @if (!empty($v[6])) <div class="mt-1"><span class="level level-{{ strtolower($v[6]) }}">{{ $v[6] }}</span></div> @endif</td>
                            <td>{{ !empty($v[7]) ? $v[7] : '' }}</td>
                        </tr>
                    @endforeach
                @else 
                    <tr>
                        <td colspan="3">No logs found in this file.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endif