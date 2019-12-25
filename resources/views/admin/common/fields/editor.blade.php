<div class="form-group">
  <label for="inputEmail3" class="col-sm-3 control-label">{{ @$field['label'] }}</label>
  <div class="col-sm-6">
    <textarea id="{{ $field['id'] or $field['name'] }}" name="{{ @$field['name'] }}" placeholder="{{ @$field['label'] }}" rows="10" cols="80" class="form-control {{ @$field['class'] }}" {{ @$field['disabled']=='true'?'disabled':'' }}>{{ @$field['value'] }}</textarea>
    <span class="text-danger">{{ $errors->first(@$field['name']) }}</span>
  </div>
  <div class="col-sm-3">
    <small>{{ $field['hint'] or "" }}</small>
  </div>
</div>
@push('scripts')
<script type="text/javascript">
	$(function () {
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace("<?=@$field['name']?>");
  });
</script>
@endpush