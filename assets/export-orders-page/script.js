$(document).ready(function () {
    main_table = $('#oper_table').DataTable({
        "iDisplayLength": '2000',
        "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
        dom: 'Blfrtip',
        buttons: [
            'excel', 'pdf', 'print'
        ],
        "language": {
            "search": "Поиск по строкам:"
        }
    });
});