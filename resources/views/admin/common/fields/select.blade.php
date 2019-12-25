<div class="form-group">
  <label for="inputEmail3" class="col-sm-3 control-label">{{ $field['label'] or ''}}</label>
  <div class="col-sm-6">
    <select class="form-control {{ $field['class'] or '' }}" id="{{ $field['id'] or $field['name']}}" name="{{ $field['name'] }}">

        @foreach ($field['options'] as $key => $value)
          <option value='{{ $key }}' {{ ( !empty($field['value']) && $field['value'] ==  $value  ) ? 'selected': NULL }}> {{ $value }}</option>
        @endforeach

    </select>
  </div>

  <div class="col-sm-3">
    <small>{{ $field['hint'] or "" }}</small>
  </div>
</div>