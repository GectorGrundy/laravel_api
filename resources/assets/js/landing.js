$(document).ready(function () {
    App.init();
});

var TARGET_GEO_LIST = "{TARGET_GEO_LIST}",
    VISITOR_COUNTRY_ID = "{VISITOR_COUNTRY_ID}",
    NOT_SELECTED_TARGET_GEO = "{NOT_SELECTED_TARGET_GEO}",
    INCORRECT_PHONE_NUMBER_MSG = "{INCORRECT_PHONE_NUMBER_MSG}",
    INCORRECT_TARGET_GEO_MSG = "{INCORRECT_TARGET_GEO_MSG}";

var App = {
    landing: {
        is_back_action: "{LANDING_IS_BACK_ACTION}",
        is_back_call: "{LANDING_IS_BACK_CALL}",
        is_vibrate_on_mobile: "{LANDING_IS_VIBRATE_ON_MOBILE}",
    },
    flow: {
        hash: '"{FLOW_HASH}"',
        is_hide_target_geo_list: "{FLOW_IS_HIDE_TARGET_GEO_LIST}",
        back_action_sec: "{FLOW_BACK_ACTION_SEC}",
        back_call_btn_sec: "{FLOW_BACK_CALL_BTN_SEC}",
        back_call_form_sec: "{FLOW_BACK_CALL_FORM_SEC}",
        vibrate_on_mobile_sec: "{FLOW_VIBRATE_ON_MOBILE_SEC}",
    },

    callback_btn: null,
    callback_form: null,
    modal_plugin_initialized: false,
    has_fallback_county: false,
    back_action_run: false,
    back_call_modal_opened: false,

    init() {
        this.initFbEvents();
        this.initCountriesSelect();
        let default_country = this.getDefaultCountyOption();
        this.changePrices(default_country.data('price'), default_country.data('old_price'));
        this.processFormAttrs();
        this.setPhoneFieldsAttrs();
        this.setNameFieldsAttrs();

        this.initBackCall();
        this.initBackAction();

        // Init vibration
        $(document).on('click', initVibration);
        let self = this;

        function initVibration() {
            self.initVibration();
            $(document).off('click', initVibration);
        }
    },

    initFbEvents: function () {
        try {
            FBEvents.init();
            FBEvents.ViewContent();
        } catch (e) {
            console.info(e);
        }
    },

    initCountriesSelect() {
        var selects = $('.js-select'), target_geo_container = $('.target_list_wrap');
        var options = '';

        if (TARGET_GEO_LIST.length > 0) {
            for (var geo in TARGET_GEO_LIST) {

                // If isset fallback target geo
                if (TARGET_GEO_LIST[geo].target_geo_hash === NOT_SELECTED_TARGET_GEO) {
                    this.has_fallback_county = true;
                }

                options += '<option value="' + TARGET_GEO_LIST[geo].target_geo_hash + '"' +
                    ' data-price="' + TARGET_GEO_LIST[geo].price + '"' +
                    ' data-old_price="' + TARGET_GEO_LIST[geo].old_price + '"' +
                    (VISITOR_COUNTRY_ID === TARGET_GEO_LIST[geo].country_id ? ' selected' : '') + '>' +
                    TARGET_GEO_LIST[geo].country_title + '</option>';
            }
        }
        selects.html(options);

        selects.on('change', function () {
            var option = $(this).find('option:selected');
            App.changePrices(option.data('price'), option.data('old_price'))
        });

        // If it is needed to hide target geo list and doesn't isset fallback target geo - hide all target geo's lists
        if ((this.flow.is_hide_target_geo_list || TARGET_GEO_LIST.length === 1) && !this.has_fallback_county) {
            target_geo_container.hide();
        }
    },

    changePrices(price, old_price) {
        $('.x_currency').text(price);
        $('.x_currency_old').text(old_price);

        // forms.find('[name=phone]').val(phone_mask)
        // .first() .focus();

        // Scrolling to top of page
        // window.scrollTo(0, 0);
    },

    processFormAttrs() {
        var forms = document.querySelectorAll("form.form_order");

        if (forms.length < 1) {
            //console.error('Order forms are undefined');
            return false;
        }

        var flow_hash_input = '<input type="hidden" name="flow_hash" value="' + this.flow.hash + '">';

        for (var i = 0; i < forms.length; i++) {

            forms[i].setAttribute("action", ORDER_PAGE_URL);
            forms[i].setAttribute("onsubmit", "return validateForm(this, event);");
            forms[i].insertAdjacentHTML("afterbegin", flow_hash_input);
            forms[i].className += ' -visor-no-click';
        }
    },

    setPhoneFieldsAttrs() {
        var phone_inputs = document.querySelectorAll("input[name='phone']");

        if (phone_inputs.length < 1) {
            // console.error('Phone inputs are undefined');
            return false;
        }

        for (var i = 0; i < phone_inputs.length; i++) {
            phone_inputs[i].setAttribute("required", "true");
            phone_inputs[i].setAttribute("pattern", "[\+0-9]{6,}");
            phone_inputs[i].setAttribute("oninput", "createTempLead(this)");
            phone_inputs[i].className += ' -metrika-nokeys';
        }
    },

    setNameFieldsAttrs() {
        var name_inputs = document.querySelectorAll("input[name='client']");

        if (name_inputs.length < 1) {
            // console.error('Name inputs are undefined');
            return false;
        }

        for (var i = 0; i < name_inputs.length; i++) {

            //name_inputs[i].setAttribute("required", "true");
            name_inputs[i].removeAttribute("required");
            name_inputs[i].className += ' -metrika-nokeys';
        }
    },

    getDefaultCountyOption() {
        return $('.js-select').first().find('option:selected');
    },

    initBackCall() {
        let custom_form = $('#custom-affcb-form');
        this.callback_form = custom_form.length ? custom_form : $('#affcb-form');
        this.callback_btn = $('#affcb');

        if (!this.landing.is_back_call) {
            return;
        }

        let self = this;

        if (this.flow.back_call_form_sec !== false) {
            // Show back call form
            setTimeout(function () {
                if (!self.back_call_modal_opened) {
                    self.openBackCallModal('backcall');
                }
            }, this.flow.back_call_form_sec * 1000);
        }

        if (this.flow.back_call_btn_sec !== false) {
            // Show back call btn
            setTimeout(function () {
                self.callback_btn.show();
            }, this.flow.back_call_btn_sec * 1000);

            // Open modal on click
            this.callback_btn.on('click', function (e) {
                e.preventDefault();
                
                self.openBackCallModal('backcall');
            });
        }
    },

    initBackAction() {
        if (this.landing.is_back_action === false || this.flow.back_action_sec === false) {
            return;
        }

        let self = this;

        function openBackActionModal() {
            if (!self.back_call_modal_opened) {
                self.openBackCallModal('backaction');
            }
            $(document).off('mouseleave', openBackActionModal);
        }

        setTimeout(function () {
            $(document).mouseleave(openBackActionModal);
        }, this.flow.back_action_sec * 1000);
    },
    
    openBackCallModal(event_name) {
        this.back_call_modal_opened = true;
        $.featherlight(this.callback_form, {
            afterOpen: function () {
                var count = 1;
                $('.featherlight-content').find('input, select').each(function () {
                    $(this).attr('tabindex', count++);
                });
                $('.featherlight-content ')
                    .find('form')
                    // We do not have copies because modal destroyes every time when opens
                    .append('<input type="hidden" name=' + event_name + ' value="1">');
            },
        });
    },

    initVibration() {
        if (this.landing.is_vibrate_on_mobile === false || this.flow.vibrate_on_mobile_sec === false) {
            return;
        }

        setInterval(function () {
            try {
                if (window.navigator && window.navigator.vibrate) {
                    window.navigator.vibrate([[100, 30, 100, 30, 100, 200, 200, 30, 200, 30, 200, 200, 100, 30, 100, 30, 100]]);
                } else {
                    window.navigator.vibrate(0);
                }
            } catch (err) {
            }
        }, this.flow.vibrate_on_mobile_sec * 1000);
    },
};

function validateForm(_this, event) {
    event.preventDefault();

    var target = _this.querySelectorAll("select[name='target_geo_hash'] option:checked")[0];
    var phone = _this.querySelectorAll("input[name='phone']")[0];
    var client = _this.querySelectorAll("input[name='client']")[0];

    if (phone.value.length < 5 || typeof phone.value !== 'string') {
        alert(INCORRECT_PHONE_NUMBER_MSG);
        return false;
    }

    if (target.value === NOT_SELECTED_TARGET_GEO) {
        alert(INCORRECT_TARGET_GEO_MSG);
        return false;
    }

    disableSubmitBtns();

    try {
        FBEvents.Lead();
    } catch (e) {
        console.info(e);
    }

    setTimeout(function () {
        _this.submit();
    }, 1000);
}

function disableSubmitBtns() {
    $('.submit_btn, [type=submit]').attr('disabled', 'disabled').css('opacity', 0.5).css('pointer-events', 'none');
}

function createTempLead(phone_el) {

    if (phone_el.value.length < 7) {
        return;
    }

    var form = phone_el.closest('form'),
        formdata = new FormData(form),
        target_geo_hash = formdata.get('target_geo_hash');

    if (target_geo_hash === '' || target_geo_hash === undefined || target_geo_hash === NOT_SELECTED_TARGET_GEO) {
        return;
    }

    $.ajax({
        type: "POST",
        url: location.origin + '/temp_lead/create' + location.search,
        data: $(form).serialize(),
    });
}