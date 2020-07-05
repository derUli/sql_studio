$(function () {
    $("*[data-sql]").click(
            (event) => {
        event.preventDefault();
        event.stopPropagation();
        const target = $(event.target);
        const sql = $(target).data("sql");
        const execute = $(target).data("execute");
        const editor = $("#sql_code").next('.CodeMirror').get(0).CodeMirror;
        editor.getDoc().setValue(sql);
        editor.save();
        if (execute) {
            $("#btn-execute").click();
        }
    });

    $("#btn-execute").click(
            (event) => {
        event.preventDefault();
        event.stopPropagation();

        const editor = $("#sql_code").next('.CodeMirror').get(0).CodeMirror
        editor.save();
        $("#sql_code").text(editor.getDoc().getValue())
        $("#result-spinner").show();
        $("#result-data").hide();

        const target = $(event.target);

        const form = $(target).closest("form");
        const url = form.attr("action");
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function (response) {
                $("#result-data").html(response)
                $("#result-data .tablesorter").DataTable({
                    language: {
                        url: $("body").data("datatables-translation")
                    }
                });
                $("#result-data").slideDown();
            },
            error: function (jqXHR, exception) {
                if (jqXHR.status === 0) {
                    bootbox.alert('Not connected.\n Verify Network.');
                } else if (jqXHR.status == 401) {
                    bootbox.alert('Unauthorizend. [401]');
                } else if (jqXHR.status == 403) {
                    bootbox.alert('Forbidden. [403]');
                } else if (jqXHR.status == 404) {
                    bootbox.alert('Requested page not found. [404]');
                } else if (jqXHR.status == 500) {
                    bootbox.alert('Internal Server Error [500].');
                } else if (exception === 'parsererror') {
                    bootbox.alert('Requested JSON parse failed.');
                } else if (exception === 'timeout') {
                    bootbox.alert('Time out error.');
                } else if (exception === 'abort') {
                    bootbox.alert('Ajax request aborted.');
                } else {
                    bootbox.alert('Uncaught Error.\n'
                            + jqXHR.responseText);
                }
            },
            complete: function (jqXHR, textStatus) {
                $("#result-spinner").hide();
            }
        });
    });
});
