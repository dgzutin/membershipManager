/**
 * Created by garbi on 12.02.17.
 */

api_version = "v1";

var counter = 1;
var limit = 100;

function addInputs(divName)
{
    if (counter == limit)  {
        alert("You have reached the limit of adding " + counter + " inputs");
    }
    else {
        var newdiv = document.createElement('div');
        newdiv.innerHTML = ' <div id="'+counter+'" class="form-group">'+
            '<div class="row">'+
            '<div class="col-xs-6 col-md-6">'+
            '<input type="text" class="form-control" name="itemName[]" required>'+
            '</div>'+
            '<div class="col-xs-3 col-md-3">'+
            '<input type="number" step="0.01" class="form-control" name="unitPrice[]" required>'+
            '</div>'+
            '<div class="col-xs-2 col-md-2">'+
            '<input type="number" class="form-control" name="quantity[]" required>'+
            '</div>'+
            '<div class="col-xs-1 col-md-1">'+
            '<span onclick="removeInputs('+counter+')"  class="fa fa-trash"></span>'+
            '</div>'+
            '</div>'+
            '</div>';

        document.getElementById(divName).appendChild(newdiv);
        counter++;
    }
}

function removeInputs(id)
{
    document.getElementById(id).remove();
}

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