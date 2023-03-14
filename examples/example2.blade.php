<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

    <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.3/r-2.4.0/rg-1.3.0/datatables.min.css"
        rel="stylesheet" />

    <script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.3/r-2.4.0/rg-1.3.0/datatables.min.js"></script>

</head>

<body>
    <div id="check">
        <table id="table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Title</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</body>
<script>
    $(document).ready(function() {
        $('#table').DataTable({
            processing: true,
            serverSide: true,
            paging: true,
            ajax: {
                url: "{{ route('yajra') }}",
                data: {

                    table_name: 'posts',
                    joins : [
                        {
                            table: 'users',
                            on: 'users.id',
                            to: 'posts.user_id'
                        },
                        {
                            table: 'comments',
                            on: 'comments.post_id',
                            to: 'posts.id'
                        }
                    ],
                    
                }
            },
            columns: [
                {
                    data: 'id',
                    name: 'posts.id',
                },
                {
                    data: 'name',
                    name: 'users.name',
                },
                {
                    data: 'email',
                    name: 'users.email',
                },
                {
                    data: 'created_at',
                    name: 'users.created_at',
                },
                {
                    data: 'updated_at',
                    name: 'users.updated_at',
                },
                {
                    data: 'title',
                    name: 'posts.title',
                },
                {
                    data: 'comment',
                    name: 'comments.comment',
                },
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return '<input type="button" id="' + row.id +
                            '" class="btn btn-warning" value="Edit"></input> ' +
                            ' <input type="button"  id="' + row.id +
                            '" class="btn btn-danger" value="Delete"></input>';
                    }
                },
            ]
        });
    });
</script>

</html>
