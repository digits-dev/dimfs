@if(is_null($value) || $value == '0000-00-00' || $value == '' || empty($value))
    
@else
    {{ date("Y-m-d", strtotime($value)) }}
@endif