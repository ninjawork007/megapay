<div class="form-group">
    <label class="col-md-3 control-label">{{$field['label']}}</label>
    <div class="col-md-3">
        <select class="form-control date-change" data-rel='{{$field['id']?$field['id']:$field['name']}}' name='day' id='day'>
            <option value=''>Day</option>
            @for($i=1; $i<=31; $i++)
                <option value='{{$i}}' {{ isset($field['value']) && $i == date('d', strtotime($field['value']))?'selected':''}}>{{$i}}</option>
            @endfor
        </select>
        <span class="help-block">{{isset($field['hint'])?$field['hint']:''}}</span>
        <span style="color:red">{{ echo form_error($field['name']); }}</span>
    </div>
    <div class="col-md-3">
        <select class="form-control date-change" data-rel='{{$field['id']?$field['id']:$field['name']}}' name='month' id='month'>
            <option value=''>Month</option>
            @for($i=1; $i<=12; $i++)
                <option value='{{$i}}' {{ isset($field['value']) && $i == date('m', strtotime($field['value']))?'selected':''}}>{{$i}}</option>
            @endfor
        </select>
    </div>
    {{--*/ $year = date('Y') /*--}}
    <div class="col-md-3">
        <select class="form-control date-change" data-rel="{{$field['id']?$field['id']:$field['name']}}" name='year' id='year'>
            <option value=''>Year</option>
            @for($i=$year-10; $i>=$year-100; $i--)
                <option value="{{$i}}" {{ isset($field['value']) && $i == date('Y', strtotime($field['value']))?'selected':''}}>{{$i}}</option>
            @endfor
        </select>
    </div>
    <input type='hidden' name="{{$field['name']}}" id="{{$field['id']?$field['id']:$field['name']}}" value="{{isset($_POST[$field['name']])?$_POST[$field['name']]:$field['value']}}">
    
</div>