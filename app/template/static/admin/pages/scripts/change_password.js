var ChangePass = function() {

    var handleChangePass = function() {

        function format(state) {
            if (!state.id) return state.text; // optgroup
            return "<img class='flag' src='../../assets/global/img/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
        }

        $('.changepass-form').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: {

                password: {
                    required: true,
                    minlength: 6
                },
                rpassword: {
                    required: true,
                    equalTo: "#changepass_password",
                    minlength: 6
                },
            },

            messages: { // custom messages for radio buttons and checkboxes
                tnc: {
                    required: "Please accept TNC first."
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit   

            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function(error, element) {
                if (element.attr("name") == "tnc") { // insert checkbox errors after the container                  
                    error.insertAfter($('#changepass_tnc_error'));
                } else if (element.closest('.input-icon').size() === 1) {
                    error.insertAfter(element.closest('.input-icon'));
                } else {
                    error.insertAfter(element);
                }
            },

            submitHandler: function(form) {
                form.submit();
            }
        });

        $('.changepass-form input').keypress(function(e) {
            if (e.which == 13) {
                if ($('.changepass-form').validate().form()) {
                    $('.changepass-form').submit();
                }
                return false;
            }
        });

        jQuery('#changepass-btn').click(function() {
            jQuery('.changepass-form').show();
        });

        jQuery('#changepass-back-btn').click(function() {
            jQuery('.changepass-form').hide();
        });
    }

    return {
        //main function to initiate the module
        init: function() {
            handleChangePass();
        }
    };
}();