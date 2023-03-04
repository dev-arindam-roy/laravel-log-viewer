<?php

namespace CreativeSyntax\LogViewer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    protected $configData;
    protected $logsPath;
    protected $regxLogs;
    protected $regxEachLog;
    protected $maxReadLength;
    protected $log_channel;

    public function __construct()
    {
        $this->configData = config('log-viewer');
        $this->logsPath = storage_path('logs');
        $this->regxLogs = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/';
        $this->regxEachLog = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}\.?(\d{6}([\+-]\d\d:\d\d)?)?)\](.*?(\w+)\.|.*?)(debug|info|notice|warning|error|critical|alert|emergency|processing|processed|failed)?: (.*?)( in [\/].*?:[0-9]+)?$/is';
        $this->maxReadLength = 10000000;
        $this->log_channel = env('LOG_CHANNEL');
    }

    public function index(Request $request)
    {
        $dataBag = [];

        self::pageLoader();

        if (!$this->configData['authentication']['is_enabled']) {
            Session::put('logViewerAdminAccessEnabled', 'NO');
        }

        if (!Session::has('logViewerAdminAccessEnabled')) {
            Session::put('logViewerAdminAccessEnabled', 'NO');
        }

        $dataBag['config_data'] = $this->configData;
        $dataBag['is_logs_writable'] = self::isLogsWritable();
        $dataBag['logs_permission'] = self::logsPermission();
        if (self::isFileExtensionEnabled()) {
            $dataBag['log_files'] = $this->logFiles('file');
        } else {
            $dataBag['log_files'] = $this->logFiles();
        }
        $dataBag['log_channel'] = $this->log_channel;
        $dataBag['today_log'] = 'laravel-' . date('Y-m-d') . '.log';
        return view('logviewer::index', $dataBag);
    }

    public function adminAccess(Request $request)
    {
        $rules = [
            'onexloginfo_loginid' => 'required',
            'onexloginfo_password' => 'required'
        ];

        $ruleMsgs = [
            'onexloginfo_loginid.required' => 'Please enter login-id',
            'onexloginfo_password.required' => 'Please enter password'
        ];

        $validation = Validator::make($request->all(), $rules, $ruleMsgs);

        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation, 'accessValiError')
                ->withInput($request->all())
                ->with('onexValiErrMsg', 'Please enter login-id & password');
        }

        if ($request->input('onexloginfo_loginid') == $this->configData['authentication']['login_id'] && $request->input('onexloginfo_password') == $this->configData['authentication']['password']) {
            Session::put('logViewerAdminAccessEnabled', 'YES');
            return redirect()->back()->with('onexSuccessMsg', 'Access Granted');
        } else {
            return redirect()->back()->with('onexAccessErrsMsg', 'Access Denied! Wrong login-id & password');
        }
        
    }

    public static function pageLoader()
    {
        header( 'Content-type: text/html; charset=utf-8' );
        header("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        ob_implicit_flush(1);
        @ini_set('implicit_flush', 1);
        @ob_end_clean();
        set_time_limit(0);
        echo "<div class='page-loader' style='width: 400px; text-align: center; margin:100px auto;'>";
            echo "<p style='font-size: 24px; font-weight: 500; word-spacing: 6px;'>logs are loading</p>";
            echo "<p style='font-size: 24px; font-weight: 500; word-spacing: 6px; margin-top: -16px;'>...Please Wait...</p>";
        echo "</div>";
        if (ob_get_level() > 0) ob_end_flush();
    }

    public function logFiles($using = 'glob')
    {
        if (!is_dir($this->logsPath)) {
            return 'logs - directory does not exist into the storage folder.';
        }
        if ($using == 'file') {
            $filesArr = self::fileNamesByFile($this->logsPath);
        } elseif ($using == 'scan') {
            $filesArr = self::fileNamesByscanDir($this->logsPath);
        } else {
            $filesArr = self::fileNamesByGlob($this->logsPath);
        }
        return self::createFileArr($filesArr, $using, $this->logsPath);
    }

    public static function createFileArr($filesArr = [], $using = 'glob', $path)
    {
        $logFiles = [];
        if (!empty($filesArr) && count($filesArr)) {
            if ($using == 'file') {
                foreach ($filesArr as $k => $v) {
                    $arr = [];
                    $arr['name'] = $v->getFilename();
                    $arr['path'] = $v->getPathname();
                    $arr['size'] = $v->getSize();
                    $arr['readable_size'] = self::convertToReadableSize($arr['size']);
                    $arr['extension'] = $v->getExtension();
                    $arr['created_at'] = date('Y-m-d H:i:s', $v->getCTime());
                    $arr['updated_at'] = date('Y-m-d H:i:s', $v->getMTime()); 
                    $arr['is_readable'] = is_readable($arr['path']);
                    array_push($logFiles, $arr);
                }
                if (!empty($logFiles[0]) && count($logFiles) > 1) {
                    $logFiles = array_reverse($logFiles);
                    $tempFile = $logFiles[0];
                    unset($logFiles[0]);
                    array_push($logFiles, $tempFile);
                }
            } else {
                foreach ($filesArr as $k => $v) {
                    $arr = [];
                    $pathInfo = pathinfo($v);
                    $arr['name'] = basename($v);
                    $arr['path'] = (!empty($pathInfo['dirname']) && $pathInfo['dirname'] == '.') ? $path . '/' . $v : $v;
                    $arr['size'] = filesize($arr['path']);
                    $arr['readable_size'] = self::convertToReadableSize($arr['size']);
                    $arr['extension'] = !empty($pathInfo['extension']) ? $pathInfo['extension'] : '';
                    $fileStats = stat($arr['path']);
                    $arr['created_at'] = !empty($fileStats['ctime']) ? date('Y-m-d H:i:s', $fileStats['ctime']) : '';
                    $arr['updated_at'] = !empty(filemtime($arr['path'])) ? date('Y-m-d H:i:s', filemtime($arr['path'])) : ''; 
                    $arr['is_readable'] = is_readable($arr['path']);
                    array_push($logFiles, $arr);
                }
            }
        }
        return $logFiles;
    }

    public static function isLogsWritable()
    {
        return is_writable(storage_path('logs')) ? true : false;
    }

    public static function logsPermission()
    {
        return substr(decoct(fileperms(storage_path('logs'))), -4);
    }

    public static function isFileExtensionEnabled()
    {
        return extension_loaded('fileinfo');
    }
    
    public static function fileNamesByFile($dirPath = '')
    {
        if (empty($dirPath)) {
            return 'logs - directory does not exist for scan.';
        }
        return File::files($dirPath);
    }

    public static function fileNamesByscanDir($dirPath = '')
    {
        $fileNamesArr = [];
        if (empty($dirPath)) {
            return 'logs - directory does not exist for scan.';
        }
        $scan = scandir($dirPath, SCANDIR_SORT_DESCENDING);
        if (!empty($scan)) {
            $count = 1;
            foreach ($scan as $k => $v) {
                if (!is_dir($v) && $count <= 100) {
                    $ext = explode('.', $v);
                    if (!empty($ext)) {
                        if (!empty(end($ext)) && end($ext) == 'log') {
                            array_push($fileNamesArr, $v);
                            $count++;
                        }
                    }
                }
            }
        }
        return self::resetFileNamesArr($fileNamesArr);
    }

    public static function fileNamesByGlob($dirPath = '')
    {
        $fileNamesArr = [];
        if (empty($dirPath)) {
            return 'logs - directory does not exist for scan.';
        }
        $fileNamesArr = glob($dirPath . '/*.log');
        if (!empty($fileNamesArr) && count($fileNamesArr)) {
            $fileNamesArr = array_reverse($fileNamesArr);
            $fileNamesArr = array_slice($fileNamesArr, 0, 100);
        }
        return self::resetFileNamesArr($fileNamesArr);
    }

    public static function resetFileNamesArr($fileNamesArr = [])
    {
        if (!empty($fileNamesArr) && count($fileNamesArr)) {
            if (in_array('laravel.log', $fileNamesArr) && count($fileNamesArr) > 1 && $fileNamesArr[0] == 'laravel.log') {
                unset($fileNamesArr[0]);
                array_push($fileNamesArr, 'laravel.log');
            }
            if (count($fileNamesArr) > 1 && !empty($fileNamesArr[0]) && strpos($fileNamesArr[0], 'laravel.log') !== false) {
                $tempFile = $fileNamesArr[0];
                unset($fileNamesArr[0]);
                array_push($fileNamesArr, $tempFile);
            }
        }
        return $fileNamesArr;
    }

    public static function convertToReadableSize($size = null)
    {
        if (empty($size)) {
            return '';
        }
        $base = log($size) / log(1024);
        $suffix = array("Byte", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) . ' ' . $suffix[$f_base];
    }

    public function logout(Request $request)
    {
        Session::put('logViewerAdminAccessEnabled', 'NO');
        Session::forget('logViewerAdminAccessEnabled');
        return redirect()->route('cssLogViewer.index')->with('onexSuccessMsg', 'Logout!');
    }

    public function viewLogs(Request $request, $file)
    {
        $dataBag = [];
        $dataBag['config_data'] = $this->configData;
        $dataBag['is_filesize_over'] = false;
        $dataBag['file_name'] = $file;

        if (empty($file)) {
            return back();
        }

        if (!file_exists($this->logsPath . '/' . $file)) {
            abort(404, 'Sorry! Log File Not Exist');
        }

        self::pageLoader();

        if (fileSize($this->logsPath . '/' . $file) > $this->maxReadLength) {
            $dataBag['is_filesize_over'] = true; 
            return view('logviewer::viewer', $dataBag);
        }

        $logsData = [];
        $fileContent = file_get_contents($this->logsPath . '/' . $file);
        preg_match_all($this->regxLogs, $fileContent, $matches);
        if (!empty($matches[0]) && is_array($matches[0]) && count($matches[0])) {
            foreach ($matches[0] as $k => $v) {
                preg_match($this->regxEachLog, $v, $extractLogs);
                if (!empty($extractLogs) && count($extractLogs)) {
                    array_push($logsData, $extractLogs);
                }
            }
            if (!empty($logsData)) {
                $logsData = array_reverse($logsData);
            }
        }
        $dataBag['logs_data'] = $logsData;
        return view('logviewer::viewer', $dataBag);
    }

    public function downloadLogs(Request $request, $file)
    {
        if (empty($file)) {
            return back();
        }

        if (!file_exists($this->logsPath . '/' . $file)) {
            abort(404, 'Sorry! Log File Not Exist');
        }

        $headers = [
            'Content-Type: text/plain',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ];
        
        if (function_exists('response')) {
            return response()->download($this->logsPath . '/' . $file, $file, $headers);
        }

        return app('\Illuminate\Support\Facades\Response')->download($this->logsPath . '/' . $file, $file, $headers);
    }

    public function clearLogs(Request $request, $file)
    {
        if (empty($file)) {
            return back();
        }

        if (!file_exists($this->logsPath . '/' . $file)) {
            abort(404, 'Sorry! Log File Not Exist');
        }

        file_put_contents($this->logsPath . '/' . $file, "");

        return back()->with('success_msg', $file . ' - logs has been cleared successfully');
    }

    public function deleteLogs(Request $request, $file)
    {
        if (empty($file)) {
            return back();
        }

        if (!file_exists($this->logsPath . '/' . $file)) {
            abort(404, 'Sorry! Log File Not Exist');
        }

        unlink($this->logsPath . '/' . $file);

        return back()->with('success_msg', $file . ' - logs has been deleted successfully');
    }

    public function bulkAction(Request $request)
    {
        $reqData = $request->all();
        if (!empty($reqData['bulk_action']) && $reqData['bulk_action'] == 'Delete All') {
            if (!empty($reqData['ischeckme']) && count($reqData['ischeckme'])) {
                self::deleteFiles($reqData['ischeckme'], $this->logsPath);
                return back()->with('success_msg', 'Selected logs has been deleted successfully');
            } else {
                self::deleteFiles('ALL', $this->logsPath);
                return back()->with('success_msg', 'All logs has been deleted successfully');
            }
        }

        if (!empty($reqData['bulk_action']) && $reqData['bulk_action'] == 'Download As Zip') {
            if (!empty($reqData['ischeckme']) && count($reqData['ischeckme'])) {
                $zip = new \ZipArchive();
                $zipFile = env('APP_NAME') . '-logs-' . date('Y-m-d') . '.zip';
                if (file_exists(public_path($zipFile))) {
                    unlink(public_path($zipFile));
                }
                if ($zip->open(public_path($zipFile), \ZipArchive::CREATE) || $zip->open(public_path($zipFile), \ZipArchive::OVERWRITE)) {
                    $allFiles = $reqData['ischeckme'];
                    foreach ($allFiles as $file) {
                        $file = $this->logsPath . '/' . $file;
                        if (is_file($file)) {
                            $zip->addFile($file, basename($file));
                        }
                    }
                    $zip->close();
                    if (function_exists('response')) {
                        return response()->download(public_path($zipFile));
                    }
                    return app('\Illuminate\Support\Facades\Response')->download(public_path($zipFile));
                }
            } else {
                $zip = new \ZipArchive();
                $zipFile = env('APP_NAME') . '-all-logs-' . date('Y-m-d') . '.zip';
                if (file_exists(public_path($zipFile))) {
                    unlink(public_path($zipFile));
                }
                if ($zip->open(public_path($zipFile), \ZipArchive::CREATE) || $zip->open(public_path($zipFile), \ZipArchive::OVERWRITE)) {
                    $allFiles = glob($this->logsPath . '/*.log');
                    foreach ($allFiles as $file) {
                        $zip->addFile($file, basename($file));
                    }
                    $zip->close();
                    if (function_exists('response')) {
                        return response()->download(public_path($zipFile));
                    }
                    return app('\Illuminate\Support\Facades\Response')->download(public_path($zipFile));
                }
            }
        }
        return back();
    }

    public static function deleteFiles($reqData, $path)
    {
        if (!empty($reqData) && $reqData == 'ALL') {
            $allFiles = glob($path . '/*.log');
            if (!empty($allFiles)) {
                foreach ($allFiles as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
        if (!empty($reqData) && is_array($reqData)) {
            foreach ($reqData as $file) {
                if (is_file($path . '/' . $file)) {
                    unlink($path . '/' . $file);
                }
            }
        }
    }
}
