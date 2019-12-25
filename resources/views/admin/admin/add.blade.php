@php 
$form_data = [
    'page_title'=> 'Add Admin User Form',
    'page_subtitle'=> 'Add Admin', 
    'form_name' => 'Admin Add Form',
    'action' => URL::to('/').'/admin/add_admin',
    'fields' => [
      ['type' => 'text', 'class' => 'validate_field', 'label' => 'Username', 'name' => 'username', 'value' => ''],
      ['type' => 'text', 'class' => 'validate_field', 'label' => 'Email', 'name' => 'email', 'value' => ''],
      ['type' => 'password', 'class' => 'validate_field', 'label' => 'Password', 'name' => 'password', 'value' => ''],
      ['type' => 'select', 'options' =>$roles, 'class' => 'validate_field', 'label' => 'Role', 'name' => 'role', 'value' => ''],
      ['type' => 'select', 'options' => ['Active' => 'Active', 'Inactive' => 'Inactive'], 'class' => 'validate_field', 'label' => 'Status', 'name' => 'status', 'value' => ''],
    ]
  ];
@endphp
@include("admin.common.form.primary", $form_data)