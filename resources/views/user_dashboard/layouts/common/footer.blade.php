<footer>
  <div class="container">
      <div class="row">
          <div class="col-md-12">
            <?php
              $company_name = getCompanyName();
            ?>
            <p class="copyright">@lang('message.footer.copyright') &copy; {{date('Y')}} &nbsp;&nbsp; {{ $company_name }} | @lang('message.footer.copyright-text')</p>
          </div>
      </div>
  </div>
</footer>

<!-- Delete Modal -->
<div class="modal fade" id="delete-warning-modal" role="dialog" style="z-index:1060; color: light blue;">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="width:100%;height:100%; background-color: aliceblue;">
            <div style="display: block" class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to delete this Data ?</strong></p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-danger" id="delete-modal-yes" href="javascript:void(0)">@lang('message.form.yes')</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('message.form.no')</button>
            </div>
        </div>
    </div>
</div>

