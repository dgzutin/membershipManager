/**
 * Created by garbi on 14.02.17.
 * Manage invoice payments
 */


function deletePayments(ids)
{
    var params = {
        ids: ids
    };

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/"+api_version+"/deletePayments",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){


            for (i=0; i<result.results.length; i++) {

                console.log(result.results[i]);

                if( result.results[i].exception == false){
                    $("#notification").notify(
                        result.results[i].message,
                        {
                            position:"right center",
                            className: "success" }
                    );

                    $('#myModal').modal('hide');
                    //delete table rows
                    $('#mainRow_'+result.results[i].paymentId).remove();
                    $('#actionsRow_'+result.results[i].paymentId).remove();
                    $('#gatewayInfoRow_'+result.results[i].paymentId).remove();
                    $('#noteRow_'+result.results[i].paymentId).remove();

                }
                else{
                    $("#notification").notify(
                        result.results[i].message,
                        {
                            position:"right center",
                            className: "warn" }
                    );
                }

            }

            $('#amountPaid').val(result.newOutstandingAmount);
            // location.reload();
        }});
}

function addPayment(result)
{
    var params = {
        invoiceId:  $('#invoiceId').val(),
        note:  $('#note').val(),
        amountPaid:  $('#amountPaid').val(),
        paymentMode: $('#paymentMode').val(),
        datePaid: $('#datePaid').val(),
        sendReceipt: $('#sendReceipt').is(":checked")

    };

    console.log(params);

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/"+api_version+"/addPayment",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){

            console.log(result);

            if( result.exception == false){
                $("#notification").notify(
                    result.message,
                    {
                        position:"right center",
                        className: "success" }
                );
                //add table rows
                $('#payments').append('<tr class="" id="mainRow_'+result.paymentId+'" style="background-color: #cccccc">' +
                    '<td>'+result.paymentId+'</td>' +
                    '<td>'+result.datePaid+'</td>' +
                    '<td>'+result.currency+' '+result.amountPaid+'</td>' +
                    '<td>'+result.paymentMode+'</td>' +
                    '<td><a onclick="confirmAndDeletePayment(['+result.paymentId+'])"><span class="fa fa-trash-o"> </span></a></td>' +
                    '</tr>' +
                    '<tr id="actionsRow_'+result.paymentId+'" style="border: hidden">' +
                    '<td colspan="6"><strong>Actions log:</strong><pre class="prettyprint"><code>'+result.systemMessage+'</code></pre></td>' +
                    '</tr>' +
                    '<tr id="noteRow_'+result.paymentId+'" style="border: hidden">' +
                    '<td colspan="6"><strong>Internal note:</strong><p class="text-muted" style="font-size: smaller">'+result.paymentNote+'</p></td>' +
                    '</tr>');

                // add modal dialog

                $('#amountPaid').val(result.newOutstandingAmount);

            }
            else{
                $("#notification").notify(
                    result.message,
                    {
                        position:"right center",
                        className: "warn" }
                );
            }

        }});

}

function confirmAndDeletePayment(ids)
{

    $( "#dialog-confirm" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Delete payment": function() {

                deletePayments(ids);

                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}