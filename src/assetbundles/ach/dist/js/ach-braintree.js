$(document).ready(()=>{
    $('#payment-method-braintree-ach #paymentForm-braintree-validate-account').on('click', (e)=>{
        e.preventDefault();
        braintree.client.create({
            authorization: $('#payment-method-braintree-ach #paymentForm-braintree-gatewayToken').val()
        }, function (clientErr, clientInstance) {
            
            if (clientErr) {
                console.error('There was an error creating the Client.');
                throw clientErr;
            } 

            braintree.usBankAccount.create({
                client: clientInstance,
            }, function (usBankAccountErr, usBankAccountInstance) {
                if (usBankAccountErr) {
                    console.error('There was an error creating the USBankAccount instance.');
                    throw usBankAccountErr;
                }

                var bankDetails = {
                    accountNumber: $('#payment-method-braintree-ach #paymentForm-braintree-account-number').val(),
                    routingNumber: $('#payment-method-braintree-ach #paymentForm-braintree-routing-number').val(),
                    accountType: $('#payment-method-braintree-ach #paymentForm-braintree-account-type').val(),
                    ownershipType: $('#payment-method-braintree-ach #paymentForm-braintree-ownership-type').val(),
                    billingAddress: {
                        streetAddress: $('#payment-method-braintree-ach #paymentForm-braintree-billing-street-address').val(),
                        extendedAddress: $('#payment-method-braintree-ach #paymentForm-braintree-billing-extended-address').val(),
                        locality: $('#payment-method-braintree-ach #paymentForm-braintree-billing-locality').val(),
                        region: $('#payment-method-braintree-ach #paymentForm-braintree-billing-region').val(),
                        postalCode: $('#payment-method-braintree-ach #paymentForm-braintree-billing-postal-code').val()
                    }
                };

                if (bankDetails.ownershipType === 'personal') {
                    bankDetails.firstName = $('#payment-method-braintree-ach #paymentForm-braintree-first-name').val();
                    bankDetails.lastName = $('#payment-method-braintree-ach #paymentForm-braintree-last-name').val();
                } else {
                    bankDetails.businessName = $('#payment-method-braintree-ach #paymentForm-braintree-business-name').val();
                }

                usBankAccountInstance.tokenize({
                    bankDetails: bankDetails,
                    mandateText: 'By clicking ["Checkout"], I authorize Braintree, a service of PayPal, on behalf of [your business name here] (i) to verify my bank account information using bank information and consumer reports and (ii) to debit my bank account.'
                }, function (tokenizeErr, tokenizedPayload) {
                    if (tokenizeErr) {
                        console.error('There was an error tokenizing the bank details.');
                        throw tokenizeErr;
                    }

                    console.log(tokenizedPayload.nonce);
                    
                    if(tokenizedPayload.nonce){
                        $.ajax({
                            url:'/actions/commerce-braintree/us-bank-verification/verify-bank-account',
                            data: {
                                nonce: tokenizedPayload.nonce,
                                gateway: 'braintree'
                            },
                            type: 'POST',
                            dataType: 'json'
                        })
                        .done(function(response){
                            console.log(response);
                            if(response.success){
                                $('#payment-method-braintree-ach #paymentForm-braintree-token').val(response.data.token);
                                $('#paymentForm-braintree-validate-account').addClass('d-none');
								$('#pay-ach').attr('disabled', false);
                                $('#pay-ach').removeClass('d-none');
                                $.each($('#paymentForm-braintree-new-ach-container input, #paymentForm-braintree-new-ach-container select'), function( index, element ) {
                                    $(element).prop('disabled', true);
                                });
                            }
                                
                        })
                        .fail(function(error) {
                            console.log('Error:', error);
                        })
                    }
                });
            });
        });
    });

    $('#paymentForm-braintree-saved-account').on('change', (e) => {
        e.preventDefault();
        let value = $(e.currentTarget).val();
        if(value != '--'){
            $('#payment-method-braintree-ach #paymentForm-braintree-token').val(value);
            enableAch();
        }else{
            disableAch();
        }
    });

    $('#process-transaction').on('click', (e) => {
        e.preventDefault();
    });

    $('#paymentForm-braintree-add-new-account').on('click', (e) => {
        e.preventDefault();
        $('#paymentForm-braintree-new-ach-container').removeClass('d-none');
        $('#paymentForm-braintree-select-ach-container').addClass('d-none');
        $(e.currentTarget).addClass('d-none');
        disableAch();
    });

    function enableAch(){
        $('#pay-ach').attr('disabled', false);
        $('#pay-ach').removeClass('d-none');
    }

    function disableAch(){
        $('#pay-ach').attr('disabled', true);
        $('#pay-ach').addClass('d-none');
    }

    
});