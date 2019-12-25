<div class="form-group">
    <label class="col-sm-3 control-label">{{ $field['label'] or ''}}</label>

    <div class="col-sm-6">
        <ul style="display: inline-block;list-style-type: none;padding:0; margin:0;">

          @foreach($field['boxes'] as $key => $value)

            <li class="checkbox" style="display: inline-block; min-width: 155px;">
              <label>
                <input type="checkbox" name="{{ $field['name'] or '' }}" value="{{ $key }}" {{ ( $field['value'] ==  $value) ? 'checked':'' }}> {{ $value }}
              </label>
            </li>

          @endforeach
        </ul>
    </div>

    <div class="col-sm-3">
	    <small>{{ $field['hint'] or "" }}</small>
	</div>
</div>

