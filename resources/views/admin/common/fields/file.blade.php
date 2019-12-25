<div class="form-group">
  <label for="inputEmail3" class="col-sm-3 control-label">{{ $field['label'] or ''}}</label>
  <div class="col-sm-6">
    <input type="file" name="{{ $field['name'] or '' }}" class="form-control {{ $field['class'] or ''}}" id="{{ $field['id'] or $field['name'] }}" value="{{ isset($_POST[$field['name']])?@$_POST[$field['name']]:@$field['value'] }}" placeholder="{{ @$field['label'] }}" {{ isset($field['readonly']) && $field['readonly']=='true'?'readonly':'' }}>
    <span class="text-danger">{{ $errors->first($field['name']) }}</span>
  	{!! isset($field['image'])?'<img style="max-width:150px;padding-top:5px;" src="'.$field['image'].'">':'' !!}
  </div>
  <div class="col-sm-3">
    <small>{{ $field['hint'] or "" }}</small>
  </div>
</div>