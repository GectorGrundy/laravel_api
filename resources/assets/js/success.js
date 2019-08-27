try {
    FBEvents.init();
    if (location.href.indexOf('first=true') !== -1) {
        FBEvents.Purchase(lead_payout, lead_currency_code);
        history.pushState({}, '', location.href.replace('first=true', 'first=false'))
    }

} catch (e) {
    console.info(e);
}

function updateOrderEmail() {

    var xhr = new XMLHttpRequest();
    var form = document.getElementById('email_form');
    var lead_hash = form.querySelectorAll("input[name='lead_hash']")[0].value;

    var email;
    try {
        email = form.querySelectorAll("input[name='email']")[0].value || '';
    } catch (error) {
        email = '';
    }

    var qs;
    if (location.search === '') {
        qs = '?';
    } else {
        qs = location.search + '&'
    }

    qs += 'lead_hash=' + lead_hash + '&email=' + email;

    xhr.open('POST', location.origin + '/updateOrderEmail' + qs, false);
    xhr.send();

    var response = JSON.parse(xhr.response);
    if (response.status === 'error') {
        alert(response.message);
    } else {
        var list_el = document.createElement('ul');
        list_el.className = 'order_info_item_wrap';
        list_el.innerHTML = '<li class="order_info_item">' +
            '<span class="order_info_item_value">' + TRANSLATIONS.email + ':</span>' + email + '</li>';
        form.parentNode.replaceChild(list_el, form);
    }
}

function updateOrderAddress() {

    var xhr = new XMLHttpRequest();
    var form = document.getElementById('address_form');
    var lead_hash = form.querySelectorAll("input[name='lead_hash']")[0].value;

    var address;
    try {
        address = form.querySelectorAll("input[name='address']")[0].value;
    } catch (error) {
        address = '';
    }

    var qs;
    if (location.search === '') {
        qs = '?';
    } else {
        qs = location.search + '&'
    }
    qs += 'lead_hash=' + lead_hash + '&address=' + address;

    xhr.open('POST', location.origin + '/updateOrderAddress' + qs, false);
    xhr.send();

    var response = JSON.parse(xhr.response);
    if (response.status === 'error') {
        alert(response.message);
    } else {
        var list_el = document.createElement('ul');
        list_el.className = 'order_info_item_wrap';
        list_el.innerHTML = '<li class="order_info_item">' +
            '<span class="order_info_item_value">' + TRANSLATIONS.address + ':</span>' + address + '</li>';
        form.parentNode.replaceChild(list_el, form);
    }
}