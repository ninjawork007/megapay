<div class="form-group">
  <label for="inputEmail3" class="col-sm-3 control-label">{{ $field['label'] }}</label>
  <div class="col-sm-6">
    <input type="text" name="{{ $field['name'] }}" class="form-control {{ $field['class'] or ''}}" id="{{ $field['id'] or $field['name'] }}" value="{{ @$_POST[$field['name']] or @$field['value'] }}" placeholder="{{ @$field['label'] }}" {{ @$field['readonly']=='true'?'readonly':'' }}>

    <span class="text-danger">{{ $errors->first($field['name']) }}</span>
  </div>

  <div class="col-sm-3">
    <small>{{ $field['hint'] or "" }}</small>
  </div>

</div>

@push('scripts')
<script type="text/javascript">
  $(function () {
    $("#"+"{{ $field['name'] }}").datepicker({
      minDate: '{{ date("d-m-Y") }}',
      dateFormat : 'dd-mm-yy',
      beforeShow: function(input, inst) {
        inst.dpDiv.css({"z-index":999999});
      },
    });

  });
</script>
@endpush