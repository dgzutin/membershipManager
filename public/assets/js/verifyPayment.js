
api_version = 'v1';
redirectUrl = $('#receiptUrl').val();

function verifyInvoiceFullPayment()
{
    var params = {
        invoiceId: $('#invoiceId').val()
    };

    console.log(params);

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api-user/"+api_version+"/verifyInvoiceFullPayment",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){

            console.log(result);

            if( result.exception == false){

                if( result.paid == false){

                    setTimeout(verifyInvoiceFullPayment, 2000);
                }
                else{
                    $("#message").html('<h3>Your payment was successfully verified! A receipt was mailed to you. </h3>' +
                        '<a href="'+$('#receiptUrl').val()+'">Click here to view your receipt</a>');

                    setTimeout(function(){
                        window.location.href = redirectUrl;
                    }, 2000);

                }
            }
            else{

                $("#message").html('<h3>'+result.message+'</h3>');

                setTimeout(function(){
                    window.location.href = redirectUrl;
                }, 2000);
            }

            // location.reload();
        }});
}/**
 * Created by garbi on 22.02.17.
 */
