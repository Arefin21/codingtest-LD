
<table id="datatable" class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
</table>
<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#datatable').DataTable({
             processing: true,
        serverSide: true,
        ajax: "{{ route('dashboard.users') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
        });

        // Open Edit Modal and Load Data
        $(document).on('click', '.editUser', function () {

             var userId = $(this).data('id');
            var userName = $(this).data('name');
            var userEmail = $(this).data('email');

            // Populate modal fields
            $('#edit_user_id').val(userId);
            $('#edit_user_name').val(userName);
            $('#edit_user_email').val(userEmail);

            // Clear previous validation errors
            $('#edit_user_name').removeClass('is-invalid');
            $('#edit_user_email').removeClass('is-invalid');
            $('#name_error').text('');
            $('#email_error').text('');

            // Show modal
            $('#editUserModal').modal('show');

        });

        // Update User via AJAX
        $('#editUserForm').on('submit', function (e) {
            e.preventDefault();

            var userId = $('#edit_user_id').val();
            var formData = {
                name: $('#edit_user_name').val(),
                email: $('#edit_user_email').val(),
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT'
            };

            // Clear previous validation errors
            $('#edit_user_name').removeClass('is-invalid');
            $('#edit_user_email').removeClass('is-invalid');
            $('#name_error').text('');
            $('#email_error').text('');

            $.ajax({
                url: "{{ route('dashboard.users.update', ':id') }}".replace(':id', userId),
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        // Close modal
                        $('#editUserModal').modal('hide');

                        // Reload DataTable
                        table.ajax.reload(null, false);

                        // Show success toast
                        toastr.success(response.message, 'Success', {
                            positionClass: 'toast-top-right',
                            timeOut: 3000
                        });
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        var errors = xhr.responseJSON.errors;

                        if (errors.name) {
                            $('#edit_user_name').addClass('is-invalid');
                            $('#name_error').text(errors.name[0]);
                        }

                        if (errors.email) {
                            $('#edit_user_email').addClass('is-invalid');
                            $('#email_error').text(errors.email[0]);
                        }

                        // Show error toast
                        toastr.error('Please fix the validation errors', 'Validation Error', {
                            positionClass: 'toast-top-right',
                            timeOut: 3000
                        });
                    } else {
                        // Other errors
                        toastr.error('An error occurred while updating the user', 'Error', {
                            positionClass: 'toast-top-right',
                            timeOut: 3000
                        });
                    }
                }
            });
        });
    });
</script>
