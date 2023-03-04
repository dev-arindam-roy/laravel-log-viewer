@if (Session::has('success_msg') && !empty(Session::get('success_msg')))
    <div class="success-box">
        <p>{{ Session::get('success_msg') }}</p>
    </div>
@endif
<div class="table-box">
    <form name="frm_logs" action="{{ route('cssLogViewer.bulkaction') }}" method="POST">
        {{ csrf_field() }}
        @if (!empty($log_files) && count($log_files))
            <div class="action-box">
                <input type="submit" name="bulk_action" class="download-zip-btn" value="Download As Zip" />
                <input type="submit" name="bulk_action" class="delete-all-btn" value="Delete All" onclick="return confirm('Are you sure? You want to delete these files');" />
                @if(Session::has('logViewerAdminAccessEnabled') && Session::get('logViewerAdminAccessEnabled') == 'YES')
                    <a href="{{ route('onexloginfoAdminLogout') }}" class="onexlogout">Logout</a>
                @endif
            </div>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 90px;"><input type="checkbox" name="checkall" id="checkall" class="checkall" value="1" onclick="checkallCheckbox();" /> SL</th>
                    <th>Log Files</th>
                    <th>Size</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($log_files) && count($log_files))
                    @php $sl = 1; @endphp
                    @foreach ($log_files as $k => $v)
                        @if (!empty($v['name']))
                            <tr>
                                <th><input type="checkbox" name="ischeckme[]" class="ischeckme" value="{{ $v['name'] }}" /> {{ $sl }}</th>
                                <td class="name">{{ $v['name'] }} @if ($v['name'] == $today_log) <span> - Today</span> @endif</td>
                                <td>{{ !empty($v['readable_size']) ? $v['readable_size'] : '0 KB' }}</td>
                                <td style="text-align:right;">
                                    <a href="{{ route('cssLogViewer.downloadlogs', array('file' => $v['name'])) }}" class="btn btn-download" title="Download Logs">Download</a>
                                    <a href="{{ route('cssLogViewer.deletelogs', array('file' => $v['name'])) }}" class="btn btn-delete" title="Delete Logs" onclick="return confirm('Are you sure? You want to delete this file');">Delete</a>
                                    <a href="{{ route('cssLogViewer.clearlogs', array('file' => $v['name'])) }}" class="btn btn-clear" title="Clear Logs">Clear</a>
                                    <a href="{{ route('cssLogViewer.viewlogs', array('file' => $v['name'])) }}" class="btn btn-view" title="View Logs" target="_blank">View</a>
                                </td>
                            </tr>
                            @php $sl++; @endphp
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td colspan="4"><strong>Sorry! No log files are found.</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </form>
</div>