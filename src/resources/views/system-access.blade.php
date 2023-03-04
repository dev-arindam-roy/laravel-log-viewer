<div class="onexsysinfo-login">
    @if(Session::has('onexValiErrMsg') && !empty(Session::get('onexValiErrMsg')))
        <div class="error-box text-center">{{ Session::get('onexValiErrMsg') }}</div>
    @endif
    @if(Session::has('onexAccessErrsMsg') && !empty(Session::get('onexAccessErrsMsg')))
        <div class="error-box text-center">{{ Session::get('onexAccessErrsMsg') }}</div>
    @endif
    <form name="onexfrm" action="{{ route('onexloginfoAdminaccess') }}" method="POST">
    {{ csrf_field() }}
    <div class="field-box">
        <div class="text-left"><label>Login ID:</label></div>
        <input type="text" class="onex-field" name="onexloginfo_loginid" placeholder="Login ID" required="required" value="{{ old('onexloginfo_loginid') }}">
        @if($errors->accessValiError->has('onexloginfo_loginid'))<div class="error-box text-left">{{ $errors->accessValiError->first('onexloginfo_loginid') }}</div>@endif
    </div>
    <div class="field-box">
        <div class="text-left"><label>Password:</label></div>
        <input type="password" class="onex-field" name="onexloginfo_password" placeholder="Password" required="required">
        @if($errors->accessValiError->has('onexloginfo_password'))<div class="error-box text-left">{{ $errors->accessValiError->first('onexloginfo_password') }}</div>@endif
    </div>
    <div class="field-box text-center">
        <button type="submit" name="okbtn" class="onexsubbtn">Get Access</button>
    </div>
    </form>
</div>