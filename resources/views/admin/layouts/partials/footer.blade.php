<?php
$company_name = getCompanyName();
?>

<div class="pull-right hidden-xs">
    <b>Version</b> 2.3
</div>
<strong>Copyright &copy; {{date("Y")}} <a href="{{ route('dashboard') }}" target="_blank">{{ $company_name }}</a> | </strong> All rights reserved.

<!-- Delete Modal for buttons-->
<div class="modal fade" id="confirmDelete" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal for href-->
<div class="modal fade" id="delete-warning-modal" role="dialog" style="z-index:1060; color: light blue;">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="width:100%;height:100%; background-color: aliceblue;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to delete?</strong></p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-danger" id="delete-modal-yes" href="javascript:void(0)">Yes</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>