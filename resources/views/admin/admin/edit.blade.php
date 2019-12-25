@php 
$form_data = [
    'page_title'=> 'Edit Admin User Form',
    'page_subtitle'=> 'Edit Admin', 
    'form_name' => 'Admin Edit Form',
    'action' => URL::to('/').'/admin/edit_admin/'.$result->id,
    'fields' => [
      ['type' => 'text', 'class' => 'validate_field', 'label' => 'Username', 'name' => 'username', 'value' => $result->username],
      ['type' => 'text', 'class' => 'validate_field', 'label' => 'Email', 'name' => 'email', 'value' => $result->email],
      ['type' => 'password', 'class' => 'validate_field', 'label' => 'Password', 'name' => 'password', 'value' => '', 'hint' => 'Enter new password only. Leave blank to use existing password.'],
      ['type' => 'select', 'options' =>$roles, 'class' => 'validate_field', 'label' => 'Role', 'name' => 'role', 'value' => $role_id],
      ['type' => 'select', 'options' => ['Active' => 'Active', 'Inactive' => 'Inactive'], 'class' => 'validate_field', 'label' => 'Status', 'name' => 'status', 'value' => $result->status],
    ]
  ];
@endphp
@include("admin.common.form.primary", $form_data)