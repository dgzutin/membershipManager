/**
 * Created by garbi on 12.02.17.
 */

api_version = "v1";

function deleteInvoices(ids)
{
    var params = {
        invoiceIds: ids
    };

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/"+api_version+"/deleteInvoices",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){


            for (i=0; i<result.results.length; i++) {

                console.log(result.results[i]);

                if( result.results[i].exception == false){
                    notify('alert-info', result.results[i].message);
                    //delete table row
                    $('#tableRow_'+result.results[i].invoiceId).remove();

                }
                else{
                    notify('alert-danger', result.results[i].message);
                }

            }
            // location.reload();
        }});
}

function confirmInvoiceDelete(id){
    $( "#dialog-confirm_delete" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Delete Invoice": function() {

                //delete article
                deleteInvoices([id]);
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}